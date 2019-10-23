<?php

namespace AppBundle\Controller\NDF;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormError;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

use AppBundle\Entity\NDF\NoteFrais;
use AppBundle\Entity\NDF\Recu;
use AppBundle\Entity\Compta\Depense;
use AppBundle\Entity\Compta\LigneDepense;

use AppBundle\Form\NDF\RecuType;
use AppBundle\Form\NDF\NoteFraisType;
use AppBundle\Form\NDF\NoteFraisUploadType;

use PHPExcel_IOFactory;

class NDFController extends Controller
{

	/**
	 * @Route("/ndf/recu/upload", name="ndf_recu_upload")
	 */
	public function NDFUploadRecuAction(Request $request)
	{
		if(!$this->getUser()->hasRole('ROLE_NDF')){
			throw new AccessDeniedException;
		}
		
		$form = $this->createFormBuilder()->getForm();
		$form->handleRequest($request);

		if ($form->isValid()) {

			//create entity
			$recu = new Recu();

			$recu->setUser($this->getUser());
			$recu->setDateCreation(new \DateTime(date('Y-m-d')));
			$recu->setUserCreation($this->getUser());
			$recu->setEtat('PROCESSING');

			//upload on server
			$file = $request->files->get('file');
			$filename = date('Ymdhms').'-'.$file->getClientOriginalName();
			$path =  $this->get('kernel')->getRootDir().'/../'.$recu->getPath();
			$file->move($path, $filename);

			$recu->setFile($filename);
			$em = $this->getDoctrine()->getManager();
			$em->persist($recu);
			$em->flush();

		}

		return $this->render('ndf/recu/ndf_recu_upload.html.twig', array(
			'form' => $form->createView()
		));
	}

	/**
	 * @Route("/ndf/recu/ajouter/{deplacementVoiture}", name="ndf_recu_ajouter")
	 */
	public function NDFRecuAjouterAction($deplacementVoiture = false)
	{
		$em = $this->getDoctrine()->getManager();

		$recu = new Recu();
		$recu->setDeplacementVoiture($deplacementVoiture);

		$fc = null;
		$ccDefaut = null;

		if($this->getUser()->getCompany()->isNicomak()){
			$fc = $em->getRepository('AppBundle:Settings')->findOneBy(array(
				'company' => $this->getUser()->getCompany(),
				'parametre' => 'analytique',
				'valeur' => 'FC'
			));

			$deplacements = $em->getRepository('AppBundle:Compta\CompteComptable')->findOneBy(array(
				'company' => $this->getUser()->getCompany(),
				'nom' => 'Voyages et déplacements',
			));

			$ccDefaut = $this->getUser()->getCompteComptableNoteFrais() ? $this->getUser()->getCompteComptableNoteFrais() : $deplacements;
		}
		
		$form = $this->createForm(new RecuType($this->getUser()->getCompany()->getId(), $fc, $ccDefaut, $deplacementVoiture, $this->getUser()), $recu);

		$form->add('next', 'submit', array(
			'label' => 'Enregistrer et ajouter un autre reçu'
		));

		$request = $this->getRequest();
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {

			if($form['projet_entity']->getData()){
				$actionCommerciale = $em->getRepository('AppBundle:CRM\Opportunite')->findOneById($form['projet_entity']->getData());
				if($actionCommerciale){
					$recu->setActionCommerciale($actionCommerciale);
				}
			}
			
			$recu->setDateCreation(new \DateTime(date('Y-m-d')));
			$recu->setUserCreation($this->getUser());
			$recu->setUser($this->getUser());
			$recu->setEtat('READ');
			
			$em->persist($recu);
			$em->flush();

			if($form->get('next')->isClicked()){
				return $this->redirect($this->generateUrl(
					'ndf_recu_ajouter'
				));
			}

			return $this->redirect($this->generateUrl(
					'ndf_recus_liste'
			));
		}

		return $this->render('ndf/recu/ndf_recu_ajouter.html.twig', array(
			'form' => $form->createView(),
			'recu' => $recu,
			'deplacementVoiture' => $deplacementVoiture
		));
	}


	/**
	 * @Route("/ndf/recu/liste", name="ndf_recus_liste")
	 */
	public function NDFRecusListeAction()
	{
		$recuRepo = $this->getDoctrine()->getManager()->getRepository('AppBundle:NDF\Recu');
		$arr_recus = $recuRepo->findBy(array(
			'ligneDepense' => null,
			'user' => $this->getUser()
		));

		$total = 0;
		foreach($arr_recus as $recu){
			$total+= $recu->getMontantTTC();
		}

		return $this->render('ndf/recu/ndf_recus_liste.html.twig', array(
			'arr_recus' => $arr_recus,
			'total' => $total
		));
	}

