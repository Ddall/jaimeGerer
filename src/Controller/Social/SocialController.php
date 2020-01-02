<?php

namespace App\Controller\Social;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;


class SocialController extends AbstractController
{

	/**
	 * @Route("/social", name="social_index")
	 */
	public function indexAction()
	{
		$em = $this->getDoctrine()->getManager();
		$tableauMerciRepo = $em->getRepository('App:Social\TableauMerci');
		$courseRepo = $em->getRepository('App:Social\Course');

		$tableauMerci = $tableauMerciRepo->findCurrent();
		$arr_courses = $courseRepo->findAll();

		return $this->render('social/social_index.html.twig', array(
			'tableauMerci' => $tableauMerci,
			'arr_courses' => $arr_courses
		));
	}
}
