<?php

namespace App\Controller\Compta;

use App\Entity\Compta\Depense;
use App\Entity\Compta\DepenseSousTraitance;
use App\Entity\Compta\LigneDepense;
use App\Entity\CRM\Compte;
use App\Entity\Settings;
use App\Form\Compta\DepenseType;
use App\Form\Compta\UploadHistoriqueDepenseMappingType;
use App\Form\Compta\UploadHistoriqueDepenseType;
use App\Util\DependancyInjectionTrait\CompteComptableServiceTrait;
use App\Util\DependancyInjectionTrait\JournalAchatsTrait;
use App\Util\DependancyInjectionTrait\JournalVentesTrait;
use App\Util\DependancyInjectionTrait\NumServiceTrait;
use App\Util\DependancyInjectionTrait\OpportuniteServiceTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class DepenseController extends AbstractController
{

    use NumServiceTrait;
    use OpportuniteServiceTrait;
    use JournalAchatsTrait;
    use JournalVentesTrait;
    use CompteComptableServiceTrait;

	/**
	 * @Route("/compta/depense/liste", name="compta_depense_liste")
	 */
	public function depenseListeAction()
	{
		$lastNumEcriture = $this->numService->getNumEcriture($this->getUser()->getCompany());

		return $this->render('compta/depense/compta_depense_liste.html.twig', array(
			'lastNumEcriture' => $lastNumEcriture-1,
		));
	}

	/**
	 * @Route("/compta/depense/liste/ajax",
	 *   name="compta_depense_liste_ajax",
	 *   options={"expose"=true}
	 * )
	 */
	public function depenseListeAjaxAction(Request $requestData)
	{
		$arr_sort = $requestData->get('order');
		$arr_cols = $requestData->get('columns');

		$col = $arr_sort[0]['column'];

		$repository = $this->getDoctrine()->getManager()->getRepository('App:Compta\Depense');

		$arr_search = $requestData->get('search');
		$arr_date = $requestData->get('dateRange');

		$list = $repository->findForList(
				$this->getUser()->getCompany(),
				$requestData->get('length'),
				$requestData->get('start'),
				$arr_cols[$col]['data'],
				$arr_sort[0]['dir'],
				$arr_search['value'],
				$arr_date
		);

		$arr_depenses = array();
		$arr_id = array();
		for($i=0; $i<count($list); $i++){

			$arr_f = $list[$i];

			$depense = $repository->find($arr_f['id']);
			$totaux = $depense->getTotaux();
			$list[$i]['totaux'] = $totaux;

			$list[$i]['avoir'] = null;
			foreach($depense->getAvoirs() as $avoir){
				$list[$i]['avoir'].=$avoir->getNum().' ';
			}
			if(!in_array($depense->getId(), $arr_id)){
				$arr_depenses[] = $arr_f;
				$arr_id[] = $depense->getId();
			}

			foreach($depense->getJournalAchats() as $ligneAchat){
				$list[$i]['num_ecriture'] = $ligneAchat->getNumEcriture();
			}
		}

		if($arr_cols[$col]['data'] == 'totaux'){
			if($arr_sort[0]['dir'] == 'asc'){
				usort($list, array($this, 'sortByTotalAsc'));
			} else {
				usort($list, array($this, 'sortByTotalDesc'));
			}

			$list = array_slice( $list, $requestData->get('start'), $requestData->get('length'));
		}  

		$response = new JsonResponse();
		$response->setData(array(
				'draw' => intval( $requestData->get('draw') ),
				'recordsTotal' => $repository->custom_count($this->getUser()->getCompany()),
				'recordsFiltered' => $repository->countForList($this->getUser()->getCompany(),$arr_search['value'], $arr_date),
				'aaData' => $list,
		));

		return $response;
	}

	private function sortByTotalAsc($a, $b)
	{
	    if ($a['totaux']['HT'] == $b['totaux']['HT']) {
	        return 0;
	    }
	    return ($a['totaux']['HT'] < $b['totaux']['HT']) ? -1 : 1;
	}

	private function sortByTotalDesc($a, $b)
	{
	    if ($a['totaux']['HT'] == $b['totaux']['HT']) {
	        return 0;
	    }
	    return ($a['totaux']['HT'] < $b['totaux']['HT']) ? 1 : -1;
	}

	/**
	 * @Route("/compta/depense/liste/retard", name="compta_depense_liste_retard")
	 */
	public function depenseListeRetardAction()
	{
		return $this->render('compta/depense/compta_depense_liste_retard.html.twig');
	}

	/**
	 * @Route("/compta/depense/liste/retard/ajax",
	 *   name="compta_depense_liste_retard_ajax",
	 *   options={"expose"=true}
	 * )
	 */
	public function depenseListeRetardAjaxAction(Request $requestData)
	{
		$arr_sort = $requestData->get('order');
		$arr_cols = $requestData->get('columns');

		$col = $arr_sort[0]['column'];

		$repository = $this->getDoctrine()->getManager()->getRepository('App:Compta\Depense');

		$arr_search = $requestData->get('search');
		$arr_date = $requestData->get('dateRange');
		
		$orderBy = $arr_cols[$col]['data'];

		$list = $repository->findForListRetard(
				$this->getUser()->getCompany(),
				$requestData->get('length'),
				$requestData->get('start'),
				$orderBy,
				$arr_sort[0]['dir'],
				$arr_search['value'],
				$arr_date
		);

		//		$chequesRepository = $this->getDoctrine()->getManager()->getRepository('App:Compta\RemiseCheque');
		//		$arr_remise_cheques = $chequesRepository->findForCompany($this->getUser()->getCompany());

		for($i=0; $i<count($list); $i++){
			$arr_f = $list[$i];
			$depense = $repository->find($arr_f['id']);
			$totaux = $depense->getTotaux();
			$list[$i]['totaux'] = $totaux;
		}

		$response = new JsonResponse();
		$response->setData(array(
			'draw' => intval( $requestData->get('draw') ),
			'recordsTotal' => $repository->custom_count($this->getUser()->getCompany()),
			'recordsFiltered' => $repository->countForListRetard($this->getUser()->getCompany(), $arr_search['value'], $arr_date),
			'aaData' => $list,
		));

		return $response;
	}

	/**
	 * Calculate the total (HT and TTC) all invoices in a date range
	 * passed as POST parameter
	 * @return Response 	Rendered view
	 *
	 * @Route("/compta/depense/total/ajax",
	 * 	name="compta_depense_total_ajax",
	 * 	options={"expose"=true}
	 * )
	 */
	public function depenseTotalAjaxAction(Request $request){

		$arr_date = $request->get('dateRange');

		$repository = $this->getDoctrine()->getManager()->getRepository('App:Compta\Depense');
		$arr_depenses = $repository->findForCompany($this->getUser()->getCompany(), $arr_date);

		$arr_totaux = array(
			'ht' => 0,
			'ttc' => 0
		);

		foreach($arr_depenses as $depense){
			$arr_totaux['ht']+= $depense->getTotalHT();
			$arr_totaux['ttc']+= $depense->getTotalTTC();
		}

		return $this->render('compta/depense/compta_depense_liste_totaux.html.twig', array(
			'arr_totaux' => $arr_totaux
		));
	}

	/**
	 * Calculate the total (HT and TTC) all invoices in a date range
	 * passed as POST parameter
	 * @return Response 	Rendered view
	 *
	 * @Route("/compta/depense-retard/total/ajax",
	 * 	name="compta_depense_retard_total_ajax",
	 * 	options={"expose"=true}
	 * )
	 */
	public function depenseRetardTotalAjaxAction(Request $request){
		$arr_date = $request->get('dateRange');

		$repository = $this->getDoctrine()->getManager()->getRepository('App:Compta\Depense');
		$arr_depenses = $repository->findDepensesRetardByCompany(
				$this->getUser()->getCompany(),
				$arr_date
		);

		$arr_totaux = array(
			'ht' => 0,
			'ttc' => 0
		);

		foreach($arr_depenses as $depense){
			$arr_totaux['ht']+= $depense->getTotalHT();
			$arr_totaux['ttc']+= $depense->getTotalTTC();
		}

		return $this->render('compta/depense/compta_depense_liste_totaux.html.twig', array(
			'arr_totaux' => $arr_totaux
		));
	}

	/**
	 * @Route("/compta/depense/ajouter", name="compta_depense_ajouter")
	 */
	public function depenseAjouterAction(Request $request)
	{
		$em = $this->getDoctrine()->getManager();
		
		$arr_opporunitesSousTraitances = $this->opportuniteService->findOpportunitesSousTraitancesAFacturer($this->getUser()->getCompany());

		$depense = new Depense();

		$form = $this->createForm(DepenseType::class, $depense, array(
		    'companyId' => $this->getUser()->getCompany()->getId(),
            'arr_opportuniteSousTraitances' => $arr_opporunitesSousTraitances
        ));

		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {

				$data = $form->getData();

				if($data->getCompte() == null){
					$compte = new Compte();
					$compte->setNom($form['compte_name']->getData());
					$compte->setUserGestion($this->getUser());
					$compte->setCompany($this->getUser()->getCompany());
					$compte->setDateCreation(new \DateTime(date('Y-m-d')));
					$compte->setUserCreation($this->getUser());
					$em->persist($compte);

					$depense->setCompte($compte);
				}

				$settingsRepository = $em->getRepository('App:Settings');
				$settingsNum = $settingsRepository->findOneBy(array('module' => 'COMPTA', 'parametre' => 'NUMERO_DEPENSE', 'company'=>$this->getUser()->getCompany()));
				if(is_null($settingsNum) || (is_countable($settingsNum) && count($settingsNum) == 0))
				{
					$settingsNum = new Settings();
					$settingsNum->setModule('COMPTA');
					$settingsNum->setParametre('NUMERO_DEPENSE');
					$settingsNum->setHelpText('Le numéro de dépense courant - ne pas modifier si vous n\'êtes pas sûr de ce que vous faites !');
					$settingsNum->setTitre('Numéro de dépense');
					$settingsNum->setType('NUM');
					$settingsNum->setNoTVA(false);
					$settingsNum->setCategorie('DEPENSE');
					$settingsNum->setCompany($this->getUser()->getCompany());
					$currentNum = 1;
				}
				else
				{
					$currentNum = $settingsNum->getValeur();
				}

				$depenseYear = $depense->getDate()->format('Y');

				if($depenseYear != date("Y")){
					//si la dépense est antidatée, récupérer le dernier numéro de dépense de l'année concernée
					$prefixe = 'D-'.$depenseYear.'-';
					$depenseRepo = $em->getRepository('App:Compta\Depense');
					$lastNum = $depenseRepo->findMaxNumForYear($depenseYear, $this->getUser()->getCompany());;
					$lastNum = substr($lastNum, 7);
					$lastNum++;
					$depense->setNum($prefixe.$lastNum);

				} else {
					$prefixe = 'D-'.date('Y').'-';
					if($currentNum < 10){
						$prefixe.='000';
					} else if ($currentNum < 100){
						$prefixe.='00';
					} else if ($currentNum < 1000){
						$prefixe.='0';
					}
					$depense->setNum($prefixe.$currentNum);

					//mise à jour du numéro de facture
					$currentNum++;
					$settingsNum->setValeur($currentNum);
					$em->persist($settingsNum);
				}
				
				$date = $depense->getDate();
				$dateReglement = clone $date;
				switch ($depense->getConditionReglement()) {
					case 'reception':
						$depense->setDateConditionReglement($dateReglement);
						break;
					case '30':
						$dateReglement->add(new \DateInterval('P30D'));
						$depense->setDateConditionReglement($dateReglement);
						break;
					case '30finMois':
						$dateReglement->add(new \DateInterval('P30D'));
						$dateReglement->modify('last day of this month');
						$depense->setDateConditionReglement($dateReglement);
						break;
					case '45':
						$dateReglement->add(new \DateInterval('P45D'));
						$depense->setDateConditionReglement($dateReglement);
						break;
					case '45finMois':
						$dateReglement->add(new \DateInterval('P45D'));
						$dateReglement->modify('last day of this month');
						$depense->setDateConditionReglement($dateReglement);
						break;
					case '60':
						$dateReglement->add(new \DateInterval('P60D'));
						$depense->setDateConditionReglement($dateReglement);
						break;
					case '60finMois':
						$dateReglement->add(new \DateInterval('P60D'));
						$dateReglement->modify('last day of this month');
						$depense->setDateConditionReglement($dateReglement);
						break;
				}

				$depense->setDateCreation(new \DateTime(date('Y-m-d')));
				$depense->setUserCreation($this->getUser());

				$depense->setEtat("ENREGISTRE");
				$depense->setTaxe(0); //pour empêcher que la TVA soit enregistrée à la fois dans la ligneDepense et dans la depense

				$em->persist($depense);

				//si le compte comptable du fournisseur n'existe pas, on le créé
				$compte = $depense->getCompte();
				if($compte->getFournisseur() == false || $compte->getCompteComptableFournisseur() == null){

					$compteComptable = $this->compteComptableService->createCompteComptableForCompte('401', $compte->getNom());
					$em->persist($compteComptable);

					$compte->setFournisseur(true);
					$compte->setCompteComptableFournisseur($compteComptable);
					$em->persist($compte);

				}
	
				$opportuniteSousTraitances = $form['opportuniteSousTraitances']->getData();

				foreach($opportuniteSousTraitances as $sousTraitance){
					$depenseSousTraitance = new depenseSousTraitance();
					$depenseSousTraitance->setDepense($depense);
					$depenseSousTraitance->setSousTraitance($sousTraitance);
					$em->persist($depenseSousTraitance);
					$depense->addSousTraitance($depenseSousTraitance);
				}
				$em->persist($depense);
				$em->flush();

				//ecrire dans le journal des achats
				$this->journalAchatService->journalAchatsAjouterDepenseAction(null, $depense);
				
				if(count($depense->getSousTraitances()) > 1){
					return $this->redirect($this->generateUrl(
							'compta_depense_sous_traitance_repartition', array(
								'id' => $depense->getId(),
								'action' => $request->request->get('action')
							)
					));
				} else if(count($depense->getSousTraitances()) == 1){
					$sousTraitance = $depense->getSousTraitances()[0];
					if('FC' == $depense->getAnalytique()->getValeur()){
						$sousTraitance->setMontantMonetaire($depense->getTotalTTC());
					} else {
						$sousTraitance->setMontantMonetaire($depense->getTotalHT());
					}
					
					$em->persist($sousTraitance);
					$em->flush();
				}

				if($request->request->get('action') == "Enregistrer et créer une nouvelle dépense"){
					return $this->redirect($this->generateUrl(
							'compta_depense_ajouter'
					));
				}

				return $this->redirect($this->generateUrl(
						'compta_depense_voir',
						array('id' => $depense->getId())
				));

		}

		return $this->render('compta/depense/compta_depense_ajouter.html.twig', array(
				'form' => $form->createView(),
		));
	}

	/**
	 * @Route("/compta/depense/sous-traitance-repartition/{id}/{action}", 
	 * 	name="compta_depense_sous_traitance_repartition")
	 */
	public function depenseSousTraitanceRepartitionAction(Request $request, Depense $depense, $action){

		$em = $this->getDoctrine()->getManager();
		$formBuilder = $this->createFormBuilder();

		foreach($depense->getSousTraitances() as $sousTraitance){
			$formBuilder->add($sousTraitance->getId(), NumberType::class, array(
				'label' => 'Montant réglé par la dépense :',
				'attr' => array('class' => 'montant'),
				'data' => $sousTraitance->getMontantMonetaire()
			));
		}

		$formBuilder->add('submit', \Symfony\Component\Form\Extension\Core\Type\SubmitType::class, array(
			'label' => 'Valider',
			'attr' => array('class' => 'btn btn-success'),
			'disabled' => true
		));

		$form = $formBuilder->getForm();


		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {

			foreach($form->getData() as $id => $montant){
				foreach($depense->getSousTraitances() as $sousTraitance){
					if($sousTraitance->getId() == $id){
						$sousTraitance->setMontantMonetaire($montant);
						$em->persist($sousTraitance);
					}
				}
			}
			$em->flush();

			if($action == "Enregistrer et créer une nouvelle dépense"){
				return $this->redirect($this->generateUrl(
						'compta_depense_ajouter'
				));
			}

			return $this->redirect($this->generateUrl(
					'compta_depense_voir',
					array('id' => $depense->getId())
			));

		}

		return $this->render('compta/depense/compta_depense_sous_traitance_repartition.html.twig', array(
			'depense' => $depense,
			'form' => $form->createView(),
		));
	}	

	/**
	 * @Route("/compta/depense/supprimer-sous-traitance/{id}", 
	 * 	name="compta_depense_supprimer_sous_traitance")
	 */
	public function depenseSupprimerSousTraitanceAction(DepenseSousTraitance $depenseSousTraitance){

		$em = $this->getDoctrine()->getManager();
		
		$depenseId = $depenseSousTraitance->getDepense()->getId();
		$em->remove($depenseSousTraitance);
		$em->flush();

		return $this->redirect($this->generateUrl(
				'compta_depense_voir',
				array('id' => $depenseId)
		));

	}	


	/**
	 * @Route("/compta/depense/ajouter-modal/{mouvement_id}", name="compta_depense_ajouter_modal", options={"expose"=true})
	 */
	public function depenseAjouterModalAction(Request $request, $mouvement_id = null)
	{
		$em = $this->getDoctrine()->getManager();
		$depense = new Depense();
		$depense->setDateCreation(new \DateTime(date('Y-m-d')));
		$depense->setUserCreation($this->getUser());
		$depense->setEtat("ENREGISTRE");

		$mouvement = null;
		if($mouvement_id != null){
			$mouvementRepo = $em->getRepository('App:Compta\MouvementBancaire');
			$mouvement = $mouvementRepo->find($mouvement_id);
			$depense->setDate($mouvement->getDate());
		}

		$arr_opporunitesSousTraitances = $this->opportuniteService->findOpportunitesSousTraitancesAFacturer($this->getUser()->getCompany());

        $form = $this->createForm(DepenseType::class, $depense, array(
            'companyId' => $this->getUser()->getCompany()->getId(),
            'arr_opportuniteSousTraitances' => $arr_opporunitesSousTraitances
        ));

		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {

			$em = $this->getDoctrine()->getManager();

			$data = $form->getData();
			if($data->getCompte() != null){
				$depense->setCompte($em->getRepository('App:CRM\Compte')->findOneById($data->getCompte()));
			} else {
				$compte = new Compte();
				$compte->setNom($form['compte_name']->getData());
				$compte->setUserGestion($this->getUser());
				$compte->setCompany($this->getUser()->getCompany());
				$compte->setDateCreation(new \DateTime(date('Y-m-d')));
				$compte->setUserCreation($this->getUser());
				$em->persist($compte);

				$depense->setCompte($compte);
				$em->flush();
			}

			$settingsRepository = $em->getRepository('App:Settings');
			$settingsNum = $settingsRepository->findOneBy(array('module' => 'COMPTA', 'parametre' => 'NUMERO_DEPENSE', 'company'=>$this->getUser()->getCompany()));
			if(is_null($settingsNum) || (is_countable($settingsNum) && count($settingsNum) == 0))
			{
				$settingsNum = new Settings();
				$settingsNum->setModule('COMPTA');
				$settingsNum->setParametre('NUMERO_DEPENSE');
				$settingsNum->setHelpText('Le numéro de dépense courant - ne pas modifier si vous n\'êtes pas sûr de ce que vous faites !');
				$settingsNum->setTitre('Numéro de dépense');
				$settingsNum->setType('NUM');
				$settingsNum->setCategorie('DEPENSE');
				$settingsNum->setCompany($this->getUser()->getCompany());
				$currentNum = 1;
			}
			else
			{
				$currentNum = $settingsNum->getValeur();
			}

			$depenseYear = $depense->getDate()->format('Y');

			if($depenseYear != date("Y")){
				//si la dépense est antidatée, récupérer le dernier numéro de dépense de l'année concernée
				$prefixe = 'D-'.$depenseYear.'-';
				$depenseRepo = $em->getRepository('App:Compta\Depense');
				$lastNum = $depenseRepo->findMaxNumForYear($depenseYear, $this->getUser()->getCompany());
				$lastNum = substr($lastNum, 7);
				$lastNum++;
				$depense->setNum($prefixe.$lastNum);

			} else {
				$prefixe = 'D-'.date('Y').'-';
				if($currentNum < 10){
					$prefixe.='000';
				} else if ($currentNum < 100){
					$prefixe.='00';
				} else if ($currentNum < 1000){
					$prefixe.='0';
				}
				$depense->setNum($prefixe.$currentNum);

				//mise à jour du numéro de facture
				$currentNum++;
				$settingsNum->setValeur($currentNum);
				$em->persist($settingsNum);
			}

			$depense->setEtat("ENREGISTRE");
			$depense->setTaxe(0); //pour empêcher que la TVA soit enregistrée à la fois dans la ligneDepense et dans la depense

			$em->persist($depense);

			//si le compte comptable du fournisseur n'existe pas, on le créé
			$compte = $depense->getCompte();
			if($compte->getFournisseur() == null){

				$compte->setFournisseur(true);

				$compteComptable = $this->compteComptableService->compteGenererAction('401', $compte->getId());
				$compte->setCompteComptableFournisseur($compteComptable);

				$em->persist($compte);
			}

			$em->flush();

			$this->journalAchatService ->journalAchatsAjouterDepenseAction(null, $depense);


			return new JsonResponse(array(
				'id' => $depense->getId(),
				's_depense' => $depense->__toString(),
				'price' => $depense->getTotalTTC(),
			));

		}

		return $this->render('compta/depense/compta_depense_ajouter_modal.html.twig', array(
				'form' => $form->createView(),
				'mouvement' => $mouvement
		));
	}

	/**
	 * @Route("/compta/depense/voir/{id}",
	 *   name="compta_depense_voir",
	 *   options={"expose"=true}
	 * )
	 */
	public function depenseVoirAction(Depense $depense)
	{
		
		$opportuniteSousTraitanceRepo = $this->getDoctrine()->getManager()->getRepository('App:CRM\OpportuniteSousTraitance');
		$arr_sousTraitances = $opportuniteSousTraitanceRepo->findHavingDepense($depense);

		$lastNumEcriture = $this->numService->getNumEcriture($this->getUser()->getCompany());
		
		$numEcriture = null;
		foreach($depense->getJournalAchats() as $ligneAchat){
			$numEcriture = $ligneAchat->getNumEcriture();
		}

		return $this->render('compta/depense/compta_depense_voir.html.twig', array(
			'depense' => $depense,
			'arr_sousTraitances' => $arr_sousTraitances,
			'numEcriture' => $numEcriture,
			'lastNumEcriture' => $lastNumEcriture-1
		));
	}

	/**
	 * @Route("/compta/depense/modal/voir/{id}", name="compta_depense_voir_modal")
	 */
	public function depenseModalVoirAction(Depense $depense)
	{
		return $this->render('compta/depense/compta_depense_voir_modal.html.twig', array(
				'depense' => $depense,
		));
	}

	/**
	 * @Route("/compta/depense/editer/{id}",
	 *   name="compta_depense_editer",
	 * 	 options={"expose"=true}
	 * )
	 */
	public function depenseEditerAction(Request $request, Depense $depense)
	{
		$em = $this->getDoctrine()->getManager();
		$opportuniteSousTraitancesRepo = $this->getDoctrine()->getManager()->getRepository('App:CRM\OpportuniteSousTraitance');

		$arr_opporunitesSousTraitances = $this->opportuniteService->findOpportunitesSousTraitancesAFacturer($this->getUser()->getCompany());
		$arr_depensesSousTraitances = array();
		$arr_depensesSousTraitancesId = array();
		foreach($depense->getSousTraitances() as $sousTraitance){
			$arr_depensesSousTraitances[] = $sousTraitance->getSousTraitance();
			$arr_depensesSousTraitancesId[] = $sousTraitance->getSousTraitance()->getId();
		}

        $form = $this->createForm(DepenseType::class, $depense, array(
            'companyId' => $this->getUser()->getCompany()->getId(),
            'arr_opportuniteSousTraitances' => $arr_opporunitesSousTraitances,
            'depenseSousTraitances' => $arr_depensesSousTraitances,
        ));

		$form->get('compte_name')->setData($depense->getCompte()->getNom());

		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {

			$data = $form->getData();

			$depense->setCompte($em->getRepository('App:CRM\Compte')->findOneById($data->getCompte()));
			$date = $depense->getDate();
			$dateReglement = clone $date;

			switch ($depense->getConditionReglement()) {
				case 'reception':
					$depense->setDateConditionReglement($dateReglement);
					break;
				case '30':
					$dateReglement->add(new \DateInterval('P30D'));
					$depense->setDateConditionReglement($dateReglement);
					break;
				case '30finMois':
					$dateReglement->add(new \DateInterval('P30D'));
					$dateReglement->modify('last day of this month');
					$depense->setDateConditionReglement($dateReglement);
					break;
				case '45':
					$dateReglement->add(new \DateInterval('P45D'));
					$depense->setDateConditionReglement($dateReglement);
					break;
				case '45finMois':
					$dateReglement->add(new \DateInterval('P45D'));
					$dateReglement->modify('last day of this month');
					$depense->setDateConditionReglement($dateReglement);
					break;
				case '60':
					$dateReglement->add(new \DateInterval('P60D'));
					$depense->setDateConditionReglement($dateReglement);
					break;
				case '60finMois':
					$dateReglement->add(new \DateInterval('P60D'));
					$dateReglement->modify('last day of this month');
					$depense->setDateConditionReglement($dateReglement);
					break;
			}

			$depense->setDateEdition(new \DateTime(date('Y-m-d')));
			$depense->setUserEdition($this->getUser());
			$depense->setTaxe(0); //pour empêcher que la TVA soit enregistrée à la fois dans la ligneDepense et dans la depense
			$em->persist($depense);

			$opportuniteSousTraitances = $form['opportuniteSousTraitances']->getData();

			foreach($depense->getSousTraitances() as $depenseSousTraitance){
				$found = false;
				foreach($opportuniteSousTraitances as $sousTraitance){
					if($sousTraitance->getId() == $depenseSousTraitance->getSousTraitance()->getId()){
						$found = true;
					}
				}
				if(!$found){
					$em->remove($depenseSousTraitance);
				}
				
			}
			$em->flush();

			foreach($opportuniteSousTraitances as $sousTraitance){
				if(!in_array($sousTraitance->getId(), $arr_depensesSousTraitancesId)){
					$depenseSousTraitance = new depenseSousTraitance();
					$depenseSousTraitance->setDepense($depense);
					$depenseSousTraitance->setSousTraitance($sousTraitance);
					$em->persist($depenseSousTraitance);
					$depense->addSousTraitance($depenseSousTraitance);
				}
				
			}
			$em->persist($depense);
			$em->flush();

			
			//supprimer les lignes du journal des achats
			$journalAchatsRepo = $em->getRepository('App:Compta\JournalAchat');
			$arr_lignes = $journalAchatsRepo->findByDepense($depense);
			$numEcriture = null;
			foreach($arr_lignes as $ligne){
				$numEcriture = $ligne->getNumEcriture();
				$em->remove($ligne);
			}

			//ecrire dans le journal des achats
			$this->journalAchatService ->journalAchatsAjouterDepenseAction($numEcriture, $depense);

			$em->flush();

			if(count($depense->getSousTraitances()) > 1){
				return $this->redirect($this->generateUrl(
						'compta_depense_sous_traitance_repartition', array(
							'id' => $depense->getId(),
							'action' => $request->request->get('action')
						)
				));
			} else if(count($depense->getSousTraitances()) == 1){
				$sousTraitance = $depense->getSousTraitances()[0];
				if('FC' == $depense->getAnalytique()->getValeur()){
					$sousTraitance->setMontantMonetaire($depense->getTotalTTC());
				} else {
					$sousTraitance->setMontantMonetaire($depense->getTotalHT());
				}
				
				$em->persist($sousTraitance);
				$em->flush();
			}

			return $this->redirect($this->generateUrl(
					'compta_depense_voir',
					array('id' => $depense->getId())
			));
		}

		return $this->render('compta/depense/compta_depense_editer.html.twig', array(
				'form' => $form->createView()
		));
	}

	/**
	 * @Route("/compta/depense/editer-modal/{id}", name="compta_depense_editer_modal", options={"expose"=true})
	 */
	public function depenseEditerModalAction(Request $request, Depense $depense)
	{
		$em = $this->getDoctrine()->getManager();

        $form = $this->createForm(DepenseType::class, $depense, array(
            'companyId' => $this->getUser()->getCompany()->getId(),
        ));

		$form->get('compte_name')->setData(	$depense->getCompte()->__toString());

		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {

			$data = $form->getData();

			$depense->setCompte($em->getRepository('App:CRM\Compte')->findOneById($data->getCompte()));

			$depense->setDateEdition(new \DateTime(date('Y-m-d')));
			$depense->setUserEdition($this->getUser());

			$em->persist($depense);
			$em->flush();

			//supprimer les lignes du journal des achats
			$journalAchatsRepo = $em->getRepository('App:Compta\JournalAchat');
			$arr_lignes = $journalAchatsRepo->findByDepense($depense);
			$numEcriture = null;
			foreach($arr_lignes as $ligne){
				$numEcriture = $ligne->getNumEcriture();
				$em->remove($ligne);
			}

			//ecrire dans le journal des achats
			$this->journalAchatService ->journalAchatsAjouterDepenseAction($numEcriture, $depense);

			$em->flush();

			return new JsonResponse(array(
				'id' => $depense->getId(),
				's_depense' => $depense->__toString(),
				'price' => $depense->getTotalTTC(),
			));

		}

		return $this->render('compta/depense/compta_depense_editer_modal.html.twig', array(
				'form' => $form->createView()
		));
	}

	/**
	 * @Route("/compta/depense/supprimer/{id}",
	 *   name="compta_depense_supprimer",
	 *   options={"expose"=true}
	 * )
	 */
	public function depenseSupprimerAction(Request $request, Depense $depense)
	{
		$form = $this->createFormBuilder()->getForm();

		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {

			$em = $this->getDoctrine()->getManager();
			$em->remove($depense);
		
			$settingsRepository = $em->getRepository('App:Settings');
			$numSettings = $settingsRepository->findOneBy(array('module' => 'COMPTA', 'parametre' => 'NUMERO_DEPENSE', 'company'=>$this->getUser()->getCompany()));
			$numSettings->setValeur($numSettings->getValeur() - 1);
			$em->persist($numSettings);

			$em->flush();

			$numEcriture = $this->numService->getNumEcriture($this->getUser()->getCompany());
			$numEcriture--;

			$this->numService->updateNumEcriture($this->getUser()->getCompany(), $numEcriture);

			return $this->redirect($this->generateUrl(
					'compta_depense_liste'
			));
		}

		return $this->render('compta/depense/compta_depense_supprimer.html.twig', array(
				'form' => $form->createView(),
				'depense' => $depense
		));
	}

	/**
	 * @Route("/compta/depense/importer-historique/mapping/{initialisation}", name="compta_depense_importer_historique_mapping")
	 */
	public function depenseImporterMappingAction(Request $request, $initialisation = false)
	{
		$session = $request->getSession();

		//recuperation et ouverture du fichier temporaire uploadé
		$path =  $this->get('kernel')->getRootDir().'/../public/upload/compta/historique_depenses';
		$filename = $session->get('import_historique_depense_filename');
		$fh = fopen($path.'/'.$filename, 'r+');

		//récupération de la première ligne pour créer le formulaire de mapping
		$arr_headers = array();
		$i = 0;

		while( ($row = fgetcsv($fh)) !== FALSE && $i<1 ) {
			$arr_headers = explode(';',$row[$i]);
			$i++;
		}
	//	$arr_headers = array_combine($arr_headers, $arr_headers); //pour que l'array ait les mêmes clés et valeurs
		$arr_headers_and_cols = array();
		$col = 'A';
		foreach($arr_headers as $key => $header){
			$s =  $header.' (col '.$col.')';
			$arr_headers_and_cols[$s] = $s;
			$col++;
		}

		$form_mapping = $this->createForm(UploadHistoriqueDepenseMappingType::class, null, array(
		    'arr_headers' => $arr_headers,
            'filename' => $filename,
        ));

		$form_mapping->handleRequest($request);

		if ($form_mapping->isSubmitted() && $form_mapping->isValid()) {

			$data = $form_mapping->getData();

			$dateFormat = $data['dateFormat'];
			$session->set('import_historique_depense_date_format', $dateFormat);

			$arr_mapping = array();
			//recuperation des colonnes
			$arr_mapping['compte'] = $data['compte'];
			$arr_mapping['num'] = $data['num'];
			$arr_mapping['date'] = $data['date'];
			$arr_mapping['dateCreation'] = $data['dateCreation'];
			$arr_mapping['modePaiement'] = $data['modePaiement'];
			$arr_mapping['etat'] =$data['etat'];
			$arr_mapping['user'] =$data['user'];
			$arr_mapping['analytique'] =$data['analytique'];
			$arr_mapping['taxe'] =$data['taxe'];

			$arr_mapping['produitNom'] = $data['produitNom'];
			$arr_mapping['produitTarif'] = $data['produitTarif'];
			$arr_mapping['produitTaxe'] = $data['produitTaxe'];
			$arr_mapping['produitCompteComptable'] = $data['produitCompteComptable'];

			$session->set('import_historique_depense_arr_mapping', $arr_mapping);

			//creation du formulaire de validation
			return $this->redirect($this->generateUrl('compta_depense_importer_historique_validation'));
		}

		return $this->render('compta/depense/compta_depense_importer_historique_mapping.html.twig', array(
				'form' => $form_mapping->createView()
		));

	}

	/**
	 * @Route("/compta/depense/importer-historique/validation/{initialisation}", name="compta_depense_importer_historique_validation")
	 */
	public function depenseImporterValidationAction(Request $request, $initialisation = false)
	{
		$session = $request->getSession();

		$em = $this->getDoctrine()->getManager();

		$compteRepo = $this->getDoctrine()->getManager()->getRepository('App:CRM\Compte');
		$compteComptableRepo = $this->getDoctrine()->getManager()->getRepository('App:Compta\CompteComptable');
		$userManager = $this->get('fos_user.user_manager');
		$userRepo = $this->getDoctrine()->getManager()->getRepository('App:User');
		$contactRepo = $this->getDoctrine()->getManager()->getRepository('App:CRM\Contact');

		//recuperation et ouverture du fichier temporaire uploadé
		$path =  $this->get('kernel')->getRootDir().'/../public/upload/compta/historique_depenses';
		$filename = $session->get('import_historique_depense_filename');

		//recuperation du mapping
		$arr_mapping = $session->get('import_historique_depense_arr_mapping');

		//parsing du CSV
		$csv = new \parseCSV();
		$csv->delimiter = ";";
		$csv->parse($path.'/'.$filename);

		//array contenant les erreurs
		$arr_err_comptes = array();
		$arr_err_users = array();
		$arr_err_cc = array();

		//parsing ligne par ligne
		$total = 0;
		$i = 0;
		$prevNum = null;
		while($i<count($csv->data)){
			$data = $csv->data[$i];

			$nomCol = substr($arr_mapping['num'], 0, -8);
			$num = $data[$nomCol];
			if($num != $prevNum){

				//est-ce que tous les comptes à importer existent ?
				$nomCol = substr($arr_mapping['compte'], 0, -8);
				$nomCompte = $data[$nomCol];
				$compte = $compteRepo->findOneBy(array(
						'nom' => $nomCompte,
						'company' => $this->getUser()->getCompany()
				));
				if($compte == null){
					if(!in_array($nomCompte, $arr_err_comptes)){
						$arr_err_comptes[] = $nomCompte;
					}
				}

				//est-ce que tous les utilisateurs à importer existent ?
				$nomCol = substr($arr_mapping['user'], 0, -8);
				$nomUser = $data[$nomCol];
				$user = $userManager->findUserByUsernameOrEmail($nomUser);
				if($user == null){
					if(!in_array($nomUser, $arr_err_users)){
						$arr_err_users[] = $nomUser;
					}
				}

				//est-ce que tous les comptes comptables à importer existent ?
				$nomCol = substr($arr_mapping['produitCompteComptable'], 0, -8);
				$nomCC = $data[$nomCol];
				$cc = $compteComptableRepo->findOneBy(array(
						'nom' => $nomCC,
						'company' => $this->getUser()->getCompany()
				));
				if($cc == null){
					if(!in_array($nomCC, $arr_err_cc)){
						$arr_err_cc[] = $nomCC;
					}
				}
				$prevNum = $num;

			}

			$i++;
		}

		$formBuilder = $this->createFormBuilder();

		if(count($arr_err_comptes) != 0){

			$arr_all_comptes = $compteRepo->findByCompany($this->getUser()->getCompany());
			$arr_all_contacts = $contactRepo->findByCompany($this->getUser()->getCompany());

			foreach($arr_err_comptes as  $key => $err_compte){

				//chercher s'il existe un fournisseur au nom similaire dans la CRM
				$dataName = null;
				$dataCompte = null;
				$found = false;
				foreach($arr_all_comptes as $compte){
					$nomCompteFournisseur = strtoupper($compte->getNom());
					if($this->startsWith($nomCompteFournisseur, $err_compte)){
						$dataName = $compte->getNom();
						$dataCompte = $compte->getId();
						$found = true;
						break;
					}
				}

				//chercher dans les contacts s'il n'y a pas de correspondance dans les comptes
				if(!$found){
					foreach($arr_all_contacts as $contact){
						$nomContactFournisseur = strtoupper($contact->getNom());
						if($this->startsWith($err_compte, $nomContactFournisseur)){
							$dataName = $contact->getCompte()->getNom().' ('.$contact.')';
							$dataCompte = $contact->getCompte()->getId();
							break;
						}
					}
				}

				$selectedOption = 'new';
				if($dataName){
					$selectedOption = 'existing';
				}

				$formBuilder->add($key.'-radio', \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, array(
						'required' => true,
						'mapped' => false,
						'expanded' => true,
						'choices' => array(
								'existing' => 'Utiliser un fournisseur existant dans la CRM',
								'new' => 'Créer un nouveau fournisseur dans la CRM',
						),
						'data' => 'new',
						'data' => $selectedOption

				))
				->add($key.'-name', \Symfony\Component\Form\Extension\Core\Type\TextType::class, array(
						'required' => false,
						'mapped' => false,
						'label' => $err_compte,
						'attr' => array('class' => 'typeahead-compte'),
						'data' => $dataName,
				))
				->add($key.'-compte', \Symfony\Component\Form\Extension\Core\Type\HiddenType::class, array(
						'required' => false,
						'attr' => array('class' => 'entity-compte'),
						'data' => $dataCompte,
				))
				->add($key.'-label', \Symfony\Component\Form\Extension\Core\Type\HiddenType::class, array(
						'required' => false,
						'attr' => array('class' => 'entity-compte'),
						'data' => $key,
				));
			}
		}

		if(count($arr_err_users) != 0){
			$arr_choices_users = array();
			$arr_all_users = $userRepo->findByCompany($this->getUser()->getCompany());
			foreach($arr_all_users as $user){
				$arr_choices_users[$user->getId()] = $user->__toString();
			}

			foreach($arr_err_users as  $key => $err_user){
				$formBuilder->add('user-'.$key, \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, array(
						'choices' => $arr_choices_users,
						'label' => $err_user,
						'required' => true
				));
			}
		}

		if(count($arr_err_cc) != 0){
			$arr_choices_cc = array();
			$arr_all_cc = $compteComptableRepo->findAllByNum(6,$this->getUser()->getCompany());
			foreach($arr_all_cc as $cc){
				$arr_choices_cc[$cc->getId()] = $cc->__toString();
			}

			foreach($arr_err_cc as  $key => $err_cc){
				$formBuilder->add('cc-'.$key, \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, array(
						'choices' => $arr_choices_cc,
						'label' => $err_cc,
						'required' => true
				));
			}
		}

		$formBuilder->add('submit',\Symfony\Component\Form\Extension\Core\Type\SubmitType::class, array(
				'label' => 'Importer',
				'attr' => array('class' => 'btn btn-success')
		));

		$form = $formBuilder->getForm();
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {

			$data = $form->getData();

			$arr_validation = array();
			foreach($arr_err_comptes as  $key => $err_compte){
				if($err_compte == ""){
					continue;
				}
				if($form[$key.'-radio']->getData() == "new"){
					$compteFournisseur = new Compte();
					$compteFournisseur->setNom($err_compte);
					$compteFournisseur->setCompany($this->getUser()->getCompany());
					$compteFournisseur->setUserCreation($this->getUser());
					$compteFournisseur->setUserGestion($this->getUser());
					$compteFournisseur->setDateCreation(new \DateTime(date('Y-m-d')));
					$em->persist($compteFournisseur);
				}else {
					$arr_validation[$err_compte] = $data[$key.'-compte'];
				}

			}
			$em->flush();


			foreach($arr_err_users as  $key => $err_user){
				$arr_validation[$err_user] = $data['user-'.$key];
			}
			foreach($arr_err_cc as  $key => $err_cc){
				$arr_validation[$err_cc] = $data['cc-'.$key];
			}

			$session->set('import_historique_depense_arr_validation', $arr_validation);

			return $this->redirect($this->generateUrl('compta_depense_importer_historique_execution', array('initialisation' => $initialisation)));
		}

		return $this->render('compta/depense/compta_depense_importer_historique_validation.html.twig', array(
				'arr_err_comptes' => $arr_err_comptes,
				'arr_err_users' => $arr_err_users,
				'arr_err_cc' => $arr_err_cc,
				'form' => $form->createView()
		));
	}

	/**
	 * @Route("/compta/depense/importer-historique/execution/{initialisation}", name="compta_depense_importer_historique_execution")
	 */
	public function depenseImporterExecutionAction(Request $request, $initialisation = false)
	{
		$session = $request->getSession();

		$em = $this->getDoctrine()->getManager();
		$compteRepo = $em->getRepository('App:CRM\Compte');
		$compteComptableRepo = $em->getRepository('App:Compta\CompteComptable');
		$userManager = $this->get('fos_user.user_manager');
		$settingsRepo = $em->getRepository('App:Settings');
		$compteComptableRepo = $this->getDoctrine()->getManager()->getRepository('App:Compta\CompteComptable');

		$arr_nums = $compteComptableRepo->findAllNumForCompany($this->getUser()->getCompany());

		//recuperation et ouverture du fichier temporaire uploadé
		$path =  $this->get('kernel')->getRootDir().'/../public/upload/compta/historique_depenses';
		$filename = $session->get('import_historique_depense_filename');

		//recuperation du mapping
		$arr_mapping = $session->get('import_historique_depense_arr_mapping');
		//recuperation de la validation (correction des comptes et users inexistants)
		$arr_validation = $session->get('import_historique_depense_arr_validation');

		//parsing du CSV
		$csv = new \parseCSV();
		$csv->delimiter = ";";
		$csv->parse($path.'/'.$filename);

		$total = 0;
		$i = 0;
		$prevNum = null;
		$arr_depenses = array();

		$dateFormat = $session->get('import_historique_depense_date_format');

		//parsing ligne par ligne
		while($i<count($csv->data)){

			$data = $csv->data[$i];
			$nomCol = substr($arr_mapping['num'], 0, -8);
			$num = $data[$nomCol];
			if($num != $prevNum){

				//creation et hydratation de la facture
				$depense = new Depense();

				$nomCol = substr($arr_mapping['compte'], 0, -8);
				$nomCompte = $data[$nomCol];
				if($nomCompte == ""){
					$i++;
					continue;
				}
				if(!array_key_exists($nomCompte, $arr_validation)){
					$compte = $compteRepo->findOneBy(array(
							'nom' => $nomCompte,
							'company' => $this->getUser()->getCompany()
					));
				} else {
					$compte = $compteRepo->findOneById($arr_validation[$nomCompte]);
				}
				if($compte->getFournisseur() == false){
					$compte->setFournisseur(true);
					$compteComptable = $this->compteComptableService->createCompteComptableForCompte('401', $compte->getNom());
					$em->persist($compteComptable);

					$compte->setCompteComptableFournisseur($compteComptable);
					$em->persist($compte);
				}
				$depense->setCompte($compte);

				$nomCol = substr($arr_mapping['dateCreation'], 0, -8);
				$dateCreation = \DateTime::createFromFormat($dateFormat, $data[$nomCol]);
				$depense->setDateCreation($dateCreation);

				$nomCol = substr($arr_mapping['date'], 0, -8);
				$date = \DateTime::createFromFormat($dateFormat, $data[$nomCol]);
				$depense->setDate($date);

				$nomCol = substr($arr_mapping['etat'], 0, -8);
				$depense->setEtat($data[$nomCol]);

				$nomCol = substr($arr_mapping['num'], 0, -8);
				$depense->setNum($data[$nomCol]);

				$nomCol = substr($arr_mapping['taxe'], 0, -8);
				$taxe = $data[$nomCol];
				$taxe = str_replace(',', '.', $taxe);
				$depense->setTaxe($taxe);

				$nomCol = substr($arr_mapping['modePaiement'], 0, -8);
				$depense->setModePaiement($data[$nomCol]);

				$nomCol = substr($arr_mapping['user'], 0, -8);
				$nomUser = $data[$nomCol];
				if(!array_key_exists($nomUser, $arr_validation)){
					$user = $userManager->findUserByUsernameOrEmail($nomUser);
				} else {
					$user = $user = $userManager->findUserBy(array('id' => $arr_validation[$nomUser]));
				}
				$depense->setUserCreation($user);

				$nomCol = substr($arr_mapping['analytique'], 0, -8);
				$analytique = $settingsRepo->findOneBy(array(
						'company' => $this->getUser()->getCompany(),
						'module' => 'COMPTA',
						'parametre' => 'ANALYTIQUE',
						'valeur' => $data[$nomCol]
				));
				$depense->setAnalytique($analytique);

				$prevNum = $num;
				$em->persist($depense);
				$arr_depenses[] = $depense;
			}

			//creation et hydratation de la ligne
			$ligne = new LigneDepense();
			$ligne->setDepense($depense);

			$nomCol = substr($arr_mapping['produitNom'], 0, -8);
			$ligne->setNom($data[$nomCol]);

			$nomCol = substr($arr_mapping['produitTarif'], 0, -8);
			$montant = $data[$nomCol];
			$montant = str_replace(',', '.', $montant);
			$ligne->setMontant($montant);

			$nomCol = substr($arr_mapping['produitTaxe'], 0, -8);
			$taxe = $data[$nomCol];
			$taxe = str_replace(',', '.', $taxe);
			$ligne->setTaxe($taxe);

			$nomCol = substr($arr_mapping['produitCompteComptable'], 0, -8);
			$nomCC = $data[$nomCol];
			if(!array_key_exists($nomCC, $arr_validation)){
				$cc = $compteComptableRepo->findOneBy(array(
						'company' => $this->getUser()->getCompany(),
						'nom' => $nomCC
				));
			} else {
				$cc = $compteComptableRepo->findOneById($arr_validation[$nomCC]);
			}

			$ligne->setCompteComptable($cc);
			$depense->addLigne($ligne);

			$em->persist($ligne);

			$i++;
		}

		$em->flush();

		foreach($arr_depenses as $depense){

			//ecrire dans le journal des achats
			$this->journalAchatService ->journalAchatsAjouterDepenseAction(null, $depense);
		}

		//suppression du fichier temporaire
		unlink($path.'/'.$filename);

		//suppression des variables de session
		$session->remove('import_historique_facture_filename');
		$session->remove('import_historique_facture_arr_mapping');
		$session->remove('import_historique_facture_arr_validation');

		if($initialisation){
			return $this->redirect($this->generateUrl('compta_activer_importer_depense_ok'));
		}

		return $this->redirect($this->generateUrl('compta_depense_liste'));
	}

	/**
	 * @Route("/compta/depense/importer-historique/upload", name="compta_depense_importer_historique_upload")
	 */
	public function depenseImporterHistoriqueUploadAction(Request $requestData)
	{
		$em = $this->getDoctrine()->getManager();
		$arr_files = $requestData->files->all();
		$file = $arr_files["files"][0];
		//enregistrement temporaire du fichier uploadé
		$filename = date('Ymdhms').'-'.$this->getUser()->getId().'-'.$file->getClientOriginalName();
		$path =  $this->get('kernel')->getRootDir().'/../public/upload/compta/historique_depenses';
		$file->move($path, $filename);

		$session = $requestData->getSession();
		$session->set('import_historique_depense_filename', $filename);

		$response = new JsonResponse();
		$response->setData(array(
				'filename' => $filename
		));

		return $response;
	}

	private function startsWith($haystack, $needle)
	{
		$length = strlen($needle);
		return (substr($haystack, 0, $length) === $needle);
	}

	/**
	 * @Route("/compta/depense/importer-historique/{initialisation}", name="compta_depense_importer_historique")
	 */
	public function depenseImporterHistoriqueAction(Request $request, $initialisation = false)
	{
		$form = $this->createForm(UploadHistoriqueDepenseType::class); // @upgrade-note removed unused parameter $this->getUser()->getCompany()

		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {

			if($form['file']->getData() == null){
				throw new \Exception("Vous n'avez pas uploadé de fichier.");
			}

			//recuperation des données du formulaire
			$data = $form->getData();
			$file = $data['file'];


			//enregistrement temporaire du fichier uploadé
			$filename = date('Ymdhms').'-'.$this->getUser()->getId().'-'.$file->getClientOriginalName();
			$path =  $this->get('kernel')->getRootDir().'/../public/upload/compta/historique_depenses';
			$file->move($path, $filename);
			$session = $request->getSession();
			$session->set('import_historique_depense_filename', $filename);

			$dateFormat = $data['dateFormat'];
			$session->set('import_historique_depense_date_format', $dateFormat);

			//creation du formulaire de mapping
			return $this->redirect($this->generateUrl('compta_depense_importer_historique_mapping'));
		}

		if($initialisation){
			return $this->render('compta/activation/compta_activation_importer_depenses.html.twig', array(
					'form' => $form->createView()
			));
		}

		return $this->render('compta/depense/compta_depense_importer_historique.html.twig', array(
				'form' => $form->createView()
		));
	}

	/**
	 * @Route("/compta/depense/get-liste",
	 *   name="compta_depense_get_liste",
	 *   options={"expose"=true}
	 * )
	 */
	public function depenseGetListeAction() {

		$repository = $this->getDoctrine()->getManager()->getRepository('App:Compta\Depense');

		$list = $repository->findForCompany($this->getUser()->getCompany());

		$res = array();
		foreach ($list as $depense) {

			$_res = array('id' => $depense->getId(), 'display' => $depense->getNum());
			$res[] = $_res;
		}

		$response = new \Symfony\Component\HttpFoundation\Response(json_encode($res));
		$response->headers->set('Content-Type', 'application/json');
		return $response;
	}

	// /**
	//  * @Route("/compta/depenses-corriger", name="compta_depenses_corriger")
	//  */
	// public function journalAchatsReinitialiser(){

	// 	$em = $this->getDoctrine()->getManager();
	// 	$depenseRepo = $em->getRepository('App:Compta\Depense');

	// 	$arr_depenses_rapprochees = $depenseRepo->findByEtat('RAPPROCHE');

	// 	foreach($arr_depenses_rapprochees as $depense){
	// 		if(count($depense->getRapprochements()) == 0){
	// 			$depense->setEtat('ENREGISTRE');
	// 			$em->persist($depense);
	// 		}
	// 	}
	// 	$em->flush();
	// 	return new Response();

	// }


// 	/**
// 	 * @Route("/compta/journal-achats/reinitialiser", name="compta_journal_achats_reinitialiser")
// 	 */
// 	public function journalAchatsReinitialiser(){

// 		$em = $this->getDoctrine()->getManager();
// 		$journalAchatsRepo = $em->getRepository('App:Compta\JournalAchat');
// 		$depenseRepo = $em->getRepository('App:Compta\Depense');
// 		$avoirRepo = $em->getRepository('App:Compta\Avoir');

// 		$arr_journal = $journalAchatsRepo->findJournalEntier($this->getUser()->getCompany());
// 		foreach($arr_journal as $ligne){
// 			$em->remove($ligne);
// 		}
// 		$em->flush();

// 		$arr_depenses = $depenseRepo->findForCompany($this->getUser()->getCompany());
// 		foreach($arr_depenses as $depense){
// 			$compte = $depense->getCompte();
// 			if($compte->getCompteComptableFournisseur() == null || $compte->getFournisseur() == false){
// 				$compteComptableRepo = $this->getDoctrine()->getManager()->getRepository('App:Compta\CompteComptable');
// 				$arr_nums = $compteComptableRepo->findAllNumForCompany($this->getUser()->getCompany());

// 				$compteComptable = new CompteComptable();
// 				$compteComptable->setNom($compte->getNom());
// 				$compteComptable->setCompany($this->getUser()->getCompany());

// 				$nbChars = 5;
// 				$num = '401';
// 				$num.= mb_strtoupper(substr($compte->getNom(),0,$nbChars), 'UTF-8');
// 				$arr_replace = array(' ','_','&','\'');
// 				$num = str_replace($arr_replace, "", $num);

// 				while(in_array($num, $arr_nums)){
// 					$nbChars++;
// 					$num = '401';
// 					$num.= strtoupper(substr($compte->getNom(),0,$nbChars));
// 					$arr_replace = array(' ','_','&','\'');
// 					$num = str_replace($arr_replace, "", $num);
// 				}
// 				$compteComptable->setNum($num);
// 				$em->persist($compteComptable);

// 				$compte->setFournisseur(true);
// 				$compte->setCompteComptableFournisseur($compteComptable);
// 				$em->persist($compte);
// 			}

// 			//ecrire dans le journal des achats
// 			$this->journalAchatService  = $this->get('appbundle.compta_journal_achats_controller');
// 			$this->journalAchatService ->journalAchatsAjouterDepenseAction(null, $depense);

// 		}

// 		$arr_avoirs = $avoirRepo->findForCompany('FOURNISSEUR', $this->getUser()->getCompany());
// 		foreach($arr_avoirs as $avoir){
// 			$compte = $avoir->getDepense()->getCompte();
// 			if($compte->getCompteComptableFournisseur() == null || $compte->getFournisseur() == false){
// 				$compteComptableRepo = $this->getDoctrine()->getManager()->getRepository('App:Compta\CompteComptable');
// 				$arr_nums = $compteComptableRepo->findAllNumForCompany($this->getUser()->getCompany());

// 				$compteComptable = new CompteComptable();
// 				$compteComptable->setNom($compte->getNom());
// 				$compteComptable->setCompany($this->getUser()->getCompany());

// 				$nbChars = 5;
// 				$num = '401';
// 				$num.= mb_strtoupper(substr($compte->getNom(),0,$nbChars), 'UTF-8');
// 				$arr_replace = array(' ','_','&','\'');
// 				$num = str_replace($arr_replace, "", $num);

// 				while(in_array($num, $arr_nums)){
// 					$nbChars++;
// 					$num = '401';
// 					$num.= strtoupper(substr($compte->getNom(),0,$nbChars));
// 					$arr_replace = array(' ','_','&','\'');
// 					$num = str_replace($arr_replace, "", $num);
// 				}
// 				$compteComptable->setNum($num);
// 				$em->persist($compteComptable);

// 				$compte->setFournisseur(true);
// 				$compte->setCompteComptableFournisseur($compteComptable);
// 				$em->persist($compte);
// 			}

// 			//ecrire dans le journal des achats
// 			$this->journalAchatService ->journalAchatsAjouterAvoirAction(null, $avoir);

// 		}

// 		$em->flush();
// 		return 0;

// 	}

// 	/**
// 	 * @Route("/compta/depense/reinitialiser", name="compta_depense_reinitialiser")
// 	 */
// 	public function numDepenseReinitialiser(){

// 		$em = $this->getDoctrine()->getManager();
// 		$depenseRepo = $em->getRepository('App:Compta\Depense');
// 		$settingsRepository = $em->getRepository('App:Settings');

// 		$settingsNum = $settingsRepository->findOneBy(array('module' => 'COMPTA', 'parametre' => 'NUMERO_DEPENSE', 'company'=>$this->getUser()->getCompany()));
// 		$arr_year = array(2012,2013,2014,2015,2016);

// 		foreach($arr_year as $year){

// 			$currentNum=1;

// 			$arr_depense = $depenseRepo->findForCompanyByYear($this->getUser()->getCompany(), $year);

// 			foreach($arr_depense as $depense){
// 				$prefixe = 'D-'.$year.'-';
// 				if($currentNum < 10){
// 					$prefixe.='00';
// 				} else if ($currentNum < 100){
// 					$prefixe.='0';
// 				}
// 				$depense->setNum($prefixe.$currentNum);

// 				//mise à jour du numéro de depese
// 				$currentNum++;
// 			}


// 		}
// 		$em->flush();


// 		return 0;
// 	}

}
