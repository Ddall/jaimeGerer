<?php

namespace App\Controller\CRM;

use App\Entity\CRM\Contact;
use App\Entity\CRM\Impulsion;
use App\Entity\CRM\Opportunite;
use App\Entity\CRM\PriseContact;
use App\Form\CRM\ImpulsionType;
use App\Form\CRM\PriseContactType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ImpulsionController extends AbstractController
{
	/**
	 * @Route("/crm/impulsion/liste", name="crm_impulsion_liste")
	 */
	public function impulsionListeAction()
	{
		$repository = $this->getDoctrine()->getManager()->getRepository('App:CRM\Impulsion');
		$list = $repository->findBy(array(
			'user' => $this->getUser(),
			//'priseContact' => null
		));

		return $this->render('crm/impulsion/crm_impulsion_liste.html.twig', array(
			'list' => $list
		));
	}
	
	/**
	 * @Route("/crm/impulsion/ajouter/{contact}/{screen}", name="crm_impulsion_ajouter")
	 */
	public function impulsionAjouterAction(Request $request, Contact $contact = null, $screen = null)
	{
		$em = $this->getDoctrine()->getManager();
		$impulsion = new Impulsion();
		$impulsion->setUser($this->getUser());

		if($contact){
			$impulsion->setContact($contact->getId());
		}
		
		$form = $this->createForm(ImpulsionType::class, $impulsion, array(
		    'userId' => $impulsion->getUser()->getId(),
            'companyId' => $this->getUser()->getCompany()->getId(),
        ));

		if($contact){
			$form->get('contact_name')->setData($contact->__toString());
		}
		
		$form->handleRequest($request);
	
		if ($form->isSubmitted() && $form->isValid() ) {

			$em = $this->getDoctrine()->getManager();
			$data = $form->getData();
			$impulsion->setContact($em->getRepository('App:CRM\Contact')->findOneById($data->getContact()));
			
			$impulsion->setUser($this->getUser());
			$impulsion->setDateCreation(new \DateTime(date('Y-m-d')));
			
			$em->persist($impulsion);
			$em->flush();

			if( 'contact' == $screen ){
				return $this->redirect($this->generateUrl(
					'crm_contact_voir', array(
						'id' => $impulsion->getContact()->getId()
					)
				));
			}
	
			return $this->redirect($this->generateUrl('crm_impulsion_liste'));
		}
		
		return $this->render('crm/impulsion/crm_impulsion_ajouter.html.twig', array(
			'form' => $form->createView(),
			'action' => $this->generateUrl('crm_impulsion_ajouter', array(
				'contact' => $contact, 'screen' => $screen
			)),
		));
	}
	
	/**
	 * @Route("/crm/impulsion/editer/{id}/{screen}", name="crm_impulsion_editer")
	 */
	public function impulsionEditerAction(Request $request, Impulsion $impulsion, $screen)
	{
		$_contact = $impulsion->getContact();
		$impulsion->setContact($_contact->getId());

        $form = $this->createForm(ImpulsionType::class, $impulsion, array(
            'userId' => $impulsion->getUser()->getId(),
            'companyId' => $this->getUser()->getCompany()->getId(),
        ));
		
		$form->get('contact_name')->setData($_contact->__toString());
	
		$form->handleRequest($request);
	
		if ($form->isSubmitted() && $form->isValid()) {

			$em = $this->getDoctrine()->getManager();
			$data = $form->getData();
			$impulsion->setContact($em->getRepository('App:CRM\Contact')->findOneById($data->getContact()));

			$em->persist($impulsion);
			$em->flush();

			if($screen == 'impulsion'){
				return $this->redirect($this->generateUrl(
					'crm_impulsion_liste'
				));
			} else if($screen == 'home'){
				return $this->redirect($this->generateUrl(
					'crm_index'
				));
			} else {
				return $this->redirect($this->generateUrl(
					'crm_contact_voir',
					array('id' => $contact->getId())
				).'#prises_contact');
			}
	
		}
	
		return $this->render('crm/impulsion/crm_impulsion_editer_modal.html.twig', array(
			'form' => $form->createView(),
			'screen' => $screen,
			'impulsion' => $impulsion,
			'action' => $this->generateUrl('crm_impulsion_editer', array('id' => $impulsion->getId(), 'screen' => $screen )),
		));
	}
	
	/**
	 * @Route("/crm/impulsion/supprimer/{id}", name="crm_impulsion_supprimer")
	 */
	public function impulsionSupprimerAction(Request $request, Impulsion $impulsion)
	{
		$form = $this->createFormBuilder()->getForm();
	
		$form->handleRequest($request);
	
		if ($form->isSubmitted() && $form->isValid()) {
	
			$em = $this->getDoctrine()->getManager();
			$em->remove($impulsion);
			$em->flush();
	
			return $this->redirect($this->generateUrl(
				'crm_impulsion_liste'
			));
		}
	
		return $this->render('crm/impulsion/crm_impulsion_supprimer.html.twig', array(
			'form' => $form->createView(),
			'impulsion' => $impulsion
		));
	}
	
	/**
	 * @Route("/crm/impulsion/get-impulsions", name="crm_impulsion_get_liste")
	 */
	public function impulsion_listAction()
	{
		$repository = $this->getDoctrine()->getManager()->getRepository('App:CRM\Impulsion');
		$list = $repository->findAll();
		
		$res = array();
		foreach ($list as $impulsion) {
			$_res = array('id' => $impulsion->getId(), 'display' => $impulsion->getContact()->getPrenom() ." ". $impulsion->getContact()->getNom());
			$res[] = $_res;
		}
	
		$response = new \Symfony\Component\HttpFoundation\Response(json_encode($res));
		$response->headers->set('Content-Type', 'application/json');
		return $response;
	}
	
	private function _sortTempsRestant($a, $b){
		if ($a->getTempsRestant() == $b->getTempsRestant()) {
        	return 0;
    	}
    	return ($a->getTempsRestant() < $b->getTempsRestant()) ? -1 : 1;
	}

	/**
	 * @Route("/crm/impulsion/check/{id}/{screen}", name="crm_impulsion_check")
	 */
	public function impulsionCheckAction(Request $request, Impulsion $impulsion, $screen)
	{
		$priseContact = new PriseContact();
		$priseContact->setUser($this->getUser());
		$priseContact->setContact($impulsion->getContact());
		$form = $this->createForm(PriseContactType::class, $priseContact);
	
		$form->handleRequest($request);
	
		if ($form->isSubmitted() && $form->isValid()) {
			
			$em = $this->getDoctrine()->getManager();
			$em->persist($priseContact);

			$impulsion->setPriseContact($priseContact);
			$em->persist($impulsion);

			$em->flush();
	
			if($screen == 'impulsion'){
				return $this->redirect($this->generateUrl(
					'crm_impulsion_liste'
				));
			} else if($screen == 'home'){
				return $this->redirect($this->generateUrl(
					'crm_index'
				));
			} else {
				return $this->redirect($this->generateUrl(
					'crm_contact_voir',
					array('id' => $contact->getId())
				).'#prises_contact');
			}

		}
	
		return $this->render('crm/priseContact/crm_prise_contact_ajouter.html.twig', array(
			'form' => $form->createView(),
			'contact' => $priseContact->getContact(),
			'screen' => $screen,
			'action' => $this->generateUrl('crm_impulsion_check', array('id' => $impulsion->getId(), 'screen' => $screen))
		));
	}

	/**
	 * @Route("/crm/impulsion/ajouter-action-commerciale/{id}", name="crm_impulsion_ajouter_action_commerciale")
	 */
	public function impulsionAjouterActionCommercialeAction(Request $request, Opportunite $actionCommerciale)
	{
		$em = $this->getDoctrine()->getManager();
		$impulsion = new Impulsion();

		$impulsion->setUser($this->getUser());
		$impulsion->setDateCreation(new \DateTime(date('Y-m-d')));

		if($actionCommerciale->getContact()){
			$impulsion->setContact($actionCommerciale->getContact()->getId());
		}
		$impulsion->setInfos('Suivi action commerciale : '.$actionCommerciale->getNom());

        $form = $this->createForm(ImpulsionType::class, $impulsion, array(
            'userId' => $impulsion->getUser()->getId(),
            'companyId' => $this->getUser()->getCompany()->getId(),
        ));

		if($actionCommerciale->getContact()){
			$form->get('contact_name')->setData($actionCommerciale->getContact()->__toString());
		}
		
		$form->handleRequest($request);
	
		if ($form->isSubmitted() && $form->isValid() ) {

			$em = $this->getDoctrine()->getManager();
			$data = $form->getData();
			$impulsion->setContact($em->getRepository('App:CRM\Contact')->findOneById($data->getContact()));
			
			$em->persist($impulsion);
			$em->flush();
	
			return $this->redirect($this->generateUrl('crm_action_commerciale_voir', array(
				'id' => $actionCommerciale->getId()
			)));
		}
		
		return $this->render('crm/impulsion/crm_impulsion_ajouter.html.twig', array(
			'form' => $form->createView(),
			'action' => $this->generateUrl('crm_impulsion_ajouter_action_commerciale', array(
				'id' => $actionCommerciale->getId()
			)),
		));
	}


}
