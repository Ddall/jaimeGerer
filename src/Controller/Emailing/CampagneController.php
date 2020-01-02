<?php

namespace App\Controller\Emailing;

use App\Entity\Emailing\Campagne;
use App\Entity\Emailing\CampagneContact;
use App\Form\Emailing\CampagneContenuType;
use App\Form\Emailing\CampagneDateEnvoiType;
use App\Form\Emailing\CampagneDestinatairesType;
use App\Form\Emailing\CampagneType;
use App\Util\DependancyInjectionTrait\MailgunServiceTrait;
use cspoo_swiftmailer_mailgun;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;


class CampagneController extends AbstractController
{

    use MailgunServiceTrait;

    /**
	 * @Route("/emailing", name="emailing_index")
	 */ 
	public function indexAction()
	{
		if(!$this->getUser()->hasRole('ROLE_COMMUNICATION')){
			throw new AccessDeniedException;
		}

		//return $this->render('emailing/emailing_index.html.twig');
		return $this->redirect($this->generateUrl(
			'emailing_campagne_liste'
		));
	}
	
	/**
	 * @Route("/emailing/campagne/liste", name="emailing_campagne_liste")
	 */
	public function campagneListeAction()
	{ 
		return $this->render('emailing/campagne/emailing_campagne_liste.html.twig'); 
	}
	
	/**
	 * @Route("/emailing/campagne/liste/ajax", name="emailing_campagne_liste_ajax", options={"expose"=true})
	 */
	public function campagneListeAjaxAction(Request $requestData)
	{
		$arr_sort = $requestData->get('order');
		$arr_cols = $requestData->get('columns');
	
		$col = $arr_sort[0]['column'];
	
		$repository = $this->getDoctrine()->getManager()->getRepository('App:Emailing\Campagne');
	
		$arr_search = $requestData->get('search');
	
		$list = $repository->findForList(
			$this->getUser()->getCompany(),
			$requestData->get('length'),
			$requestData->get('start'),
			$arr_search['value']
		);

		for($i = 0; $i<count($list); $i++){
			$id = $list[$i]['id'];
			$campagne = $repository->find($id);
			$list[$i]['nbContacts'] = $campagne->getNbDestinataires();
		}

		$response = new JsonResponse();
		$response->setData(array(
			'draw' => intval( $requestData->get('draw') ),
			'recordsTotal' => $repository->custom_count($this->getUser()->getCompany()),
			'recordsFiltered' => $repository->countForList($this->getUser()->getCompany(), $arr_search['value']),
			'aaData' => $list,
		));
	
		return $response;
	}

	/**
	 * @Route("/emailing/campagne/voir/{id}", name="emailing_campagne_voir", options={"expose"=true})
	 */
	public function campagneVoirAction(Campagne $campagne)
	{
		return $this->render('emailing/campagne/emailing_campagne_voir.html.twig', array(
			'campagne' => $campagne
		));
	}

	/**
	 * @Route("/emailing/campagne/liste-contacts/ajax/{id}", name="emailing_campagne_liste_contacts_ajax", options={"expose"=true})
	 */
	public function campagneListeContactsAjaxAction(Request $requestData, Campagne $campagne)
	{

		$arr_sort = $requestData->get('order');
		$arr_cols = $requestData->get('columns');
		$col = $arr_sort[0]['column'];

		$orderBy = $arr_cols[$col]['data'];
		$orderDir = $arr_sort[0]['dir'];

		$search = $requestData->get('search')['value'];

		$list = array();
		foreach($campagne->getCampagneContacts() as $campagneContact){

			$arrContact = array();
			$arrContact['id'] = $campagneContact->getContact()->getId();
			$arrContact['prenom'] = $campagneContact->getContact()->getPrenom();
			$arrContact['nom'] = $campagneContact->getContact()->getNom();
			$arrContact['organisation'] = $campagneContact->getContact()->getCompte()->getNom();
			$arrContact['titre'] = $campagneContact->getContact()->getTitre();
			$arrContact['email'] = $campagneContact->getContact()->getEmail();
			$arrContact['open'] = $campagneContact->getOpen()?1:0;
			$arrContact['click'] = $campagneContact->getClick()?1:0;
			$arrContact['bounce'] = $campagneContact->getBounce()?1:0;
			$arrContact['unsubscribe'] = $campagneContact->getUnsubscribed()?1:0;

			if($search !== null && $search !== ''){
				if( stripos($arrContact['prenom'], $search) !== false ||
					stripos($arrContact['nom'], $search) !== false ||
					stripos($arrContact['organisation'], $search) !== false ||
					stripos($arrContact['titre'], $search) !== false ||
					stripos($arrContact['email'], $search) !== false
				){
					$list[] = $arrContact;
				}
			} else {
				$list[] = $arrContact;
			}
			
		}

		$nbFiltered = count($list);

		usort($list, function($a, $b) use ($orderBy, $orderDir) {
	        $result = null;

	        if(strtoupper($orderDir) == 'ASC'){
    			$result = strcasecmp($a[$orderBy], $b[$orderBy]);
    		} else {
    			$result = strcasecmp($b[$orderBy], $a[$orderBy]);
    		}

	        return $result;  
	    });

	    $list = array_slice( $list, $requestData->get('start'), $requestData->get('length'));

		$response = new JsonResponse();
		$response->setData(array(
			'draw' =>  intval( $requestData->get('draw') ),
			'recordsTotal' => $campagne->getNbDestinataires(),
			'recordsFiltered' => $nbFiltered,
			'aaData' => $list,
		));
	
		return $response;
	}
	 

