<?php

namespace App\Controller\Compta;

use App\Entity\Settings;
use App\Form\SettingsAssociationType;
use App\Form\SettingsClassificationType;
use App\Form\SettingsType;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Serializer;

class SettingsController extends AbstractController
{
	/**
	 * @Route("/compta/settings/liste", name="compta_settings_liste")
	 */
	public function settingsListeAction()
	{
		$em = $this->getDoctrine()->getManager();
		$settingsRepo = $em->getRepository('App:Settings');
		$arr_settings = array();

		//settings existants
		$arr_settings = $settingsRepo->findBy(
			array(
				'company' => $this->getUser()->getCompany(),
				'module' => 'COMPTA'
			),array(
				'parametre' => 'asc'
			));
		$arr_parametres = array();
		foreach($arr_settings as $settings){
			$arr_parametres[$settings->getParametre()] = $settings;
		}

		//settings par defaut
		$arr_settings_default = $settingsRepo->findBy(
			array(
				'company' => null,
				'module' => 'COMPTA'
			),
			array(
				'parametre' => 'asc'
		));

		foreach($arr_settings_default as $settings_def){
			if(!array_key_exists($settings_def->getParametre(), $arr_parametres)){
				$settings = clone $settings_def;
				$settings->setCompany($this->getUser()->getCompany());
				$em->persist($settings);

				$arr_parametres[$settings->getParametre()] = $settings;
			}
		}
		$em->flush();

		//recherche des différentes valeurs pour les listes
		$arr_valeursSettingsListe = array();
		foreach($arr_parametres as $parametre => $settings){
			if($settings->getType() == "LISTE"){
				$arr_valeurs = $settingsRepo->findBy(array(
						'company' => $this->getUser()->getCompany(),
						'module' => 'COMPTA',
						'parametre' => $parametre
				));

				$arr_valeursSettingsListe[$settings->getId()] = $arr_valeurs;
			}
		}

		$ccRepo = $this->getDoctrine()->getManager()->getRepository('App:Compta\CompteComptable');
		$arr_cc = $ccRepo->findAllByNum(421, $this->getUser()->getCompany());

		$arr_cc_json = array();
		foreach($arr_cc as  $cc){
			$arr_cc_json[$cc->getId()] = $cc->__toString();
		}

		$serializer = new Serializer(array(new GetSetMethodNormalizer()), array('json' => new JsonEncoder()));
		$json = $serializer->serialize($arr_cc_json, 'json');

		return $this->render('compta/settings/compta_settings_liste.html.twig', array(
				'arr_settings' => $arr_parametres,
				'arr_valeursSettingsListe' => $arr_valeursSettingsListe,
				'arr_cc' => $json
		));
	}

	/**
	 * @Route("/compta/settings/editer/{id}", name="compta_settings_editer")
	 */
	public function settingsEditerAction(Request $request, Settings $settings)
	{
		$form = $this->createForm(SettingsType::class, $settings);

		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {

			$em = $this->getDoctrine()->getManager();
			$em->persist($settings);
			$em->flush();
			return $this->redirect($this->generateUrl('compta_settings_liste'));
		}
		return $this->redirect($this->generateUrl('compta_settings_liste'));
	}

	/**
	 * @Route("/compta/settings/editer-liaison/{id}", name="compta_settings_editer_liaison")
	 */
	public function settingsEditerLiaisonAction(Request $request, Settings $settings)
	{
		$form = $this->createForm(SettingsAssociationType::class, $settings, array(
		    'companyId' => $this->getUser()->getCompany()
        ));

		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {

			$em = $this->getDoctrine()->getManager();
			$em->persist($settings);
			$em->flush();
			return $this->redirect($this->generateUrl('compta_settings_liste'));
		}
		return $this->redirect($this->generateUrl('compta_settings_liste'));
	}

	/**
	 * @Route("/compta/settings/supprimer/{id}", name="compta_settings_supprimer")
	 */
	public function settingsSupprimerAction(Request $request, Settings $settings)
	{
		$form = $this->createFormBuilder()->getForm();

		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {

			$em = $this->getDoctrine()->getManager();
			$em->remove($settings);
			$em->flush();

			return $this->redirect($this->generateUrl(
					'compta_settings_liste'
			));
		}

		return $this->render('compta/settings/compta_settings_supprimer.html.twig', array(
				'form' => $form->createView(),
				'settings' => $settings
		));
	}

	/**
	 * @Route("/compta/settings/editer-classification/{id}", name="compta_settings_editer_classification")
	 */
	public function settingsEditerClassificationAction(Request $request, Settings $settings)
	{
	    // @upgrade note: this form is missing @see https://github.com/Web4Change/jaimeGerer/search?q=SettingsClassificationType&unscoped_q=SettingsClassificationType
//		$form = $this->createForm(new SettingsClassificationType($this->getUser()->getCompany(), null), $settings);
		$form = $this->createForm(SettingsClassificationType::class, $settings, array(
            'company' => $this->getUser()->getCompany(),
        ));

		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {

			$em = $this->getDoctrine()->getManager();
			$em->persist($settings);
			$em->flush();
			return $this->redirect($this->generateUrl('compta_settings_liste'));
		}
		return $this->redirect($this->generateUrl('compta_settings_liste'));
	}

	/**
	 * @Route("/compta/settings/ajouter-liaison-tva", name="compta_settings_ajouter_liaison_tva")
	 */
	public function ajouterLiasonTVA(Request $request){

		$form = $this->createFormBuilder()
		->add('compteComptable', EntityType::class, array(
				'class'=>'App:Compta\CompteComptable',
				'query_builder' => function (EntityRepository $er) {
					return $er->createQueryBuilder('c')
					->where('c.company = :company')
					->setParameter('company', $this->getUser()->getCompany());
				},
				'required' => false,
				'label' => 'Compte comptable',
				'attr' => array('class' => 'produit-type')
		))
		->add('compteTva', EntityType::class, array(
				'class'=>'App:Compta\CompteComptable',
				'query_builder' => function (EntityRepository $er) {
					return $er->createQueryBuilder('c')
					->where('c.company = :company')
					->andWhere('c.num LIKE :num')
					->setParameter('company', $this->getUser()->getCompany())
					->setParameter('num', '445%');
				},
				'required' => false,
				'label' => 'Compte TVA',
				'attr' => array('class' => 'produit-type')
		))
		->getForm();


		if ($request->getMethod() == 'POST') {
			$form->handleRequest($request);
			// data is an array with "name", "email", and "message" keys
			$data = $form->getData();
			$compteComptable = $data['compteComptable'];
			$compteComptable->setCompteTVA($data['compteTva']);

			$em = $this->getDoctrine()->getManager();
			$em->persist($compteComptable);
			$em->flush();

			return $this->redirect($this->generateUrl('compta_settings_liste'));
		}

		return $this->redirect($this->generateUrl('compta_settings_liste'));
	}
}
