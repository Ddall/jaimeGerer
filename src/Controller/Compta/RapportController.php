<?php

namespace App\Controller\Compta;

use App\Service\Compta\FECService;
use App\Service\LegacyExcelFactory as Factory;
use App\Service\UtilsService;
use App\Util\DependancyInjectionTrait\BalanceGeneraleServiceTrait;
use App\Util\DependancyInjectionTrait\GrandLivreServiceTrait;
use App\Util\DependancyInjectionTrait\KnpSnappyPDFTrait;
use App\Util\DependancyInjectionTrait\TableauBordServiceTrait;
use App\Util\DependancyInjectionTrait\TableauTresorerieServiceTrait;
use App\Util\DependancyInjectionTrait\TableauTVAServiceTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;

class RapportController extends AbstractController
{

    use TableauTresorerieServiceTrait;
    use TableauBordServiceTrait;
    use BalanceGeneraleServiceTrait;
    use TableauTVAServiceTrait;
    use GrandLivreServiceTrait;
    use KnpSnappyPDFTrait;

	/**
	 * @Route("/compta/rapport/tva",
	 *   name="compta_rapport_tva_index"
	 * )
	 */
	public function rapportTVAIndexAction()
	{
		$settingsRepo = $this->getDoctrine()->getManager()->getRepository('App:Settings');

		$settingsEntree = $settingsRepo->findOneBy(array(
				'company' => $this->getUser()->getCompany(),
				'module' => 'COMPTA',
				'parametre' => 'TVA_ENTREE'
		));
		$settingsSortie = $settingsRepo->findOneBy(array(
				'company' => $this->getUser()->getCompany(),
				'module' => 'COMPTA',
				'parametre' => 'TVA_SORTIE'
		));

		$activationRepo = $this->getDoctrine()->getManager()->getRepository('App:SettingsActivationOutil');
		$activation = $activationRepo->findOneBy(array(
			'company' => $this->getUser()->getCompany(),
			'outil' => 'COMPTA'
		));
		$yearActivation = $activation->getDate()->format('Y');

		$currentYear = date('Y');
		$arr_years = array();
		for($i = $yearActivation ; $i<=$currentYear; $i++){
				$arr_years[$i] = $i;
		}

		$formBuilder = $this->createFormBuilder();
		$formBuilder->add('years', \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, array(
				'required' => true,
				'label' => 'Année',
				'choices' => $arr_years,
				'attr' => array('class' => 'year-select'),
				'data' => $currentYear
		));

		$form = $formBuilder->getForm();

		return $this->render('compta/rapport/compta_rapport_tva_index.html.twig', array(
				'settingsEntree' => $settingsEntree,
				'settingsSortie' => $settingsSortie,
				'form' => $form->createView()
		));
	}

	/**
	 * @Route("/compta/rapport/tva/voir/{year}",
	 *   name="compta_rapport_tva_voir",
	 *   options={"expose"=true}
	 * )
	 */
	public function rapportTVAVoirAction($year)
	{
		$arr_tva = $this->tableauTVAService->creerTableauTVA( $this->getUser()->getCompany(), $year );

		return $this->render('compta/rapport/compta_rapport_tva_voir.html.twig', array(
			'arr_tva' => $arr_tva,
		));

	}