	/**
	 * @Route("/emailing/campagne/supprimer/{id}", name="emailing_campagne_supprimer" , options={"expose"=true})
	 */
	public function campagneSupprimerAction(Request $request, Campagne $campagne)
	{
		$form = $this->createFormBuilder()->getForm();
		
		$form->handleRequest($request);
		
		if ($form->isSubmitted() && $form->isValid()) {

			$em = $this->getDoctrine()->getManager();
			$em->remove($campagne);
			$em->flush();
		
			return $this->redirect($this->generateUrl(
				'emailing_campagne_liste'
			));
		}
		
		return $this->render('emailing/campagne/emailing_campagne_supprimer.html.twig', array(
				'form' => $form->createView(),
				'campagne' => $campagne
		));
	}

	/**
	 * @Route("/emailing/campagne/ajouter", name="emailing_campagne_ajouter")
	 */
	public function campagneAjouterAction(Request $request)
	{
		$campagne = new Campagne();
		$form = $this->createForm(
			CampagneType::class,
			$campagne
		);
		$form->handleRequest($request);
		
		if ($form->isSubmitted() && $form->isValid()) {

			$campagne->setDateCreation(new \DateTime(date('Y-m-d')));
			$campagne->setUserCreation($this->getUser());
			
			$em = $this->getDoctrine()->getManager();					
			$em->persist($campagne);
			$em->flush();

			return $this->redirect($this->generateUrl(
				'emailing_campagne_contenu', array(
					'id' => $campagne->getId()
				)
			));
		}
		
		return $this->render('emailing/campagne/emailing_campagne_ajouter.html.twig', array(
			'form' => $form->createView(),
			'campagne' => $campagne

		));
	}

	/**
	 * @Route("/emailing/campagne/contenu/{id}", name="emailing_campagne_contenu")
	 */
	public function campagneContenuAction(Request $request, Campagne $campagne){

		if( !$campagne->isDraft() ){
			throw new AccessDeniedException;
		}

		$form = $this->createForm(
			CampagneContenuType::class,
			$campagne
		);
		$form->handleRequest($request);
		
		if ($form->isSubmitted() && $form->isValid()) {

			$file = $form['file']->getData();
			$html = file_get_contents($file);

			//for outlook to display apostrophes properly
			$html = str_replace('&apos;', '&#8217;', $html);
			$campagne->setHTML($html);

			$em = $this->getDoctrine()->getManager();					
			$em->persist($campagne);
			$em->flush();

			if(null != $campagne->getNomRapport() && 0 != $campagne->getNbDestinataires()){
				return $this->redirect($this->generateUrl(
					'emailing_campagne_recap', array(
						'id' => $campagne->getId()
					)
				));
			}

			return $this->redirect($this->generateUrl(
				'emailing_campagne_destinataires', array(
					'id' => $campagne->getId()
				)
			));
		}
		return $this->render('emailing/campagne/emailing_campagne_contenu.html.twig', array(
			'form' => $form->createView(),
			'campagne' => $campagne
		));
	}

