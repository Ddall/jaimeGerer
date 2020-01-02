<?php

namespace App\Controller\TimeTracker;

use App\Entity\CRM\Opportunite;
use App\Entity\TimeTracker\Temps;
use App\Form\TimeTracker\TempsType;
use App\Service\ChartService;
use App\Service\TimeTracker\TimeTrackerService;
use App\Service\UtilsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class TimeTrackerController extends AbstractController
{

	/**
	 * @Route("/time-tracker", name="time_tracker_index")
	 */
	public function indexAction(Request $request)
	{
		if(!$this->getUser()->hasRole('ROLE_TIMETRACKER')){
			throw new AccessDeniedException;
		}
		
		$em = $this->getDoctrine()->getManager();
		$repo = $em->getRepository('App:CRM\Opportunite');

		$temps = new Temps();
		$temps->setDate(new \DateTime(date('Y-m-d')));
		$temps->setUser($this->getUser());

		$form = $this->createForm(
			TempsType::class,
			$temps
		);

		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {

			if($form['projet_entity']->getData()){
				$actionCommerciale = $repo->findOneById($form['projet_entity']->getData());
				if($actionCommerciale){
					$temps->setActionCommerciale($actionCommerciale);
				}
			}

			$em->persist($temps);
			$em->flush();

			$this->get('session')->getFlashBag()->add(
				'success',
				$temps->getDureeAsString(). ' ajoutées à '.$temps->getActionCommerciale()->getNom()
			);

			return $this->redirect($this->generateUrl(
				'time_tracker_index'
			));
		}
		
		$arr_projets = $repo->findWonNotClosed($this->getUser()->getCompany());

		return $this->render('time_tracker/time_tracker_index.html.twig', array(
			'form' => $form->createView(),
			'arr_projets' => $arr_projets
		));
	}

	/**
	 * @Route("/time-tracker/reporting", name="time_tracker_reporting")
	 */
	public function reporting(ChartService $chartService, TimeTrackerService $timeTrackerService){

		$formBuilder = $this->createFormBuilder();
		$formBuilder->add('projet_name', \Symfony\Component\Form\Extension\Core\Type\TextType::class, array(
            'label' => 'Projet',
            'attr' => array('class' => 'typeahead-projet', 'autocomplete' => 'off')
        ));

		$form = $formBuilder->getForm();

		$year = date('Y');
		$dataChartTempsTravailMois = $timeTrackerService->getDataChartTempsParMois($this->getUser()->getCompany(), $year);
		$chartTempsTravailMois = $chartService->timeTrackerTempsTravailParMois($dataChartTempsTravailMois);

		$dataChartTempsTravailAnnee= $timeTrackerService->getDataChartTempsParAnnee($this->getUser()->getCompany());
		$chartTempsTravailAnnee = $chartService->timeTrackerTempsTravailParAnnee($dataChartTempsTravailAnnee);

		return $this->render('time_tracker/time_tracker_reporting.html.twig', array(
			'form' => $form->createView(),
			'chartTempsTravailMois' => $chartTempsTravailMois,
			'chartTempsTravailAnnee' => $chartTempsTravailAnnee
		));
	}

	/**
	 * @Route("/time-tracker/reporting/project/{id}", name="time_tracker_reporting_project", 
	 *  options={"expose"=true})
	 */
	public function reportingProject(Opportunite $actionCommerciale, UtilsService $utilsService)
	{

		$arr_mois = array();
		foreach($actionCommerciale->getTemps() as $temps){

			$moisStr = $utilsService->getMoisAsStringFR($temps->getDate()->format('m'));
			$mois = $moisStr.' '.$temps->getDate()->format('Y');

			if(!array_key_exists($mois, $arr_mois)){
				$arr_mois[$mois] = array(
					'total_temps' => 0,
					'total_euro' => 0,
					'users' => array()
				);
			}

			if(!array_key_exists( $temps->getUser()->__toString(), $arr_mois[$mois]['users'] )){
				$arr_mois[$mois]['users'][$temps->getUser()->__toString()] = array(
					'total_temps' => 0,
					'total_euro' => 0
				);
			}

			$arr_mois[$mois]['users'][$temps->getUser()->__toString()]['total_temps']+= $temps->getDuree();
			$arr_mois[$mois]['total_temps']+= $temps->getDuree();

			if($temps->getUser()->getTauxHoraire()){
				$arr_mois[$mois]['users'][$temps->getUser()->__toString()]['total_euro']+= $temps->getDuree()*$temps->getUser()->getTauxHoraire();
				$arr_mois[$mois]['total_euro']+= $temps->getDuree()*$temps->getUser()->getTauxHoraire();
			}
		}

		foreach($arr_mois as $mois => $arr_data){
			$arr_mois[$mois]['total_temps'] = $utilsService->getTempsAsString($arr_data['total_temps']);
			foreach($arr_data['users'] as $id => $arr_user){
				$arr_mois[$mois]['users'][$id]['total_temps'] = $utilsService->getTempsAsString($arr_user['total_temps']);
			}
		}


		return $this->render('time_tracker/time_tracker_reporting_project.html.twig', array(
			'arr_mois' => $arr_mois,
			'actionCommerciale' => $actionCommerciale
		));
	}

}