	/**
	 * @Route("/compta/rapport/tva/exporter/{year}",
	 *   name="compta_rapport_tva_exporter",
	 *   options={"expose"=true}
	 * )
	 */
	public function rapportTVAExporterAction($year)
	{
		$settingsRepo = $this->getDoctrine()->getManager()->getRepository('App:Settings');

		$settingsEntree = $settingsRepo->findOneBy(array(
				'company' => $this->getUser()->getCompany(),
				'module' => 'COMPTA',
				'parametre' => 'TVA_ENTREE'
		));
		$settingsSortie = $settingsRepo->findOneBy(array(
				'company' => $this->getUser()->getCompany(),
				'module' => 'COMPTA',
				'parametre' => 'TVA_SORTIE'
		));

		$arr_tva = $this->tableauTVAService->creerTableauTVA( $this->getUser()->getCompany(), $year );

		$html = $this->renderView('compta/rapport/compta_rapport_tva_exporter.html.twig', array(
				'settingsEntree' => $settingsEntree,
				'settingsSortie' => $settingsSortie,
				'arr_tva' => $arr_tva
		));

		$filename = 'tva-'.$this->getUser()->getCompany()->getNom().'.pdf';

		return new Response(
				$this->getKnpSnappyPdf()->getOutputFromHtml($html,
						array(
								'margin-bottom' => '10mm',
								'margin-top' => '10mm',
								//'zoom' => 0.8, //prod only, zoom level is not the same on Windows
								'default-header'=>false,
						)
				),
				200,
				array(
						'Content-Type'          => 'application/pdf',
						'Content-Disposition'   => 'attachment; filename='.$filename,
				)
		);
	}



	/**
	 * @Route("/compta/rapport/tableau-tresorerie", name="compta_rapport_tableau_tresorerie_index")
	 */
	public function rapportTableauTresorerieIndexAction()
	{
		/*creation du dropdown pour choisir le compte bancaire*/
		$repo = $this->getDoctrine()->getManager()->getRepository('App:Compta\CompteBancaire');
		$arr_comptesBancaires = $repo->findByCompany($this->getUser()->getCompany());

		$arr_choices = array();
		foreach($arr_comptesBancaires as $compteBancaire){
			$arr_choices[$compteBancaire->getId()] = $compteBancaire;
		}
		$arr_choices['all'] = 'Tous';

		$activationRepo = $this->getDoctrine()->getManager()->getRepository('App:SettingsActivationOutil');
		$activation = $activationRepo->findOneBy(array(
			'company' => $this->getUser()->getCompany(),
			'outil' => 'COMPTA'
		));
		$yearActivation = $activation->getDate()->format('Y');

		$currentYear = date('Y');
		$arr_years = array();
		for($i = $yearActivation ; $i<=$currentYear; $i++){
			$arr_years[$i] = $i;
		}

		$formBuilder = $this->createFormBuilder();

		$formBuilder
			->add('comptes', \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, array(
				'required' => true,
				'label' => 'Compte bancaire',
				'choices' => $arr_choices,
				'attr' => array('class' => 'compte-select')
			))
			->add('years', \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, array(
				'required' => true,
				'label' => 'Année',
				'choices' => $arr_years,
				'attr' => array('class' => 'year-select'),
				'data' => $currentYear
			));

		return $this->render('compta/rapport/compta_rapport_tableau_tresorerie_index.html.twig', array(
				'form' => $formBuilder->getForm()->createView(),

		));
	}

	/**
	 * @Route("/compta/rapport/tableau-tresorerie/voir/{id}/{year}",
	 *   name="compta_rapport_tableau_tresorerie_voir",
	 *   options={"expose"=true}
	 * )
	 */
	public function rapportTableauTresorerieVoirAction($id, $year){

		try{
			$tableauPrevisionnel = $this->tableauTresorerieService->creerTableauTresorerie($id, $this->getUser()->getCompany(), $year);
		} catch(\Exception $e){
			$response = new Response();
			$response->setStatusCode(204);
			return $response;
		}

		return $this->render('compta/rapport/compta_rapport_tableau_tresorerie_voir.html.twig', array(
			'compteBancaire' => $tableauPrevisionnel['compteBancaire'],
			'arr_prev' => $tableauPrevisionnel['arr_prev'],
			'arr_accurate' => $tableauPrevisionnel['arr_accurate'],
			'year' => $year
		));
	}

	/**
	 * @Route("/compta/rapport/tableau-tresorerie/previsionnel/completer/{mois}/{poste}/{compteBancaire_id}/{year}",
	 *   name="compta_rapport_tableau_tresorerie_previsionnel_completer"
	 * )
	 */
	public function prevTresoCompleterAction(Request $requestData, $mois, $poste, $compteBancaire_id, $year){
		$response = new Response();

		$valeur = $requestData->get('value');

		try{
			$this->tableauTresorerieService->ajouterTresoreriePrevisionnelle($valeur, $mois, $poste, $compteBancaire_id, $year);
		} catch(\Exception $e){
			$response->setStatusCode(204);
			return $response;
		}

		$response->setStatusCode(200);
		return $response;
	}

