<?php

namespace App\Controller\Social;

use App\Entity\Social\Course;
use App\Form\Social\CourseType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;


class CourseController extends AbstractController
{


	/**
	 * @Route("/compta/course/ajouter",
	 *   name="compta_course_ajouter",
	 *  )
	 */
	public function ajouter(Request $request){

		$em = $this->getDoctrine()->getManager();

		$course = new Course();
		$course->setUser($this->getUser());

		$form = $this->createForm(
			CourseType::class,
			$course
		);

		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {

			$em->persist($course);
			$em->flush();

			return $this->redirect($this->generateUrl('social_index'));
		}

		return $this->render('social/course/social_course_ajouter.html.twig', array(
			'form' => $form->createView()
		));

	}

	/**
	 * @Route("/compta/course/supprimer",
	 *   name="compta_course_supprimer",
	 *   options={"expose"=true}
	 *  )
	 */
	public function supprimer(Request $request){

		$em = $this->getDoctrine()->getManager();
		$courseRepo = $em->getRepository('App:Social\Course');

		$arr_courses = $request->request->get('arr_courses');
		foreach($arr_courses as $courseId){
			$course = $courseRepo->find($courseId);
			$em->remove($course);
			$em->flush();
		}

		return $this->redirect($this->generateUrl('social_index'));
	}
}