	/**
	 * @Route("/ndf/recu/afficher-modal/{id}", name="ndf_recu_afficher_modal")
	 */
	public function NDFRecuAfficherModalAction(Recu $recu)
	{

		return $this->render('ndf/recu/ndf_recu_modal.html.twig', array(
			'recu' => $recu
		));
	}

	/**
	 * @Route("/ndf/recu/modifier/{id}", name="ndf_recu_modifier")
	 */
	public function NDFRecuModifierAction(Recu $recu)
	{
		$em = $this->getDoctrine()->getManager();

		$form = $this->createForm(new RecuType($this->getUser()->getCompany()->getId(), null, null, $recu->getDeplacementVoiture()), $recu);

		if($recu->getActionCommerciale()){
			$form->get('projet_name')->setData($recu->getActionCommerciale()->getNom());
			$form->get('projet_entity')->setData($recu->getActionCommerciale()->getId());
		}


		$request = $this->getRequest();
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$actionCommerciale = $em->getRepository('AppBundle:CRM\Opportunite')->findOneById($form['projet_entity']->getData());
			if($actionCommerciale){
				$recu->setActionCommerciale($actionCommerciale);
			}
			$recu->setDateEdition(new \DateTime(date('Y-m-d')));
			$recu->setUserEdition($this->getUser());
			$recu->setEtat('READ');
			
			$em->persist($recu);
			$em->flush();

			return $this->redirect($this->generateUrl(
					'ndf_recus_liste'
			));
		}

