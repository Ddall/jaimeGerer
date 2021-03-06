<?php

namespace App\Controller\CRM;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class BonCommandeController extends AbstractController
{

	/**
	 * @Route("/crm/bon-commande/get-liste",
	 *   name="crm_bon_commande_get_liste",
	 *   options={"expose"=true}
	 * )
	 */
	public function bonCommandeGetListeAction() {

		$repository = $this->getDoctrine()->getManager()->getRepository('App:CRM\BonCommande');

		$list = $repository->findByCompany($this->getUser()->getCompany());

		$res = array();
		foreach ($list as $bc) {

			$_res = array('id' => $bc->getId(), 'display' => $bc->getNum());
			$res[] = $_res;
		}

		$response = new \Symfony\Component\HttpFoundation\Response(json_encode($res));
		$response->headers->set('Content-Type', 'application/json');
		return $response;
	}

	/**
	 * @Route("/crm/bon_commande/liste",
	 *   name="crm_bon_commande_liste",
	 *  )
	 */
	public function bonCommandeListeAction()
	{

		$activationRepo = $this->getDoctrine()->getManager()->getRepository('App:SettingsActivationOutil');
		$activation = $activationRepo->findOneBy(array(
			'company' => $this->getUser()->getCompany(),
			'outil' => 'CRM'
		));
		$yearActivation = $activation->getDate()->format('Y');

		$currentYear = date('Y');
		$arr_years = array('all' => 'Toutes les années');
		for($i = $yearActivation ; $i<=$currentYear+1; $i++){
			$arr_years[$i] = $i;
		}

		$formBuilder = $this->createFormBuilder();
		$formBuilder
			->add('year', ChoiceType::class, array(
					'required' => true,
					'label' => 'Année',
					'choices' => array_flip($arr_years),
					'attr' => array('class' => 'year-select'),
					'data' => 'all'
			))
			->add('etat', ChoiceType::class, array(
					'required' => true,
					'label' => 'Etat',
					'choices' => array_flip(array(
						'all' => 'Tous',
						'ok' => 'OK',
						'current' => 'En cours',
						'ko' => 'Problème'
					)),
					'attr' => array('class' => 'etat-radio'),
					'expanded' => true,
					'data' => 'all'
			));


		$form = $formBuilder->getForm();

		return $this->render('crm/bon-commande/crm_bon_commande_liste.html.twig', array(
			'form' => $form->createView()
		));
	}	

	/**
	 * @Route("/crm/bon-commande/check",
	 *   name="crm_bon_commande_check",
	 *   options={"expose"=true}
	 * )
	 */
	public function bonCommandeCheckAction(Request $requestData) {

		$repository = $this->getDoctrine()->getManager()->getRepository('App:CRM\BonCommande');

		$arr_sort = $requestData->get('order');
		$arr_cols = $requestData->get('columns');
		$col = $arr_sort[0]['column'];
		$arr_search = $requestData->get('search');

		$year = $requestData->get('year');
		$etat = $requestData->get('etat');

		$arr_all = $repository->findForList(
			$this->getUser()->getCompany(),
			$requestData->get('length'),
			$requestData->get('start'),
			$arr_cols[$col]['data'],
			$arr_sort[0]['dir'],
			$arr_search['value'],
			$year
		);

		$i = 0;
		$list = array();
		foreach ($arr_all as $bc) {

			$ok = false;
			if(strtoupper($etat) == "ALL"){
				$ok = true;
			} else if( strtoupper($etat) == "OK" && $bc->getMontant() ==  $bc->getTotalFacture() && (false == $bc->getFraisRefacturables()  || $bc->getActionCommerciale()->getTotalFrais() == $bc->getActionCommerciale()->getTotalFraisFactures() )  ){
				$ok = true;
			} else if ( strtoupper($etat) == "CURRENT" && ( $bc->getMontant() > $bc->getTotalFacture() ||  (true == $bc->getFraisRefacturables() && $bc->getActionCommerciale()->getTotalFrais() > $bc->getActionCommerciale()->getTotalFraisFactures() ) ) ){
				$ok = true;
			} else if ( strtoupper($etat) == 'KO' && ($bc->getMontant() < $bc->getTotalFacture() || $bc->getActionCommerciale()->getTotalFrais() < $bc->getActionCommerciale()->getTotalFraisFactures() )) {
				$ok = true;
			}

			if($ok){
				$list[$i]['num'] = $bc->getNum();
				$list[$i]['compte'] = $bc->getActionCommerciale()->getCompte()->getNom();
				$list[$i]['compte_id'] = $bc->getActionCommerciale()->getCompte()->getId();
				$list[$i]['objet'] = $bc->getActionCommerciale()->getNom();
				$list[$i]['action_commerciale'] = $bc->getActionCommerciale()->getId();
				$list[$i]['montant'] = $bc->getMontantMonetaire();
				$list[$i]['montant_facture'] = $bc->getTotalFactureMonetaire();
				$list[$i]['frais'] = $bc->getFraisRefacturables();
				$list[$i]['frais_total'] = $bc->getActionCommerciale()->getTotalFrais();
				if($bc->getFraisRefacturables()){
					$list[$i]['frais_factures'] = $bc->getActionCommerciale()->getTotalFraisFactures();
				} else {
					$list[$i]['frais_factures'] = null;
				}
				
					
				if(count($bc->getFactures()) == 0){
					$list[$i]['factures'] = null;
					$list[$i]['factures_id'] = null;
				} else {
					$list[$i]['factures'] = array();
					$list[$i]['factures_id'] = array();

					foreach($bc->getFactures() as $facture ){
						if(false == $facture->getFactureFrais()){
							$list[$i]['factures'][] = $facture->getNum();
							$list[$i]['factures_id'][]= $facture->getId();
							$list[$i]['avoir'] = null;
							foreach($facture->getAvoirs() as $avoir){
								$list[$i]['avoir'].=$avoir->getNum().' ';
							}
						}
					} 
				}

				if(count($bc->getFacturesFrais()) == 0){
					$list[$i]['factures_frais'] = null;
					$list[$i]['factures_frais_id'] = null;
				} else {
					$list[$i]['factures_frais'] = array();
					$list[$i]['factures_frais_id'] = array();

					foreach($bc->getFacturesFrais() as $factureFrais ){
						$list[$i]['factures_frais'][] = $factureFrais->getNum();
						$list[$i]['factures_frais_id'][]= $factureFrais->getId();
						$list[$i]['factures_frais_avoir'] = null;
						foreach($factureFrais->getAvoirs() as $avoir){
							$list[$i]['factures_frais_avoir'].=$avoir->getNum().' ';
						}
					} 
				}
				
				$i++;
			}
			
		}

		$list = array_slice( $list, $requestData->get('start'), $requestData->get('length'));

		$response = new JsonResponse();
		$response->setData(array(
			'draw' => intval( $requestData->get('draw') ),
			'recordsTotal' => $repository->custom_count($this->getUser()->getCompany()),
			'recordsFiltered' => $i,
			'aaData' => $list,
		));
		return $response;

	}