	/**
	 * @Route("/emailing/campagne/destinataires/{id}", name="emailing_campagne_destinataires")
	 */
	public function campagneDestinatairesAction(Request $request, Campagne $campagne){

		if( !$campagne->isDraft() ){
			throw new AccessDeniedException;
		}

		$em = $this->getDoctrine()->getManager();	

		$form = $this->createForm(CampagneDestinatairesType::class, $campagne, array(
		    'company' => $this->getUser()->getCompany()
        ));

		$form->handleRequest($request);
		
		if ($form->isSubmitted() && $form->isValid()) {

			$rapport = $form['rapport']->getData();
	
			$campagne->setNomRapport($rapport->getNom());

			$contactRepo = $em->getRepository('App:CRM\Contact');
			$filterRepo = $em->getRepository('App:CRM\RapportFilter');

			$arr_filters = $filterRepo->findByRapport($rapport);
			$arr_obj = $contactRepo->createQueryAndGetResult($arr_filters, $rapport->getCompany(), true, false, false, false);

			foreach($campagne->getCampagneContacts() as $cc){
				$campagne->removeCampagneContact($cc);
				$em->remove($cc);
			}

			$em->flush();

			foreach($arr_obj as $contact){
				$campagneContact = new CampagneContact();
				$campagneContact->setContact($contact);
				$campagne->addCampagneContact($campagneContact);
			}		
							
			$em->persist($campagne);
			$em->flush();

			return $this->redirect($this->generateUrl(
				'emailing_campagne_recap', array(
					'id' => $campagne->getId()
				)
			));

		}

		return $this->render('emailing/campagne/emailing_campagne_destinataires.html.twig', array(
			'form' => $form->createView(),
			'campagne' => $campagne
		));
	}

	/**
	 * @Route("/emailing/campagne/recap/{id}", name="emailing_campagne_recap")
	 */
	public function campagneRecapAction(Campagne $campagne){

		if( !$campagne->isDraft() && !$campagne->isScheduled() && !$campagne->isDelivering()){
			throw new AccessDeniedException;
		}

		return $this->render('emailing/campagne/emailing_campagne_recap.html.twig', array(
			'campagne' => $campagne
		));

	}

	/**
	 * @Route("/emailing/campagne/tester/{id}", name="emailing_campagne_tester")
	 */
	public function campagneTesterAction(Request $request, Campagne $campagne)
	{

		if( !$campagne->isDraft() ){
			throw new AccessDeniedException;
		}

		$form = $this->createFormBuilder()->getForm();
		$form->add('to', EmailType::class, array(
				'required' => true,
				'label' => 'Destinataire',
		));

		$form->add('submit', \Symfony\Component\Form\Extension\Core\Type\SubmitType::class, array(
				'label' => 'Envoyer',
				'attr' => array('class' => 'btn btn-success')
		));

		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {

			$to = $form->get('to')->getData();
			
			try{
				$this->mailgunService->sendTestViaAPI($campagne, $to);
				$this->get('session')->getFlashBag()->add('success', "Un email de test a été envoyé à $to");

			} catch(\Exception $e){
				$error =  $e->getMessage();
				$this->get('session')->getFlashBag()->add('danger', "L'email n'a pas été envoyé pour la raison suivante : $error");
			}

			return $this->redirect($this->generateUrl(
					'emailing_campagne_recap',
					array('id' => $campagne->getId())
			));
		}

		return $this->render('emailing/campagne/emailing_campagne_tester.html.twig', array(
				'form' => $form->createView(),
				'campagne' => $campagne
		));
	}

	/**
	 * @Route("/emailing/campagne/envoyer/{id}", name="emailing_campagne_envoyer")
	 */
	public function campagneEnvoyerAction(Campagne $campagne)
	{

		if( !$campagne->isDraft() ){
			throw new AccessDeniedException;
		}

		$em = $this->getDoctrine()->getManager();

		try{
			$result = $this->mailgunService->sendCampagneViaAPI($campagne);

			$this->get('session')->getFlashBag()->add('success', 'La campagne '.$campagne->getNom().' a été envoyée !');

			$campagne->setDateEnvoi(new \DateTime(date('Y-m-d')));
			$campagne->setEtat('DELIVERING');
					
			$em->persist($campagne);
			$em->flush();
		
		} catch(\Exception $e){
			$error =  $e->getMessage();
			$this->get('session')->getFlashBag()->add('danger', "La campagne n'a pas été envoyée pour la raison suivante : $error");
			
			$campagne->setEtat('ERROR');
			$em->persist($campagne);
			$em->flush();

			return $this->redirect($this->generateUrl(
				'emailing_campagne_recap',
				array('id' => $campagne->getId())
			));
		}

		return $this->redirect($this->generateUrl(
			'emailing_campagne_voir',
			array('id' => $campagne->getId())
		));
		
	}

	/**
	 * @Route("/emailing/campagne/editer/{id}", name="emailing_campagne_editer" , options={"expose"=true})
	 */
	public function campagneEditerAction(Campagne $campagne)
	{
		if( !$campagne->isDraft() ){
			throw new AccessDeniedException;
		}

		if(null === $campagne->getHTML() || '' === $campagne->getHTML()){
			return $this->redirect($this->generateUrl(
				'emailing_campagne_contenu',
				array('id' => $campagne->getId())
			));
		} else if(null === $campagne->getNomRapport() || 0 == $campagne->getNbDestinataires()){
			return $this->redirect($this->generateUrl(
				'emailing_campagne_destinataires',
				array('id' => $campagne->getId())
			));
		} 
		
		return $this->redirect($this->generateUrl(
			'emailing_campagne_recap',
			array('id' => $campagne->getId())
		));
	}

