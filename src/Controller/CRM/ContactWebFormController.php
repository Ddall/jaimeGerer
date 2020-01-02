<?php

namespace App\Controller\CRM;


//~ use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
//~ use Sensio\Bundle\FrameworkExtraBundle\Configurationion\Method;
//~ use Symfony\Component\Routing\Annotation\Route;
//~ use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use App\Entity\CRM\Compte;
use App\Entity\CRM\Contact;
use App\Entity\CRM\ContactWebForm;
use App\Entity\CRM\Impulsion;
use App\Entity\Rapport;
use App\Form\CRM\CompteFilterType;
use App\Form\CRM\ContactWebFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;


class ContactWebFormController extends AbstractController
{

	/**
	 * @Route("/crm/contactwebForm/liste", name="crm_contactwebform_liste")
	 */
	public function contactwebformListeAction()
	{
		return $this->render('crm/contactwebForm/crm_contactwebform_liste.html.twig');
	}

	/**
	 * @Route("/crm/contactwebForm/liste/ajax", name="crm_contactwebform_liste_ajax")
	 */
	public function contactwebformListeAjaxAction(Request $requestData)
	{
		$arr_sort = $requestData->get('order');
		$arr_cols = $requestData->get('columns');

		$col = $arr_sort[0]['column'];

		$repository = $this->getDoctrine()->getManager()->getRepository('App:CRM\ContactWebForm');

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
				'recordsTotal' => $repository->custom_count($this->getUser()->getCompany()),
				'recordsFiltered' => $repository->countForList($this->getUser()->getCompany(), $arr_search['value']),
				'aaData' => $list,
		));

		return $response;
	}

	/**
	 * @Route("/crm/contactwebForm/ajouter", name="crm_contactwebform_ajouter")
	 */
	public function contactwebformAjouterAction(Request $request)
	{
		$contact = new ContactWebForm();
		$contact->setUserGestion($this->getUser());
		$contact->setCompany($this->getUser()->getCompany());

		$form = $this->createForm(ContactWebFormType::class, $contact, array(
            'userGestionId' => $contact->getUserGestion()->getId(),
            'companyId' => $this->getUser()->getCompany()->getId(),
            'request' =>$request,
        ));

		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {

			$contact->setDateCreation(new \DateTime(date('Y-m-d')));
			$contact->setUserCreation($this->getUser());
			$em = $this->getDoctrine()->getManager();
			$em->persist($contact);
			$em->flush();

			return $this->redirect($this->generateUrl(
					'crm_contactwebform_voir',
					array('id' => $contact->getId())
			));
		}

		return $this->render('crm/contactwebForm/crm_contactwebform_ajouter.html.twig', array(
				'form' => $form->createView(),
				'hide_tiny' => true
		));
	}


	/**
	 * @Route("/crm/contactwebForm/editer/{id}", name="crm_contactwebform_editer")
	 */
	public function contactwebformEditerAction(Request $request, ContactWebForm $contactwebform)
	{
        $form = $this->createForm(ContactWebFormType::class, $contactwebform, array(
            'userGestionId' => $contactwebform->getUserGestion()->getId(),
            'companyId' => $this->getUser()->getCompany()->getId(),
            'request' =>$request,
        ));

		$repository = $this->getDoctrine()->getManager()->getRepository('App:Settings');

		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {

			$contactwebform->setDateEdition(new \DateTime(date('Y-m-d')));
			$contactwebform->setUserEdition($this->getUser());
			$em = $this->getDoctrine()->getManager();
			$em->persist($contactwebform);
			$em->flush();

			return $this->redirect($this->generateUrl(
					'crm_contactwebform_voir',
					array('id' => $contactwebform->getId())
			));
		}

		return $this->render('crm/contactwebForm/crm_contactwebform_editer.html.twig', array(
				'form' => $form->createView(),
				'hide_tiny' => true
		));
	}


	/**
	 * @Route("/crm/contactwebForm/supprimer/{id}", name="crm_contactwebform_supprimer")
	 */
	public function contactwebformSupprimerAction(Request $request, ContactWebForm $contactwebform)
	{
		$form = $this->createFormBuilder()->getForm();

		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {

			$em = $this->getDoctrine()->getManager();
			$em->remove($contactwebform);
			$em->flush();

			return $this->redirect($this->generateUrl(
					'crm_contactwebform_liste'
			));
		}

		return $this->render('crm/contactwebForm/crm_contactwebform_supprimer.html.twig', array(
				'form' => $form->createView(),
				'contactwebform' => $contactwebform
		));
	}

	/**
	 * @Route("/crm/contactwebForm/voir/{id}", name="crm_contactwebform_voir")
	 */
	public function contactwebformVoirAction(ContactWebForm $contactwebform)
	{
		//~ var_dump($contactwebform); exit;
		return $this->render('crm/contactwebForm/crm_contactwebform_voir.html.twig', array(
				'contactwebform' => $contactwebform,
		));
	}

	/**
	 * @Route("/crm/contactwebForm/addContactWeb", name="crm_contactwebform_addcontactweb")
	 */
	public function addContactWebAction(Request $request)
	{

		//autoriser le cross domain
	    if(isset($_SERVER['HTTP_ORIGIN'])){
	      switch ($_SERVER['HTTP_ORIGIN']) {
	        case 'https://www.jaime-gerer.com':
	          header('Access-Control-Allow-Origin: '.$_SERVER['HTTP_ORIGIN']);
	          header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
	          header('Access-Control-Max-Age: 1000');
	          header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
	          break;
	      }
	    }

	    $posts = array_values($request->request->all());

		$repository = $this->getDoctrine()->getManager()->getRepository('App:CRM\ContactWebForm');
		$contactRepository = $this->getDoctrine()->getManager()->getRepository('App:CRM\Contact');
		$em = $this->getDoctrine()->getManager();

		$ContactWebForm = $repository->find($posts[0]['id']);
		$champs = $em->getClassMetadata('App:CRM\Contact')->getFieldNames();

		$exists = false;
		if( isset($posts[0]['email']) && ($ContactWebForm->getEmail() != '' || $posts[0]['email'] != '') ){
			$contact = $contactRepository->findOneByEmail($posts[0]['email']);
			if($contact != null){
				$exists = true;
				$compte = $contact->getCompte();
			}
		}

		if(!$exists) {
			$compte = new Compte();
			$compte->setDateCreation(new \DateTime(date('Y-m-d')));
			$compte->setUserCreation($ContactWebForm->getUserCreation());
			$contact = new Contact();
			$contact->setDateCreation(new \DateTime(date('Y-m-d')));
			$contact->setUserCreation($ContactWebForm->getUserCreation());
		}

		// creation du compte
		if( $ContactWebForm->getNomCompte() != '' && $posts[0]['nomCompte'] != '' ) $compte->setNom($posts[0]['nomCompte']);
		if( $ContactWebForm->getUrl() != '' && $posts[0]['url'] != '' ) $compte->setUrl($posts[0]['url']);
		$compte->setUserGestion($ContactWebForm->getUserGestion());
		$compte->setCompany($ContactWebForm->getUserGestion()->getCompany());
		$em->persist($compte);
		$em->flush();

		// creation du contact
		$contact->setCompte($compte);
		if( $ContactWebForm->getPrenomContact() != '' && $posts[0]['prenomContact'] != '' ) $contact->setPrenom($posts[0]['prenomContact']);
		if( $ContactWebForm->getNomContact() != '' && $posts[0]['nomContact'] != '' ) $contact->setNom($posts[0]['nomContact']);
		if( $ContactWebForm->getAdresse() != '' && $posts[0]['adresse'] != '' ) $contact->setAdresse($posts[0]['adresse']);
		if( $ContactWebForm->getCodePostal() != '' && $posts[0]['codePostal'] != '' ) $contact->setCodePostal($posts[0]['codePostal']);
		if( $ContactWebForm->getVille() != '' && $posts[0]['ville'] != '' ) $contact->setVille($posts[0]['ville']);
		if( $ContactWebForm->getRegion() != '' && $posts[0]['region'] != '' ) $contact->setRegion($posts[0]['region']);
		if( $ContactWebForm->getPays() != '' && $posts[0]['pays'] != '' ) $contact->setPays($posts[0]['pays']);
		if( $ContactWebForm->getTelephoneFixe() != '' && $posts[0]['telephoneFixe'] != '' ) $contact->setTelephoneFixe($posts[0]['telephoneFixe']);
		if( $ContactWebForm->getTelephonePortable() != '' && $posts[0]['telephonePortable'] != '' ) $contact->setTelephonePortable($posts[0]['telephonePortable']);
		if( $ContactWebForm->getEmail() != '' && $posts[0]['email'] != '' ) $contact->setEmail($posts[0]['email']);
		if( $ContactWebForm->getFax() != '' && $posts[0]['fax'] != '' ) $contact->setFax($posts[0]['fax']);
		if( $ContactWebForm->getReseau() != '' ) $contact->setReseau($ContactWebForm->getReseau());
		if( $ContactWebForm->getOrigine() != '' ) $contact->setOrigine($ContactWebForm->getOrigine());
		if( $ContactWebForm->getCarteVoeux() != '' ) $contact->setCarteVoeux($ContactWebForm->getCarteVoeux());
		if( $ContactWebForm->getNewsletter() != '' ) $contact->setNewsletter($ContactWebForm->getNewsletter());

		// ajout de setting
		foreach( $ContactWebForm->getSettings() as $settings )
		{
			$contact->addSetting($settings);
		}

		$contact->setUserGestion($ContactWebForm->getUserGestion());
		$em->persist($contact);
		$em->flush();

		// ajout impulsion
		if( $ContactWebForm->getGestionnaireSuivi() != '' && $ContactWebForm->getDelaiUnit() != '' && $ContactWebForm->getDelaiNum() != '')
		{
			$impulsion = new Impulsion();
			$impulsion->setUser($ContactWebForm->getGestionnaireSuivi());
			$impulsion->setContact($contact);
			$impulsion->setDelaiNum($ContactWebForm->getDelaiNum());
			$impulsion->setDelaiUnit($ContactWebForm->getDelaiUnit());
			$impulsion->setDateCreation(new \DateTime(date('Y-m-d')));
			$em->persist($impulsion);
			$em->flush();
		}

		// envoi email
		if( $ContactWebForm->getEnvoyerEmail() && $posts[0]['email'] != '' )
		{
			//echo $ContactWebForm->getObjetEmail() . ' ----- '. $ContactWebForm->getUserGestion()->getEmail() .'=>'. $ContactWebForm->getExpediteur() . $posts[0]['email'] . $ContactWebForm->getCorpsEmail() . $ContactWebForm->getUserGestion()->getEmail();
			//exit;
			$mail = (new \Swift_Message)
				->setSubject($ContactWebForm->getObjetEmail())
				->setFrom(array($ContactWebForm->getUserGestion()->getEmail() => $ContactWebForm->getExpediteur()))
				->setTo($posts[0]['email'])
				->setBody($ContactWebForm->getCorpsEmail(), 'text/html')
			;
			if( $ContactWebForm->getCopieEmail() ) $mail->addCc($ContactWebForm->getUserGestion()->getEmail());
			$this->get('mailer')->send($mail);
		}
		return $this->redirect($ContactWebForm->getReturnUrl());
	}


}