	/**
	 * @Route("/crm/bon-commande/get_compte_contact/{id}", name="crm_bon_commande_get_compte_contact", options={"expose"=true})
	 */
	public function bonCommandeGetCompteContact($id)
	{
		$repository = $this->getDoctrine()->getManager()->getRepository('App:CRM\BonCommande');
		$bc = $repository->find($id);
	
		$response = new JsonResponse();
		$response->setData(array(
    		'compte_id' => $bc->getActionCommerciale()->getCompte()->getId(),
    		'compte_toString' => $bc->getActionCommerciale()->getCompte()->__toString(),
			'contact_id' => $bc->getActionCommerciale()->getContact()->getId(),
			'contact_toString' => $bc->getActionCommerciale()->getContact()->__toString(),
			'analytique' => $bc->getActionCommerciale()->getAnalytique()->getId(),
		));

		return $response;

	}

	/**
	 * @Route("/crm/bon-commande/search",
	 *   name="crm_bon_commande_search",
	 *   options={"expose"=true}
	 * )
	 */
	public function bonCommandeSearchAction() {

		$repository = $this->getDoctrine()->getManager()->getRepository('App:CRM\BonCommande');

		$list = $repository->findWon($this->getUser()->getCompany());

		$res = array();
		foreach ($list as $bc) {

			$_res = array(
				'id' => $bc->getActionCommerciale()->getId(), 
				'display' => 'BC-'.$bc->getNum().' - '.$bc->getActionCommerciale()->getCompte()->getNom().' : '.$bc->getActionCommerciale()->getNom(),
				'refacturable' => $bc->getFraisRefacturables()
			);
			$res[] = $_res;
		}

		$response = new \Symfony\Component\HttpFoundation\Response(json_encode($res));
		$response->headers->set('Content-Type', 'application/json');
		return $response;
	}


	// /**
	//  * @Route("/crm/bon-commande/init",
	//  *   name="crm_bon_commande_init",
	//  *   options={"expose"=true}
	//  * )
	//  */
	// public function init(){

	// 	$em  = $this->getDoctrine()->getManager();
	// 	$bonCommandeRepo = $em->getRepository('App:CRM\BonCommande');
	// 	$factureRepo = $em->getRepository('App:CRM\DocumentPrix');


	// 	$all_bc = $bonCommandeRepo->findAll();

	// 	foreach($all_bc as $bc){
	// 		if($bc->getFacture()){
	// 			$facture = $bc->getFacture();
	// 			$facture->setBonCommande($bc);
	// 			$em->persist($facture);
	// 		}
	// 	}

	// 	$em->flush();

	// 	return new Response();

	// }



}
