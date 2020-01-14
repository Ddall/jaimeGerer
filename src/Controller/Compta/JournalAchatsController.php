<?php

namespace App\Controller\Compta;

use App\Entity\Compta\Avoir;
use App\Entity\Compta\Depense;
use App\Entity\Compta\JournalAchat;
use App\Util\DependancyInjectionTrait\NumServiceTrait;
use PHPExcel;
use PHPExcel_Shared_Date;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class JournalAchatsController extends AbstractController
{

    use NumServiceTrait;

	/**
	 * @Route("/compta/journal-achats",
	 *   name="compta_journal_achats_index"
	 * )
	 */
	public function indexAction()
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
		$formBuilder->add('years', \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, array(
			'required' => true,
			'label' => 'Année',
			'choices' => $arr_years,
			'attr' => array('class' => 'year-select'),
			'data' => $currentYear
		));

		$form = $formBuilder->getForm();

		return $this->render('compta/journal_achats/compta_journal_achats_index.html.twig', array(
			'form' => $form->createView()
		));
	}

	/**
	 * @Route("/compta/journal-achats/voir/{year}",
	 *   name="compta_journal_achats_voir_annee",
	 *   options={"expose"=true}
	 * )
	 */
	public function voirAction($year)
	{
		$repo = $this->getDoctrine()->getManager()->getRepository('App:Compta\JournalAchat');
		$arr_journalAchat = $repo->findJournalEntier($this->getUser()->getCompany(), $year);

		$arr_totaux = array(
	 		'debit' => 0,
	 		'credit' => 0
		);

		foreach($arr_journalAchat as $ligne){
		 	$arr_totaux['debit']+=$ligne->getDebit();
		 	$arr_totaux['credit']+=$ligne->getCredit();
		}

		return $this->render('compta/journal_achats/compta_journal_achats_voir.html.twig', array(
			'arr_journalAchat' => $arr_journalAchat,
			'arr_totaux' => $arr_totaux
		));
	}

	/**
	 * @Route("/compta/journal-achats/ajouter/depense/{numEcriture}/{id}", name="compta_journal_achats_ajouter_depense")
	 */
	public function journalAchatsAjouterDepenseAction($numEcriture = null, Depense $depense){

		$em = $this->getDoctrine()->getManager();

		$totaux = $depense->getTotaux();

		$ccRepository = $em->getRepository('App:Compta\CompteComptable');
		$compteAttente = $ccRepository->findOneBy(array(
			'num' => '471',
			'company' => $this->getUser()->getCompany()
		));

		$newNumEcriture = false;
		if(!$numEcriture){
			$numEcriture = $this->numService->getNumEcriture($this->getUser()->getCompany());
			$newNumEcriture = true;
		}

		//credit au compte 401-nom du fournisseur (ou 421NDFxxx si c'est une note de frais) du total TTC
		$ligne = new JournalAchat();
		$ligne->setDepense($depense);
		$ligne->setCodeJournal('AC');
		$ligne->setDebit(null);
		$ligne->setCredit($totaux['TTC']);
		if($depense->getNoteFrais()){
			$cc = $depense->getNoteFrais()->getCompteComptable();		
		} else {
			$cc = $depense->getCompte()->getCompteComptableFournisseur();
		}
		$ligne->setCompteComptable($cc);
		$ligne->setModePaiement($depense->getModePaiement());
		$ligne->setAnalytique($depense->getAnalytique());
		$ligne->setNumEcriture($numEcriture);
		$em->persist($ligne);
		$depense->addJournalAchat($ligne);

		//debit au compte 6xxxxx pour chaque ligne de la dépense
		foreach($depense->getLignes() as $ligneDepense){
			$ligne = new JournalAchat();
			$ligne->setDepense($depense);
			$ligne->setCodeJournal('AC');
			$ligne->setDebit($ligneDepense->getMontant());
			$ligne->setCredit(null);
			$ligne->setCompteComptable($ligneDepense->getCompteComptable());
			$ligne->setModePaiement($depense->getModePaiement());
			$ligne->setAnalytique($depense->getAnalytique());
			$ligne->setNumEcriture($numEcriture);
			$em->persist($ligne);
			$depense->addJournalAchat($ligne);

			//si TVA : debit au compte 445xxxxx (ou compte d'attente 471 si le compte n'est pas associé à un compte de TVA)
			if($ligneDepense->getTaxe() != null && $ligneDepense->getTaxe() != 0){

				$compteTVA = $ligneDepense->getCompteComptable()->getCompteTVA();
				if($compteTVA == null){
					$compteTVA = $ccRepository->findOneBy(array(
							'num' => '44566000',
							'company' => $this->getUser()->getCompany()
					));
				}

				$ligne = new JournalAchat();
				$ligne->setDepense($depense);
				$ligne->setCodeJournal('AC');
				$ligne->setDebit($ligneDepense->getTaxe());
				$ligne->setCredit(null);
				if($compteTVA != null){
					$cc = $compteTVA;
				} else {
					$cc  = $compteAttente;
				}
				$ligne->setCompteComptable($cc);
	
				$ligne->setModePaiement($depense->getModePaiement());
				$ligne->setAnalytique($depense->getAnalytique());
				$ligne->setNumEcriture($numEcriture);;
				$em->persist($ligne);
				$depense->addJournalAchat($ligne);
			}

		}

		//si TVA : debit au compte 445xxxxx (ou compte d'attente 471 si le compte n'est pas associé à un compte de TVA)
		$compteTVA = $depense->getLignes()[0]->getCompteComptable()->getCompteTVA();
		if($compteTVA == null){
			$compteTVA = $ccRepository->findOneBy(array(
					'num' => '44566000',
					'company' => $this->getUser()->getCompany()
			));
		}
		if($depense->getTaxe() != 0){
			$ligne = new JournalAchat();
			$ligne->setDepense($depense);
			$ligne->setCodeJournal('AC');
			$ligne->setDebit($depense->getTaxe());
			$ligne->setCredit(null);
			if($compteTVA != null){
				$cc = $compteTVA;
			} else {
				$cc = $compteAttente;
			}
			$ligne->setCompteComptable($cc);
			$ligne->setModePaiement($depense->getModePaiement());
			$ligne->setAnalytique($depense->getAnalytique());
			$ligne->setNumEcriture($numEcriture);
			$em->persist($ligne);
			$depense->addJournalAchat($ligne);
		}

		$em->persist($depense);
		$em->flush();

		if($newNumEcriture){
			$numEcriture++;
			$this->numService->updateNumEcriture($this->getUser()->getCompany(), $numEcriture);
		}

		$response = new Response();
		$response->setStatusCode(200);
		return $response;

	}

	/**
	 * @Route("/compta/journal-achats/ajouter/avoir/{numEcriture}/{id}", name="compta_journal_achats_ajouter_avoir")
	 */
	public function journalAchatsAjouterAvoirAction($numEcriture = null, Avoir $avoir){
		//AVOIR FOURNISSEUR

		$em = $this->getDoctrine()->getManager();

		$totaux = $avoir->getTotaux();

		$newNumEcriture = false;
		if(!$numEcriture){
			$numEcriture = $this->numService->getNumEcriture($this->getUser()->getCompany());
			$newNumEcriture = true;
		}

		$ccRepository = $em->getRepository('App:Compta\CompteComptable');
		$compteAttente = $ccRepository->findOneBy(array(
				'num' => '471',
				'company' => $this->getUser()->getCompany()
		));
		$compteTVA = $avoir->getDepense()->getLignes()[0]->getCompteComptable()->getCompteTVA();
		if($compteTVA == null){
			$compteTVA = $ccRepository->findOneBy(array(
					'num' => '44566000',
					'company' => $this->getUser()->getCompany()
			));
		}

		//debit au compte 401-nom du fournisseur
		$ligne = new JournalAchat();
		$ligne->setAvoir($avoir);
		$ligne->setCodeJournal('AC');
		$ligne->setDebit($totaux['TTC']);
		$ligne->setCredit(null);
		$ligne->setCompteComptable($avoir->getDepense()->getCompte()->getCompteComptableFournisseur());
		$ligne->setModePaiement($avoir->getModePaiement());
		$ligne->setAnalytique($avoir->getDepense()->getAnalytique());
		$ligne->setNumEcriture($numEcriture);
		$em->persist($ligne);

		foreach($avoir->getLignes() as $ligneAvoir){

			//credit au compte 60xxxx du montant HT
			$ligne = new JournalAchat();
			$ligne->setAvoir($avoir);
			$ligne->setCodeJournal('AC');
			$ligne->setDebit(null);
			$ligne->setCredit($ligneAvoir->getMontant());
			$ligne->setCompteComptable($ligneAvoir->getCompteComptable());
			$ligne->setModePaiement($avoir->getModePaiement());
			$ligne->setAnalytique($avoir->getDepense()->getAnalytique());
			$ligne->setNumEcriture($numEcriture);
			$em->persist($ligne);


			if($ligneAvoir->getTaxe() != null && $ligneAvoir->getTaxe() != 0){
				$ligne = new JournalAchat();
				$ligne->setAvoir($avoir);
				$ligne->setCodeJournal('AC');
				$ligne->setDebit(null);
				$ligne->setCredit($ligneAvoir->getTaxe());
				$cc = $compteTVA;
				if($compteTVA == null) {
					$cc = $compteAttente;
				}
				$ligne->setCompteComptable($cc);
				$ligne->setModePaiement($avoir->getModePaiement());
				$ligne->setAnalytique($avoir->getDepense()->getAnalytique());
				$ligne->setNumEcriture($numEcriture);
				$em->persist($ligne);
			}
		}

		$em->flush();

		if($newNumEcriture){
			$numEcriture++;
			$this->numService->updateNumEcriture($this->getUser()->getCompany(), $numEcriture);
		}
		
		$response = new Response();
		$response->setStatusCode(200);
		return $response;

	}

	/**
	 * @Route("/compta/journal-achats/exporter/{year}",
	 *   name="compta_journal_achats_exporter",
	 *   options={"expose"=true}
	 * )
	 */
	public function journalAchatsExporterAction($year){
		$repo = $this->getDoctrine()->getManager()->getRepository('App:Compta\JournalAchat');
	  	$arr_journalAchat = $repo->findJournalEntier($this->getUser()->getCompany(), $year);

		 $arr_totaux = array(
		 	'debit' => 0,
		 	'credit' => 0
		 );

		$objPHPExcel = new PHPExcel();
		$objPHPExcel->getActiveSheet()->setTitle('Journal Achats '.$year);

		// header row
		$arr_header = array(
			'Code journal',
			'Date',
			'Compte',
			'Compte auxiliaire',
			'Libellé',
			'Débit',
			'Crédit',
			'Analytique',
			'Commentaire'
		);
		$row = 1;
		$col = 'A';
		foreach($arr_header as $header){
				$objPHPExcel->getActiveSheet ()->setCellValue ($col.$row, $header);
				$col++;
		}

	  foreach($arr_journalAchat as $ligne){
			$col = 'A';
			$row++;

			$objPHPExcel->getActiveSheet ()->setCellValue ($col.$row, $ligne->getCodeJournal());
			$col++;
			$objPHPExcel->getActiveSheet ()->setCellValue ($col.$row, (string)PHPExcel_Shared_Date::PHPToExcel( $ligne->getDate()) );
			$objPHPExcel->getActiveSheet()->getStyle($col.$row)->getNumberFormat()->setFormatCode('dd/mm/yyyy');
			$col++;
			$objPHPExcel->getActiveSheet ()->setCellValue ($col.$row, substr($ligne->getCompteComptable()->getNum(),0,3));
			$col++;
			$objPHPExcel->getActiveSheet ()->setCellValue ($col.$row, $ligne->getCompteComptable()->getNum());
			$col++;
			$objPHPExcel->getActiveSheet ()->setCellValue ($col.$row, $ligne->getLibelleWithoutNum());
			$col++;
			$objPHPExcel->getActiveSheet ()->setCellValue ($col.$row, $ligne->getDebit());
			$col++;
			$objPHPExcel->getActiveSheet ()->setCellValue ($col.$row, $ligne->getCredit());
			$col++;
			$settingsAnalytique = $ligne->getAnalytique();
			if(!$settingsAnalytique){
				$analytique = "";
			} else {
				$analytique = $settingsAnalytique->getValeur();
			}
			$objPHPExcel->getActiveSheet ()->setCellValue ($col.$row, $analytique);
			$col++;
			$objPHPExcel->getActiveSheet ()->setCellValue ($col.$row, $ligne->getCommentaire());
	  }

		//set column width
		foreach(range('A','H') as $col) {
    		$objPHPExcel->getActiveSheet()->getColumnDimension($col)->setAutoSize(true);
		}

		$response = new Response();

		$response->headers->set('Content-Type', 'application/vnd.ms-excel');
		$response->headers->set('Content-Disposition', 'attachment;filename="journal_achats.xlsx"');
		$response->headers->set('Cache-Control', 'max-age=0');
		$response->sendHeaders();
		$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		$objWriter->save('php://output');
		exit();

	}


		// /**
		//  * @Route("/compta/journal-achats/reinitialiser", name="compta_journal_achats_reinitialiser")
		//  */
		// public function journalAchatsReinitialiser(){

		// 	$em = $this->getDoctrine()->getManager();
		// 	$journalAchatsRepo = $em->getRepository('App:Compta\JournalAchat');
		// 	$depenseRepo = $em->getRepository('App:Compta\Depense');
		// 	$avoirRepo = $em->getRepository('App:Compta\Avoir');

		// 	$journalAchatsService = $$this->get('appbundle.compta_journal_achats_controller');
		// 	$compteComptableService = $this->get('appbundle.compta_compte_comptable_controller');

		// 	$arr_journal = $journalAchatsRepo->findJournalEntier($this->getUser()->getCompany());
		// 	foreach($arr_journal as $ligne){
		// 		$em->remove($ligne);
		// 	}
		// 	$em->flush();

		// 	$arr_depenses = $depenseRepo->findForCompany($this->getUser()->getCompany());
		// 	foreach($arr_depenses as $depense){
		// 		$compte = $depense->getCompte();
		// 		if($compte->getCompteComptableFournisseur() == null || $compte->getFournisseur() == false){
		// 				$compteComptable = $compteComptableService->createCompteComptableForCompte('401', $compte->getNom());
		// 				$em->persist($compteComptable);

		// 				$compte->setFournisseur(true);
		// 				$compte->setCompteComptableFournisseur($compteComptable);
		// 				$em->persist($compte);
		// 		}

		// 		//ecrire dans le journal des achats
		// 		$journalAchatsService->journalAchatsAjouterDepenseAction($depense);
		// 	}

		// 	$arr_avoirs = $avoirRepo->findForCompany('FOURNISSEUR', $this->getUser()->getCompany());
		// 	foreach($arr_avoirs as $avoir){
		// 		$compte = $avoir->getDepense()->getCompte();

		// 		//ecrire dans le journal des ventes
		// 		$journalAchatsService->journalAchatsAjouterAvoirAction($avoir);

		// 	}

		// 	$em->flush();
		// 	return new Response();

		// }


}
