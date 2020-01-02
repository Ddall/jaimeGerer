<?php

namespace App\Controller\CRM;

use App\Entity\SettingsActivationOutil;
use App\Service\CRM\TodoListService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class CRMController extends AbstractController
{
	/**
	 * @Route("/crm", name="crm_index")
	 */
	public function indexAction(TodoListService $todoListService)
	{
		if(!$this->getUser()->hasRole('ROLE_COMMERCIAL')){
			throw new AccessDeniedException;
		}

		//vérifier si l'outil CRM a été activé
		$settingsActivationRepo = $this->getDoctrine()->getManager()->getRepository('App:SettingsActivationOutil');
		$settingsActivationCRM = $settingsActivationRepo->findBy(array(
				'company' => $this->getUser()->getCompany(),
				'outil' => 'CRM'
		));
		 
		//outil non activé : paramétrage
		if($settingsActivationCRM == null){
			return $this->redirect($this->generateUrl('crm_activer_start'));
		}

		//todo list
		$todoList = $todoListService->createTodoList($this->getUser());

		//santé de la CRM
		$contactRepository = $this->getDoctrine()->getManager()->getRepository('App:CRM\Contact');
		$nbContacts = $contactRepository->custom_count($this->getUser()->getCompany());
		$nbNoEmail = $contactRepository->countNoEmail($this->getUser()->getCompany());
		$nbNoTel = $contactRepository->countNoTel($this->getUser()->getCompany());
		$nbBounce = $contactRepository->countBounce($this->getUser()->getCompany());

		return $this->render('crm/crm_index.html.twig', array(
			'nbContacts' => $nbContacts,
			'nbNoEmail' => $nbNoEmail,
			'nbNoTel' => $nbNoTel,
			'nbBounce' => $nbBounce,
			'todoList' => $todoList
		));
	}

	/**
	 * @Route("/crm/activation/start", name="crm_activer_start")
	 */
	public function activationStartAction(){
		return $this->render('crm/activation/crm_activation_start.html.twig');
	}
	
	/**
	 * @Route("/crm/activation/import", name="crm_activer_import")
	 */
	public function activationImportAction(){
		return $this->render('crm/activation/crm_activation_import.html.twig');
	}
	
	/**
	 * @Route("/crm/activation", name="crm_activer")
	 */
	public function activationAction(){
		
		//activer la CRM
		$em = $this->getDoctrine()->getManager();
		$activationCRM = new SettingsActivationOutil();
		$activationCRM->setCompany($this->getUser()->getCompany());
		$activationCRM->setDate(new \DateTime(date('Y-m-d')));
		$activationCRM->setOutil('CRM');
		$em->persist($activationCRM);
		$em->flush();
		
		
		return $this->render('crm/activation/crm_activation.html.twig');
	}


	
	
}
