<?php

namespace App\Controller\CRM;

use App\Controller\Compta\CompteComptableController;
use App\Controller\Compta\JournalVentesController;
use App\Entity\CRM\DocumentPrix;
use App\Entity\CRM\Impulsion;
use App\Entity\CRM\PriseContact;
use App\Form\CRM\DevisType;
use App\Util\DependancyInjectionTrait\KnpSnappyPDFTrait;
use App\Util\DependancyInjectionTrait\OpportuniteServiceTrait;
use App\Util\DependancyInjectionTrait\UtilsServiceTrait;
use Swift_Attachment;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DevisController extends AbstractController
{
    const FILTER_DATE_NONE      = 0;
    const FILTER_DATE_MONTH     = 1;
    const FILTER_DATE_2MONTH    = 2;
    const FILTER_DATE_YEAR      = 3;
    const FILTER_DATE_CUSTOM    = -1;

    use UtilsServiceTrait;
    use OpportuniteServiceTrait;
    use KnpSnappyPDFTrait;

	/**
	 * @Route("/crm/devis/liste/ajax/{etat}",
	 *   name="crm_devis_liste_ajax",
	 *   options={"expose"=true}
	 *  )
	 */
	public function devisListeAjaxAction(Request $requestData, $etat = null)
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
				'DEVIS',
				$requestData->get('length'),
				$requestData->get('start'),
				$arr_cols[$col]['data'],
				$arr_sort[0]['dir'],
				$arr_search['value'],
				$etat,
                null,
                null,
                $dateRange
		);

		for($i=0; $i<count($list); $i++){

			$arr_d = $list[$i];

			$devis = $repository->find($arr_d['id']);
			$totaux = $devis->getTotaux();
			$list[$i]['totaux'] = $totaux;
		}

		$response = new JsonResponse();
		$response->setData(array(
				'draw' => intval( $requestData->get('draw') ),
				'recordsTotal' => $repository->custom_count($this->getUser()->getCompany(), 'DEVIS', $etat),
				'recordsFiltered' => $repository->countForList($this->getUser()->getCompany(), 'DEVIS',
                                                               $arr_search['value'], $etat, null, null,
                                                               $dateRange),
				'aaData' => $list,
		));

		return $response;
	}

	/**
	 * @Route("/crm/devis/liste/{etat}", name="crm_devis_liste")
	 */
	public function devisListeAction($etat = null)
	{
		return $this->render('crm/devis/crm_devis_liste.html.twig', array(
				'etat' => $etat
		));
	}

	/**
	 * @Route("/crm/devis/voir/{id}",
	 *   name="crm_devis_voir",
	 *   options={"expose"=true}
	 * )
	 */
	public function devisVoirAction(DocumentPrix $devis)
	{
		$repository = $this->getDoctrine()->getManager()->getRepository('App:CRM\DocumentPrix');
		//~ $facture = $repository->findOneByDevis($devis);
		$facture = $repository->findByDevis($devis);

		$priseContactsRepository = $this->getDoctrine()->getManager()->getRepository('App:CRM\PriseContact');
		$listPriseContacts = $priseContactsRepository->findByDocumentPrix($devis);

		return $this->render('crm/devis/crm_devis_voir.html.twig', array(
				'devis' => $devis,
				'facture' => $facture,
				'listPriseContacts' => $listPriseContacts
		));
	}

	/**
	 * @Route("/crm/devis/ajouter", name="crm_devis_ajouter")
	 */
	public function devisAjouterAction(Request $request)
	{
		$em = $this->getDoctrine()->getManager();
		$devis = new DocumentPrix($this->getUser()->getCompany(),'DEVIS', $em);
		$devis->setUserGestion($this->getUser());

		$form = $this->createForm(DevisType::class, $devis, array(
		    'userGestionId' => $devis->getUserGestion()->getId(),
            'companyId' => $this->getUser()->getCompany()->getId(),
        ));

		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {

			$data = $form->getData();

			$devis->setCompte($em->getRepository('App:CRM\Compte')->findOneById($data->getCompte()));
			$devis->setContact($em->getRepository('App:CRM\Contact')->findOneById($data->getContact()));

			$devis->setType("DEVIS");

			$devis->setDateCreation(new \DateTime(date('Y-m-d')));
			$devis->setUserCreation($this->getUser());

			$settingsRepository = $em->getRepository('App:Settings');
			$settingsNum = $settingsRepository->findOneBy(array('company' => $this->getUser()->getCompany(), 'module' => 'CRM', 'parametre' => 'NUMERO_DEVIS'));
			$currentNum = $settingsNum->getValeur();

			$prefixe = date('Y').'-';
			if($currentNum < 10){
				$prefixe.='00';
			} else if ($currentNum < 100){
				$prefixe.='0';
			}
			$devis->setNum($prefixe.$currentNum);
			$em->persist($devis);

			$currentNum++;
			$settingsNum->setValeur($currentNum);
			$em->persist($settingsNum);

			$em->flush();

			return $this->redirect($this->generateUrl(
					'crm_devis_voir',
					array('id' => $devis->getId())
			));
		}

		return $this->render('crm/devis/crm_devis_ajouter.html.twig', array(
				'form' => $form->createView()
		));
	}

	/**
	 * @Route("/crm/devis/editer/{id}",
	 *   name="crm_devis_editer",
	 *   options={"expose"=true}
	 * )
	 */
	public function devisEditerAction(Request $request, DocumentPrix $devis)
	{
		$_compte = $devis->getCompte();
		$_contact = $devis->getContact();
		$devis->setCompte($_compte->getId());
		if($_contact != null){
			$devis->setContact($_contact->getId());
		}

        $form = $this->createForm(DevisType::class, $devis, array(
            'userGestionId' => $devis->getUserGestion()->getId(),
            'companyId' => $this->getUser()->getCompany()->getId(),
        ));

		$form->get('compte_name')->setData($_compte->__toString());
		if($_contact != null){
			$form->get('contact_name')->setData($_contact->__toString());
		}

		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {

			$em = $this->getDoctrine()->getManager();

			$data = $form->getData();

			$devis->setCompte($em->getRepository('App:CRM\Compte')->findOneById($data->getCompte()));
			$devis->setContact($em->getRepository('App:CRM\Contact')->findOneById($data->getContact()));

			$devis->setDateEdition(new \DateTime(date('Y-m-d')));
			$devis->setUserEdition($this->getUser());
			$em = $this->getDoctrine()->getManager();
			$em->persist($devis);
			$em->flush();

			return $this->redirect($this->generateUrl(
					'crm_devis_voir',
					array('id' => $devis->getId())
			));
		}

		return $this->render('crm/devis/crm_devis_editer.html.twig', array(
				'form' => $form->createView()
		));
	}

	/**
	 * @Route("/crm/devis/supprimer/{id}",
	 *   name="crm_devis_supprimer",
	 *   options={"expose"=true}
	 * )
	 */
	public function devisSupprimerAction(Request $request, DocumentPrix $devis)
	{
		$form = $this->createFormBuilder()->getForm();

		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {

			$em = $this->getDoctrine()->getManager();
			$em->remove($devis);
			$em->flush();

			return $this->redirect($this->generateUrl(
					'crm_devis_liste'
			));
		}

		return $this->render('crm/devis/crm_devis_supprimer.html.twig', array(
				'form' => $form->createView(),
				'devis' => $devis
		));
	}

	/**
	 * @Route("/crm/devis/exporter/{id}",
	 *   name="crm_devis_exporter",
	 *   options={"expose"=true}
	 * )
	 */
	public function devisExporterAction(DocumentPrix $devis)
	{

		$settingsRepository = $this->getDoctrine()->getManager()->getRepository('App:Settings');
		$footerDevis = $settingsRepository->findOneBy(array('company' => $this->getUser()->getCompany(), 'module' => 'CRM', 'parametre' => 'PIED_DE_PAGE_DEVIS'));

		$contactAdmin = $settingsRepository->findOneBy(array('company' => $this->getUser()->getCompany(), 'module' => 'CRM', 'parametre' => 'CONTACT_ADMINISTRATIF'));

		$html = $this->renderView('crm/devis/crm_devis_exporter.html.twig',array(
				'devis' => $devis,
				'footer' => $footerDevis,
				'contact_admin' => $contactAdmin,
		));

		$nomClient = $this->utilsService->removeSpecialChars($devis->getCompte()->getNom());
		$filename = $devis->getNum().'.'.$nomClient.'.pdf';

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

	private function _devisCreatePDF(DocumentPrix $devis)
	{

		$settingsRepository = $this->getDoctrine()->getManager()->getRepository('App:Settings');
		$footerDevis = $settingsRepository->findOneBy(array('company' => $this->getUser()->getCompany(), 'module' => 'CRM', 'parametre' => 'PIED_DE_PAGE_DEVIS'));

		$contactAdmin = $settingsRepository->findOneBy(array('company' => $this->getUser()->getCompany(), 'module' => 'CRM', 'parametre' => 'CONTACT_ADMINISTRATIF'));

		$html = $this->renderView('crm/devis/crm_devis_exporter.html.twig',array(
				'devis' => $devis,
				'footer' => $footerDevis,
				'contact_admin' => $contactAdmin->getValeur(),
		));

		$filename = 'devis_'.$devis->getNum().'.pdf';

		$pdfFolder = $this->container->getParameter('kernel.root_dir').'/../public/files/crm/'.$this->getUser()->getCompany()->getId().'/devis/';
		$nomClient = $this->utilsService->removeSpecialChars($devis->getCompte()->getNom());
		$fileName =$pdfFolder.$devis->getNum().'.'.$nomClient.'.pdf';

		$this->getKnpSnappyPdf()->generateFromHtml($html, $fileName, array('javascript-delay' => 60), true);

		return $fileName;
	}

	/**
	 * @Route("/crm/devis/implusion/{id}", name="crm_devis_impulsion")
	 */
	public function devisImpulsionAction(Request $request, DocumentPrix $devis)
	{
		$impulsion = new Impulsion();
		$impulsion->setUser($this->getUser());
		$contact = $devis->getContact();

    	$arr_delaiNum = array();
    	for($i=1; $i<13; $i++){
    		$arr_delaiNum[$i] = $i;
    	}

    	$arr_delaiUnit= array(
    			'DAY' => 'jours',
    			'WEEK' => 'semaines',
    			'MONTH' => 'mois');

		$form = $this->createFormBuilder()->getForm();

		$form->add('delaiNum', 'integer', array(
           		'label' => 'Contacter tous les',
           		'required' => true,
     		))
           	->add('delaiUnit', \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, array(
           			'choices' => $arr_delaiUnit,
           			'label_attr' => array('class' => 'invisible'),
           			'required' => true,
           	));


		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid() ) {

			$em = $this->getDoctrine()->getManager();
			$data = $form->getData();
			$impulsion->setDelaiUnit($data['delaiUnit']);
			$impulsion->setDelaiNum($data['delaiNum']);
			$impulsion->setContact($devis->getContact());

			$impulsion->setUser($this->getUser());
			$impulsion->setDateCreation(new \DateTime(date('Y-m-d')));

			$em->persist($impulsion);
			$em->flush();
			echo 1; exit;
		}

		return $this->render('crm/devis/crm_devis_impulsion.html.twig', array(
				'form' => $form->createView(),
				'devis'=> $devis
		));

	}

	/**
	 * @Route("/crm/devis/convertir/{id}", name="crm_devis_convertir")
	 */
	public function devisConvertirAction(Request $request, JournalVentesController $journalVenteService, CompteComptableController $compteComptableService, DocumentPrix $devis)
	{
		$form = $this->createFormBuilder()->getForm();

		$form->add('objet', \Symfony\Component\Form\Extension\Core\Type\TextType::class, array(
			'required' => true,
			'label' => 'Objet',
			'data' => $devis->getObjet()
		));

		$form->add('submit', \Symfony\Component\Form\Extension\Core\Type\SubmitType::class, array(
  		  'label' => 'Enregistrer',
		  'attr' => array('class' => 'btn btn-success')
		));

		//~ $form->setAction($this->router->generate('crm_devis_convertir', array('id' => $devis->getId()) ));

		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {

			$em = $this->getDoctrine()->getManager();
			$data = $form->getData();
			$facture = clone $devis;

            $devis->setEtat("WON");

			$settingsRepository = $em->getRepository('App:Settings');
			$settingsNum = $settingsRepository->findOneBy(array('module' => 'CRM', 'parametre' => 'NUMERO_FACTURE', 'company'=>$this->getUser()->getCompany()));
			$currentNum = $settingsNum->getValeur();

			$facture->setType('FACTURE');
			$facture->setObjet($data['objet']);
			$facture->setDevis($devis);
			$facture->setDateCreation(new \DateTime(date('Y-m-d')));
			$facture->setUserCreation($this->getUser());

			$settingsCGV = $settingsRepository->findOneBy(array('module' => 'CRM', 'parametre' => 'CGV_FACTURE', 'company'=>$this->getUser()->getCompany()));
			$facture->setCgv($settingsCGV->getValeur());

			$prefixe = date('Y').'-';
			if($currentNum < 10){
				$prefixe.='00';
			} else if ($currentNum < 100){
				$prefixe.='0';
			}
			$facture->setNum($prefixe.$currentNum);
			$em->persist($facture);

			$currentNum++;
			$settingsNum->setValeur($currentNum);

      		$settingsActivationRepo = $this->getDoctrine()->getManager()->getRepository('App:SettingsActivationOutil');
			$activationCompta = $settingsActivationRepo->findOneBy(array(
					'company' => $this->getUser()->getCompany(),
					'outil' => 'COMPTA',
			));
			if($activationCompta){
				$facture->setCompta(true);
		        //si le compte comptable du client n'existe pas, on le créé
		        $compte = $facture->getCompte();
		        if($compte->getClient() == false || $compte->getCompteComptableClient() == null){

		          $compteComptable = $compteComptableService->createCompteComptableForCompte('411', $compte->getNom());

		          $em->persist($compteComptable);

		          $compte->setClient(true);
		          $compte->setCompteComptableClient($compteComptable);
		          $em->persist($compte);
		         }
			} else{
				$facture->setCompta(false);
			}

			if($devis->getOpportunite()){
				foreach($devis->getOpportunite()->getBonsCommande() as $bonCommande){
					$facture->addBonCommande($bonCommande);
				}
			}

			$em->persist($facture);
      		$em->persist($devis);
			$em->persist($settingsNum);

			//ecrire dans le journal de vente
			$journalVenteService->journalVentesAjouterFactureAction(null, $facture);

			$em->flush();

			return $this->redirect($this->generateUrl(
					'crm_facture_voir',
					array('id' => $facture->getId())
			));
		}
		return $this->render('crm/devis/crm_devis_convertir.html.twig', array(
				'form' 		=> $form->createView(),
				'devis'		=> $devis
		));
	}

	/**
	 * @Route("/crm/devis/envoyer/{id}", name="crm_devis_envoyer")
	 */
	public function devisEnvoyerAction(Request $request, DocumentPrix $devis)
	{

		$form = $this->createFormBuilder()->getForm();

		$form->add('objet', \Symfony\Component\Form\Extension\Core\Type\TextType::class, array(
			'required' => true,
			'label' => 'Objet',
			'data' => 'Devis : '.$devis->getObjet()
		));

		$form->add('message', 'textarea', array(
				'required' => true,
				'label' => 'Message',
				'data' => $this->renderView('crm/devis/crm_devis_email.html.twig', array(
					'devis' => $devis
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

			$filename = $this->_devisCreatePDF($devis);
			
			try{
				$mail = (new \Swift_Message)
					->setSubject($objet)
					->setFrom($this->getUser()->getEmail())
					->setTo($devis->getContact()->getEmail())
					->setBody($message, 'text/html')
					->attach(Swift_Attachment::fromPath($filename))
				;
				if( $form->get('addcc')->getData() ) $mail->addCc($this->getUser()->getEmail());
				$this->get('mailer')->send($mail);
				$this->get('session')->getFlashBag()->add(
						'success',
						'Le devis a bien été envoyé.'
				);

				unlink($filename);

				$priseContact = new PriseContact();
				$priseContact->setType('DEVIS');
				$priseContact->setDate(new \DateTime(date('Y-m-d')));
				$priseContact->setDescription("Envoi du devis");
				$priseContact->setDocumentPrix($devis);
				$priseContact->setContact($devis->getContact());
				$priseContact->setUser($this->getUser());
				$priseContact->setMessage($message);

				if($devis->getEtat() != 'SENT'){
					$devis->setEtat('SENT');
				}

				$em = $this->getDoctrine()->getManager();
				$em->persist($priseContact);
				$em->persist($devis);
				$em->flush();

			} catch(\Exception $e){
    			$error =  $e->getMessage();
    			$this->get('session')->getFlashBag()->add('danger', "L'email n'a pas été envoyé pour la raison suivante : $error");
    		}


			return $this->redirect($this->generateUrl(
					'crm_action_commerciale_voir',
					array('id' => $devis->getOpportunite()->getId())
			));
		}

		return $this->render('crm/devis/crm_devis_envoyer.html.twig', array(
				'form' => $form->createView(),
				'devis' => $devis
		));
	}

	// /**
	//  * @Route("/crm/devis/dupliquer/{id}",
	//  *   name="crm_devis_dupliquer",
	//  *   options={"expose"=true}
	//  * )
	//  */
	// public function devisDupliquerAction(DocumentPrix $devis)
	// {
	// 	$em = $this->getDoctrine()->getManager();
	// 	$newDevis = clone $devis;
	// 	$newDevis->setUserCreation($this->getUser());
	// 	$newDevis->setObjet('COPIE '.$devis->getObjet());

	// 	$settingsRepository = $em->getRepository('App:Settings');
	// 	$settingsNum = $settingsRepository->findOneBy(array('module' => 'CRM', 'parametre' => 'NUMERO_DEVIS', 'company'=>$this->getUser()->getCompany()));
	// 	$currentNum = $settingsNum->getValeur();

	// 	$prefixe = date('Y').'-';
	// 	if($currentNum < 10){
	// 		$prefixe.='00';
	// 	} else if ($currentNum < 100){
	// 		$prefixe.='0';
	// 	}
	// 	$newDevis->setNum($prefixe.$currentNum);

	// 	$currentNum++;
	// 	$settingsNum->setValeur($currentNum);
	// 	$em->persist($settingsNum);

	// 	$em->persist($newDevis);
	// 	$em->flush();

	// 	return $this->redirect($this->generateUrl(
	// 			'crm_devis_voir',
	// 			array('id' => $newDevis->getId())
	// 	));
	// }

	/**
	 * @Route("/crm/devis/gagner/{id}",
	 *   name="crm_devis_gagner",
	 *   options={"expose"=true}
	 * )
	 */
	public function devisGagner(DocumentPrix $devis)
	{

		$this->opportuniteService->win($devis);

		return $this->redirect($this->generateUrl(
				'crm_devis_voir',
				array('id' => $devis->getId())
		));
	}

	/**
	 * @Route("/crm/devis/perdre/{id}",
	 *   name="crm_devis_perdre",
	 *   options={"expose"=true}
	 * )
	 */
	public function devisPerdre(DocumentPrix $devis)
	{

		$this->opportuniteService->lose($devis);

		return $this->redirect($this->generateUrl(
				'crm_devis_voir',
				array('id' => $devis->getId())
		));
	}

	/**
	 * @Route("/crm/devis/analytique",
	 *   name="crm_devis_analytique"
	 * )
	 */
	public function devisAnalytique()
	{
		$em = $this->getDoctrine()->getManager();
		$devisRepo = $em->getRepository('App:CRM\DocumentPrix');
		$settingsRepo = $em->getRepository('App:Settings');

		$arr_settings_analytiques = $settingsRepo->findBy(array(
			'company' => $this->getUser()->getCompany(),
			'parametre' => 'ANALYTIQUE'
		));

		$arr_analytique = array();
		foreach($arr_settings_analytiques as $a){
			$arr_analytique[$a->getValeur()] = $a;
		}

		$arr_devis = $devisRepo->findForCompany($this->getUser()->getCompany(), 'DEVIS');
		foreach($arr_devis as $devis){
			$produit = $devis->getProduits()[0];
			if($produit->getType()){
				$analytique = $arr_analytique[$produit->getType()->getValeur()];
				$devis->setAnalytique($analytique);
				$em->persist($devis);
				$em->flush();
			}
			
		}

		return new Response();
	}

}