	/**
	 * @Route("/compta/rapport/tableau-tresorerie/totaux/{mois}/{poste}/{compteBancaire_id}/{year}",
	 *   name="compta_rapport_tableau_tresorerie_totaux",
	 *   options={"expose"=true}
	 * )
	 */
	public function prevTresoTotauxAction($mois, $poste, $compteBancaire_id, $year){

		try{
			$arr = $this->tableauTresorerieService->calculTotaux($mois, $poste, $compteBancaire_id, $year);
		} catch(\Exception $e){
			throw $e;
		}
		return new JsonResponse($arr);

	}

	/**
	 * @Route("/compta/rapport/balance/generale",
	 *   name="compta_rapport_balance_generale_index"
	 * )
	 */
	public function rapportBalanceGeneraleIndexAction()
	{
		$builder = $this->createFormBuilder();
		$builder
			->add('periode', \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, array(
				'choices' => array(
					'ANNEE' => 'Année en cours',
					'TRIMESTRE' => 'Trimestre en cours',
					'MOIS' => 'Mois en cours',
					'ANNEE_PRECEDENTE' => 'Année précédente',
					'ANNEE_COURS_ET_PRECEDENTE' => 'Année précédente et année en cours'
				),
				'label' => 'Période',
				'data' => 'ANNEE',
				'attr' => array('class' => 'periode-select')
			))
			->add('equilibre', \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, array(
				'choices' => array(
					'ALL' => 'Tous les comptes',
					'EQUILIBRE' => 'Comptes équilibrés',
					'DESEQUILIBRE' => 'Comptes déséquilibrés'
				),
				'attr' => array('class' => 'equilibre-radio'),
				'multiple' => false,
				'expanded' => true,
				'label' => 'Afficher les comptes',
				'data' => 'ALL'
			));

		$form = $builder->getForm();

		return $this->render('compta/rapport/compta_rapport_balance_generale_index.html.twig', array(
			'form' => $form->createView()
		));
	}

	/**
	 * @Route("/compta/rapport/balance/generale/voir/{periode}/{equilibre}",
	 *   name="compta_rapport_balance_voir_periode",
	 *   options={"expose"=true}
	 * )
	 */
	public function rapportBalanceGeneraleVoirPeriodeAction($periode, $equilibre)
	{
			try{
				$arr_balance = $this->balanceGeneraleService->creerBalanceGenerale($this->getUser()->getCompany(), $periode, $equilibre);
			} catch(\Exception $e){
				$response = new Response();
				$response->setStatusCode(204);
				return $response;
			}

			return $this->render('compta/rapport/compta_rapport_balance_generale_voir.html.twig', array(
				'arr_cc' => $arr_balance['arr_cc'],
				'totalSoldeDebiteur' => $arr_balance['totalSoldeDebiteur'],
				'totalSoldeCrediteur' => $arr_balance['totalSoldeCrediteur'],
				'soldeDebiteur' => $arr_balance['soldeDebiteur'],
				'soldeCrediteur' => $arr_balance['soldeCrediteur'],
				'periode' => $periode,
				'equilibre' => $equilibre
			));

	}

