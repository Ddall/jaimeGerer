<?php

namespace App\Controller\CRM;

use App\Entity\CRM\CompetCom;
use App\Entity\CRM\Opportunite;
use App\Form\CRM\ActionCommercialeUserCompetComType;
use App\Form\CRM\CompetComType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;


class CompetitionCommercialeController extends AbstractController
{

	/**
	 * @Route("/crm/competition-commerciale", name="crm_competition_commerciale")
	 */
	public function competitionCommerciale()
	{
		$em = $this->getDoctrine()->getManager();
		$compteComRepo = $em->getRepository('App:CRM\CompetCom');
		$actionCommercialeRepo = $em->getRepository('App:CRM\Opportunite');
		$userRepo = $em->getRepository('App:User');

		$competCom = $compteComRepo->findCurrent();
		$users = $userRepo->findBy(array(
			'company' => $this->getUser()->getCompany(),
			'enabled' => true,
			'competCom' => true
		));

		$arr_users = array();
		foreach($users as $user){
			$arr_users[$user->getId()]['user'] = $user;
			$arr_users[$user->getId()]['CATotal'] = 0;
			$arr_users[$user->getId()]['CAPublic'] = 0;
			$arr_users[$user->getId()]['CAPrive'] = 0;
			//$arr_users[$user->getId()]['ratioCA'] = 0;
			$arr_users[$user->getId()]['nbGagnes'] = 0;
			$arr_users[$user->getId()]['prescriptions'] = array();
			$arr_users[$user->getId()]['nouveauxComptes'] = array();
			$arr_users[$user->getId()]['gagneesPublic'] = array();
			$arr_users[$user->getId()]['gagneesPrive'] = array();
		}

		$arr_winners = array(
			'nouveauxComptes' => null,
			//'CA' => null,
			'prescriptions' => null,
			'CAPublic' => null,
			'CAPrive' => null
		);

		if($competCom && count($arr_users)){

			$arr_actionsCommerciales = $actionCommercialeRepo->findWonBetweenDates(
				$this->getUser()->getCompany(),
				$competCom->getStartDate(), 
				$competCom->getEndDate()
			);

			foreach($arr_actionsCommerciales as $actionCommerciale){

				$user = $actionCommerciale->getUserCompetCom();

				if(!array_key_exists($user->getId(), $arr_users)){
					continue;
				}

				$arr_users[$user->getId()]['CATotal']+= $actionCommerciale->getMontant();
				if($actionCommerciale->getPriveOrPublic() == "PUBLIC"){
					$arr_users[$user->getId()]['CAPublic']+= $actionCommerciale->getMontant();
					$arr_users[$user->getId()]['gagneesPublic'][] = $actionCommerciale;
				} else {
					$arr_users[$user->getId()]['CAPrive']+= $actionCommerciale->getMontant();
					$arr_users[$user->getId()]['gagneesPrive'][] = $actionCommerciale;
				}
				
				if($actionCommerciale->getPrescription()){
					$arr_users[$user->getId()]['prescriptions'][] = $actionCommerciale;
				}

				if($actionCommerciale->getPriveOrPublic() == "PRIVE" && true === $actionCommerciale->getNouveauCompte()){
					$arr_users[$user->getId()]['nouveauxComptes'][] = $actionCommerciale;
				}
			}

			$maxNouveauxComptes = 0;
			//$maxRatioCA = 0;
			$maxPrescriptions = 0;
			$maxCAPublic = 0;
			$maxCAPrive = 0;
			foreach($arr_users as $userId => $arr_user){

				if(count($arr_users[$user->getId()]['gagneesPublic']) || count($arr_users[$user->getId()]['gagneesPrive'])){

					// $ratioCA = $arr_users[$userId]['CA']/count($arr_users[$userId]['gagnees']);
					// $arr_users[$userId]['ratioCA'] = $ratioCA;

					// if($ratioCA > $maxRatioCA){
					// 	$maxRatioCA = $arr_users[$userId]['ratioCA'];
					// 	$arr_winners['CA'] = $userId;
					// }

					if($arr_users[$userId]['CAPublic'] > $maxCAPublic){
						$maxCAPublic = $arr_users[$userId]['CAPublic'];
						$arr_winners['CAPublic'] = $userId;
					}

					if($arr_users[$userId]['CAPrive'] > $maxCAPrive){
						$maxCAPrive = $arr_users[$userId]['CAPrive'];
						$arr_winners['CAPrive'] = $userId;
					}

					if(count($arr_users[$userId]['nouveauxComptes']) > $maxNouveauxComptes){
						$maxNouveauxComptes = count($arr_users[$userId]['nouveauxComptes']);
						$arr_winners['nouveauxComptes'] = $userId;
					}

					if(count($arr_users[$userId]['prescriptions']) > $maxPrescriptions){
						$maxPrescriptions = count($arr_users[$userId]['prescriptions']);
						$arr_winners['prescriptions'] = $userId;
					}

				}
			}
		}


		return $this->render('crm/competition-commerciale/crm_competition_commerciale.html.twig', array(
			'arr_winners' => $arr_winners,
			'arr_users'		=> $arr_users,
			'competCom' => $competCom,
		));

	}

