<?php

namespace App\Controller\CRM;

use App\Entity\CRM\Contact;
use App\Entity\CRM\PriseContact;
use App\Form\CRM\PriseContactType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;


class PriseContactController extends AbstractController
{
	/**
	 * @Route("/crm/prise_contact/ajouter/{id}/{screen}", name="crm_prise_contact_ajouter")
	 */
	public function priseContactAjouterAction(Request $request, Contact $contact, $screen)
	{
		$priseContact = new PriseContact();
		$form = $this->createForm( PriseContactType::class, $priseContact);
	
		$form->handleRequest($request);
	
		if ($form->isSubmitted() && $form->isValid()) {

			$priseContact->setUser($this->getUser());
			$priseContact->setContact($contact);
			
			$em = $this->getDoctrine()->getManager();
			$em->persist($priseContact);
			$em->flush();
	
			if($screen == 'impulsion'){
			
				return $this->redirect($this->generateUrl(
						'crm_impulsion_liste'
				));
			} else{
				return $this->redirect($this->generateUrl(
						'crm_contact_voir',
						array('id' => $contact->getId())
				). '#prises_contact');
			}

		}
	
		return $this->render('crm/priseContact/crm_prise_contact_ajouter.html.twig', array(
				'form' => $form->createView(),
				'contact' => $contact,
				'screen' => $screen
		));
	}

	/**
	 * @Route("/crm/prise_contact/voir/{id}", name="crm_prise_contact_voir", options={"expose"=true})
	 */
	public function priseContactVoir(Contact $contact)
	{
		
		return $this->render('crm/priseContact/crm_prise_contact_liste_part.html.twig', array(
			'contact' => $contact,
			'div_id' => 'table_prises_contact',
			'readOnly' => true
		));
	}

	


}