	/**
	 * @Route("/compta/rapport/balance/generale/exporter",
	 *   name="compta_rapport_balance_generale_exporter",
	 * )
	 */
	public function rapportBalanceGeneraleExporterAction(Request $request){

			$builder = $this->createFormBuilder();
			$builder->add('periode-select', \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, array(
				'choices' => array(
					'ANNEE' => 'Année',
					'TRIMESTRE' => 'Trimestre',
					'MOIS' => 'Mois',
					'ANNEE_PRECEDENTE' => 'Année précédente',
					'ANNEE_COURS_ET_PRECEDENTE' => 'Année précédente et année en cours'
				),
				'label' => 'Période',
				'data' => 'ANNEE',
				'attr' => array('class' => 'periode-select')
			))
			->add('submit', \Symfony\Component\Form\Extension\Core\Type\SubmitType::class, array(
				'label' => 'Exporter',
				'attr' => array('class' => 'btn btn-primary')
			));

			$form = $builder->getForm();
			$form->handleRequest($request);

			if ($form->isSubmitted() && $form->isValid()) {

				$periode = $form->get('periode-select')->getData();
				$arr_balance = $this->balanceGeneraleService->creerBalanceGenerale($this->getUser()->getCompany(), $periode, 'ALL');

				//convert UTF8 strings to  ISO-8859-1 since most people will open this CSV file with Excel which doesn't handle UTF8
				$header[] = utf8_decode ('Compte');
				$header[] = utf8_decode ('Libellé');
				$header[] = utf8_decode ('Débit');
				$header[] = utf8_decode ('Crédit');
				$header[] = utf8_decode ('Solde débiteur');
				$header[] = utf8_decode ('Solde créditeur');

				$respdata = [];
				$respdata[] = $header;

				foreach($arr_balance['arr_cc'] as $cc){
					$ccData = array();
					$ccData[0] =  utf8_decode ($cc->getNum() );
					$ccData[1] =  utf8_decode ($cc->getNom() );
					$ccData[2] =  utf8_decode ($cc->getTotalDebit($periode) );
					$ccData[3] =  utf8_decode ($cc->getTotalCredit($periode) );
					$ccData[4] =  utf8_decode ($cc->getSoldeDebiteur($periode) );
					$ccData[5] =  utf8_decode ($cc->getSoldeCrediteur($periode) );
					$respdata[] = $ccData;
				}
				$totalData[0] = '';
				$totalData[1] = '';
				$totalData[2] = '';
				$totalData[3] = '';
				$totalData[4] = $arr_balance['totalSoldeDebiteur'];
				$totalData[5] = $arr_balance['totalSoldeCrediteur'];
				$respdata[] = $totalData;

				$response = new StreamedResponse();
				$response->setCallback(
					function () use ($respdata) {
						$handle = fopen('php://output', 'r+');
						foreach ($respdata as $row) {
							fputcsv($handle, $row);
						}
						fclose($handle);
					}
				);

				$response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
				$response->headers->set('Content-Encoding', 'UTF-8');
				$response->headers->set('Content-Disposition', 'attachment; filename="balance.csv"');

				return $response;

			}

			return $this->render('compta/rapport/compta_rapport_balance_generale_exporter_modal.html.twig', array(
				'form' => $form->createView()
		));

	}

	/**
	 * @Route("/compta/rapport/tableau-bord",
	 *   name="compta_rapport_tableau_bord_index",
	 * )
	 */
	public function rapportTableauBordIndexAction(){

		$activationRepo = $this->getDoctrine()->getManager()->getRepository('App:SettingsActivationOutil');
		$activation = $activationRepo->findOneBy(array(
			'company' => $this->getUser()->getCompany(),
			'outil' => 'COMPTA'
		));

		$yearActivation = $activation->getDate()->format('Y');

		$currentYear = date('Y');
		$arr_years = array();
		for($i = $yearActivation ; $i<=$currentYear+3; $i++){
			$arr_years[$i] = $i;
		}

		$formBuilder = $this->createFormBuilder();
		$formBuilder->add('years', \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, array(
				'required' => true,
				'label' => 'Année',
				'choices' => $arr_years,
				'attr' => array('class' => 'year-select'),
				'data' => $currentYear
		));

		$moisDebutPredictif = $this->tableauBordService->getMoisDebutPredictif();

		return $this->render('compta/rapport/compta_rapport_tableau_bord_index.html.twig', array(
			'form' => $formBuilder->getForm()->createView(), 
			'moisDebutPredictif' => $moisDebutPredictif
		));
	}