	/**
	 * @Route("/crm/competition-commerciale/ajouter", name="crm_competition_commerciale_ajouter")
	 */
	public function competitionCommercialeAjouter(Request $request)
	{
		$em = $this->getDoctrine()->getManager();
		$competCom = new CompetCom();

		$form = $this->createForm(CompetComType::class, $competCom);

		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {

			$em->persist($competCom);
			$em->flush();

			return $this->redirect($this->generateUrl('crm_competition_commerciale'));
		}

		return $this->render('crm/competition-commerciale/crm_competition_commerciale_ajouter.html.twig', array(
			'form' => $form->createView()
		));
	}

	/**
	 * @Route("/crm/competition-commerciale/modifier-user/{id}", name="crm_competition_commerciale_modifier_user")
	 */
	public function competitionCommercialeModifierUser(Request $request, Opportunite $actionCommerciale)
	{
		$em = $this->getDoctrine()->getManager();
		$form = $this->createForm(ActionCommercialeUserCompetComType::class, $actionCommerciale);

		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {

			$em->persist($actionCommerciale);
			$em->flush();

			return $this->redirect($this->generateUrl('crm_competition_commerciale'));
		}

		return $this->render('crm/competition-commerciale/crm_competition_commerciale_modifier_user_modal.html.twig', array(
			'form' => $form->createView(),
			'actionCommerciale' => $actionCommerciale
		));
	}	

	/**
	 * @Route("/crm/objectifs-commerciaux/", name="crm_objectifs_commerciaux")
	 */
	public function objectifsCommerciaux(){

		$em = $this->getDoctrine()->getManager();
		$compteComRepo = $em->getRepository('App:CRM\CompetCom');
		$actionCommercialeRepo = $em->getRepository('App:CRM\Opportunite');
		$userRepo = $em->getRepository('App:User');

		$total = 0;
		$totalPublic = 0;
		$totalPrive = 0;

		$competCom = $compteComRepo->findCurrent();
		$users = $userRepo->findBy(array(
			'company' => $this->getUser()->getCompany(),
			'enabled' => true,
			'competCom' => true
		));

		$arr_users = array();
		foreach($users as $user){
			$arr_users[$user->getId()]['user'] = $user;
			$arr_users[$user->getId()]['CATotal'] = 0;
			$arr_users[$user->getId()]['CAPublic'] = 0;
			$arr_users[$user->getId()]['CAPrive'] = 0;
		}


		if($competCom && count($arr_users)){

			$arr_actionsCommerciales = $actionCommercialeRepo->findWonBetweenDates(
				$this->getUser()->getCompany(),
				$competCom->getStartDate(), 
				$competCom->getEndDate()
			);

			foreach($arr_actionsCommerciales as $actionCommerciale){

				$user = $actionCommerciale->getUserCompetCom();

				if(!array_key_exists($user->getId(), $arr_users)){
					continue;
				}

				$arr_users[$user->getId()]['CATotal']+= $actionCommerciale->getMontant();
				$total+= $actionCommerciale->getMontant();
				if($actionCommerciale->getPriveOrPublic() == "PUBLIC"){
					$totalPublic+= $actionCommerciale->getMontant();
					$arr_users[$user->getId()]['CAPublic']+= $actionCommerciale->getMontant();
				} else {
					$totalPrive+= $actionCommerciale->getMontant();
					$arr_users[$user->getId()]['CAPrive']+= $actionCommerciale->getMontant();
				}
				
			}
			
		}

		usort($arr_users, function($a, $b) {
	   		if($a['CATotal'] < $b['CATotal']){
	   			return 1;
	   		}
	   		return -1;
		});

		return $this->render('crm/competition-commerciale/crm_objectifs_commerciaux.html.twig', array(
			'arr_users'		=> $arr_users,
			'totalPublic' => $totalPublic,
			'totalPrive' => $totalPrive,
			'total' => $total
		));	
	}


}
