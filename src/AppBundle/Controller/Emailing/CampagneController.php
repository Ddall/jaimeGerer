<?php

namespace AppBundle\Controller\Emailing;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

use Swift_Attachment;
use Mailgun\Mailgun;
use cspoo_swiftmailer_mailgun;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

use AppBundle\Entity\Emailing\Campagne;
use AppBundle\Entity\Emailing\CampagneContact;

use AppBundle\Form\Emailing\CampagneType;
use AppBundle\Form\Emailing\CampagneContenuType;
use AppBundle\Form\Emailing\CampagneDestinatairesType;


class CampagneController extends Controller
{

	/**
	 * @Route("/emailing", name="emailing_index")
	 */ 
	public function indexAction()
	{
		return $this->render('emailing/emailing_index.html.twig');
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
	public function campagneListeAjaxAction()
	{
		$requestData = $this->getRequest();
		$arr_sort = $requestData->get('order');
		$arr_cols = $requestData->get('columns');
	
		$col = $arr_sort[0]['column'];
	
		$repository = $this->getDoctrine()->getManager()->getRepository('AppBundle:Emailing\Campagne'); 
	
		$arr_search = $requestData->get('search');
	
		$list = $repository->findForList(
				$this->getUser()->getCompany(),
				$requestData->get('length'),
				$requestData->get('start'),
				$arr_cols[$col]['data'],
				$arr_sort[0]['dir'],
				$arr_search['value']
		);

		$response = new JsonResponse();
		$response->setData(array(
			'draw' => intval( $requestData->get('draw') ),
			'recordsTotal' => $repository->count($this->getUser()->getCompany()),
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
	public function campagneListeContactsAjaxAction(Campagne $campagne)
	{

		$list = array();
		foreach($campagne->getCampagneContacts() as $campagneContact){
			$arrContact = array();
			$arrContact['prenom'] = $campagneContact->getContact()->getPrenom();
			$arrContact['nom'] = $campagneContact->getContact()->getNom();
			$arrContact['organisation'] = $campagneContact->getContact()->getCompte()->getNom();
			$arrContact['titre'] = $campagneContact->getContact()->getTitre();
			$arrContact['email'] = $campagneContact->getContact()->getEmail();
			$arrContact['open'] = $campagneContact->getOpen();
			$arrContact['click'] = $campagneContact->getClick();
			$arrContact['bounce'] = $campagneContact->getBounce();

			$list[] = $arrContact;
		}

		$requestData = $this->getRequest();
		$list = array_slice($list, $requestData->get('start'), $requestData->get('length'));

		$response = new JsonResponse();
		$response->setData(array(
			'draw' => 25,
			'recordsTotal' => $campagne->getNbDestinataires(),
			'recordsFiltered' => count($list),
			'aaData' => $list,
		));
	
		return $response;
	}
	 

	/**
	 * @Route("/emailing/campagne/supprimer/{id}", name="emailing_campagne_supprimer" , options={"expose"=true})
	 */
	public function campagneSupprimerAction(Campagne $campagne)
	{
		$form = $this->createFormBuilder()->getForm();
		
		$request = $this->getRequest();
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
	public function campagneAjouterAction()
	{
		$campagne = new Campagne();
		$form = $this->createForm(
			new CampagneType(), 
			$campagne
		);
		$request = $this->getRequest();
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
			'form' => $form->createView()
		));
	}

	/**
	 * @Route("/emailing/campagne/contenu/{id}", name="emailing_campagne_contenu")
	 */
	public function campagneContenuAction(Campagne $campagne){

		if( !$campagne->isDraft() ){
			throw new AccessDeniedException;
		}

		$form = $this->createForm(
			new CampagneContenuType(), 
			$campagne
		);
		$request = $this->getRequest();
		$form->handleRequest($request);
		
		if ($form->isSubmitted() && $form->isValid()) {

			$file = $form['file']->getData();
			$html = file_get_contents($file);
			$campagne->setHTML($html);

			$em = $this->getDoctrine()->getManager();					
			$em->persist($campagne);
			$em->flush();

			return $this->redirect($this->generateUrl(
				'emailing_campagne_destinataires', array(
					'id' => $campagne->getId()
				)
			));
		}
		return $this->render('emailing/campagne/emailing_campagne_contenu.html.twig', array(
			'form' => $form->createView()
		));
	}

	/**
	 * @Route("/emailing/campagne/destinataires/{id}", name="emailing_campagne_destinataires")
	 */
	public function campagneDestinatairesAction(Campagne $campagne){

		if( !$campagne->isDraft() ){
			throw new AccessDeniedException;
		}

		$em = $this->getDoctrine()->getManager();	

		$form = $this->createForm(
			new CampagneDestinatairesType($this->getUser()->getCompany()), 
			$campagne
		);
		$request = $this->getRequest();
		$form->handleRequest($request);
		
		if ($form->isSubmitted() && $form->isValid()) {

			$rapport = $form['rapport']->getData();
	
			$campagne->setNomRapport($rapport->getNom());

			$contactRepo = $em->getRepository('AppBundle:CRM\Contact');
			$filterRepo = $em->getRepository('AppBundle:CRM\RapportFilter');

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
			'form' => $form->createView()
		));
	}

	/**
	 * @Route("/emailing/campagne/recap/{id}", name="emailing_campagne_recap")
	 */
	public function campagneRecapAction(Campagne $campagne){

		if( !$campagne->isDraft() ){
			throw new AccessDeniedException;
		}

		return $this->render('emailing/campagne/emailing_campagne_recap.html.twig', array(
			'campagne' => $campagne
		));

	}

	/**
	 * @Route("/emailing/campagne/tester/{id}", name="emailing_campagne_tester")
	 */
	public function campagneTesterAction(Campagne $campagne)
	{

		if( !$campagne->isDraft() ){
			throw new AccessDeniedException;
		}

		$form = $this->createFormBuilder()->getForm();
		$form->add('to', 'email', array(
				'required' => true,
				'label' => 'Destinataire',
		));

		$form->add('submit', 'submit', array(
				'label' => 'Envoyer',
				'attr' => array('class' => 'btn btn-success')
		));

		$request = $this->getRequest();
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {

			$to = $form->get('to')->getData();
			
			try{
				$mailgunService = $this->get('appbundle.mailgun');
				$mailgunService->sendTestViaAPI($campagne, $to);
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

		try{
			$mailgunService = $this->get('appbundle.mailgun');
			$mailgunService->ajouterLienDesinscription($campagne);
			$mailgunService->sendCampagneViaAPI($campagne);
		} catch(\Exception $e){
			$error =  $e->getMessage();
			$this->get('session')->getFlashBag()->add('danger', "La campagne n'a pas été envoyé pour la raison suivante : $error");
			return $this->redirect($this->generateUrl(
					'emailing_campagne_recap',
					array('id' => $campagne->getId())
			));
		}

		$this->get('session')->getFlashBag()->add('success', 'La campagne '.$campagne->getNom().' a été envoyée !');

		$campagne->setDateEnvoi(new \DateTime(date('Y-m-d')));
		$campagne->setEtat('SENT');
		$em = $this->getDoctrine()->getManager();					
		$em->persist($campagne);
		$em->flush();

		return $this->redirect($this->generateUrl(
			'emailing_campagne_voir',
			array('id' => $campagne->getId())
		));
		
	}

	
}