	/**
	 * @Route("/compta/rapport/tableau-bord/voir/{year}",
	 *   name="compta_rapport_tableau_bord_voir",
	 *   options={"expose"=true}
	 * )
	 */
	public function rapportTableauBordVoirAction($year){

		$tableauBord = $this->tableauBordService->creerTableauBord($year, $this->getUser()->getCompany());
		// try {
		// 	$tableauBord = $this->tableauBordService->creerTableauBord($year, $this->getUser()->getCompany());
		// } catch(\Exception $e){
		// 	$response = new Response();
		// 	$response->setStatusCode(204);
		// 	return $response;
		// }

		$moisDebutPredictif = $this->tableauBordService->getMoisDebutPredictif();

		return $this->render('compta/rapport/compta_rapport_tableau_bord_voir.html.twig', array(
			'arr_prev' => $tableauBord['arr_prev'],
			'arr_accurate' => $tableauBord['arr_accurate'],
			'arr_predictif' => $tableauBord['arr_predictif'],
			'arr_totaux' => $tableauBord['arr_totaux'],
			'arr_postes' => $tableauBord['arr_postes'],
			'arr_couts_marginaux' => $tableauBord['arr_couts_marginaux'],
			'arr_couts_exploitation' => $tableauBord['arr_couts_exploitation'],
			'year' => $year,
			'moisDebutPredictif' => $moisDebutPredictif
		));

	}

	/**
	 * @Route("/compta/rapport/tableau-bord/details/{year}/{month}/{poste}/{sous_poste}",
	 *   name="compta_rapport_tableau_bord_details",
	 *   options={"expose"=true}
	 * )
	 */
	public function rapportTableauBordDetailsAction($year, $month, $poste, $sous_poste){

		$arr_details = $this->tableauBordService->displayDetails($year, $month, $this->getUser()->getCompany(), $poste, $sous_poste);
		try {

		} catch(\Exception $e){
			$response = new Response();
			$response->setStatusCode(204);
			return $response;
		}

		return $this->render('compta/rapport/compta_rapport_tableau_bord_details_modal.html.twig', array(
			'arr_details' => $arr_details,
			'month' => $month,
			'year' => $year,
			'poste' => $poste,
			'sous_poste' => $sous_poste
		));

	}

	/**
	 * @Route("/compta/rapport/tableau-bord/importer/previsionnel",
	 *   name="compta_rapport_tableau_bord_importer_previsionnel",
	 *   options={"expose"=true}
	 * )
	 */
	public function rapportTableauBordImportPrevisionnelAction(Request $request, Factory $phpExcel){


		$form = $this->createFormBuilder()
		 ->add('file', FileType::class, array(
			 'label' => 'Importer le fichier rempli',
			))
		 ->add('year', \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, array(
		 	'label' => 'Année',
		 	'choices' => array(
		 		date('Y') => date('Y'), 
		 		date('Y')+1 => date('Y')+1,
		 		date('Y')+2 => date('Y')+2,
		 		date('Y')+3 => date('Y')+3,
		 	)
		 ))
		 ->add('submit', \Symfony\Component\Form\Extension\Core\Type\SubmitType::class, array(
			 'label' => 'Importer',
			 'attr' => array(
				 'class' => 'btn btn-success',
			 )
		 ))
		 ->getForm();

		 $form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$objPHPExcel = $phpExcel->createPHPExcelObject($form['file']->getData());
			$this->tableauBordService->importPrevisionnelExcel($this->getUser()->getCompany(), $objPHPExcel, $form['year']->getData());
			
			return $this->redirect($this->generateUrl('compta_rapport_tableau_bord_index'));
		}

