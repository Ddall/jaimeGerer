<?php

namespace App\Controller\Compta;

use App\Entity\Compta\Avoir;
use App\Entity\CRM\PriseContact;
use App\Form\Compta\AvoirType;
use App\Util\DependancyInjectionTrait\JournalAchatsTrait;
use App\Util\DependancyInjectionTrait\JournalVentesTrait;
use App\Util\DependancyInjectionTrait\NumServiceTrait;
use Swift_Attachment;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class AvoirController extends AbstractController
{

    use NumServiceTrait;
    use JournalAchatsTrait;
    use JournalVentesTrait;
    

    /**
	 * @Route("/compta/avoir/{type}", name="compta_avoir_liste")
	 */
	public function avoirListeAction($type)
	{
		$lastNumEcriture = $this->numService->getNumEcriture($this->getUser()->getCompany());

		return $this->render('compta/avoir/compta_avoir_liste.html.twig', array(
			'type' => $type,
			'lastNumEcriture' => $lastNumEcriture-1,
		));
	}

	/**
	 * @Route("/compta/avoir/liste/ajax/{type}",
	 *   name="compta_avoir_liste_ajax",
	 *   options={"expose"=true}
	 * )
	 */
	public function avoirListeAjaxAction(Request $requestData, $type)
	{
		$arr_sort = $requestData->get('order');
		$arr_cols = $requestData->get('columns');

		$col = $arr_sort[0]['column'];

		$repository = $this->getDoctrine()->getManager()->getRepository('App:Compta\Avoir');

		$arr_search = $requestData->get('search');
		$arr_date = $requestData->get('dateRange');

		$list = $repository->findForList(
				$this->getUser()->getCompany(),
				$type,
				$requestData->get('length'),
				$requestData->get('start'),
				$arr_cols[$col]['data'],
				$arr_sort[0]['dir'],
				$arr_search['value'],
				$arr_date
		);

		for($i=0; $i<count($list); $i++){

			$arr_a = $list[$i];

			$avoir = $repository->find($arr_a['id']);
			$totaux = $avoir->getTotaux();
			$list[$i]['totaux'] = $totaux;

			foreach($avoir->getJournalVentes() as $ligneVente){
				$list[$i]['num_ecriture'] = $ligneVente->getNumEcriture();
			}

			foreach($avoir->getJournalAchats() as $ligneAchat){
				$list[$i]['num_ecriture'] = $ligneAchat->getNumEcriture();
			}

		}

		$response = new JsonResponse();
		$response->setData(array(
				'draw' => intval( $requestData->get('draw') ),
				'recordsTotal' => $repository->custom_count($this->getUser()->getCompany(), $type),
				'recordsFiltered' => $repository->countForList($this->getUser()->getCompany(), $type, $arr_search['value'],$arr_date),
				'aaData' => $list,
		));

		return $response;
	}

	/**
	 * @Route("/compta/avoir/voir/{id}",
	 *   name="compta_avoir_voir",
	 *   options={"expose"=true}
	 * )
	 */
	public function avoirVoirAction(Avoir $avoir)
	{
		$lastNumEcriture = $this->numService->getNumEcriture($this->getUser()->getCompany());

		$numEcriture = null;
		foreach($avoir->getJournalVentes() as $ligneVente){
			$numEcriture = $ligneVente->getNumEcriture();
		}

		foreach($avoir->getJournalAchats() as $ligneAchat){
			$numEcriture = $ligneAchat->getNumEcriture();
		}

		return $this->render('compta/avoir/compta_avoir_voir.html.twig', array(
			'avoir' => $avoir,
			'numEcriture' => $numEcriture,
			'lastNumEcriture' => $lastNumEcriture-1
		));
	}


	/**
	 * @Route("/compta/avoir/ajouter/{type}", name="compta_avoir_ajouter")
	 */
	public function avoirAjouterAction(Request $request,  $type)
	{
		$em = $this->getDoctrine()->getManager();
		$avoir = new Avoir($type);
		$avoir->setUserGestion($this->getUser());

		$form = $this->createForm(AvoirType::class, $avoir, array(
		    'userGestionId' => $avoir->getUserGestion()->getId(),
            'companyId' => $this->getUser()->getCompany()->getId(),
            'type' => $type,
        ));

		$form->handleRequest($request);


		if ($form->isSubmitted() && $form->isValid()) {

			$em = $this->getDoctrine()->getManager();

			$avoir->setDateCreation(new \DateTime(date('Y-m-d')));
			$avoir->setUserCreation($this->getUser());

			$settingsRepository = $em->getRepository('App:Settings');

			if($type == "CLIENT"){
				$prefixe = 'A-';
				$settingsNum = $settingsRepository->findOneBy(array('module' => 'COMPTA', 'parametre' => 'NUMERO_AVOIR', 'company'=>$this->getUser()->getCompany()));
			} elseif($type == "FOURNISSEUR"){
				$prefixe = 'AF-';
				$settingsNum = $settingsRepository->findOneBy(array('module' => 'COMPTA', 'parametre' => 'NUMERO_AVOIR_FOURNISSEUR', 'company'=>$this->getUser()->getCompany()));
			}

			$prefixe.= date('Y').'-';
			$currentNum = $settingsNum->getValeur();

			if($currentNum < 10){
				$prefixe.='00';
			} else if ($currentNum < 100){
				$prefixe.='0';
			}

			$avoir->setNum($prefixe.$currentNum);
			$em->persist($avoir);

			$currentNum++;
			$settingsNum->setValeur($currentNum);
			$em->persist($settingsNum);

			$em->flush();

			if($type == "CLIENT"){
				$this->journalVentesService->journalVentesAjouterAvoirAction(null, $avoir);
			} else {
				$this->journalAchatService->journalAchatsAjouterAvoirAction(null, $avoir);
			}

			return $this->redirect($this->generateUrl(
					'compta_avoir_voir',
					array('id' => $avoir->getId())
			));

		}

		return $this->render('compta/avoir/compta_avoir_ajouter.html.twig', array(
				'form' => $form->createView(),
				'type' => $type
		));
	}

	/**
	 * @Route("/compta/avoir/editer/{id}",
	 *   name="compta_avoir_editer",
	 *   options={"expose"=true}
	 *  )
	 */
	public function avoirEditerAction(Request $request, Avoir $avoir)
	{

        $form = $this->createForm(AvoirType::class, $avoir, array(
            'userGestionId' => $avoir->getUserGestion()->getId(),
            'companyId' => $this->getUser()->getCompany()->getId(),
            'type' => $avoir->getType(),
        ));

		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {

			$em = $this->getDoctrine()->getManager();

			$avoir->setDateEdition(new \DateTime(date('Y-m-d')));
			$avoir->setUserEdition($this->getUser());

			$em->persist($avoir);
			
			$settingsActivationRepo = $this->getDoctrine()->getManager()->getRepository('App:SettingsActivationOutil');
			$activationCompta = $settingsActivationRepo->findOneBy(array(
					'company' => $this->getUser()->getCompany(),
					'outil' => 'COMPTA',
			));

			if($activationCompta){
				//supprimer les lignes du journal de vente
				$numEcriture = null;
				if($avoir->getType() == "CLIENT"){
					
					foreach($avoir->getJournalVentes() as $ligne){
						$numEcriture = $ligne->getNumEcriture();
						$em->remove($ligne);
					}
					//ecrire dans le journal de vente
					$result = $this->journalVentesService->journalVentesAjouterAvoirAction($numEcriture, $avoir);
				} else {
					foreach($avoir->getJournalAchats() as $ligne){
						$numEcriture = $ligne->getNumEcriture();
						$em->remove($ligne);
					}
					//ecrire dans le journal des achats
					$result = $this->journalAchatService->journalAchatsAjouterAvoirAction($numEcriture, $avoir);
				}
			}

			$em->flush();


			return $this->redirect($this->generateUrl(
					'compta_avoir_voir',
					array('id' => $avoir->getId())
			));
		}

		return $this->render('compta/avoir/compta_avoir_editer.html.twig', array(
				'form' => $form->createView(),
				'type' => $avoir->getType()
		));
	}

	/**
	 * @Route("/compta/avoir/supprimer/{id}",
	 *   name="compta_avoir_supprimer",
	 *   options={"expose"=true}
	 * )
	 */
	public function avoirSupprimerAction(Request $request, Avoir $avoir)
	{
		$form = $this->createFormBuilder()->getForm();

		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {

			$em = $this->getDoctrine()->getManager();


			$settingsRepository = $em->getRepository('App:Settings');
			if($avoir->getType() == "CLIENT"){
				$settingsNum = $settingsRepository->findOneBy(array('module' => 'COMPTA', 'parametre' => 'NUMERO_AVOIR', 'company'=>$this->getUser()->getCompany()));
			} else {
				$settingsNum = $settingsRepository->findOneBy(array('module' => 'COMPTA', 'parametre' => 'NUMERO_AVOIR_FOURNISSEUR', 'company'=>$this->getUser()->getCompany()));
			}

			$settingsNum->setValeur($settingsNum->getValeur() - 1);
			$em->persist($settingsNum);

			$em->remove($avoir);
			$em->flush();

			$numEcriture = $this->numService->getNumEcriture($this->getUser()->getCompany());
			$numEcriture--;
			$this->numService->updateNumEcriture($this->getUser()->getCompany(), $numEcriture);

			return $this->redirect($this->generateUrl(
				'compta_avoir_liste', array(
					'type' => $avoir->getType()
				)
			));
		}

		return $this->render('compta/avoir/compta_avoir_supprimer.html.twig', array(
			'form' => $form->createView(),
			'avoir' => $avoir
		));
	}

	/**
	 * @Route("/compta/avoir/exporter/{id}", name="compta_avoir_exporter")
	 */
	public function avoirExporterAction(Avoir $avoir)
	{
		$settingsRepository = $this->getDoctrine()->getManager()->getRepository('App:Settings');
		$footerfacture = $settingsRepository->findOneBy(array('module' => 'CRM', 'parametre' => 'PIED_DE_PAGE_FACTURE', 'company'=>$this->getUser()->getCompany()));

		$contactAdmin = $settingsRepository->findOneBy(array('module' => 'CRM', 'parametre' => 'CONTACT_ADMINISTRATIF', 'company'=>$this->getUser()->getCompany()));

		$html = $this->renderView('compta/avoir/compta_avoir_exporter.html.twig',array(
				'avoir' => $avoir,
				'footer' => $footerfacture,
				'contact_admin' => $contactAdmin->getValeur(),
		));

		$nomClient = strtolower(str_ireplace(' ','', $avoir->getFacture()->getCompte()->getNom()));
		$filename = $avoir->getNum().'.'.$nomClient.'.pdf';

		return new Response(
				$this->get('knp_snappy.pdf')->getOutputFromHtml($html,
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

	private function _avoirCreatePDF(Avoir $avoir)
	{
		$settingsRepository = $this->getDoctrine()->getManager()->getRepository('App:Settings');
		$footerFacture = $settingsRepository->findOneBy(array('module' => 'CRM', 'parametre' => 'PIED_DE_PAGE_FACTURE', 'company'=>$this->getUser()->getCompany()));

		$contactAdmin = $settingsRepository->findOneBy(array('module' => 'CRM', 'parametre' => 'CONTACT_ADMINISTRATIF', 'company'=>$this->getUser()->getCompany()));

		$html = $this->renderView('compta/avoir/compta_avoir_exporter.html.twig',array(
				'avoir' => $avoir,
				'footer' => $footerFacture,
				'contact_admin' => $contactAdmin->getValeur(),
		));

		$pdfFolder = $this->container->getParameter('kernel.root_dir').'/../web/files/compta/'.$this->getUser()->getCompany()->getId().'/avoir/';

		$nomClient = strtolower(str_ireplace(' ','', $avoir->getFacture()->getCompte()->getNom()));
		$fileName =$pdfFolder.$avoir->getNum().'.'.$nomClient.'.pdf';

		$this->get('knp_snappy.pdf')->generateFromHtml($html, $fileName, array('javascript-delay' => 60), true);

		return $fileName;
	}

	/**
	 * @Route("/compta/avoir/envoyer/{id}", name="compta_avoir_envoyer")
	 */
	public function avoirEnvoyerAction(Request $request, Avoir $avoir)
	{

		$form = $this->createFormBuilder()->getForm();

		$form->add('objet', \Symfony\Component\Form\Extension\Core\Type\TextType::class, array(
				'required' => true,
				'label' => 'Objet',
				'data' => 'Avoir sur la facture '.$avoir->getFacture()->getNum()
		));

		$form->add('message', 'textarea', array(
				'required' => true,
				'label' => 'Message',
				'data' => $this->renderView('compta/avoir/compta_avoir_email.html.twig', array(
						'avoir' => $avoir
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

			$filename = $this->_avoirCreatePDF($avoir);

			try{
				$mail = (new \Swift_Message)
				->setSubject($objet)
				->setFrom($this->getUser()->getEmail())
				->setTo($avoir->getFacture()->getContact()->getEmail())
				->setBody($message, 'text/html')
				->attach(Swift_Attachment::fromPath($filename))
				;
				if( $form->get('addcc')->getData() ) $mail->addCc($this->getUser()->getEmail());
				$this->get('mailer')->send($mail);
				$this->get('session')->getFlashBag()->add(
						'success',
						'L\'avoir a bien été envoyé.'
				);
				unlink($filename);

				$priseContact = new PriseContact();
				$priseContact->setType('AVOIR');
				$priseContact->setDate(new \DateTime(date('Y-m-d')));
				$priseContact->setDescription("Envoi de l'avoir");
				$priseContact->setAvoir($avoir);
				$priseContact->setContact($avoir->getFacture()->getContact());
				$priseContact->setUser($this->getUser());
				$priseContact->setMessage($message);

				$em = $this->getDoctrine()->getManager();
				$em->persist($priseContact);
				$em->flush();

			} catch(\Exception $e){
				$error =  $e->getMessage();
				$this->get('session')->getFlashBag()->add('danger', "L'email n'a pas été envoyé pour la raison suivante : $error");
			}


			return $this->redirect($this->generateUrl(
					'compta_avoir_voir',
					array('id' => $avoir->getId())
			));
		}

		return $this->render('compta/avoir/compta_avoir_envoyer.html.twig', array(
				'form' => $form->createView(),
				'avoir' => $avoir
		));
	}

	/**
	 * @Route("/compta/avoir/get-liste/{type}",
	 *   name="compta_avoir_get_liste",
	 *   options={"expose"=true}
	 * )
	 */
	public function avoirGetListeAction($type) {

		$repository = $this->getDoctrine()->getManager()->getRepository('App:Compta\Avoir');

		$list = $repository->findForCompany($type, $this->getUser()->getCompany());

		$res = array();
		foreach ($list as $avoir) {

			$_res = array('id' => $avoir->getId(), 'display' => $avoir->getNum());
			$res[] = $_res;
		}

		$response = new \Symfony\Component\HttpFoundation\Response(json_encode($res));
		$response->headers->set('Content-Type', 'application/json');
		return $response;
	}

}