		return $this->render('ndf/recu/ndf_recu_modifier.html.twig', array(
			'recu' => $recu,
			'form' => $form->createView(),
		));
	}

	/**
	 * @Route("/ndf/recu/non-refacturable/{id}", name="ndf_recu_non_refacturable")
	 */
	public function NDFRecuNonRefacturableAction(Recu $recu)
	{
		$actionCommerciale = $recu->getActionCommerciale();

		$em = $this->getDoctrine()->getManager();
		$recu->setRefacturable(false);
		$recu->removeActionCommerciale();
			
		$em->persist($recu);
		$em->flush();

		return $this->redirect($this->generateUrl(
			'crm_action_commerciale_voir', array('id' => $actionCommerciale->getId())
		));
	}

	/**
	 * @Route("/ndf/recu/supprimer/{id}", name="ndf_recu_supprimer")
	 */
	public function NDFRecuSupprimerAction(Recu $recu)
	{
		$form = $this->createFormBuilder()->getForm();

		$request = $this->getRequest();
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {

			$em = $this->getDoctrine()->getManager();
			$em->remove($recu);
			$em->flush();

			//delete from Receipt Bank
			/**
			 * @todo : connect to Receipt Bank API and delete
			 */

			return $this->redirect($this->generateUrl(
					'ndf_recus_liste'
			));
		}

		return $this->render('ndf/recu/ndf_recu_supprimer.html.twig', array(
				'form' => $form->createView(),
				'recu' => $recu
		));
	}

	/**
	 * @Route("/ndf/liste", name="ndf_liste")
	 */
	public function NDFListeAction()
	{
		$ndfRepo = $this->getDoctrine()->getManager()->getRepository('AppBundle:NDF\NoteFrais');
		$arr_ndf = $ndfRepo->findBy(array(
			'user' => $this->getUser()
		), array(
			'year' => 'DESC',
			'month' => 'DESC'
		));

		return $this->render('ndf/ndf_liste.html.twig', array(
			'arr_ndf' => $arr_ndf
		));
	}

	/**
	 * @Route("/ndf/ajouter", name="ndf_ajouter")
	 */
	public function NDFAjouterAction()
	{
		$em = $this->getDoctrine()->getManager();
		$journalAchatsService = $this->container->get('appbundle.compta_journal_achats_controller');
		$lettrageService = $this->get('appbundle.compta_lettrage_service');

		$ndf = new NoteFrais();

		$recuRepo = $this->getDoctrine()->getManager()->getRepository('AppBundle:NDF\Recu');
		$arr_recus = $recuRepo->findBy(array(
				'user' => $this->getUser(),
				'etat' => 'READ',
				'ligneDepense' => NULL
			), array(
				'date' => 'DESC'
		));

		$arr_recus_by_id = array();

	  	$request = $this->get('request');
		$formatter = new \IntlDateFormatter($request->getLocale(), \IntlDateFormatter::LONG, \IntlDateFormatter::LONG);
		$formatter->setPattern('MMMM');

		$arr_recus_by_month = array();
		foreach($arr_recus as $recu){
			$arr_recus_by_month[ucfirst($formatter->format($recu->getDate()))][$recu->getId()] = $recu;
		}

		$form = $this->createForm(new NoteFraisType($arr_recus_by_month), $ndf);

		$request = $this->getRequest();
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {

			$ndf->setDateCreation(new \DateTime(date('Y-m-d')));
			$ndf->setUserCreation($this->getUser());
			$ndf->setUser($this->getUser());

			if ($form->get('draft')->isClicked()) {
				$ndf->setEtat('DRAFT');
			} else {

				if($ndf->getSignatureEmploye()){

					if(!$this->getUser()->getSignature()){
						$form->get('signatureEmploye')->addError(new FormError('Vous devez uploader votre signature'));
						return $this->render('ndf/ndf_ajouter.html.twig', array(
							'form' => $form->createView(),
							'ndf' => $ndf
						));
					}

					$ndf->setEtat('ENREGISTRE');
				} else {
					$form->get('signatureEmploye')->addError(new FormError('Vous devez cocher cette case'));
					return $this->render('ndf/ndf_ajouter.html.twig', array(
						'form' => $form->createView(),
						'ndf' => $ndf
					));
				}

			}


			if($ndf->getUser()->getCompteComptableNoteFrais()){
				$ndf->setCompteComptable($ndf->getUser()->getCompteComptableNoteFrais());
			} else {
				//find default
				$compteComptableRepo = $this->getDoctrine()->getManager()->getRepository('AppBundle:Compta\CompteComptable');
				$compteDefault = $compteComptableRepo->findOneBy(array(
					'company' => $this->getUser()->getCompany(),
					'num' => '42100000'
				));
				$ndf->setCompteComptable($compteDefault);
			}

			$em->persist($ndf);

			//recuperation des données du formulaire
			$arr_recus_id = $form['recus']->getData();
			$arr_recus_checked = array();
			$arr_analytiques = array();
			$arr_dates = array();
			foreach($arr_recus_id as $id){
				$recu = $recuRepo->find($id);
				$arr_recus_checked[] = $recu;
				if(!array_key_exists($recu->getAnalytique()->getId(), $arr_analytiques)){
					$arr_analytiques[$recu->getAnalytique()->getId()] = $recu->getAnalytique();
				}

				$date = $recu->getDate()->format('Y-m');
				if( !array_key_exists($date, $arr_dates) ){
					$arr_dates[$date] = 0;
				}
				$arr_dates[$date]++;

			}

			arsort($arr_dates);
			reset($arr_dates);
			$ndfDate = explode('-',key($arr_dates));
			$ndf->setYear($ndfDate[0]);
			$ndf->setMonth($ndfDate[1]);

			$numService = $this->container->get('appbundle.num_service');
			$arr_num = $numService->getNextNum('DEPENSE', $ndf->getUser()->getCompany());
			$currentNum = $arr_num['num'];

			$lettrage = $lettrageService->findNextNum($ndf->getCompteComptable());

			foreach($arr_analytiques as $analytique){

				$depense = new Depense();
				$depense->setAnalytique($analytique);
				$depense->setLibelle('NDF '.$ndf->getUser()->getFirstname().' '.$ndf->getUser()->getLastname().' '.$ndf->getMonth().'/'.$ndf->getYear().' ('.$analytique.')');
				$depense->setCompte(null);
				$dateNDF = \DateTime::createFromFormat('Y-m-d', $ndf->getYear().'-'.$ndf->getMonth().'-01');
				$depense->setDate($dateNDF);
				$depense->setNoteFrais($ndf);
				$depense->setDateCreation(new \DateTime(date('Y-m-d')));
				$depense->setUserCreation($this->getUser());
				$depense->setEtat("ENREGISTRE");
				$numDepense = $arr_num['prefixe'].$currentNum;
				$depense->setNum($numDepense);
				$currentNum++;

				foreach($arr_recus_checked as $recu){
					//créer les lignes
					if($recu->getAnalytique()->getId() == $analytique->getId()){

						$ligne = new LigneDepense();
						$nom = $recu->getDate()->format('d/m/Y').' : ';
						if($recu->getDeplacementVoiture() === true){
							$nom.= 'trajet '.$recu->getTrajet();
						} else {
							$nom.=$recu->getFournisseur();
						}

						if($recu->getLibelle()){
							$nom.=' - '.$recu->getLibelle();
						}
						$ligne->setNom($nom);
						$ligne->setMontant($recu->getMontantHT());
						$ligne->setTaxe($recu->getTva());
						$ligne->setDepense($depense);
						$ligne->setCompteComptable($recu->getCompteComptable());
						$ligne->setRecu($recu);
						$em->persist($ligne);

						$depense->addLigne($ligne);

						$recu->setLigneDepense($ligne);
						$em->persist($recu);
					}

				}

				$em->persist($depense);
				$ndf->addDepense($depense);

				//ecrire dans le journal des achats
				$journalAchatsService->journalAchatsAjouterDepenseAction(null, $depense);
			}

			$numService->updateDepenseNum($ndf->getUser()->getCompany(), $currentNum);
			$em->flush();

			return $this->redirect($this->generateUrl(
					'ndf_liste'
			));
		}

		return $this->render('ndf/ndf_ajouter.html.twig', array(
			'form' => $form->createView(),
			'ndf' => $ndf
		));
	}

	/**
	 * @Route("/ndf/editer/{id}",
	 *   name="ndf_editer",
	 *   options={"expose"=true}
	 * )
	 */
	public function NDFEditerAction(NoteFrais $ndf)
	{
		$em = $this->getDoctrine()->getManager();
		$recuRepo = $this->getDoctrine()->getManager()->getRepository('AppBundle:NDF\Recu');
		$arr_recus = $recuRepo->findBy(array(
				'user' => $this->getUser(),
				'etat' => 'READ',
				'ligneDepense' => NULL
			), array(
				'date' => 'DESC'
		));

		$arr_recus = array_merge($arr_recus, $ndf->getRecus());

		$arr_recus_by_id = array();

	  	$request = $this->get('request');
		$formatter = new \IntlDateFormatter($request->getLocale(), \IntlDateFormatter::LONG, \IntlDateFormatter::LONG);
		$formatter->setPattern('MMMM');

		$arr_recus_by_month = array();
		foreach($arr_recus as $recu){
			$arr_recus_by_month[ucfirst($formatter->format($recu->getDate()))][$recu->getId()] = $recu;
		}

		$form = $this->createForm(new NoteFraisType($arr_recus_by_month), $ndf);

		$request = $this->getRequest();
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {

			$ndf->setDateEdition(new \DateTime(date('Y-m-d')));
			$ndf->setUserEdition($this->getUser());

			if ($form->get('draft')->isClicked()) {
				$ndf->setEtat('DRAFT');
			} else {
				$ndf->setEtat('ENREGISTRE');
			}

			foreach($ndf->getDepenses() as $formerDepense){
				$ndf->removeDepense($formerDepense);
				$em->remove($formerDepense);
				foreach($formerDepense->getLignes() as $formerLigne){
						$formerDepense->removeLigne($formerLigne);
						$em->remove($formerLigne);
						$formerLigne->getRecu()->setLigneDepense(null);
				}
			}
			$em->flush();

			//recuperation des données du formulaire
			$arr_recus_id = $form['recus']->getData();
			$arr_recus_checked = array();
			$arr_analytiques = array();
			$arr_dates = array();
			foreach($arr_recus_id as $id){
				$recu = $recuRepo->find($id);
				$arr_recus_checked[] = $recu;
				if(!array_key_exists($recu->getAnalytique()->getId(), $arr_analytiques)){
					$arr_analytiques[$recu->getAnalytique()->getId()] = $recu->getAnalytique();
				}

				$date = $recu->getDate()->format('Y-m');
				if( !array_key_exists($date, $arr_dates) ){
					$arr_dates[$date] = 0;
				}
				$arr_dates[$date]++;

			}

			arsort($arr_dates);
			reset($arr_dates);
			$ndfDate = explode('-',key($arr_dates));
			$ndf->setYear($ndfDate[0]);
			$ndf->setMonth($ndfDate[1]);

			$journalAchatsService = $this->container->get('appbundle.compta_journal_achats_controller');
			$numService = $this->container->get('appbundle.num_service');
			$arr_num = $numService->getNextNum('DEPENSE', $ndf->getUser()->getCompany());
			$currentNum = $arr_num['num'];

			foreach($arr_analytiques as $analytique){

				$depense = new Depense();
				$depense->setAnalytique($analytique);
				$depense->setLibelle('NDF '.$ndf->getUser()->getFirstname().' '.$ndf->getUser()->getLastname().' '.$ndf->getMonth().'/'.$ndf->getYear().' ('.$analytique.')');
				$depense->setCompte(null);
				$dateNDF = \DateTime::createFromFormat('Y-m-d', $ndf->getYear().'-'.$ndf->getMonth().'-01');
				$depense->setDate($dateNDF);
				$depense->setNoteFrais($ndf);
				$depense->setDateCreation(new \DateTime(date('Y-m-d')));
				$depense->setUserCreation($this->getUser());
				$depense->setEtat("ENREGISTRE");
				$numDepense = $arr_num['prefixe'].$currentNum;
				$depense->setNum($numDepense);
				$currentNum++;
				$em->persist($depense);

				foreach($arr_recus_checked as $recu){

					$em->persist($recu);

					//créer les lignes
					if($recu->getAnalytique()->getId() == $analytique->getId()){

						$ligne = new LigneDepense();
						$nom = $recu->getDate()->format('d/m/Y').' : '.$recu->getFournisseur();
						if($recu->getLibelle()){
							$nom.=' - '.$recu->getLibelle();
						}
						$ligne->setNom($nom);
						$ligne->setMontant($recu->getMontantHT());
						$ligne->setTaxe($recu->getTva());
						$ligne->setCompteComptable($recu->getCompteComptable());
						$ligne->setRecu($recu);
						$em->persist($ligne);

						$depense->addLigne($ligne);

					}
				}

				//ecrire dans le journal des achats
				$journalAchatsService->journalAchatsAjouterDepenseAction(null, $depense);
			}

			$em->persist($ndf);
			$em->flush();

			$numService->updateDepenseNum($ndf->getUser()->getCompany(), $currentNum);

			return $this->redirect($this->generateUrl(
					'ndf_liste'
			));
		}

		return $this->render('ndf/ndf_editer.html.twig', array(
			'form' => $form->createView(),
			'ndf' => $ndf
		));
	}


	/**
	 * @Route("/ndf/supprimer/{id}",
	 *    name="ndf_supprimer",
	 *    options={"expose"=true}
	 *  )
	 */
	public function NDFSupprimerAction(NoteFrais $ndf)
	{
		$form = $this->createFormBuilder()->getForm();

		$request = $this->getRequest();
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {

			$em = $this->getDoctrine()->getManager();
			$em->remove($ndf);
			$em->flush();

			//delete from Receipt Bank
			/**
			 * @todo : connect to Receipt Bank API and delete
			 */

			return $this->redirect($this->generateUrl(
					'ndf_liste'
			));
		}

		return $this->render('ndf/ndf_supprimer.html.twig', array(
				'form' => $form->createView(),
				'ndf' => $ndf
		));
	}

	/**
	 * @Route("/ndf/valider/{id}",
	 *    name="ndf_valider",
	 *    options={"expose"=true}
	 *  )
	 */
	public function NDFValiderAction(NoteFrais $ndf)
	{
		$ndf->setEtatValide();
		$ndf->setSignatureResponsable(true);
		$em = $this->getDoctrine()->getManager();
		$em->persist($ndf);
		$em->flush();

		return $this->redirect($this->generateUrl('ndf_liste_admin'));
	}

	/**
	 * @Route("/ndf/refuser/{id}",
	 *    name="ndf_refuser",
	 *    options={"expose"=true}
	 *  )
	 */
	public function NDFRefuserAction(NoteFrais $ndf)
	{

		$form = $this->createFormBuilder()->getForm();

		$form->add('message', 'textarea', array(
				'label' => 'Raison du refus',
				'attr' => array('class' => 'tinymce'),
				'required' => true,
				'data' => ''
		));

		$form->add('submit', 'submit', array(
  		  'label' => 'Envoyer',
		  'attr' => array('class' => 'btn btn-success')
		));

		$request = $this->getRequest();
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {

			$objet = "Note de frais refusée";
			$message = '<p>'.$ndf->getUser()->getFirstname().',</p>';
			$message.= '<p>Votre note de frais de '.$ndf->getMonth().'/'.$ndf->getYear().' d\'un montant de '.$ndf->getTotal().' € a été refusée pour la raison suivante :</p>';

			$message.= $form->get('message')->getData();

			try{
				$mail = \Swift_Message::newInstance()
					->setSubject($objet)
					->setFrom($this->getUser()->getEmail())
					->setTo($ndf->getUser()->getEmail())
					->setBody($message, 'text/html')
				;
				$this->get('mailer')->send($mail);
				$this->get('session')->getFlashBag()->add(
						'success',
						'Le message a bien été envoyé.'
				);

				$ndf->setEtatRefus();
				$em = $this->getDoctrine()->getManager();
				$em->persist($ndf);
				$em->flush();

			} catch(\Exception $e){
    			$error =  $e->getMessage();
    			$this->get('session')->getFlashBag()->add('danger', "L'email n'a pas été envoyé pour la raison suivante : $error");
    		}

			return $this->redirect($this->generateUrl('ndf_liste_admin'));
		}

		return $this->render('ndf/ndf_refuser_modal.html.twig', array(
				'form' => $form->createView(),
				'ndf' => $ndf
		));
		
	}


		/**
		 * @Route("/ndf/exporter/{id}", name="ndf_exporter", options={"expose"=true})
		 */
		public function NDFExporterAction(NoteFrais $ndf)
		{

			$html = $this->renderView('ndf/ndf_exporter.html.twig', array(
					'ndf' => $ndf
			));


			$filename = $ndf->getLibelle().'.pdf';

			return new Response(
					$this->get('knp_snappy.pdf')->getOutputFromHtml($html,
							array(
									'margin-bottom' => '10mm',
									'margin-top' => '10mm',
									//'zoom' => 0.8, //prod only, zoom level is not the same on Windows
									'default-header'=>false,
							)
					),
					200,
					array(
							'Content-Type'          => 'application/pdf',
							'Content-Disposition'   => 'attachment; filename='.$filename,
					)
			);


		}

		// /**
		//  * @Route("/ndf/recus/exporter/{id}", name="ndf_recus_exporter")
		//  */
		// public function NDFRecusExporterAction(NoteFrais $ndf)
		// {
		// 	$im = new \Imagick('C:\wamp\www\jaimegerer-ndf\web\images\logo-couleur-500px.png');
		// 	$im->setImageFormat('jpg');
		// 	header('Content-Type: image/jpeg');
		// 	echo $im;

		// 	return new Response();
		// 	return $this->render('ndf/ndf_exporter_recus.html.twig', array(
		// 			'ndf' => $ndf
		// 	));


		// 	$filename = 'recus_'.$ndf->getLibelle().'.pdf';

		// 	return new Response(
		// 			$this->get('knp_snappy.pdf')->getOutputFromHtml($html,
		// 					array(
		// 							'margin-bottom' => '10mm',
		// 							'margin-top' => '10mm',
		// 							'zoom' => 0.8, //prod only, zoom level is not the same on Windows
		// 							'default-header'=>false,
		// 					)
		// 			),
		// 			200,
		// 			array(
		// 					'Content-Type'          => 'application/pdf',
		// 					'Content-Disposition'   => 'attachment; filename='.$filename,
		// 			)
		// 	);


		// }

	/**
	 * @Route("/ndf/recu/get-data/{id}", name="ndf_recu_get_data", options={"expose"=true})
	 */
	public function recuGetData(Recu $recu)
	{
		return $this->render('ndf/ndf_preview_ligne.html.twig', array(
			'recu' => $recu
		));
	}

	/**
	 * @Route("/ndf/liste-admin", name="ndf_liste_admin")
	 */
	public function NDFListeAdminAction()
	{
		return $this->render('ndf/ndf_liste_admin.html.twig');
	}

	/**
	 * @Route("/ndf/liste/ajax",
	 *   name="ndf_liste_ajax",
	 *   options={"expose"=true}
	 * )
	 */
	public function NDFListeAjaxAction()
	{
		$requestData = $this->getRequest();
		$arr_sort = $requestData->get('order');
		$arr_cols = $requestData->get('columns');

		$col = $arr_sort[0]['column'];

		$repository = $this->getDoctrine()->getManager()->getRepository('AppBundle:NDF\NoteFrais');

		$arr_search = $requestData->get('search');
		$etat = $requestData->get('etat');
		$arr_date = $requestData->get('dateRange');
		$arr_creation_date = $requestData->get('dateCreationRange');


		$list = $repository->findForList(
				$this->getUser()->getCompany(),
				$requestData->get('length'),
				$requestData->get('start'),
				$arr_cols[$col]['data'],
				$arr_sort[0]['dir'],
				$arr_search['value'],
				$etat,
				$arr_date,
				$arr_creation_date
		);


		for($i=0; $i<count($list); $i++){

			$arr_f = $list[$i];

			$noteFrais = $repository->find($arr_f['id']);
			$total = $noteFrais->getTotal();
			$list[$i]['total'] = $total;
			$list[$i]['etat'] = $noteFrais->getEtatPourCompta();
		}

		$response = new JsonResponse();
		$response->setData(array(
			'draw' => intval( $requestData->get('draw') ),
			'recordsTotal' => $repository->count($this->getUser()->getCompany()),
			'recordsFiltered' => $repository->countForList($this->getUser()->getCompany(), $arr_search['value'],$etat,$arr_date),
			'aaData' => $list,
		));

		return $response;
	}

	/**
	 * @Route("/ndf/voir/{id}",
	 *   name="ndf_voir",
	 *   options={"expose"=true}
	 * )
	 */
	public function NDFVoirAction(NoteFrais $noteFrais)
	{
		return $this->render('ndf/compta_note_frais_voir.html.twig', array(
				'noteFrais' => $noteFrais,
		));
	}

	// /**
	//  * @Route("/ndf/uploader", name="ndf_uploader")
	//  */
	// public function NDFUploaderAction()
	// {
	// 	$em = $this->getDoctrine()->getManager();
	// 	$compteRepo = $em->getRepository('AppBundle:CRM\Compte');
	// 	$settingsRepository = $em->getRepository('AppBundle:Settings');
	// 	$compteComptableRepo = $em->getRepository('AppBundle:Compta\CompteComptable');

	// 	$noteFrais = new NoteFrais();

	// 	$form = $this->createForm(
	// 			new NoteFraisUploadType($this->getUser()->getCompany()->getId()),
	// 			$noteFrais
	// 	);

	// 	$request = $this->getRequest();
	// 	$form->handleRequest($request);

	// 	if ($form->isSubmitted() && $form->isValid()) {

	// 		//recuperation des données du formulaire
	// 		$file = $form['file']->getData();

	// 		//enregistrement temporaire du fichier uploadé
	// 		$filename = date('Ymdhms').'-'.$this->getUser()->getId().'-'.$file->getClientOriginalName();
	// 		$path =  $this->get('kernel')->getRootDir().'/../web/upload/compta/notes-de-frais';
	// 		$file->move($path, $filename);

	// 		//lecture du fichier Excel
	// 		$fileType = PHPExcel_IOFactory::identify($path.'/'.$filename);
	// 		$readerObject = PHPExcel_IOFactory::createReader($fileType);
	// 		$objPHPExcel = $readerObject->load($path.'/'.$filename);
	// 		//fichier dans un array associatif
	// 		$arr_data = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);

	// 		$arr_analytiques = array();
	// 		foreach($arr_data as $data){
	// 			if(!in_array($data['S'], $arr_analytiques)){
	// 				$arr_analytiques[] = $data['S'];
	// 			}
	// 		}

	// 		$noteFrais->setEtat('ENREGISTRE');
	// 		$noteFrais->setDateCreation(new \DateTime(date('Y-m-d')));
	// 		$noteFrais->setUserCreation($this->getUser());

	// 		$em->persist($noteFrais);

	// 		foreach($arr_analytiques as $analytique){

	// 			$settingsAnalytique = $settingsRepository->findOneBy(array('module' => 'CRM', 'parametre' => 'ANALYTIQUE', 'company'=>$this->getUser()->getCompany(), 'valeur' => $analytique));
	// 			if(!$settingsAnalytique){
	// 				continue;
	// 			}

	// 			$arr_comptes = array();

	// 			$depense = new Depense();
	// 			$depense->setLibelle('NDF '.$noteFrais->getLibelle().' '.$noteFrais->getMonth().'/'.$noteFrais->getYear().' ('.$analytique.')');

	// 			$depense->setCompte(null);

	// 			$dateNDF = \DateTime::createFromFormat('Y-m-d', $noteFrais->getYear().'-'.$noteFrais->getMonth().'-01');
	// 			$depense->setDate($dateNDF);

	// 			$depenseYear = $depense->getDate()->format('Y');


	// 			$settingsNum = $settingsRepository->findOneBy(array('module' => 'COMPTA', 'parametre' => 'NUMERO_DEPENSE', 'company'=>$this->getUser()->getCompany()));
	// 			if( count($settingsNum) == 0)
	// 			{
	// 				$settingsNum = new Settings();
	// 				$settingsNum->setModule('COMPTA');
	// 				$settingsNum->setParametre('NUMERO_DEPENSE');
	// 				$settingsNum->setHelpText('Le numéro de dépense courant - ne pas modifier si vous n\'êtes pas sûr de ce que vous faites !');
	// 				$settingsNum->setTitre('Numéro de dépense');
	// 				$settingsNum->setType('NUM');
	// 				$settingsNum->setNoTVA(false);
	// 				$settingsNum->setCategorie('DEPENSE');
	// 				$settingsNum->setCompany($this->getUser()->getCompany());
	// 				$currentNum = 1;
	// 			}
	// 			else
	// 			{
	// 				$currentNum = $settingsNum->getValeur();
	// 			}
	// 			if($depenseYear != date("Y")){
	// 				//si la dépense est antidatée, récupérer le dernier numéro de dépense de l'année concernée
	// 				$prefixe = 'D-'.$depenseYear.'-';
	// 				$depenseRepo = $em->getRepository('AppBundle:Compta\Depense');
	// 				$lastNum = $depenseRepo->findMaxNumForYear('DEPENSE', $depenseYear, $this->getUser()->getCompany());
	// 				$lastNum = substr($lastNum, 7);
	// 				$lastNum++;
	// 				$depense->setNum($prefixe.$lastNum);
	// 			} else {
	// 				$prefixe = 'D-'.date('Y').'-';
	// 				if($currentNum < 10){
	// 					$prefixe.='00';
	// 				} else if ($currentNum < 100){
	// 					$prefixe.='0';
	// 				}
	// 				$depense->setNum($prefixe.$currentNum);

	// 				//mise à jour du numéro de facture
	// 				$currentNum++;
	// 				$settingsNum->setValeur($currentNum);
	// 				$em->persist($settingsNum);
	// 			}


	// 			$depense->setAnalytique($settingsAnalytique);

	// 			$depense->setNoteFrais($noteFrais);
	// 			$depense->setDateCreation(new \DateTime(date('Y-m-d')));
	// 			$depense->setUserCreation($this->getUser());
	// 			$depense->setEtat("ENREGISTRE");

	// 			$em->persist($depense);

	// 			foreach($arr_data as $data){
	// 				if($data['S'] == $analytique){

	// 					if(!array_key_exists($data['G'], $arr_comptes)){
	// 						$arr_comptes[$data['G']] = array('HT' => 0, 'TVA' => 0);
	// 					}

	// 					$ligne = new LigneDepense();
	// 					$ligne->setNom($data['C'].' : '.$data['F']);
	// 					$ligne->setMontant($data['M']-$data['L']);
	// 					$ligne->setTaxe($data['L']);

	// 					$ligne->setDepense($depense);

	// 					$cc = $compteComptableRepo->findOneBy(array('company' => $this->getUser()->getCompany(), 'nom' => $data['G']));
	// 					$ligne->setCompteComptable($cc);

	// 					$em->persist($ligne);
	// 					$depense->addLigne($ligne);

	// 				}
	// 			}

	// 			$em->persist($depense);

	// 			$em->flush();

	// 			//ecrire dans le journal des achats
	// 			$journalAchatsService = $this->container->get('appbundle.compta_journal_achats_controller');
	// 			$journalAchatsService->journalAchatsAjouterDepenseAction(null, $depense);
	// 		}

	// 		return $this->redirect($this->generateUrl(
	// 				'ndf_voir', array('id' => $noteFrais->getId())));

	// 	}
	// 	$ccRepo = $this->getDoctrine()->getManager()->getRepository('AppBundle:Compta\CompteComptable');
	// 	$compteComptable = $ccRepo->findOneBy(array(
	// 		'company' => $this->getUser()->getCompany(),
	// 		'num' => 421
	// 	));

	// 	if($compteComptable == null){
	// 		$compteComptable = $ccRepo->findOneBy(array(
	// 				'company' => $this->getUser()->getCompany(),
	// 				'num' => 42100000
	// 		));
	// 	}

	// 	return $this->render('ndf/compta_note_frais_ajouter.html.twig', array(
	// 			'form' => $form->createView(),
	// 			'compteComptable' => $compteComptable
	// 	));
	// }


	// /**
	//  * @Route("/ndf/rb", name="ndf_rb")
	//  */
	// public function noteFraisRBAction()
	// {
	// //	$receiptBankService = $this->get('appbundle.receiptbank_service');
	// 	$browser = $this->container->get('buzz');
	//
	// 	$response = $browser->post('https://app.rblogistics.com/oauth/authorize',
	// 		array(),
	// 		json_encode(array(
	// 			'client_id'=>'2211839188',
	// 			'response_type'=>'code',
	// 			'redirect_uri' => 'http://dev.jaimegerer-ndf.com'
	// 	)) );
	//
	// 	echo $response;
	//
	// 	return 0;
	// }

}
