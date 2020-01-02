<?php

namespace App\Controller\Social;

use App\Entity\Social\Merci;
use App\Entity\Social\TableauMerci;
use App\Form\Social\MerciType;
use App\Form\Social\TableauMerciType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class MerciController extends AbstractController
{

	/**
	 * @Route("/compta/merci/choisir-objectifs",
	 *   name="compta_merci_choisir_objectifs",
	 *  )
	 */
	public function choisirObjectifs(Request $request){

		$em = $this->getDoctrine()->getManager();
		$tableauMerci = new TableauMerci();

		$form = $this->createForm(
			TableauMerciType::class,
			$tableauMerci
		);

		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {

			$em->persist($tableauMerci);
			$em->flush();

			return $this->redirect($this->generateUrl('social_index'));
		}

		return $this->render('social/merci/social_merci_choisir_objectif.html.twig', array(
			'form' => $form->createView()
		));
	}

	/**
	 * @Route("/compta/merci/ajouter/{type}",
	 *   name="compta_merci_ajouter",
	 *  )
	 */
	public function ajouter(Request $request, $type){

		$em = $this->getDoctrine()->getManager();
		$tableauMerciRepo = $em->getRepository('App:Social\TableauMerci');

		$tableauMerci = $tableauMerciRepo->findCurrent();

		$merci = new Merci();
		$merci->setType($type);
		$merci->setDate(new \DateTime(date('Y-m-d')));
		$merci->setTableauMerci($tableauMerci);

		if(strtolower($type) == "externe"){
			$merci->setTo($this->getUser());
		} else {
			$merci->setFromUser($this->getUser());
		}

		$form = $this->createForm(MerciType::class, $merci,array(
		   'companyId'=> $this->getUser()->getCompany()
        ));

		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {

			$em->persist($merci);
			$em->flush();

			return $this->redirect($this->generateUrl('social_index'));
		}

		return $this->render('social/merci/social_merci_ajouter.html.twig', array(
			'form' => $form->createView(),
			'type' => $type
		));

	}
}
