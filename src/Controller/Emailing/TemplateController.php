<?php

namespace App\Controller\Emailing;


use App\Entity\Emailing\TemplateEmailing;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Emailing\Template controller.
 *
 * @Route("/emailing/template")
 */
class TemplateController extends AbstractController
{
	/**
	 * @Route("/emailing/template/liste", name="emailing_template_emailing_liste")
	 */
	public function indexAction()
	{
		return $this->render('emailing/emailing_index.html.twig');
	}
	/**
	 * @Route("/emailing/template/statistique", name="emailing_statistiques")
	 */
	public function statsAction()
	{
		return $this->render('emailing/emailing_index.html.twig');
	}
	/**
	 * @Route("/emailing/template/contact", name="emailing_contact_liste")
	 */
	public function contactAction()
	{
		return $this->render('emailing/emailing_index.html.twig');
	}
}