		return $this->render('compta/rapport/compta_rapport_tableau_bord_import_previsionnel.html.twig', array(
			'form' => $form->createView()
		));
	}

	/**
	 * @Route("/compta/rapport/tableau-bord/exporter/import-previsionnel-excel",
	 *   name="compta_rapport_tableau_bord_exporter_import_previsionnel-excel",
	 *   options={"expose"=true}
	 * )
	 */
	public function rapportTableauBordExporterImportPrevisionnelExcelAction(){


			try{
				$objPHPExcel = $this->tableauBordService->exportImportPrevisionnelExcel($this->getUser()->getCompany());
			} catch(\Exception $e){
					throw $e;
			}

		 $response = new Response();
		 $response->headers->set('Content-Type', 'application/vnd.ms-excel');
		 $response->headers->set('Content-Disposition', 'attachment;filename="fichier_import_tableau_bord_previsionnel.xlsx"');
		 $response->headers->set('Cache-Control', 'max-age=0');
		 $response->sendHeaders();
		 $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		 $objWriter->save('php://output');

		 exit();

	}

	/**
	 * @Route("/compta/rapport/tableau-bord/previsionnel/completer/{annee}/{mois}/{poste}/{analytique}/{priveOrPublic}",
	 *   name="compta_rapport_tableau_bord_previsionnel_completer"
	 * )
	 */
	public function prevTableauBordCompleterAction(Request $requestData, $annee, $mois, $poste, $analytique, $priveOrPublic){
		$response = new Response();

		$valeur = $requestData->get('value');

		try{
			$this->tableauBordService->ajouterPrevisionnel($valeur, $annee, $mois, $poste, $analytique, $priveOrPublic, $this->getUser()->getCompany());
		} catch(\Exception $e){
			$response->setStatusCode(204);
			return $response;
		}

		$response->setStatusCode(200);
		return $response;
	}

	/**
	 * @Route("/compta/rapport/tableau-bord/totaux/{annee}",
	 *   name="compta_rapport_tableau_bord_totaux",
	 *   options={"expose"=true}
	 * )
	 */
	public function prevTableauBordTotauxAction($annee){

		try{
			$arr = $this->tableauBordService->calculTotaux($annee, $this->getUser()->getCompany());
		} catch(\Exception $e){
			throw $e;
		}
		return new JsonResponse($arr);

	}

	/**
	 * @Route("/compta/rapport/grand-livre",
	 *   name="compta_rapport_grand_livre_index"
	 * )
	 */
	public function rapportGrandLivreIndexAction(UtilsService $utilsService)
	{
			$today = new \DateTime();
			$firstDayOfYear = $utilsService->getFirstDayOfCurrentYear();

			$builder = $this->createFormBuilder();
			$builder
				->add('start', DateType::class, array(
				   'widget' => 'single_text',
					 'input' => 'datetime',
					 'format' => 'dd/MM/yyyy',
					 'attr' => array('class' => 'dateInput start-date'),
					 'required' => true,
					 'label' => 'Du',
					 'data' => $firstDayOfYear
				))
				->add('end', DateType::class, array(
				   'widget' => 'single_text',
					 'input' => 'datetime',
					 'format' => 'dd/MM/yyyy',
					 'attr' => array('class' => 'dateInput end-date'),
					 'required' => true,
					 'label' => 'au',
					 'data' => $today
				))
				->add('submit', \Symfony\Component\Form\Extension\Core\Type\SubmitType::class, array(
					'label' => 'Générer le grand livre',
					'attr' => array('class' => 'btn btn-primary')
				));

			$form = $builder->getForm();

			return $this->render('compta/rapport/compta_rapport_grand_livre_index.html.twig', array(
				'form' => $form->createView()
			));
	}


	/**
	 * @Route("/compta/rapport/grand-livre/voir/{startDate}/{endDate}",
	 *   name="compta_rapport_grand_livre_voir_periode",
	 *   options={"expose"=true}
	 * )
	 */
	public function rapportGrandLivreVoirPeriodeAction($startDate, $endDate)
	{
			$arr_grand_livre = array();

			$arr_grand_livre = $this->grandLivreService->creerGrandLivre($this->getUser()->getCompany(), $startDate, $endDate);

			return $this->render('compta/rapport/compta_rapport_grand_livre_voir.html.twig', array(
				'arr_grand_livre' => $arr_grand_livre,
			));

	}

	/**
	 * @Route("/compta/rapport/grand-livre/exporter/{startDate}/{endDate}",
	 *   name="compta_rapport_grand_livre_exporter_periode",
	 *   options={"expose"=true}
	 * )
	 */
	public function rapportGrandLivreExporterPeriodeAction($startDate, $endDate)
	{
			$arr_grand_livre = array();

			$objPHPExcel = $this->grandLivreService->exportGrandLivre($this->getUser()->getCompany(), $startDate, $endDate);

			 $response = new Response();
			 $response->headers->set('Content-Type', 'application/vnd.ms-excel');
			 $response->headers->set('Content-Disposition', 'attachment;filename="grand_livre.xlsx"');
			 $response->headers->set('Cache-Control', 'max-age=0');
			 $response->sendHeaders();
			 $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
			 $objWriter->save('php://output');
			 exit();

	}


	/**
	 * @Route("/compta/erreurs-factures",
	 *   name="compta_erreurs_factures",
	 *   
	 * )
	 */
	public function rapportErreursFacturesAction()
	{
		$facturesRepo = $this->getDoctrine()->getManager()->getRepository('App:CRM\DocumentPrix');

		$arr_factures = $facturesRepo->findForCompany(
			$this->getUser()->getCompany(),
			'FACTURE',
			true
		);

		foreach($arr_factures as $facture){
			$analytique = $facture->getAnalytique();
			$ok = true;
			if($facture->getAnalytique() == null){
				echo $facture->getNum().' : NO ANALYTIQUE - '.$facture->getTaxe();
				echo '<br />';
			} else if($facture->getAnalytique()->getValeur() != 'FC' && $facture->getTaxe() == 0 && $facture->getPays() == "France"){
				echo $facture->getNum().' : '.$facture->getAnalytique().' - '.$facture->getTaxe();
				echo '<br />';
			}
	
		}

		return new Response();

	}

		/**
	 * @Route("/compta/rapport/fec",
	 *   name="compta_rapport_fec_index"
	 * )
	 */
	public function rapportFECIndexAction(Request $request, FECService $fecService)
	{
		$activationRepo = $this->getDoctrine()->getManager()->getRepository('App:SettingsActivationOutil');

		$activation = $activationRepo->findOneBy(array(
			'company' => $this->getUser()->getCompany(),
			'outil' => 'COMPTA'
		));
		$yearActivation = $activation->getDate()->format('Y');

		$currentYear = date('Y');
		$arr_years = array();
		for($i = $yearActivation ; $i<=$currentYear; $i++){
			$arr_years[$i] = $i;
		}

		$formBuilder = $this->createFormBuilder();
		$formBuilder
			->add('year', \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, array(
				'required' => true,
				'label' => 'Année',
				'choices' => $arr_years,
				'attr' => array('class' => 'year-select'),
				'data' => $currentYear-1
			))
			->add('downloadFEC', \Symfony\Component\Form\Extension\Core\Type\SubmitType::class, array(
				'label' => 'Exporter le FEC',
				'attr' => array('class' => 'btn btn-primary')
			))
			->add('downloadDesc', \Symfony\Component\Form\Extension\Core\Type\SubmitType::class, array(
				'label' => 'Exporter le fichier descriptif',
				'attr' => array('class' => 'btn btn-primary')
			));

		$form = $formBuilder->getForm();
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {

			$year = $form->get('year')->getData();

			if($form->get('downloadFEC')->isClicked()){
				try{
					$filename = $fecService->createFECFile($this->getUser()->getCompany(), $year);
				} catch(\Exception $e){
					throw $e;
				}
				
			} else {
				try{
					$filename = $fecService->createFECDescFile($this->getUser()->getCompany(), $year);
				} catch(\Exception $e){
					throw $e;
				}
			}

			$response = new BinaryFileResponse($filename);
			$response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT);

			return $response;
		}

		return $this->render('compta/rapport/compta_rapport_fec_index.html.twig', array(
			'form' => $form->createView()
		));
	}

}
