<?php

namespace App\Controller\CRM;

use App\Entity\CRM\DocumentPrix;
use App\Entity\CRM\Frais;
use App\Entity\CRM\Opportunite;
use App\Entity\CRM\OpportuniteSousTraitance;
use App\Entity\CRM\PlanPaiement;
use App\Entity\CRM\PriseContact;
use App\Entity\CRM\Produit;
use App\Form\CRM\ActionCommercialeType;
use App\Form\CRM\FraisType;
use App\Form\CRM\OpportuniteSousTraitanceType;
use App\Form\CRM\OpportuniteWonBonCommandeType;
use App\Form\CRM\OpportuniteWonPlanPaiementType;
use App\Form\CRM\OpportuniteWonRepartitionType;
use App\Service\ChartService;
use App\Util\DependancyInjectionTrait\ActionCommercialeFileUploaderTrait;
use App\Util\DependancyInjectionTrait\CompteComptableServiceTrait;
use App\Util\DependancyInjectionTrait\DevisServiceTrait;
use App\Util\DependancyInjectionTrait\FactureServiceTrait;
use App\Util\DependancyInjectionTrait\JournalVentesTrait;
use App\Util\DependancyInjectionTrait\OpportuniteServiceTrait;
use Swift_Attachment;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ActionCommercialeController extends AbstractController
{

	const FILTER_DATE_NONE      = 0;
    const FILTER_DATE_MONTH     = 1;
    const FILTER_DATE_2MONTH    = 2;
    const FILTER_DATE_YEAR      = 3;
    const FILTER_DATE_CUSTOM    = -1;

    use OpportuniteServiceTrait;
    use DevisServiceTrait;
    use FactureServiceTrait;
    use CompteComptableServiceTrait;
    use ActionCommercialeFileUploaderTrait;
    use JournalVentesTrait;

	/**
	 * @Route("/crm/action-commerciale/liste/",
	 *   name="crm_action_commerciale_liste",
	 *  )
	 */
	public function actionCommercialeListeAction()
	{
		$arr_gestionnaires = array();
		$userRepo = $this->getDoctrine()->getManager()->getRepository('App:User');
		$arr_gestionnaires = $userRepo->findBy(array(
			'company' => $this->getUser()->getCompany(),
			'enabled' => true,
			'competCom' => true
		));

		return $this->render('crm/action-commerciale/crm_action_commerciale_liste.html.twig', array(
			'arr_gestionnaires' => $arr_gestionnaires
		));
	}	

	/**
	 * @Route("/crm/action-commerciale/liste/ajax",
	 *   name="crm_action_commerciale_liste_ajax",
	 *   options={"expose"=true}
	 * )
	 */
	public function actionCommercialeListeAjaxAction(Request $requestData)
	{
		$arr_sort = $requestData->get('order');
		$arr_cols = $requestData->get('columns');

		$col = $arr_sort[0]['column'];

		$repository = $this->getDoctrine()->getManager()->getRepository('App:CRM\Opportunite');
		$arr_search = $requestData->get('search');

		$etat = $requestData->get('etat');
		$gestionnaire = $requestData->get('gestionnaire');

		$dateSearch = $requestData->get('date_search', 0);
        $startDate = null;
        $endDate = null;
        switch ($dateSearch) {
            case self::FILTER_DATE_MONTH:
                $startDate = new \DateTime('first day of this month');
                $endDate = new \DateTime('last day of this month');
                break;
            case self::FILTER_DATE_2MONTH:
                $startDate = new \DateTime('first day of previous month');
                $endDate = new \DateTime('last day of this month');
                break;
            case self::FILTER_DATE_YEAR:
                $startDate = new \DateTime('first day of january');
                $endDate = new \DateTime('last day of december');
                break;
            case self::FILTER_DATE_CUSTOM:
                $startDate = $requestData->get('start_date', null);
                $startDate = ($startDate ? new \DateTime($startDate) : null);
                $endDate = $requestData->get('end_date', null);
                $endDate = ($endDate ? new \DateTime($endDate) : null);
                break;
        }

        $dateRange = null;
        if ($startDate) {
            $dateRange = array(
                'start' => $startDate,
                'end'   => $endDate
            );
        }

		$list = $repository->findForList(
			$this->getUser()->getCompany(),
			$requestData->get('length'),
			$requestData->get('start'),
			$arr_cols[$col]['data'],
			$arr_sort[0]['dir'],
			$arr_search['value'],
			$dateRange,
			$etat,
			$gestionnaire
		);

		for($i=0; $i<count($list); $i++){
			$arr_o = $list[$i];
			$opportunite = $repository->find($arr_o['id']);
			if($opportunite->getDevis()){
				$list[$i]['numero_devis'] = $opportunite->getDevis()->getNum();	
				$totaux = $opportunite->getDevis()->getTotaux();
				$list[$i]['totaux'] = $totaux;
				$list[$i]['devis_id'] = $opportunite->getDevis()->getId();
			} else {
				$list[$i]['numero_devis'] = null;	
				$list[$i]['totaux']['HT'] = $opportunite->getMontant();
				$list[$i]['totaux']['TTC'] = null;
				$list[$i]['devis_id'] = null;
			}

			$bonsCommande = "";
			foreach($opportunite->getBonsCommande() as $bc){
				$bonsCommande.=$bc->getNum().'<br />';
			}
			$list[$i]['bon_commande'] = $bonsCommande;

		}

		$response = new JsonResponse();
		$response->setData(array(
			'draw' => intval( $requestData->get('draw') ),
			'recordsTotal' => $repository->custom_count($this->getUser()->getCompany()),
			'recordsFiltered' => $repository->countForList($this->getUser()->getCompany(),$arr_search['value'],$dateRange,$etat),
			'aaData' => $list,
		));

		return $response;
	}

	/**
	 * @Route("/crm/action-commerciale/reporting",
	 *   name="crm_action_commerciale_reporting",
	 *  )
	 */
	public function actionCommercialeReporting(ChartService $chartService){
		$dataTauxTransformation = $this->opportuniteService->getTauxTransformationData($this->getUser()->getCompany(), date('Y'));

		$chartTauxTransformation = $chartService->opportuniteTauxTransformationPieChart($dataTauxTransformation);

		$dataChartActionsCoAnalytique = $this->opportuniteService->getDataChartActionsCoAnalytique($this->getUser()->getCompany(), date('Y'));
		$chartActionsCoAnalytique = $chartService->actionsCoAnalytique($dataChartActionsCoAnalytique);

		$dataChartActionsCoRhoneAlpes = $this->opportuniteService->getDataChartActionsCoRhoneAlpes($this->getUser()->getCompany(), date('Y'));
		$chartActionsCoRhoneAlpes = $chartService->actionsCoRhoneAlpes($dataChartActionsCoRhoneAlpes);

		$dataChartActionsCoAnalytique3Mois = $this->opportuniteService->getDataChartActionsCoAnalytique3Mois($this->getUser()->getCompany());
		$chartActionsCoAnalytique3Mois = $chartService->actionsCoAnalytique($dataChartActionsCoAnalytique3Mois);

		$dataChartActionsCoRhoneAlpes3Mois = $this->opportuniteService->getDataChartActionsCoRhoneAlpes3Mois($this->getUser()->getCompany(), date('Y'));
		$chartActionsCoRhoneAlpes3Mois = $chartService->actionsCoRhoneAlpes($dataChartActionsCoRhoneAlpes3Mois);

		$dataChartTempsCommercialeAnalytique = $this->opportuniteService->getDataChartTempsCommercialeAnalytique($this->getUser()->getCompany(), date('Y'));
		$chartTempsCommercialeAnalytique = $chartService->actionsCoTempsCommercialeAnalytique($dataChartTempsCommercialeAnalytique);

		$dataChartTempsCommercialeAO = $this->opportuniteService->getDataChartTempsCommercialeAO($this->getUser()->getCompany(), date('Y'));
		$chartTempsCommercialeAO = $chartService->actionsCoTempsCommercialeAO($dataChartTempsCommercialeAO);

		$dataChartTempsCommercialeAnalytiqueRepartition = $this->opportuniteService->getDataChartTempsCommercialeAnalytiqueRepartition($this->getUser()->getCompany(), date('Y'));
		$chartTempsCommercialeAnalytiqueRepartition = $chartService->actionsCoTempsCommercialeAnalytiqueRepartition($dataChartTempsCommercialeAnalytiqueRepartition);

		$dataChartTempsCommercialeAORepartition = $this->opportuniteService->getDataChartTempsCommercialeAORepartition($this->getUser()->getCompany(), date('Y'));
		$chartTempsCommercialeAORepartition = $chartService->actionsCoTempsCommercialeAORepartition($dataChartTempsCommercialeAORepartition);

		$dataChartTempsCommercialePrivePublic = $this->opportuniteService->getDataChartTempsCommercialePrivePublic($this->getUser()->getCompany(), date('Y'));
		$chartTempsCommercialePrivePublic = $chartService->actionsCoTempsCommercialePrivePublic($dataChartTempsCommercialePrivePublic);

		$dataChartTempsCommercialePrivePublicRepartition = $this->opportuniteService->getDataChartTempsCommercialePrivePublicRepartition($this->getUser()->getCompany(), date('Y'));
		$chartTempsCommercialePrivePublicRepartition = $chartService->actionsCoTempsCommercialePrivePublicRepartition($dataChartTempsCommercialePrivePublicRepartition);

		$dataChartTempsCommercialParMontant = $this->opportuniteService->getDataChartTempsCommercialParMontant($this->getUser()->getCompany(), date('Y'));
		$chartTempsCommercialParMontant = $chartService->tempsCommercialParMontant($dataChartTempsCommercialParMontant);


		return $this->render('crm/action-commerciale/crm_action_commerciale_reporting.html.twig', array(
			'chartTauxTransformation' => $chartTauxTransformation,
			'chartActionsCoAnalytique' => $chartActionsCoAnalytique,
			'chartActionsCoRhoneAlpes' => $chartActionsCoRhoneAlpes,
			'chartActionsCoAnalytique3Mois' => $chartActionsCoAnalytique3Mois,
			'chartActionsCoRhoneAlpes3Mois' => $chartActionsCoRhoneAlpes3Mois,
			'chartTempsCommercialeAnalytique' => $chartTempsCommercialeAnalytique,
			'chartTempsCommercialeAO' => $chartTempsCommercialeAO,
			'chartTempsCommercialeAnalytiqueRepartition' => $chartTempsCommercialeAnalytiqueRepartition,
			'chartTempsCommercialeAORepartition' => $chartTempsCommercialeAORepartition,
			'chartTempsCommercialePrivePublic' => $chartTempsCommercialePrivePublic,
			'chartTempsCommercialePrivePublicRepartition' => $chartTempsCommercialePrivePublicRepartition,
			'chartTempsCommercialParMontant' => $chartTempsCommercialParMontant,
		));
	}

	/**
	 * @Route("/crm/action-commerciale/ajouter/{compteId}/{contactId}",
	 *   name="crm_action_commerciale_ajouter",
	 *  )
	 */
	public function actionCommercialeAjouterAction(Request $request, $compteId = null, $contactId = null)
	{
		$em = $this->getDoctrine()->getManager();
		$compteRepo = $em->getRepository('App:CRM\Compte');
		$contactRepo = $em->getRepository('App:CRM\Contact');

		$opportunite = new Opportunite();
		$devis = new DocumentPrix($this->getUser()->getCompany(),'DEVIS', $em);

		$compte = null;
		if($compteId){
			$compte = $compteRepo->find($compteId);
			$opportunite->setCompte($compteId);
			$devis->setCompte($compteId);
		}
		$contact = null;
		if($contactId){
			$contact = $contactRepo->find($contactId);
			$opportunite->setContact($contactId);
			$devis->setContact($contactId);
		}

		$opportunite->setUserGestion($this->getUser());

		$form = $this->createForm(ActionCommercialeType::class, $opportunite, array(
		    'userGestionId' => $opportunite->getUserGestion()->getId(),
            'companyId' => $this->getUser()->getCompany()->getId(),
            'devis' => $devis,
            'compte' => $compte,
        ));

		if($compte){
			$form->get('compte_name')->setData($compte->__toString());
		}
		if($contact){
			$form->get('contact_name')->setData($contact->__toString());
		}

		$form->get('dateValidite')->setData($devis->getDateValidite());
		$form->get('date')->setData(new \DateTime(date('Y-m-d')));
		$form->get('cgv')->setData($devis->getCgv());

		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {

			$data = $form->getData();

			$opportunite->setCompte($em->getRepository('App:CRM\Compte')->findOneById($data->getCompte()));
			$opportunite->setContact($em->getRepository('App:CRM\Contact')->findOneById($data->getContact()));
			$opportunite->setDateCreation(new \DateTime(date('Y-m-d')));
			$opportunite->setUserCreation($this->getUser());
			$opportunite->setUserCompetCom($this->getUser());
			$opportunite->setMontant($form->get('totalHT')->getData());

			$opportunite->setDevis($devis);

			$file = $opportunite->getFichier();
			if($file){
				try{
					$fileName = $this->actionCommercialeFileUploader->upload($file, $this->getUser());
				} catch(\Exception $e){
					throw $e;
				}
		       	
		       	$opportunite->setFichier($fileName);
		    }

			$em->persist($opportunite);

			$devis = $this->devisService->createFromOpportunite($devis, $opportunite);

			$devis = $this->devisService->setNum($devis);
			$devis->setDateValidite($form->get('dateValidite')->getData());
			$devis->setAdresse($form->get('adresse')->getData());
			$devis->setVille($form->get('ville')->getData());
			$devis->setCodePostal($form->get('codePostal')->getData());
			$devis->setRegion($form->get('region')->getData());
			$devis->setPays($form->get('pays')->getData());
			$devis->setDescription($form->get('description')->getData());
			$devis->setCGV($form->get('cgv')->getData());
			$devis->setRemise($form->get('remise')->getData());
			$devis->setTaxe($form->get('taxe')->getData());
			$devis->setTaxePercent($form->get('taxePercent')->getData());

			foreach($form->get('produits')->getData() as $produit){
				$devis->addProduit($produit);
			}
			
			$em->persist($devis);

			//mettre type de relation commerciale du contact comme "prospect" si rien d'autre indiqué et pas de facture
			if($devis->getContact()){
				$contact = $devis->getContact();
				$settingsRepository = $em->getRepository('App:Settings');
				$settingsProspect = $settingsRepository->findOneBy(array(
					'company' => $this->getUser()->getCompany(),
					'parametre' => 'TYPE',
					'module' => 'CRM',
					'valeur' => 'Prospect'
				));

				if(count($contact->getTypeRelationCommerciale()) == 0){
					if(count($contact->getFactures()) == 0){
						$contact->addSetting($settingsProspect);
						$em->persist($contact);
					}
				}	
			}

			$em->flush();
			
			return $this->redirect($this->generateUrl(
				'crm_action_commerciale_voir',
				array('id' => $opportunite->getId())
			));
		}

		return $this->render('crm/action-commerciale/crm_action_commerciale_ajouter.html.twig', array(
			'form' => $form->createView(),
			'actionCommerciale' => $opportunite
		));
	}

	/**
	 * @Route("/crm/action-commerciale/voir/{id}",
	 *   name="crm_action_commerciale_voir",
	 * 	 options={"expose"=true}
	 *  )
	 */
	public function actionCommercialeVoirAction(Opportunite $actionCommerciale)
	{
		$factures = array();
		if($actionCommerciale->getDevis()){
			$repository = $this->getDoctrine()->getManager()->getRepository('App:CRM\DocumentPrix');
			$arr_devis = $repository->findByDevis($actionCommerciale->getDevis());

			foreach($arr_devis as $facture){
				$factures[$facture->getId()] = $facture;
			}

			foreach($actionCommerciale->getBonsCommande() as $bc){
				$arr_bc = $repository->findBy(array(
					'type' => 'FACTURE',
					'bonCommande' => $bc
				));

				foreach($arr_bc as $facture){
					if(!array_key_exists($facture->getId(), $factures)){
						$factures[$facture->getId()] = $facture;
					}
				}
			}
			
		}
		
		return $this->render('crm/action-commerciale/crm_action_commerciale_voir.html.twig', array(
			'opportunite' => $actionCommerciale,
			'facture' => $factures
		));

	}	

	/**
	 * @Route("/crm/action-commerciale/editer/{id}",
	 *   name="crm_action_commerciale_editer",
	 *   options={"expose"=true}
	 *  )
	 */
	public function actionCommercialeEditerAction(Request $request, Opportunite $actionCommerciale)
	{
		$em = $this->getDoctrine()->getManager();

		$devis = $actionCommerciale->getDevis();
		if($devis == null){
			$devis = new DocumentPrix($this->getUser()->getCompany(),'DEVIS', $em);
			$devis->setUserCreation($this->getUser());
			$devis->setDateCreation(new \DateTime(date('Y-m-d')));
			
			$devis = $this->devisService->createFromOpportunite($devis, $actionCommerciale);
			$devis = $this->devisService->setNum($devis);

			$devis->setAdresse($actionCommerciale->getCompte()->getAdresse());
			$devis->setVille($actionCommerciale->getCompte()->getVille());
			$devis->setCodePostal($actionCommerciale->getCompte()->getCodePostal());
			$devis->setRegion($actionCommerciale->getCompte()->getRegion());
			$devis->setPays($actionCommerciale->getCompte()->getPays());

			$produit = new Produit();
			$produit->setNom($actionCommerciale->getNom());
			$produit->setDescription($actionCommerciale->getNom());
			$produit->setTarifUnitaire($actionCommerciale->getMontant());
			$produit->setQuantite(1);

			$settingsRepository = $em->getRepository('App:Settings');
			$settingsType = $settingsRepository->findOneBy(array(
				'company' => $this->getUser()->getCompany(),
				'parametre' => 'TYPE_PRODUIT',
				'valeur' => $actionCommerciale->getAnalytique()->getValeur()
			));
			$produit->setType($settingsType);
			$em->persist($produit);

			$devis->addProduit($produit);
			$em->persist($devis);

			$actionCommerciale->setDevis($devis);

			$em->persist($actionCommerciale);
		}

		$_compte = $actionCommerciale->getCompte();
		$_contact = $actionCommerciale->getContact();

		$actionCommerciale->setCompte($_compte->getId());
		if($_contact){
			$actionCommerciale->setContact($_contact->getId());
		}

		$oldFile = null;
		if($actionCommerciale->getFichier()){
			$oldFile = $actionCommerciale->getFichier();
			$actionCommerciale->setFichier(
			    new File($this->getParameter('actions_commerciales_fichier_directory').'/'.$actionCommerciale->getFichier())
			);
		}

        $form = $this->createForm(ActionCommercialeType::class, $actionCommerciale, array(
            'userGestionId' => $actionCommerciale->getUserGestion()->getId(),
            'companyId' => $this->getUser()->getCompany()->getId(),
            'devis' => $devis,
            'compte' => $_compte,
        ));

		$form->get('compte_name')->setData($_compte->__toString());
		if($_contact){
			$form->get('contact_name')->setData($_contact->__toString());
		}

		if($devis){
			$form->get('cgv')->setData($devis->getCgv());
		} else {

		}

		$form->add('updateFichier', \Symfony\Component\Form\Extension\Core\Type\HiddenType::class, array(
			'mapped' => false,
			'data' => $actionCommerciale->getFichier() == null ? 1:0,
			'attr' => array('class' => 'updateFichierFlag')
		));

		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {

			$em = $this->getDoctrine()->getManager();

			$data = $form->getData();

			$actionCommerciale->setCompte($em->getRepository('App:CRM\Compte')->findOneById($data->getCompte()));
			$actionCommerciale->setContact($em->getRepository('App:CRM\Contact')->findOneById($data->getContact()));

			$actionCommerciale->setDateEdition(new \DateTime(date('Y-m-d')));
			$actionCommerciale->setUserEdition($this->getUser());
			$actionCommerciale->setMontant($form->get('totalHT')->getData());

			if($form->get('updateFichier')->getData() == 1){
				$file = $actionCommerciale->getFichier();
				if($file){
					try{
						$fileName = $this->actionCommercialeFileUploader->upload($file, $this->getUser());
					} catch(\Exception $e){
						throw $e;
					}
			       	$actionCommerciale->setFichier($fileName);
				}
				if($oldFile){
					$wholePath = $this->getParameter('actions_commerciales_fichier_directory').DIRECTORY_SEPARATOR.$oldFile;
					if(file_exists($wholePath)){
						unlink($wholePath);
					}
					
				}
				
			} else {

				$actionCommerciale->setFichier($oldFile);
			}

			$em->persist($actionCommerciale);
			$em->flush();

			$devis->setDateEdition(new \DateTime(date('Y-m-d')));
			$devis->setUserEdition($this->getUser());
			$devis = $this->devisService->createFromOpportunite($devis, $actionCommerciale);
			$devis->setDateValidite($form->get('dateValidite')->getData());
			$devis->setAdresse($form->get('adresse')->getData());
			$devis->setVille($form->get('ville')->getData());
			$devis->setCodePostal($form->get('codePostal')->getData());
			$devis->setRegion($form->get('region')->getData());
			$devis->setPays($form->get('pays')->getData());
			$devis->setDescription($form->get('description')->getData() == null ? '' : $form->get('description')->getData());
			$devis->setCGV($form->get('cgv')->getData());
			$devis->setRemise($form->get('remise')->getData());
			$devis->setTaxe($form->get('taxe')->getData());
			$devis->setTaxePercent($form->get('taxePercent')->getData());

			foreach($devis->getProduits() as $oldProduit){
				$devis->removeProduit($oldProduit);
			}
			$em->persist($devis);

			foreach($form->get('produits')->getData() as $produit){
				$devis->addProduit($produit);
				$em->persist($devis);
			}

			$em->flush();

			$actionCommerciale->setDevis($devis);
			$em->flush();

			return $this->redirect($this->generateUrl(
				'crm_action_commerciale_voir',
				array('id' => $actionCommerciale->getId())
			));
		}

		return $this->render('crm/action-commerciale/crm_action_commerciale_editer.html.twig', array(
			'form' => $form->createView(),
			'actionCommerciale' => $actionCommerciale
		));

	}

	/**
	 * @Route("/crm/action-commerciale/supprimer/{id}",
	 *   name="crm_action_commerciale_supprimer",
	 *   options={"expose"=true}
	 *  )
	 */
	public function actionCommercialeSupprimerAction(Request $request, Opportunite $actionCommerciale)
	{

		$form = $this->createFormBuilder()->getForm();

		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {

			$em = $this->getDoctrine()->getManager();

			$devis = $actionCommerciale->getDevis();
			if($devis){
				$em->remove($devis);
			}

			if($actionCommerciale->getFichier()){
				$wholePath = $this->getParameter('actions_commerciales_fichier_directory').DIRECTORY_SEPARATOR.$actionCommerciale->getFichier();
				if(file_exists($wholePath)){
					unlink($wholePath);
				}
			}
			
			
			$em->remove($actionCommerciale);
			$em->flush();

			return $this->redirect($this->generateUrl(
				'crm_action_commerciale_liste'
			));
		}

		return $this->render('crm/action-commerciale/crm_action_commerciale_supprimer.html.twig', array(
				'form' => $form->createView(),
				'actionCommerciale' => $actionCommerciale
		));

	}

	/**
	 * @Route("/crm/action-commerciale/exporter/{id}",
	 *   name="crm_action_commerciale_exporter",
	 *   options={"expose"=true}
	 *  )
	 */
	public function actionCommercialeExporter(Opportunite $actionCommerciale){
		return $this->redirect($this->generateUrl('crm_devis_exporter', array('id' => $actionCommerciale->getDevis()->getId())));
	}

	/**
	 * @Route("/crm/action-commerciale/perdre/{id}",
	 *   name="crm_action_commerciale_perdre",
	 *   options={"expose"=true}
	 * )
	 */
	public function actionCommercialePerdreAction(Opportunite $actionCommerciale)
	{
		$this->opportuniteService->lose($actionCommerciale);

		if($actionCommerciale->getDevis()){
			$this->devisService->lose($actionCommerciale->getDevis());
		}

		return $this->redirect($this->generateUrl('crm_action_commerciale_liste'));
	}


	/**
	 * @Route("/crm/action-commerciale/gagner/{id}",
	 *   name="crm_action_commerciale_gagner",
	 *   options={"expose"=true}
	 * )
	 */
	public function actionCommercialeGagnerAction(Opportunite $actionCommerciale)
	{
		$this->opportuniteService->win($actionCommerciale);

		if($actionCommerciale->getDevis()){
			$this->devisService->win($actionCommerciale->getDevis());
		}

		return $this->redirect($this->generateUrl('crm_action_commerciale_gagner_bon_commande', array(
			'id' => $actionCommerciale->getId()
		)));
	}

	/**
	 * @Route("/crm/action-commerciale/gagner/bon-commande/{id}/{edition}",
	 *   name="crm_action_commerciale_gagner_bon_commande",
	 *   options={"expose"=true}
	 * )
	 */
	public function actionCommercialeGagnerBonCommandeAction(Request $request, Opportunite $actionCommerciale, $edition = false)
	{
		$form = $this->createForm(
			OpportuniteWonBonCommandeType::class,
			$actionCommerciale
		);

		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {

			$em = $this->getDoctrine()->getManager();
			$em->persist($actionCommerciale);
			$em->flush();

			if($edition){
				return $this->redirect($this->generateUrl('crm_action_commerciale_voir', array(
					'id' => $actionCommerciale->getId()
				)));
			}

			return $this->redirect($this->generateUrl('crm_action_commerciale_gagner_plan_paiement', array(
				'id' => $actionCommerciale->getId()
			)));
		}

		return $this->render('crm/action-commerciale/crm_action_commerciale_won_bon_commande.html.twig', array(
			'actionCommerciale' => $actionCommerciale,
			'form' => $form->createView(),
			'edition' => $edition
		));
	}

	/**
	 * @Route("/crm/action-commerciale/gagner/plan-paiement/{id}/{edition}",
	 *   name="crm_action_commerciale_gagner_plan_paiement",
	 *   options={"expose"=true}
	 * )
	 */
	public function actionCommercialeGagnerPlanPaiementAction(Request $request, Opportunite $actionCommerciale, $edition = false)
	{
		$form = $this->createForm(
			OpportuniteWonPlanPaiementType::class,
			$actionCommerciale
		);

		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {

			$em = $this->getDoctrine()->getManager();

			$type = $form->get('type')->getData();
			if('COMMANDE' == $type){
				$planPaiement = new PlanPaiement();
				$planPaiement->setPourcentage(100);
				$planPaiement->setCommande(true);
				$planPaiement->setNom('Commande');
				$planPaiement->setDate( $actionCommerciale->getDate() );

				$actionCommerciale->clearPlanPaiements();
				$actionCommerciale->addPlanPaiement($planPaiement);
			} elseif('FIN' == $type){
				$planPaiement = new PlanPaiement();
				$planPaiement->setPourcentage(100);
				$planPaiement->setFinProjet(true);
				$planPaiement->setNom('Fin du projet');
				$actionCommerciale->clearPlanPaiements();
				$actionCommerciale->addPlanPaiement($planPaiement);
			} 

			$em->persist($actionCommerciale);
			$em->flush();

			if($edition){
				return $this->redirect($this->generateUrl('crm_action_commerciale_voir', array(
					'id' => $actionCommerciale->getId()
				)));
			}

			return $this->redirect($this->generateUrl('crm_action_commerciale_gagner_repartition', array(
				'id' => $actionCommerciale->getId()
			)));
		}

		return $this->render('crm/action-commerciale/crm_action_commerciale_won_plan_paiement.html.twig', array(
			'actionCommerciale' => $actionCommerciale,
			'form' => $form->createView(),
			'edition' => $edition
		));
	}


	/**
	 * @Route("/crm/action-commerciale/gagner/repartition/{id}/{edition}",
	 *   name="crm_action_commerciale_gagner_repartition",
	 *   options={"expose"=true}
	 * )
	 */
	public function actionCommercialeGagnerRepartitionAction(Request $request, Opportunite $actionCommerciale, $edition = false)
	{

		$form = $this->createForm(OpportuniteWonRepartitionType::class, $actionCommerciale, array(
		    'isEdition' => $edition
        ));

		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {

			$em = $this->getDoctrine()->getManager();
			$em->persist($actionCommerciale);
			$em->flush();

			if($form['sousTraitance']->getData() === true){
			  return $this->redirect($this->generateUrl('crm_action_commerciale_gagner_sous_traitance', array(
					'id' => $actionCommerciale->getId()
				)));
			}

			return $this->redirect($this->generateUrl('crm_action_commerciale_voir', array(
				'id' => $actionCommerciale->getId()
			)));

		}

		return $this->render('crm/action-commerciale/crm_action_commerciale_won_repartition.html.twig', array(
			'actionCommerciale' => $actionCommerciale,
			'form' => $form->createView(),
		));
	}

	/**
	 * @Route("/crm/action-commerciale/gagner/sous-traitance/{id}",
	 *   name="crm_action_commerciale_gagner_sous_traitance",
	 *   options={"expose"=true}
	 * )
	 */
	public function actionCommercialeGagnerSousTraitanceAction(Request $request, Opportunite $actionCommerciale)
	{

		$opportuniteSousTraitance = new OpportuniteSousTraitance();
		$opportuniteSousTraitance->setOpportunite($actionCommerciale);

		if(null == $actionCommerciale->getRepartitionStartDate() || null == $actionCommerciale->getRepartitionEndDate()){
			$this->get('session')->getFlashBag()->add(
				'warning',
				'Vous devez remplir la répartition du montant en fonction de l\'activité avant de pouvoir ajouter un sous-traitant.'
			);

			return $this->redirect(
				$this->generateUrl('crm_action_commerciale_gagner_repartition', array(
					'id' => $actionCommerciale->getId()
				))
			);
		}

		$form = $this->createForm(
			OpportuniteSousTraitanceType::class,
			$opportuniteSousTraitance
		);

		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {

			$actionCommerciale->addOpportuniteSousTraitance($opportuniteSousTraitance);

			$em = $this->getDoctrine()->getManager();
			$em->persist($actionCommerciale);
			$em->flush();


			if ($form->has('add') && $form->get('add')->isClicked()){
				// reload
				return $this->redirect($this->generateUrl('crm_action_commerciale_gagner_sous_traitance', array(
					'id' => $actionCommerciale->getId()
				)));

			}

			return $this->redirect($this->generateUrl('crm_action_commerciale_voir', array(
				'id' => $opportuniteSousTraitance->getOpportunite()->getId()
			)));

		}

		return $this->render('crm/action-commerciale/crm_action_commerciale_won_sous_traitance.html.twig', array(
			'actionCommerciale' => $actionCommerciale,
			'form' => $form->createView()
		));
	}

	/**
	 * @Route("/crm/action-commerciale/sous-traitance/editer/{id}",
	 *   name="crm_action_commerciale_sous_traitance_editer"
	 * )
	 */
	public function actionCommercialeSousTraitanceEditerAction(Request $request, OpportuniteSousTraitance $sousTraitance)
	{
		$form = $this->createForm(
			OpportuniteSousTraitanceType::class,
			$sousTraitance
		);

		$form->remove('submit')
			->remove('add');

		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {

			$em = $this->getDoctrine()->getManager();
			$em->persist($sousTraitance);
			$em->flush();

			return $this->redirect($this->generateUrl('crm_action_commerciale_voir', array(
				'id' => $sousTraitance->getOpportunite()->getId()
			)));

		}

		return $this->render('crm/action-commerciale/crm_action_commerciale_sous_traitance_editer.html.twig', array(
				'opportuniteSousTraitance' => $sousTraitance,
				'form' => $form->createView()
		));
	}


	/**
	 * @Route("/crm/action-commerciale/dupliquer/{id}",
	 *   name="crm_action_commerciale_dupliquer",
	 *   options={"expose"=true}
	 * )
	 */
	public function actionCommercialeDupliquerAction(Opportunite $actionCommerciale)
	{
		$em = $this->getDoctrine()->getManager();

		$newActionCommerciale = clone $actionCommerciale;
		$newActionCommerciale->setUserCreation($this->getUser());
		$newActionCommerciale->setDateCreation(new \DateTime(date('Y-m-d')));
		$newActionCommerciale->setDateEdition(null);
		$newActionCommerciale->setDate(new \DateTime(date('Y-m-d')));
		$newActionCommerciale->setUserEdition(null);
		$newActionCommerciale->setNom('COPIE '.$actionCommerciale->getNom());
		$newActionCommerciale->setEtat('ONGOING');

		if($actionCommerciale->getDevis()){

			$devis = clone $actionCommerciale->getDevis();
			$devis->setUserCreation($this->getUser());
			$devis->setDateCreation(new \DateTime(date('Y-m-d')));
			$devis->setDateEdition(null);
			$devis->setUserEdition(null);
			$devis->setObjet('COPIE '.$actionCommerciale->getDevis()->getObjet());
			$devis = $this->devisService->setNum($devis);

		} else {

			$devis = new DocumentPrix($this->getUser()->getCompany(),'DEVIS', $em);
			$devis->setUserCreation($this->getUser());
			$devis->setDateCreation(new \DateTime(date('Y-m-d')));
			
			$devis = $this->devisService->createFromOpportunite($devis, $actionCommerciale);
			$devis = $this->devisService->setNum($devis);

			$devis->setAdresse($actionCommerciale->getCompte()->getAdresse());
			$devis->setVille($actionCommerciale->getCompte()->getVille());
			$devis->setCodePostal($actionCommerciale->getCompte()->getCodePostal());
			$devis->setRegion($actionCommerciale->getCompte()->getRegion());
			$devis->setPays($actionCommerciale->getCompte()->getPays());

			$produit = new Produit();
			$produit->setNom($actionCommerciale->getNom());
			$produit->setDescription($actionCommerciale->getNom());
			$produit->setTarifUnitaire($actionCommerciale->getMontant());
			$produit->setQuantite(1);

			$settingsRepository = $em->getRepository('App:Settings');
			$settingsType = $settingsRepository->findOneBy(array(
				'company' => $this->getUser()->getCompany(),
				'parametre' => 'TYPE_PRODUIT',
				'valeur' => $actionCommerciale->getAnalytique()->getValeur()
			));
			$produit->setType($settingsType);
			$em->persist($produit);

			$devis->addProduit($produit);
		}

		$em->persist($devis);

		$newActionCommerciale->setDevis($devis);

		$em->persist($newActionCommerciale);
		$em->flush();

		return $this->redirect($this->generateUrl(
				'crm_action_commerciale_voir',
				array('id' => $newActionCommerciale->getId())
		));
	}

	/**
	 * @Route("/crm/action-commerciale/convertir/{id}/{planPaiementId}", name="crm_action_commerciale_convertir")
	 */
	public function actionCommercialeConvertirAction(Request $request, Opportunite $actionCommerciale, $planPaiementId = null)
	{
		$devis = $actionCommerciale->getDevis();

		$form = $this->createFormBuilder()->getForm();

		if(count($actionCommerciale->getPlanPaiements()) > 1 ){

			$choices = array();
			foreach($actionCommerciale->getPlanPaiements() as $planPaiement){
				if( null == $planPaiement->getFacture() ){
					$choices[$planPaiement->getId()] = $planPaiement->__toString();
				}
			}

			if(count($choices)){
				$form->add('planPaiement', \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, array(
					'required' => true,
					'label' => 'Quelle phase facturez-vous ?',
					'choices' => $choices,
					'multiple' => false,
					'expanded' => true,
					'required' => true,
					'data' => $planPaiementId
				));
			}

		}

		$form->add('objet', TextType::class, array(
			'required' => true,
			'label' => 'Objet de la facture',
			'data' => $devis->getObjet()
		));

		$form->add('submit', \Symfony\Component\Form\Extension\Core\Type\SubmitType::class, array(
  		  'label' => 'Enregistrer',
		  'attr' => array('class' => 'btn btn-success')
		));

		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {

			$em = $this->getDoctrine()->getManager();
			$data = $form->getData();
			$facture = clone $devis;

            $devis->setEtat("WON");
            $em->persist($devis);

			$settingsRepository = $em->getRepository('App:Settings');
			$settingsNum = $settingsRepository->findOneBy(array('module' => 'CRM', 'parametre' => 'NUMERO_FACTURE', 'company'=>$this->getUser()->getCompany()));
			$currentNum = $settingsNum->getValeur();

			$facture->setType('FACTURE');
			$facture->setObjet($data['objet']);
			$facture->setDevis($devis);
			$facture->setDateCreation(new \DateTime(date('Y-m-d')));
			$facture->setUserCreation($this->getUser());

			if($facture->getCompte()->getAdresseFacturation()){
				$facture->setNomFacturation($facture->getCompte()->getNomFacturation());
				$facture->setAdresse($facture->getCompte()->getAdresseFacturation());
				$facture->setAdresseLigne2($facture->getCompte()->getAdresseFacturationLigne2());
				$facture->setCodePostal($facture->getCompte()->getCodePostalFacturation());
				$facture->setVille($facture->getCompte()->getVilleFacturation());
				$facture->setRegion(null);
				$facture->setPays($facture->getCompte()->getPaysFacturation());
			}

			$settingsCGV = $settingsRepository->findOneBy(array('module' => 'CRM', 'parametre' => 'CGV_FACTURE', 'company'=>$this->getUser()->getCompany()));
			$facture->setCgv($settingsCGV->getValeur());

			$prefixe = date('Y').'-';
			if($currentNum < 10){
				$prefixe.='00';
			} else if ($currentNum < 100){
				$prefixe.='0';
			}
			$facture->setNum($prefixe.$currentNum);
			$em->persist($facture);

			foreach($actionCommerciale->getBonsCommande() as $bonCommande){
				$facture->setBonCommande($bonCommande);
			}

			$currentNum++;
			$settingsNum->setValeur($currentNum);
			$em->persist($settingsNum);


      		$settingsActivationRepo = $this->getDoctrine()->getManager()->getRepository('App:SettingsActivationOutil');
			$activationCompta = $settingsActivationRepo->findOneBy(array(
					'company' => $this->getUser()->getCompany(),
					'outil' => 'COMPTA',
			));
			if(!$activationCompta){
				$facture->setCompta(false);
			} else{
				$facture->setCompta(true);
			}

			if(count($actionCommerciale->getPlanPaiements()) > 1 ){

				$planPaiementId = $form->get('planPaiement')->getData();
				
				if( $planPaiementId ){
					$planPaiementRepo = $em->getRepository('App:CRM\PlanPaiement');
					$planPaiement = $planPaiementRepo->find($planPaiementId);
					
					foreach($facture->getProduits() as $produit){
						$em->remove($produit);
					}
					$facture->clearProduits();
					if(null !== $facture->getTaxe()){
						$facture->setTaxe(0);
					}

					$produit = $this->factureService->createProduitFromPlanPaiement($planPaiement);
					$facture->addProduit($produit);
					$em->persist($facture);

					$facture->calculateTaxe();

					$planPaiement->setFacture($facture);
					$em->persist($planPaiement);
				}
			}

			$em->persist($facture);

			//mettre type de relation commerciale du contact comme "client"
			if($facture->getContact()){
				$contact = $facture->getContact();
				$settingsRepository = $em->getRepository('App:Settings');
				$settingsClient = $settingsRepository->findOneBy(array(
					'company' => $this->getUser()->getCompany(),
					'parametre' => 'TYPE',
					'module' => 'CRM',
					'valeur' => 'Client'
				));
				$settingsProspect = $settingsRepository->findOneBy(array(
					'company' => $this->getUser()->getCompany(),
					'parametre' => 'TYPE',
					'module' => 'CRM',
					'valeur' => 'Prospect'
				));

				if($contact->hasTypeRelationCommerciale('PROSPECT')){
					$contact->removeSetting($settingsProspect);
					$em->persist($contact);
				}

				if(count($contact->getTypeRelationCommerciale()) == 0){
					$contact->addSetting($settingsClient);
					$em->persist($contact);
				}
					
			}
			
			$em->flush();
			
			if($activationCompta){

				//si le compte comptable du client n'existe pas, on le créé
				$compte = $facture->getCompte();
				if($compte->getClient() == false || $compte->getCompteComptableClient() == null){

					try {
						$compteComptable = $this->compteComptableService->createCompteComptableClient($compte);
					} catch(\Exception $e){
						return $this->redirect($this->generateUrl(
							'crm_facture_creer_compte_comptable', 
							array('id' => $facture->getId())
						));
					}

					$compte->setClient(true);
					$compte->setCompteComptableClient($compteComptable);
					$em->persist($compte);
					$em->flush();
				}

				//ecrire dans le journal de vente
				$this->journalVentesService->journalVentesAjouterFactureAction(null, $facture);
			}

			$em->flush();

			return $this->redirect($this->generateUrl(
				'crm_facture_voir',
				array('id' => $facture->getId())
			));
		} 

		return $this->render('crm/action-commerciale/crm_action_commerciale_convertir.html.twig', array(
			'form' 		=> $form->createView(),
			'actionCommerciale'		=> $actionCommerciale
		));
	}

		/**
		 * @Route("/crm/action-commerciale/facturer-frais/{id}", name="crm_action_commerciale_facturer_frais")
		 */
		public function actionCommercialeFacturerFraisAction(Request $request, Opportunite $actionCommerciale)
		{
			$devis = $actionCommerciale->getDevis();

			$form = $this->createFormBuilder()->getForm();

			if($actionCommerciale->hasFraisRefacturables()){

				$arr_frais = array();
				foreach($actionCommerciale->getFraisNonFactures() as $frais){
				    $arr_frais[$frais->getId()] = $frais->__toString();
				}
				$form->add('frais', \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, array(
					'required' => true,
					'label' => 'Quels frais facturez-vous ?',
					'choices' => $arr_frais,
					'multiple' => true,
					'expanded' => true,
					'required' => true
				));
				

				$arr_recus = array();
				foreach($actionCommerciale->getRecusValidesNonFactures() as $recu){
				    $arr_recus[$recu->getId()] = $recu->getUser().' - '.$recu->getFournisseur().' - '.$recu->getMontantHT().' € HT';
				}
				$form->add('recus', \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, array(
					'required' => true,
					'label' => ' ',
					'choices' => $arr_recus,
					'multiple' => true,
					'expanded' => true,
					'required' => true
				));
				

				$arr_sous_traitances = array();
				foreach($actionCommerciale->getFraisSousTraitantsNonFactures() as $fraisSousTraitance){
				    $arr_sous_traitances[$fraisSousTraitance->getId()] = $fraisSousTraitance->getOpportuniteSousTraitance()->getSousTraitant().' - '.$fraisSousTraitance->getDate()->format('m/Y').' - '.$fraisSousTraitance->getFraisMonetaire().' €';
				}
				$form->add('sous_traitances', \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, array(
					'required' => true,
					'label' => ' ',
					'choices' => $arr_sous_traitances,
					'multiple' => true,
					'expanded' => true,
					'required' => true
				));				

			}

			$form->add('objet', \Symfony\Component\Form\Extension\Core\Type\TextType::class, array(
				'required' => true,
				'label' => 'Objet de la facture',
				'data' => $devis->getObjet().' - Frais'
			));

			$form->add('submit', \Symfony\Component\Form\Extension\Core\Type\SubmitType::class, array(
	  		  'label' => 'Enregistrer',
			  'attr' => array('class' => 'btn btn-success')
			));

			$form->handleRequest($request);

			if ($form->isSubmitted() && $form->isValid()) {

				$em = $this->getDoctrine()->getManager();
				$data = $form->getData();
				$facture = clone $devis;
				$facture->setDevis($devis);
				$facture->setOpportunite(null);
				$facture->setObjet($data['objet']);
				$facture->setFactureFrais(true);
				$facture->setRemise(0); //pas de remise sur les frais !

				if($facture->getCompte()->getAdresseFacturation()){
					$facture->setNomFacturation($facture->getCompte()->getNomFacturation());
					$facture->setAdresse($facture->getCompte()->getAdresseFacturation());
					$facture->setAdresseLigne2($facture->getCompte()->getAdresseFacturationLigne2());
					$facture->setCodePostal($facture->getCompte()->getCodePostalFacturation());
					$facture->setVille($facture->getCompte()->getVilleFacturation());
					$facture->setRegion(null);
					$facture->setPays($facture->getCompte()->getPaysFacturation());
				}

				$settingsRepository = $em->getRepository('App:Settings');
				$settingsNum = $settingsRepository->findOneBy(array('module' => 'CRM', 'parametre' => 'NUMERO_FACTURE', 'company'=>$this->getUser()->getCompany()));
				$currentNum = $settingsNum->getValeur();

				$facture->setType('FACTURE');
				
				
				$facture->setDateCreation(new \DateTime(date('Y-m-d')));
				$facture->setUserCreation($this->getUser());

				$settingsCGV = $settingsRepository->findOneBy(array('module' => 'CRM', 'parametre' => 'CGV_FACTURE', 'company'=>$this->getUser()->getCompany()));
				$facture->setCgv($settingsCGV->getValeur());

				$prefixe = date('Y').'-';
				if($currentNum < 10){
					$prefixe.='00';
				} else if ($currentNum < 100){
					$prefixe.='0';
				}
				$facture->setNum($prefixe.$currentNum);
				$em->persist($facture);

				foreach($actionCommerciale->getBonsCommande() as $bonCommande){
					$facture->setBonCommande($bonCommande);
				}

				$currentNum++;
				$settingsNum->setValeur($currentNum);
				$em->persist($settingsNum);


	      		$settingsActivationRepo = $this->getDoctrine()->getManager()->getRepository('App:SettingsActivationOutil');
				$activationCompta = $settingsActivationRepo->findOneBy(array(
						'company' => $this->getUser()->getCompany(),
						'outil' => 'COMPTA',
				));
				if(!$activationCompta){
					$facture->setCompta(false);
				} else{
					$facture->setCompta(true);
				}

				 foreach($facture->getProduits() as $produit){
		            $em->remove($produit);
		        }
		        $facture->clearProduits();
				$this->factureService->createProduitsFrais($facture, $data['frais'], 'frais');
				$this->factureService->createProduitsFrais($facture, $data['recus'], 'recu');
				$this->factureService->createProduitsFrais($facture, $data['sous_traitances'], 'sousTraitance');

				$facture->calculateTaxe();
		
				$em->persist($facture);

				if($activationCompta){

					//si le compte comptable du client n'existe pas, on le créé
					$compte = $facture->getCompte();
					if($compte->getClient() == false || $compte->getCompteComptableClient() == null){

						try {
							$compteComptable = $this->compteComptableService->createCompteComptableClient($compte);
						} catch(\Exception $e){
							return $this->redirect($this->generateUrl(
								'crm_facture_creer_compte_comptable', 
								array('id' => $facture->getId())
							));
						}

						$compte->setClient(true);
						$compte->setCompteComptableClient($compteComptable);
						$em->persist($compte);
						$em->flush();
					}

					//ecrire dans le journal de vente
					$this->journalVentesService->journalVentesAjouterFactureAction(null, $facture);
				}

				$em->flush();

				return $this->redirect($this->generateUrl(
					'crm_facture_voir',
					array('id' => $facture->getId())
				));
			} 

			return $this->render('crm/action-commerciale/crm_action_commerciale_facturer_frais.html.twig', array(
				'form' 		=> $form->createView(),
				'actionCommerciale'		=> $actionCommerciale
			));
		}

	/**
	 * Envoyer le devis par email
	 * 
	 * @Route("/crm/action-commerciale/envoyer/{id}", name="crm_action_commerciale_envoyer")
	 */
	public function actionCommercialeEnvoyerAction(Request $request, Opportunite $actionCommerciale)
	{
		$devis = $actionCommerciale->getDevis();
		$settingsRepository = $this->getDoctrine()->getManager()->getRepository('App:Settings');
 		$contactAdmin = $settingsRepository->findOneBy(array('company' => $devis->getUserCreation()->getCompany(), 'module' => 'CRM', 'parametre' => 'CONTACT_ADMINISTRATIF'));

		$form = $this->createFormBuilder()->getForm();

		
		$form->add('objet', \Symfony\Component\Form\Extension\Core\Type\TextType::class, array(
			'required' => true,
			'label' => 'Objet',
			'data' => 'Devis : '.$devis->getObjet()
		));

		$form->add('message', 'textarea', array(
			'required' => true,
			'label' => 'Message',
			'data' => $this->renderView('crm/devis/crm_devis_email.html.twig', array(
				'devis' => $devis
			)),
			'attr' => array('class' => 'tinymce')
		));

        $form->add('addcc', 'checkbox', array(
        	'required' => false,
  			'label' => 'Recevoir une copie de l\'email'
        ));

        $form->add('includePropale', 'checkbox', array(
        	'required' => false,
  			'label' => 'Inclure la proposition commerciale en pièce jointe',
  			'attr' => array('disabled' => $actionCommerciale->getFichier() ? false : true)
        ));

		$form->add('submit', \Symfony\Component\Form\Extension\Core\Type\SubmitType::class, array(
  		  'label' => 'Envoyer',
		  'attr' => array('class' => 'btn btn-success')
		));

		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {

			$objet = $form->get('objet')->getData();
			$message = $form->get('message')->getData();

			$filename = $this->devisService->createDevisPDF($devis);
			
			try{
				$mail = (new \Swift_Message)
					->setSubject($objet)
					->setFrom($this->getUser()->getEmail())
					->setTo($devis->getContact()->getEmail())
					->setBody($message, 'text/html')
					->attach(Swift_Attachment::fromPath($filename))
				;
				if( $form->get('addcc')->getData() ) $mail->addCc($this->getUser()->getEmail());
				if( $form->get('includePropale')->getData() && $actionCommerciale->getFichier() ){
					$propalePath = $this->container->getParameter('actions_commerciales_fichier_directory').DIRECTORY_SEPARATOR.$actionCommerciale->getFichier();
					$mail->attach(Swift_Attachment::fromPath($propalePath));

				} 
				$this->get('mailer')->send($mail);
				$this->get('session')->getFlashBag()->add(
						'success',
						'Le devis a bien été envoyé.'
				);

				unlink($filename);

				$priseContact = new PriseContact();
				$priseContact->setType('DEVIS');
				$priseContact->setDate(new \DateTime(date('Y-m-d')));
				$priseContact->setDescription("Envoi du devis");
				$priseContact->setDocumentPrix($devis);
				$priseContact->setContact($devis->getContact());
				$priseContact->setUser($this->getUser());
				$priseContact->setMessage($message);

				if($devis->getEtat() != 'SENT'){
					$devis->setEtat('SENT');
				}

				$em = $this->getDoctrine()->getManager();
				$em->persist($priseContact);
				$em->persist($devis);
				$em->flush();

			} catch(\Exception $e){
    			$error =  $e->getMessage();
    			$this->get('session')->getFlashBag()->add('danger', "L'email n'a pas été envoyé pour la raison suivante : $error");
    		}


			return $this->redirect($this->generateUrl(
					'crm_action_commerciale_voir',
					array('id' => $actionCommerciale->getId())
			));
		}

		return $this->render('crm/devis/crm_devis_envoyer.html.twig', array(
				'form' => $form->createView(),
				'devis' => $devis,
				'actionCommerciale' => $actionCommerciale
		));
	}

	/**
	 * @Route("/crm/action-commerciale/frais/editer/{id}", name="crm_action_commerciale_frais_editer")
	 */
	public function actionCommercialeFraisEditerAction(Request $request, Frais $frais)
	{

		$form = $this->createForm(FraisType::class, $frais, array(
		    'companyId' => $this->getUser()->getCompany(),
        ));
	
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
	
			$em = $this->getDoctrine()->getManager();
			$em->persist($frais);
			$em->flush();

			return $this->redirect($this->generateUrl(
					'crm_action_commerciale_voir',
					array('id' => $frais->getActionCommerciale()->getId())
			));
		}

		return $this->render('crm/action-commerciale/crm_action_commerciale_frais_editer.html.twig', array(
			'form' => $form->createView(),
			'frais' => $frais
		));
	}

	/**
	 * @Route("/crm/action-commerciale/frais/supprimer/{id}", name="crm_action_commerciale_frais_supprimer")
	 */
	public function actionCommercialeFraisSupprimerAction(Request $request, Frais $frais)
	{
		$form = $this->createFormBuilder()->getForm();
		
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
	
			$em = $this->getDoctrine()->getManager();
			$em->remove($frais);
			$em->flush();

			return $this->redirect($this->generateUrl(
					'crm_action_commerciale_voir',
					array('id' => $frais->getActionCommerciale()->getId())
			));
		}

		return $this->render('crm/action-commerciale/crm_action_commerciale_frais_supprimer.html.twig', array(
			'form' => $form->createView(),
			'frais' => $frais
		));
	}

	/**
	 * @Route("/crm/action-commerciale/frais/ajouter/{id}", name="crm_action_commerciale_frais_ajouter")
	 */
	public function actionCommercialeFraisAjouterAction(Request $request, Opportunite $actionCommerciale)
	{
		$frais = new Frais();
		$frais->setActionCommerciale($actionCommerciale);

        $form = $this->createForm(FraisType::class, $frais, array(
            'companyId' => $this->getUser()->getCompany(),
        ));
	
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
	
			$em = $this->getDoctrine()->getManager();
			$em->persist($frais);
			$em->flush();

			return $this->redirect($this->generateUrl(
					'crm_action_commerciale_voir',
					array('id' => $frais->getActionCommerciale()->getId())
			));
		}

		return $this->render('crm/action-commerciale/crm_action_commerciale_frais_editer.html.twig', array(
			'form' => $form->createView(),
			'frais' => $frais,
			'ajout' => true
		));
	}

	/**
	 * @Route("/crm/action-commerciale/temps/details/{id}", name="crm_action_commerciale_temps_details")
	 */
	public function getDetails(Opportunite $actionCommerciale){

		return $this->render('crm/action-commerciale/crm_action_commerciale_temps_details_modal.html.twig', array(
			'actionCommerciale' => $actionCommerciale
		));

	}

	/**
	 * @Route("/crm/action-commerciale/terminer/{id}/{screen}", name="crm_action_commerciale_terminer")
	 */
	public function terminer(Request $request, Opportunite $actionCommerciale, $screen = 'action_commerciale'){

		$form = $this->createFormBuilder()->getForm();
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {

			$actionCommerciale->setTermine(true);
			$em = $this->getDoctrine()->getManager();
			$em->persist($actionCommerciale);
			$em->flush();

			if('time_tracker' == $screen){
				return $this->redirect($this->generateUrl(
					'time_tracker_index'
				));
			}

			return $this->redirect($this->generateUrl(
				'crm_action_commerciale_voir', array('id' => $actionCommerciale->getId())
			));
		}

		return $this->render('crm/action-commerciale/crm_action_commerciale_terminer.html.twig', array(
			'form' => $form->createView(),
			'actionCommerciale' => $actionCommerciale,
			'screen' => $screen
		));

	}


}
