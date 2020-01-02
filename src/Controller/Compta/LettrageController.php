<?php

namespace App\Controller\Compta;

use App\Service\Compta\LettrageService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class LettrageController extends AbstractController
{
	
	/**
	 * @Route("/compta/lignes-non-lettrees", name="compta_lignes_non_lettrees")
	 */
	public function comptesNonLettresAction(){

		$arr_years = array();
		$currentYear = date('Y');
		for($year = 2016; $year <= $currentYear; $year++ ){
			$arr_years[$year] = $year;
		}

		$formBuilder = $this->createFormBuilder();
		$formBuilder->add('years', \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, array(
			'required' => true,
			'label' => 'Année',
			'choices' => $arr_years,
			'attr' => array('class' => 'year-select'),
			'data' => $currentYear
		));

		$form = $formBuilder->getForm();

		return $this->render('compta/lettrage/compta_lignes_non_lettrees.html.twig', array(
			'form' => $form->createView()
		));
	
	}

	/**
	 * @Route("/compta/lignes-non-lettrees-voir-annee/{year}",
	 *  name="compta_lignes_non_lettrees_voir_annee",
	 *  options={"expose"=true}
	 * )
	 */
	public function comptesNonLettresVoirAnneesAction(LettrageService $lettrageService, $year){

		$start = new \DateTime($year.'-01-01');
    	$end = new \DateTime($year.'-12-31');

		$repoJournalVente = $this->getDoctrine()->getManager()->getRepository('App:Compta\JournalVente');
		$arr_journal_vente = $repoJournalVente->findNonLettreesByCompanyAndYear($this->getUser()->getCompany(), $year);

		$repoJournalAchat = $this->getDoctrine()->getManager()->getRepository('App:Compta\JournalAchat');
		$arr_journal_achat = $repoJournalAchat->findNonLettreesByCompanyAndYear($this->getUser()->getCompany(), $year);

		$repoJournalBanque = $this->getDoctrine()->getManager()->getRepository('App:Compta\JournalBanque');
		$arr_journal_banque = $repoJournalBanque->findNonLettreesByCompanyAndYear($this->getUser()->getCompany(), $year);

		$repoOperationDiverse = $this->getDoctrine()->getManager()->getRepository('App:Compta\OperationDiverse');
		$arr_operation_diverse = $repoOperationDiverse->findNonLettreesByCompanyAndYear($this->getUser()->getCompany(), $year);

		//regroupement dans 1 seul array
		$arr_lignes = array_merge($arr_journal_vente, $arr_journal_achat, $arr_journal_banque, $arr_operation_diverse);

		$arr_non_lettrees = array();
		foreach($arr_lignes as $ligne){
			
			if(!array_key_exists($ligne->getCompteComptable()->getNum(), $arr_non_lettrees)){
				$arr_non_lettrees[$ligne->getCompteComptable()->getNum()] = array(
					'compteComptable' => $ligne->getCompteComptable(),
					'lettre' => $lettrageService->findNextNum($ligne->getCompteComptable(), $year),
					'lignes' => array()
				);
			}

			$arr_non_lettrees[$ligne->getCompteComptable()->getNum()]['lignes'][] = $ligne;

		}

		ksort($arr_non_lettrees);

		return $this->render('compta/lettrage/compta_lignes_non_lettrees_annee.html.twig', array(
			'arr_non_lettrees' => $arr_non_lettrees,
		));
	
	}



}
