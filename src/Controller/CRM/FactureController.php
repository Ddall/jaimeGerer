<?php

namespace App\Controller\CRM;

use App\Controller\Compta\CompteComptableController;
use App\Entity\Compta\CompteComptable;
use App\Entity\CRM\DocumentPrix;
use App\Entity\CRM\PriseContact;
use App\Form\Compta\CompteComptableType;
use App\Form\CRM\FactureType;
use App\Service\Compta\CompteComptableService;
use App\Service\LegacyExcelFactory as Factory;
use App\Service\UtilsService;
use App\Util\DependancyInjectionTrait\JournalVentesTrait;
use App\Util\DependancyInjectionTrait\KnpSnappyPDFTrait;
use App\Util\DependancyInjectionTrait\NumServiceTrait;
use App\Util\DependancyInjectionTrait\UtilsServiceTrait;
use PHPExcel;
use Swift_Attachment;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FactureController extends AbstractController
{
	const FILTER_DATE_NONE      = 0;
	const FILTER_DATE_MONTH     = 1;
	const FILTER_DATE_2MONTH    = 2;
	const FILTER_DATE_YEAR      = 3;
	const FILTER_DATE_CUSTOM    = -1;

	use UtilsServiceTrait;
	use NumServiceTrait;
	use JournalVentesTrait;
	use KnpSnappyPDFTrait;

	/**
	 * @Route("/crm/facture/liste", name="crm_facture_liste")
	 * @Route("/crm/factures/devis/liste/{id}", name="crm_factures_devis_liste")
	 */
	public function factureListeAction(DocumentPrix $DevisParent=NULL)
	{
		$lastNumEcriture = $this->numService->getNumEcriture($this->getUser()->getCompany());
		
		$ajax_url = is_null($DevisParent) ? $this->generateUrl('crm_facture_liste_ajax') : $this->generateUrl('crm_factures_devis_liste_ajax', array('id' => $DevisParent->getId()));
		
		$titre_page = is_null($DevisParent) ? 'Factures' : 'Factures liées au devis n° : '. $DevisParent->getNum();
		
		return $this->render('crm/facture/crm_facture_liste.html.twig', array(
				'lastNumEcriture' => $lastNumEcriture-1,
				'ajax_url' =>$ajax_url,
				'titre_page' =>$titre_page
		));
	}

	/**
	 * @Route("/crm/facture/liste/ajax", name="crm_facture_liste_ajax")
	 * @Route("/crm/factures/devis/liste/ajax/{id}", name="crm_factures_devis_liste_ajax")
	 */
	public function factureListeAjaxAction(Request $requestData, DocumentPrix $DevisParent=null)
	{
		$arr_sort = $requestData->get('order');
		$arr_cols = $requestData->get('columns');

		$col = $arr_sort[0]['column'];

		$repository = $this->getDoctrine()->getManager()->getRepository('App:CRM\DocumentPrix');

		$arr_search = $requestData->get('search');

		$dateSearch = $requestData->get('date_search', 0);
		$startDate = null;
		$endDate = null;
		switch ($dateSearch) {
			case self::FILTER_DATE_MONTH:
				$startDate = new \DateTime('first day of this month');
				$endDate = new \DateTime('last day of this month');
				break;
			case self::FILTER_DATE_2MONTH:
				$startDate = new \DateTime('first day of previous month');
				$endDate = new \DateTime('last day of this month');
				break;
			case self::FILTER_DATE_YEAR:
				$startDate = new \DateTime('first day of january');
				$endDate = new \DateTime('last day of december');
				break;
			case self::FILTER_DATE_CUSTOM:
				$startDate = $requestData->get('start_date', null);
				$startDate = ($startDate ? new \DateTime($startDate) : null);
				$endDate = $requestData->get('end_date', null);
				$endDate = ($endDate ? new \DateTime($endDate) : null);
				break;
		}
		$dateRange = null;
		if ($startDate) {
			$dateRange = array(
				'start' => $startDate,
				'end'   => $endDate
			);
		}

		$list = $repository->findForList(
				$this->getUser()->getCompany(),
				'FACTURE',
				$requestData->get('length'),
				$requestData->get('start'),
				$arr_cols[$col]['data'],
				$arr_sort[0]['dir'],
				$arr_search['value'],
				null,
				$DevisParent,
				null,
				$dateRange
		);

		for($i=0; $i<count($list); $i++){

			$arr_f = $list[$i];

			$facture = $repository->find($arr_f['id']);
			$totaux = $facture->getTotaux();
			$list[$i]['totaux'] = $totaux;

			$list[$i]['avoir'] = null;
			foreach($facture->getAvoirs() as $avoir){
				$list[$i]['avoir'].=$avoir->getNum().' ';
			}

			$bonsCommande = "";
			if($facture->getBonCommande()){
				$bonsCommande.=$facture->getBonCommande()->getNum().'<br />';
			}
			
			$list[$i]['bon_commande'] = $bonsCommande;

			foreach($facture->getJournalVentes() as $ligneVente){
				$list[$i]['num_ecriture'] = $ligneVente->getNumEcriture();
			}
		}

		$response = new JsonResponse();
		$response->setData(array(
				'draw' => intval( $requestData->get('draw') ),
				'recordsTotal' => $repository->custom_count($this->getUser()->getCompany(), 'FACTURE', NULL, $DevisParent),
				'recordsFiltered' => $repository->countForList($this->getUser()->getCompany(), 'FACTURE', $arr_search['value'], $DevisParent, NULL,NULL,$dateRange),
				'aaData' => $list,
		));

		return $response;
	}

	
	/**
	 * @Route("/crm/facture/voir/{id}", name="crm_facture_voir", options={"expose"=true})
	 */
	public function factureVoirAction(DocumentPrix $facture)
	{
		$lastNumEcriture = $this->numService->getNumEcriture($this->getUser()->getCompany());
		
		$priseContactsRepository = $this->getDoctrine()->getManager()->getRepository('App:CRM\PriseContact');
		$listPriseContacts = $priseContactsRepository->findByDocumentPrix($facture);

		$relancesRepository = $this->getDoctrine()->getManager()->getRepository('App:Compta\Relance');
		$listRelances = $relancesRepository->findByFacture($facture);

		$numEcriture = null;
		foreach($facture->getJournalVentes() as $ligneVente){
			$numEcriture = $ligneVente->getNumEcriture();
		}

		return $this->render('crm/facture/crm_facture_voir.html.twig', array(
				'facture' => $facture,
				'lastNumEcriture' => $lastNumEcriture-1,
				'listPriseContacts' => $listPriseContacts,
				'listRelances' => $listRelances,
				'numEcriture' => $numEcriture
		));
	}

	/**
	 * @Route("/crm/facture/ajouter/{compteId}/{contactId}", name="crm_facture_ajouter")
	 */
	public function factureAjouterAction(CompteComptableService $compteComptableService, Request $request, $compteId = null, $contactId = null)
	{
		$em = $this->getDoctrine()->getManager();
		$compteRepo = $em->getRepository('App:CRM\Compte');
		$contactRepo = $em->getRepository('App:CRM\Contact');
		$settingsActivationRepo = $this->getDoctrine()->getManager()->getRepository('App:SettingsActivationOutil');

		$facture = new DocumentPrix($this->getUser()->getCompany(),'FACTURE', $em);
		$facture->setUserGestion($this->getUser());

		$compte = null;
		if($compteId){
			$compte = $compteRepo->find($compteId);
			$facture->setCompte($compteId);
		}
		$contact = null;
		if($contactId){
			$contact = $contactRepo->find($contactId);
			$facture->setContact($contactId);
		}

		$form = $this->createForm(FactureType::class, $facture, array(
            'userGestionId' => $facture->getUserGestion()->getId(),
            'companyId' => $this->getUser()->getCompany()->getId(),
            'compte' => $compte
        ));

		if($compte){
			$form->get('compte_name')->setData($compte->__toString());
		}
		if($contact){
			$form->get('contact_name')->setData($contact->__toString());
		}



		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {

			$em = $this->getDoctrine()->getManager();

			$data = $form->getData();
			$facture->setCompte($em->getRepository('App:CRM\Compte')->findOneById($data->getCompte()));
			$facture->setContact($em->getRepository('App:CRM\Contact')->findOneById($data->getContact()));
			$facture->setBonCommande($em->getRepository('App:CRM\BonCommande')->findOneById($form['bc_entity']->getData()));

			$settingsRepository = $em->getRepository('App:Settings');
			$settingsNum = $settingsRepository->findOneBy(array('module' => 'CRM', 'parametre' => 'NUMERO_FACTURE', 'company' => $this->getUser()->getCompany()));
			$currentNum = $settingsNum->getValeur();

			if($facture->getDateCreation() == null){
				$facture->setDateCreation(new \DateTime(date('Y-m-d')));
			}
			$factureYear = $facture->getDateCreation()->format('Y');

			if($factureYear != date("Y")){
				//si la facture est antidatée, récupérer le dernier numéro de facture de l'année concernée
				$prefixe = $factureYear.'-';
				$factureRepo = $em->getRepository('App:CRM\DocumentPrix');
				$lastNum = $factureRepo->findMaxNumForYear('FACTURE', $factureYear, $this->getUser()->getCompany());
				$lastNum = substr($lastNum, 5);
				$lastNum++;
				$facture->setNum($prefixe.$lastNum);
			} else {
				$prefixe = date('Y').'-';
				if($currentNum < 10){
					$prefixe.='00';
				} else if ($currentNum < 100){
					$prefixe.='0';
				}
				$facture->setNum($prefixe.$currentNum);

				//mise à jour du numéro de facture
				$currentNum++;
				$settingsNum->setValeur($currentNum);
				$em->persist($settingsNum);
			}

			$facture->setUserCreation($this->getUser());

			$activationCompta = $settingsActivationRepo->findOneBy(array(
				'company' => $this->getUser()->getCompany(),
				'outil' => 'COMPTA',
			));

			if($activationCompta){
				$facture->setCompta(true);
			}
			else{
				$facture->setCompta(false);
			}

			$em->persist($facture);

			//mettre type de relation commerciale du contact comme "client"
			if($facture->getContact()){
				$contact = $facture->getContact();
				$settingsRepository = $em->getRepository('App:Settings');
				$settingsClient = $settingsRepository->findOneBy(array(
					'company' => $this->getUser()->getCompany(),
					'parametre' => 'TYPE',
					'module' => 'CRM',
					'valeur' => 'Client'
				));
				$settingsProspect = $settingsRepository->findOneBy(array(
					'company' => $this->getUser()->getCompany(),
					'parametre' => 'TYPE',
					'module' => 'CRM',
					'valeur' => 'Prospect'
				));

				if($contact->hasTypeRelationCommerciale('PROSPECT')){
					$contact->removeSetting($settingsProspect);
					$em->persist($contact);
				}

				if(count($contact->getTypeRelationCommerciale()) == 0){
					$contact->addSetting($settingsClient);
					$em->persist($contact);
				}
					
			}

			$em->flush();

			if($activationCompta){

				//si le compte comptable du client n'existe pas, on le créé
				$compte = $facture->getCompte();
				if($compte->getClient() == false || $compte->getCompteComptableClient() == null){

					try {
						$compteComptable = $compteComptableService->createCompteComptableClient($compte);
					} catch(\Exception $e){
						return $this->redirect($this->generateUrl(
							'crm_facture_creer_compte_comptable', 
							array('id' => $facture->getId())
						));
					}

					$compte->setClient(true);
					$compte->setCompteComptableClient($compteComptable);
					$em->persist($compte);
					$em->flush();
				}

				//ecrire dans le journal de vente
				$this->journalVentesService->journalVentesAjouterFactureAction(null, $facture);
			}

			return $this->redirect($this->generateUrl(
				'crm_facture_voir',
				array('id' => $facture->getId())
			));

		}

		return $this->render('crm/facture/crm_facture_ajouter.html.twig', array(
			'form' => $form->createView(),
			'facture' => $facture
		));
	}

	/**
	 * @Route("/crm/facture/creer-compte-comptable/{id}", name="crm_facture_creer_compte_comptable")
	 */
	public function factureCreerCompteComptableAction(Request $request, DocumentPrix $facture)
	{
		$em = $this->getDoctrine()->getManager();
		$compteComptableRepo = $em->getRepository('App:Compta\CompteComptable');

		//find array of existing nums for this company
        $arr_nums = $compteComptableRepo->findAllNumForCompany($this->getUser()->getCompany());
        $arr_existings_nums = array();
        foreach($arr_nums as $arr){
            $arr_existings_nums[] = $arr['num'];
        }

        $compteComptable = new CompteComptable();
		$compteComptable->setCompany($this->getUser()->getCompany());
		$compteComptable->setNom($facture->getCompte()->getNom());
		$form = $this->createForm(CompteComptableType::class, $compteComptable);

		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {

			$num = $compteComptable->getNum();
			$num = '411'.strtoupper($num);
			$compteComptable->setNum($num);

			$em->persist($compteComptable);

			$compte = $facture->getCompte();
			$compte->setClient(true);
			$compte->setCompteComptableClient($compteComptable);
	
			$em->flush();

			//ecrire dans le journal de vente
			$this->journalVentesService->journalVentesAjouterFactureAction(null, $facture);

			return $this->redirect($this->generateUrl(
				'crm_facture_voir', 
				array('id' => $facture->getId())
			));
		}


        return $this->render('crm/facture/crm_facture_creer_compte_comptable.html.twig', array(
			'form' => $form->createView(),
			'facture' => $facture,
			'compteComptable' => $compteComptable,
			'arr_existings_nums' => $arr_existings_nums
		));
	}

	/**
	 * @Route("/crm/facture/editer/{id}", name="crm_facture_editer", options={"expose"=true})
	 */
	public function factureEditerAction(Request $request, DocumentPrix $facture)
	{
		$_compte = $facture->getCompte();
		$_contact = $facture->getContact();
	
		$dateCreation = $facture->getDateCreation();
		$facture->setCompte($_compte->getId());
		if($_contact!=null){
			$facture->setContact($_contact->getId());
		}

        $form = $this->createForm(FactureType::class, $facture, array(
            'userGestionId' => $facture->getUserGestion()->getId(),
            'companyId' => $this->getUser()->getCompany()->getId(),
        ));

        $form->get('compte_name')->setData($_compte->__toString());
		if($_contact!=null){
			$form->get('contact_name')->setData($_contact->__toString());
		}
		if($facture->getBonCommande() != null){
			$bc = $facture->getBonCommande();
			$form->get('bc_name')->setData($bc->getNum());
			$form->get('bc_entity')->setData($bc->getId());
		}

		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {

			$em = $this->getDoctrine()->getManager();

			$data = $form->getData();
			$facture->setCompte($em->getRepository('App:CRM\Compte')->findOneById($data->getCompte()));
			$facture->setContact($em->getRepository('App:CRM\Contact')->findOneById($data->getContact()));
			$facture->setBonCommande($em->getRepository('App:CRM\BonCommande')->findOneById($form['bc_entity']->getData()));
			$facture->setDateCreation($dateCreation);
			$facture->setDateEdition(new \DateTime(date('Y-m-d')));
			$facture->setUserEdition($this->getUser());

			if($facture->getEtat() == 'DRAFT'){
				if($facture->getNumBCInterne() != null){
					$facture->setEtat('READY');
				}
			}

			$em->persist($facture);

			$settingsActivationRepo = $this->getDoctrine()->getManager()->getRepository('App:SettingsActivationOutil');
			$activationCompta = $settingsActivationRepo->findOneBy(array(
					'company' => $this->getUser()->getCompany(),
					'outil' => 'COMPTA',
			));

			if($activationCompta){
				//supprimer les lignes du journal de vente
				$numEcriture = null;
				$journalVentesRepo = $em->getRepository('App:Compta\JournalVente');
				$arr_lignes = $journalVentesRepo->findByFacture($facture);
				foreach($arr_lignes as $ligne){
					$numEcriture = $ligne->getNumEcriture();
					$em->remove($ligne);
				}
				//ecrire dans le journal de vente
				$result = $this->journalVentesService->journalVentesAjouterFactureAction($numEcriture, $facture);
			}

			$em->flush();

			return $this->redirect($this->generateUrl(
					'crm_facture_voir',
					array('id' => $facture->getId())
			));
		}

		return $this->render('crm/facture/crm_facture_editer.html.twig', array(
				'form' => $form->createView(),
				'facture' => $facture
		));
	}

	/**
	 * @Route("/crm/facture/supprimer/{id}", name="crm_facture_supprimer", options={"expose"=true})
	 */
	public function factureSupprimerAction(Request $request, DocumentPrix $facture)
	{
		$form = $this->createFormBuilder()->getForm();

		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {

			$em = $this->getDoctrine()->getManager();

			//supprimer les lignes du journal de vente
			$journalVentesRepo = $em->getRepository('App:Compta\JournalVente');
			$arr_lignes = $journalVentesRepo->findByFacture($facture);
			foreach($arr_lignes as $ligne){
				$em->remove($ligne);
			}

			$em->remove($facture);

			$settingsRepository = $em->getRepository('App:Settings');
			$numSettings = $settingsRepository->findOneBy(array('module' => 'CRM', 'parametre' => 'NUMERO_FACTURE', 'company'=>$this->getUser()->getCompany()));
			$numSettings->setValeur($numSettings->getValeur() - 1);
			$em->persist($numSettings);

			$em->flush();

			$numEcriture = $this->numService->getNumEcriture($this->getUser()->getCompany());
			$numEcriture--;
			$this->numService->updateNumEcriture($this->getUser()->getCompany(), $numEcriture);

			return $this->redirect($this->generateUrl(
					'crm_facture_liste'
			));
		}

		return $this->render('crm/facture/crm_facture_supprimer.html.twig', array(
				'form' => $form->createView(),
				'facture' => $facture
		));
	}

	/**
	 * @Route("/crm/facture/exporter/{id}", name="crm_facture_exporter", options={"expose"=true})
	 */
	public function factureExporterAction(DocumentPrix $facture, UtilsService $utilsService)
	{
		if( is_null($facture->getNumBCInterne() and $facture->getBonCommande() == null) )
		{
			return $this->redirect($this->generateUrl(
					'crm_facture_voir', array('id' => $facture->getId())
			));
		}
		$settingsRepository = $this->getDoctrine()->getManager()->getRepository('App:Settings');
		$footerfacture = $settingsRepository->findOneBy(array('module' => 'CRM', 'parametre' => 'PIED_DE_PAGE_FACTURE', 'company'=>$this->getUser()->getCompany()));

		$arr_pub = array();
		$arr_pub['texte'] = $settingsRepository->findOneBy(array('module' => 'CRM', 'parametre' => 'PUB_FACTURE_TEXTE', 'company'=>$this->getUser()->getCompany()));
		$arr_pub['image'] = $settingsRepository->findOneBy(array('module' => 'CRM', 'parametre' => 'PUB_FACTURE_IMAGE', 'company'=>$this->getUser()->getCompany()));

		$contactAdmin = $settingsRepository->findOneBy(array('module' => 'CRM', 'parametre' => 'CONTACT_ADMINISTRATIF', 'company'=>$this->getUser()->getCompany()));
		$rib = $settingsRepository->findOneBy(array('module' => 'CRM', 'parametre' => 'RIB', 'company'=>$this->getUser()->getCompany()));

		$html = $this->renderView('crm/facture/crm_facture_exporter.html.twig',array(
			'facture' => $facture,
			'footer' => $footerfacture,
			'pub' => $arr_pub,
			'contact_admin' => $contactAdmin->getValeur(),
			'RIB' => $rib
		));

		$nomClient = $utilsService->removeSpecialChars($facture->getCompte()->getNom());
		
		$filename = $facture->getNum().'.'.$nomClient.'.pdf';
		return new Response(
				$this->getKnpSnappyPdf()->getOutputFromHtml($html,
						array(
								'margin-bottom' => '10mm',
								'margin-top' => '10mm',
								//'zoom' => 0.8, //prod only, zoom level is not the same on Windows
								'default-header'=>false,
								'javascript-delay'=> 400,
								'no-stop-slow-scripts' => true
						)
				),
				200,
				array(
						'Content-Type'          => 'application/pdf',
						'Content-Disposition'   => 'attachment; filename='.$filename,
				)
		);
	}

	private function _factureCreatePDF(DocumentPrix $facture)
	{

		$settingsRepository = $this->getDoctrine()->getManager()->getRepository('App:Settings');
		$footerFacture = $settingsRepository->findOneBy(array('module' => 'CRM', 'parametre' => 'PIED_DE_PAGE_FACTURE', 'company'=>$this->getUser()->getCompany()));

		$arr_pub = array();
		$arr_pub['texte'] = $settingsRepository->findOneBy(array('module' => 'CRM', 'parametre' => 'PUB_FACTURE_TEXTE', 'company'=>$this->getUser()->getCompany()));
		$arr_pub['image'] = $settingsRepository->findOneBy(array('module' => 'CRM', 'parametre' => 'PUB_FACTURE_IMAGE', 'company'=>$this->getUser()->getCompany()));

		$contactAdmin = $settingsRepository->findOneBy(array('module' => 'CRM', 'parametre' => 'CONTACT_ADMINISTRATIF', 'company'=>$this->getUser()->getCompany()));
		$rib = $settingsRepository->findOneBy(array('module' => 'CRM', 'parametre' => 'RIB'));

		$html = $this->renderView('crm/facture/crm_facture_exporter.html.twig',array(
				'facture' => $facture,
				'footer' => $footerFacture,
				'pub' => $arr_pub,
				'contact_admin' => $contactAdmin->getValeur(),
				'RIB' => $rib
		));

		$pdfFolder = $this->container->getParameter('kernel.root_dir').'/../public/files/crm/'.$this->getUser()->getCompany()->getId().'/facture/';

		$nomClient = $this->utilsService->removeSpecialChars($facture->getCompte()->getNom());
		$fileName =$pdfFolder.$facture->getNum().'.'.$nomClient.'.pdf';

		$this->getKnpSnappyPdf()->generateFromHtml($html, $fileName, array('javascript-delay' => 60), true);

		return $fileName;
	}

	/**
	 * @Route("/crm/facture/envoyer/{id}", name="crm_facture_envoyer")
	 */
	public function factureEnvoyerAction(Request $request, DocumentPrix $facture)
	{

		$form = $this->createFormBuilder()->getForm();

		$form->add('objet', \Symfony\Component\Form\Extension\Core\Type\TextType::class, array(
				'required' => true,
				'label' => 'Objet',
				'data' => 'Facture : '.$facture->getObjet()
		));

		$form->add('message', 'textarea', array(
				'required' => true,
				'label' => 'Message',
				'data' => $this->renderView('crm/facture/crm_facture_email.html.twig', array(
						'facture' => $facture
				)),
				'attr' => array('class' => 'tinymce')
		));

      $form->add('addcc', 'checkbox', array(
      		'required' => false,
			'label' => 'Recevoir une copie de l\'email'
      ));

		$form->add('submit', \Symfony\Component\Form\Extension\Core\Type\SubmitType::class, array(
				'label' => 'Envoyer',
				'attr' => array('class' => 'btn btn-success')
		));

		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {

			$objet = $form->get('objet')->getData();
			$message = $form->get('message')->getData();

			$filename = $this->_factureCreatePDF($facture);

			try{
				$mail = (new \Swift_Message)
				->setSubject($objet)
				->setFrom($this->getUser()->getEmail())
				->setTo($facture->getContact()->getEmail())
				->setBody($message, 'text/html')
				->attach(Swift_Attachment::fromPath($filename))
				;
				if( $form->get('addcc')->getData() ) $mail->addCc($this->getUser()->getEmail());
				$this->get('mailer')->send($mail);
				$this->get('session')->getFlashBag()->add(
						'success',
						'La facture a bien été envoyée.'
				);
				unlink($filename);

				$priseContact = new PriseContact();
				$priseContact->setType('FACTURE');
				$priseContact->setDate(new \DateTime(date('Y-m-d')));
				$priseContact->setDescription("Envoi de la facture");
				$priseContact->setDocumentPrix($facture);
				$priseContact->setContact($facture->getContact());
				$priseContact->setUser($this->getUser());
				$priseContact->setMessage($message);

				if($facture->getEtat() != 'SENT'){
					$facture->setEtat('SENT');
				}

				$em = $this->getDoctrine()->getManager();
				$em->persist($priseContact);
				$em->persist($facture);
				$em->flush();

			} catch(\Exception $e){
				$error =  $e->getMessage();
				$this->get('session')->getFlashBag()->add('danger', "L'email n'a pas été envoyé pour la raison suivante : $error");
			}


			return $this->redirect($this->generateUrl(
					'crm_facture_voir',
					array('id' => $facture->getId())
			));
		}

		return $this->render('crm/facture/crm_facture_envoyer.html.twig', array(
				'form' => $form->createView(),
				'facture' => $facture
		));
	}

	/**
	 * @Route("/crm/facture/dupliquer/{id}", name="crm_facture_dupliquer", options={"expose"=true})
	 */
	public function factureDupliquerAction(CompteComptableController $compteComptableService, DocumentPrix $facture)
	{
		if( (is_null($facture->getBonCommande() && is_null($facture->getNumBCInterne()))) || is_null($facture->getAnalytique()) )
		{
			return $this->redirect($this->generateUrl(
					'crm_facture_voir', array('id' => $facture->getId())
			));
		}
		$em = $this->getDoctrine()->getManager();
		$newFacture = clone $facture;
		$newFacture->setUserCreation($this->getUser());
		$newFacture->setDateCreation(new \DateTime(date('Y-m-d')));
		$newFacture->setObjet('COPIE '.$facture->getObjet());

		$settingsRepository = $em->getRepository('App:Settings');
		$settingsNum = $settingsRepository->findOneBy(array('module' => 'CRM', 'parametre' => 'NUMERO_FACTURE', 'company'=>$this->getUser()->getCompany()));
		$currentNum = $settingsNum->getValeur();

		$prefixe = date('Y').'-';
		if($currentNum < 10){
			$prefixe.='00';
		} else if ($currentNum < 100){
			$prefixe.='0';
		}
		$newFacture->setNum($prefixe.$currentNum);

		$currentNum++;
		$settingsNum->setValeur($currentNum);
		$em->persist($settingsNum);

        $cgv = $settingsRepository->findOneBy(array('module' => 'CRM', 'parametre' => 'CGV_FACTURE', 'company' => $this->getUser()->getCompany()));
        if($cgv){
            $newFacture->setCgv($cgv->getValeur());
        }

		$settingsActivationRepo = $this->getDoctrine()->getManager()->getRepository('App:SettingsActivationOutil');
		$activationCompta = $settingsActivationRepo->findOneBy(array(
				'company' => $this->getUser()->getCompany(),
				'outil' => 'COMPTA',
		));
		if($activationCompta){
			$newFacture->setCompta(true);
		}
		else{
			$newFacture->setCompta(false);
		}

		$em->persist($newFacture);

		//si le compte comptable du client n'existe pas, on le créé
		$compte = $newFacture->getCompte();
		if($compte->getClient() == false){

			$compteComptable = $compteComptableService->createCompteComptableForCompte('411', $compte->getNom());

			$em->persist($compteComptable);

			$compte->setClient(true);
			$compte->setCompteComptableClient($compteComptable);
			$em->persist($compte);
		}

		if($activationCompta){
			//ecrire dans le journal de vente
			$result = $this->journalVentesService->journalVentesAjouterFactureAction(null, $newFacture);
		}

		$em->flush();

		return $this->redirect($this->generateUrl(
				'crm_facture_voir',
				array('id' => $newFacture->getId())
		));
	}

	/**
	 * @Route("/crm/facture/export/evoliz", name="crm_facture_export_evoliz")
	 */
	public function factureExportEvolizAction(Request $request, Factory $phpExcel)
	{
		$form = $this->createFormBuilder()->getForm();

		$form->add('num', \Symfony\Component\Form\Extension\Core\Type\TextType::class, array(
				'required' => true,
				'label' => 'Exporter à partir de quel numéro de facture (inclus) ?',
		));

		$form->add('submit', \Symfony\Component\Form\Extension\Core\Type\SubmitType::class, array(
				'label' => 'Télécharger',
				'attr' => array('class' => 'btn btn-success')
		));

		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {

			$num = $form->get('num')->getData();
			$em = $this->getDoctrine()->getManager();
			$repository = $em->getRepository('App:CRM\DocumentPrix');
			$arr_factures = $repository->findForExportEvoliz($this->getUser()->getCompany(), $num);

			$arr_codesEvolizReq = $em->getRepository('App:CRM\Compte')->findAllCodesEvoliz();
			$arr_codesEvoliz = array();

			foreach($arr_codesEvolizReq as $arr_codeReq){
				$arr_codesEvoliz[] = $arr_codeReq['codeEvoliz'];
			}

			$path = __DIR__.'/../../../../public/files/crm/facture/';

			/***
			 * Creation du fichier d'import des clients dans Evoliz - on met les nouveaux en haut de la liste
			 ***/
			$fileName = 'template_evoliz_clients.xlsx';
			$phpExcelObject = $phpExcel->createPHPExcelObject($path.$fileName);
			$line = 2;
			$arr_contacts = array();
			foreach($arr_factures as $facture){

				$compte = $facture->getCompte();

				if(!in_array($compte->getId(), $arr_contacts)){

					if($compte->getCodeEvoliz() == null){

						$nbChars = 6;

						$newCodeEvoliz = mb_strtoupper(substr($compte->getNom(),0,$nbChars), 'UTF-8');
						$newCodeEvoliz = str_replace(" ", "-", $newCodeEvoliz);

						//replace code if it already exists
						while(in_array($newCodeEvoliz, $arr_codesEvoliz)){
							$nbChars++;
							$newCodeEvoliz = strtoupper(substr($compte->getNom(),0,$nbChars));
							$newCodeEvoliz = str_replace(" ", "-", $newCodeEvoliz);
						}

						$compte->setCodeEvoliz($newCodeEvoliz);
						$em->persist($compte);

						$phpExcelObject->getActiveSheet()
						->getStyle('A'.$line.':V'.$line)
						->applyFromArray(
								array(
										'font' => array(
											'color' => array('rgb' => 'EC741B')
										)
								)
						);


					}

					//code
					$phpExcelObject->setActiveSheetIndex(0)->setCellValue('A'.$line, (string)$compte->getCodeEvoliz());
					//nom
					$phpExcelObject->setActiveSheetIndex(0)->setCellValue('B'.$line, (string)$compte->getNom());
					//adresse
					$phpExcelObject->setActiveSheetIndex(0)->setCellValue('K'.$line, (string)$compte->getAdresse());
					//code postal
					$phpExcelObject->setActiveSheetIndex(0)->setCellValue('M'.$line, (string)$compte->getCodePostal());
					//ville
					$phpExcelObject->setActiveSheetIndex(0)->setCellValue('N'.$line, (string)$compte->getVille());
					//pays
					$phpExcelObject->setActiveSheetIndex(0)->setCellValue('O'.$line, (string)$compte->getPays());
					//telephone
					$phpExcelObject->setActiveSheetIndex(0)->setCellValue('V'.$line, (string)$compte->getTelephone());

					$arr_contacts[] = $compte->getId();

					$line++;
				}

			}

			$fileNameContacts = '1_evoliz_contacts.xlsx';

			$writer = $phpExcel->createWriter($phpExcelObject, 'Excel2007');
			$writer->save($path.$fileNameContacts);


			/***
			 * Creation du fichier d'import des factures dans Evoliz
			***/
			$fileName = 'template_evoliz_factures.xlsx';
			$phpExcelObject = $phpExcel->createPHPExcelObject($path.$fileName);
			$line = 2;
			foreach($arr_factures as $facture){

				$arr_produits = $facture->getProduits();
				foreach($arr_produits as $produit){
					//num facture
					$phpExcelObject->setActiveSheetIndex(0)->setCellValue('A'.$line, (string)$facture->getNum());
					//date facture
					$phpExcelObject->setActiveSheetIndex(0)->setCellValue('B'.$line, (string)($facture->getDateCreation()->format('d/m/Y')));
					//code client
					$phpExcelObject->setActiveSheetIndex(0)->setCellValue('C'.$line, (string)$facture->getCompte()->getCodeEvoliz());
					//code métier Evoliz
					$phpExcelObject->setActiveSheetIndex(0)->setCellValue('D'.$line, (string)($produit->getType()->getValeur()));
					//conditions de règlement
					$phpExcelObject->setActiveSheetIndex(0)->setCellValue('R'.$line, "30 jours");
					//ref
					$phpExcelObject->setActiveSheetIndex(0)->setCellValue('AA'.$line, (string)($produit->getType()->getValeur()));
					//designation
					$phpExcelObject->setActiveSheetIndex(0)->setCellValue('AB'.$line, (string)strip_tags($produit->getDescription()));
					//quantité
					$phpExcelObject->setActiveSheetIndex(0)->setCellValue('AC'.$line, (string)$produit->getQuantite());
					//tarif
					$phpExcelObject->setActiveSheetIndex(0)->setCellValue('AE'.$line, (string)$produit->getTarifUnitaire());
					//remise
					$remise = $produit->getRemise();
					if($produit->getRemise() == null){
						$remise = 0;
					}
					$phpExcelObject->setActiveSheetIndex(0)->setCellValue('AF'.$line, (string)$remise);
					//TVA
					$phpExcelObject->setActiveSheetIndex(0)->setCellValue('AG'.$line, (string)($facture->getTaxePercent()*100));

					$line++;
				}
			}

			$fileNameFactures = '2_evoliz_factures.xlsx';
			$writer = $phpExcel->createWriter($phpExcelObject, 'Excel2007');
			$writer->save($path.$fileNameFactures);


			/***
			 * Creation du fichier zip contenant les 2 fichiers Excel
			***/
			$zip = new \ZipArchive();
			$zipName = 'export_evoliz.zip';
			$zip->open($path.$zipName,  \ZipArchive::CREATE);

			$content = file_get_contents($path.$fileNameFactures);
			$zip->addFromString(basename($path.$fileNameFactures),$content);

			$content = file_get_contents($path.$fileNameContacts);
			$zip->addFromString(basename($path.$fileNameContacts),$content);

			$zip->close();

			$em->flush();

			return new Response($zipName, 200, array());

		}

		return $this->render('crm/facture/crm_facture_export_evoliz.html.twig', array(
			'form' => $form->createView(),
		));
	}

	/**
	 * @Route("/crm/facture/export", name="crm_facture_export")
	 */
	public function factureExportAction()
	{
		
		$em = $this->getDoctrine()->getManager();
		$repository = $em->getRepository('App:CRM\DocumentPrix');
		$arr_factures = $repository->findForCompany($this->getUser()->getCompany(), 'FACTURE', null, array('start' => new \DateTime('2017-01-01'), 'end' => new \DateTime('2017-12-31') ));
	

		$objPHPExcel = new PHPExcel();
		$objPHPExcel->getActiveSheet()->setTitle('factures');

		// header row
		$arr_header = array(
			'Numero',
			'Date',
			'Client',
			'Analytique',
			'Montant',
			'Objet',
		);
		$row = 1;
		$col = 'A';
		foreach($arr_header as $header){
			$objPHPExcel->getActiveSheet ()->setCellValue ($col.$row, (string)$header);
			$col++;
		}
		
		foreach($arr_factures as $facture){

			$col = 'A';
			$row++;

			$objPHPExcel->getActiveSheet ()->setCellValue ($col.$row, (string)$facture->getNum());
			$col++;

			$objPHPExcel->getActiveSheet ()->setCellValue ($col.$row, (string)$facture->getDateCreation()->format('d/m/Y'));
			$col++;

			$objPHPExcel->getActiveSheet ()->setCellValue ($col.$row, (string)$facture->getCompte()->getNom());
			$col++;

			$objPHPExcel->getActiveSheet ()->setCellValue ($col.$row, (string)$facture->getAnalytique()->getValeur());
			$col++;

			$objPHPExcel->getActiveSheet ()->setCellValue ($col.$row, (string)$facture->getTotalTTC());
			$col++;

			$objPHPExcel->getActiveSheet ()->setCellValue ($col.$row, (string)$facture->getObjet());
			$col++;

		}

		//set column width
		foreach(range('A','H') as $col) {
    		$objPHPExcel->getActiveSheet()->getColumnDimension($col)->setAutoSize(true);
		}

		$response = new Response();

		$response->headers->set('Content-Type', 'application/vnd.ms-excel');
		$response->headers->set('Content-Disposition', 'attachment;filename="factures.xlsx"');
		$response->headers->set('Cache-Control', 'max-age=0');
		$response->sendHeaders();
		$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		$objWriter->save('php://output');
		exit();

	}



}