	/**
	 * @Route("/emailing/campagne/editer-infos/{id}", name="emailing_campagne_editer_infos")
	 */
	public function campagneEditerInfosAction(Request $request, Campagne $campagne)
	{
		if( !$campagne->isDraft() ){
			throw new AccessDeniedException;
		}

		$form = $this->createForm(
			CampagneType::class,
			$campagne
		);
		$form->handleRequest($request);
		
		if ($form->isSubmitted() && $form->isValid()) {

			$em = $this->getDoctrine()->getManager();					
			$em->persist($campagne);
			$em->flush();

			return $this->redirect($this->generateUrl(
				'emailing_campagne_recap',
				array('id' => $campagne->getId())
			));
		}
		
		return $this->render('emailing/campagne/emailing_campagne_ajouter.html.twig', array(
			'form' => $form->createView(),
			'campagne' => $campagne
		));
	}

	/**
	 * @Route("/emailing/campagne/planifier/{id}", name="emailing_campagne_planifier")
	 */
	public function campagnePlanifierAction(Request $request, Campagne $campagne)
	{
		if( !$campagne->isDraft() ){
			throw new AccessDeniedException;
		}

		$campagne->setDateEnvoi(new \DateTime());

		$form = $this->createForm(
			CampagneDateEnvoiType::class,
			$campagne
		);
		$form->handleRequest($request);
		
		if ($form->isSubmitted() && $form->isValid()) {

			try{
				$em = $this->getDoctrine()->getManager();			

				$campagne->setEtat('SCHEDULED');	
				$em->persist($campagne);
				$em->flush();

				if($campagne->getDateEnvoi()->format('Ymd') == date('Ymd')){
				    $result = $this->mailgunService->sendCampagneViaAPI($campagne);
				    $campagne->setEtat('DELIVERING');

				    $em->persist($campagne);
					$em->flush();
				}

				$this->get('session')->getFlashBag()->add('success', 'La campagne '.$campagne->getNom().' a été planifiée pour le '.$campagne->getDateEnvoi()->format('d/m/Y').' à '.$campagne->getDateEnvoi()->format('h:i').' !');

			
			} catch(\Exception $e){

				$error =  $e->getMessage();
				$this->get('session')->getFlashBag()->add('danger', "La campagne n'a pas été planifiée pour la raison suivante : $error");

				$campagne->setEtat('DRAFT');	
				$em->persist($campagne);
				$em->flush();

				return $this->redirect($this->generateUrl(
						'emailing_campagne_recap',
						array('id' => $campagne->getId())
				));
			}
			
			return $this->redirect($this->generateUrl(
				'emailing_campagne_recap',
				array('id' => $campagne->getId())
			));
		}
		
		return $this->render('emailing/campagne/emailing_campagne_planifier_modal.html.twig', array(
			'form' => $form->createView(),
			'campagne' => $campagne
		));

	}

	/**
	 * @Route("/emailing/campagne/annuler-envoi/{id}", name="emailing_campagne_annuler_envoi")
	 */
	public function campagneAnnulerEnvoiAction(Campagne $campagne)
	{
		if( false === $campagne->isScheduled() || true === $campagne->isDelivering()){
			throw new AccessDeniedException;
		}

		$campagne->setEtat('DRAFT');	
		$campagne->setDateEnvoi(null);

		$em = $this->getDoctrine()->getManager();			
		$em->persist($campagne);
		$em->flush();

		return $this->redirect($this->generateUrl(
			'emailing_campagne_recap',
			array('id' => $campagne->getId())
		));

	}


	/**
	 * @Route("/emailing/mailgun-webhook", name="emailing_mailgun_webhook")
	 */
	public function mailgunWebhookAction(Request $request)
	{
		$response = new Response();

		$content = json_decode($request->getContent(), true);

		//check the signature
		$signature = $content['signature'];
		if ( $this->mailgunService->checkWebhookSignature($signature['token'], $signature['timestamp'], $signature['signature'] ) === false ) {
       		$response->setStatusCode('401');
			return $response;
		}

		$eventData = $content['event-data'];
		if( $this->mailgunService->saveWebhookEvent($eventData) === false ){
			$response->setStatusCode('500');
			return $response;
		}

		return $response;
	}
	
}
