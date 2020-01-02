<?php

namespace App\Controller\Compta;

use App\Entity\Compta\ChequePiece;
use App\Entity\Compta\OperationDiverse;
use App\Entity\Compta\RemiseCheque;
use App\Form\Compta\RemiseChequeType;
use App\Service\NumService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RemiseChequeController extends AbstractController
{
	/**
	 * @Route("/compta/remise-cheque", name="compta_remise_cheque_liste")
	 */
	public function remiseChequeListeAction()
	{
		return $this->render('compta/remise-cheque/compta_remise_cheque_liste.html.twig');
	}

	/**
	 * @Route("/compta/remise-cheque/liste/ajax",
	 *   name="compta_remise_cheque_liste_ajax",
	 *   options={"expose"=true}
	 * )
	 */
	public function remiseChequeListeAjaxAction(Request $requestData)
	{
		$arr_sort = $requestData->get('order');
		$arr_cols = $requestData->get('columns');

		$col = $arr_sort[0]['column'];

		$repository = $this->getDoctrine()->getManager()->getRepository('App:Compta\RemiseCheque');

		$arr_search = $requestData->get('search');

		$list = $repository->findForList(
				$this->getUser()->getCompany(),
				$requestData->get('length'),
				$requestData->get('start'),
				$arr_cols[$col]['data'],
				$arr_sort[0]['dir'],
				$arr_search['value']
		);


		for($i=0; $i<count($list); $i++){
			$arr_f = $list[$i];

			$remiseCheques = $repository->find($arr_f['id']);
			$total = $remiseCheques->getTotal();
			$list[$i]['total'] = $total;
			$list[$i]['nbCheques'] = count($remiseCheques->getCheques());

		}


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
	 * @Route("/compta/remise-cheque/voir/{id}",
	 *   name="compta_remise_cheque_voir",
	 *   options={"expose"=true}
	 * )
	 */
	public function remiseChequeVoirAction(RemiseCheque $remiseCheque)
	{
		return $this->render('compta/remise-cheque/compta_remise_cheque_voir.html.twig', array(
				'remiseCheque' => $remiseCheque,
		));
	}

	/**
	 * @Route("/compta/remise-cheque/ajouter", name="compta_remise_cheque_ajouter")
	 */
	public function remiseChequeAjouterAction(NumService $numService, Request $request)
	{
		$em = $this->getDoctrine()->getManager();
		$repoRemiseCheque = $em->getRepository('App:Compta\RemiseCheque');
		$repoFactures = $em->getRepository('App:CRM\DocumentPrix');

		//remises de cheque
		$arr_all_remises_cheques = $repoRemiseCheque->findForCompany($this->getUser()->getCompany());
		$arr_remises_cheques = array();
		$arr_factures_rapprochees_par_remises_cheques = array();
		$arr_avoirs_rapprochees_par_remises_cheques = array();
		foreach($arr_all_remises_cheques as $remiseCheque){
			if($remiseCheque->getTotalRapproche() < $remiseCheque->getTotal()){
				$arr_remises_cheques[] = $remiseCheque;
			} else {
				foreach($remiseCheque->getCheques() as $cheque){
					foreach($cheque->getPieces() as $piece){
						if($piece->getFacture()){
							$arr_factures_rapprochees_par_remises_cheques[] = $piece->getFacture()->getId();
						}else if($piece->getAvoir()){
							$arr_avoirs_rapprochees_par_remises_cheques[] = $piece->getFacture()->getId();
						}
					}
				}
			}
		}

		//factures
		$arr_all_factures = $repoFactures->findForRemiseCheque($this->getUser()->getCompany(), 'FACTURE', true);
		$arr_factures = array();
		foreach($arr_all_factures as $facture){
			$arr_factures['F'.$facture->getId()] = $facture->getNum().' - '.$facture->getCompte().' - '.$facture->getTotalTTC().'€';
		}

		$avoirsRepo = $em->getRepository('App:Compta\Avoir');
		$arr_avoirs = array();
		$arr_avoirs_tmp = $avoirsRepo->findForCompany('FOURNISSEUR', $this->getUser()->getCompany());
		foreach($arr_avoirs_tmp as $avoir){
			if($avoir->getTotalRapproche() < $avoir->getTotalTTC() && !in_array($avoir->getId(), $arr_avoirs_rapprochees_par_remises_cheques) && !$avoir->isLettre()){
				$arr_avoirs['A'.$avoir->getId()] = $avoir->getNum().' - '.$avoir->getDepense()->getCompte().' - '.$avoir->getTotalTTC();
			}
		}


		$arr_pieces = array(
			'FACTURES' => $arr_factures,
			'AVOIRS FOURNISSEURS' => $arr_avoirs
		);

		$remiseCheque = new RemiseCheque();
		$remiseCheque->setDate(new \DateTime(date('Y-m-d')));

		$form = $this->createForm(RemiseChequeType::class, $remiseCheque, array(
		    'companyId' => $this->getUser()->getCompany()->getId(),
            'arr_cheque_pieces' => $arr_pieces,
        ));

		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {

			$data = $form->getData();

			$arr_cheques = $form['cheques'];
			$i = 0;
			foreach($arr_cheques as $form_cheque){

				$cheque = $data->getCheques()[$i];

				if( $form_cheque['autre']->getData() ){
					$numEcriture = $numService->getNumEcriture($this->getUser()->getCompany());

					$od = new OperationDiverse();
					$od->setDate(new \DateTime(date('Y-m-d')));
					$od->setLibelle($form_cheque['libelle']->getData());
					$od->setCodeJournal('OD');
					$od->setCompteComptable($form_cheque['compteComptable']->getData());
					$od->setDebit($form_cheque['montant']->getData());
					$od->setNumEcriture($numEcriture);
					$em->persist($od);

					$od = new OperationDiverse();
					$od->setDate(new \DateTime(date('Y-m-d')));
					$od->setLibelle($form_cheque['libelle']->getData());
					$od->setCodeJournal('OD');
					$od->setCompteComptable($form_cheque['compteComptableTiers']->getData());
					$od->setCredit($form_cheque['montant']->getData());
					$od->setNumEcriture($numEcriture);
					$em->persist($od);

					$numEcriture++;
					$numService->updateNumEcriture($this->getUser()->getCompany(), $numEcriture);

					$cheque->setEmetteur($form_cheque['emetteur']->getData());

					$chequePiece = new ChequePiece();
					$chequePiece->setCheque($cheque);
					$chequePiece->setOperationDiverse($od);
					$cheque->addPiece($chequePiece);

				} else {
					$arr_pieces_id = $form_cheque['select']->getData();
					foreach($arr_pieces_id as $s_id){
						$chequePiece = new ChequePiece();
						
						$chequePiece->setCheque($cheque);
						$type = substr($s_id,0,1);
						$num = substr($s_id,1);

						if($type == 'F'){
							$facture = $repoFactures->find($num);
							$facture->setEtat('PAID');
							$em->persist($facture);
							$chequePiece->setFacture($facture);
						} else if($type == 'A'){
							$avoir = $avoirsRepo->find($num);
							$chequePiece->setAvoir($avoir);
						}
						$cheque->addPiece($chequePiece);
					}
				}
				$i++;
			}

			$remiseCheque->setDateCreation(new \DateTime(date('Y-m-d')));
			$remiseCheque->setUserCreation($this->getUser());

			//numéro de remise de cheque
			$settingsRepository = $em->getRepository('App:Settings');
			$settingsNum = $settingsRepository->findOneBy(array('module' => 'COMPTA', 'parametre' => 'NUMERO_REMISE_CHEQUE', 'company'=>$this->getUser()->getCompany()));
			$currentNum = $settingsNum->getValeur();
			$prefixe = 'RDC-'.date('Y').'-';
			if($currentNum < 10){
				$prefixe.='00';
			} else if ($currentNum < 100){
				$prefixe.='0';
			}
			$remiseCheque->setNum($prefixe.$currentNum);

			//mise à jour du numero de remise de cheque
			$currentNum++;
			$settingsNum->setValeur($currentNum);
			$em->persist($settingsNum);

			$em->persist($remiseCheque);
			$em->flush();

			return $this->redirect($this->generateUrl(
				'compta_remise_cheque_liste')
			);

		}

		return $this->render('compta/remise-cheque/compta_remise_cheque_ajouter.html.twig', array(
				'form' => $form->createView(),
		));
	}

	/**
	 * @Route("/compta/remise-cheque/editer/{id}",
	 *   name="compta_remise_cheque_editer",
	 *   options={"expose"=true}
	 * )
	 */
	public function remiseChequeEditerAction(Request $request, RemiseCheque $remiseCheque)
	{
		$em = $this->getDoctrine()->getManager();
		$repoRemiseCheque = $em->getRepository('App:Compta\RemiseCheque');
		$repoFactures = $em->getRepository('App:CRM\DocumentPrix');

		//remises de cheque
		$arr_all_remises_cheques = $repoRemiseCheque->findForCompany($this->getUser()->getCompany());
		$arr_remises_cheques = array();
		$arr_factures_rapprochees_par_remises_cheques = array();
		$arr_avoirs_rapprochees_par_remises_cheques = array();
		$arr_od_rapprochees_par_remises_cheques = array();
		foreach($arr_all_remises_cheques as $rc){
			if($rc->getTotalRapproche() < $rc->getTotal()){
				$arr_remises_cheques[] = $rc;
			} else {
				foreach($rc->getCheques() as $cheque){
					foreach($cheque->getPieces() as $piece){
						if($piece->getFacture()){
							$arr_factures_rapprochees_par_remises_cheques[] = $piece->getFacture()->getId();
						} else if($piece->getAvoir()){
							$arr_avoirs_rapprochees_par_remises_cheques[] = $piece->getFacture()->getId();
						}
					}
				}
			}
		}

		//factures
		$arr_all_factures = $repoFactures->findForCompany($this->getUser()->getCompany(), 'FACTURE', true);
		$arr_factures = array();
		foreach($arr_all_factures as $facture){
			if($facture->getTotalRapproche() < $facture->getTotalTTC() && $facture->getEtat() != "PAID" && !in_array($facture->getId(), $arr_factures_rapprochees_par_remises_cheques) && $facture->getTotalAvoirs() < $facture->getTotalTTC()){
				$arr_factures['F'.$facture->getId()] = $facture->getNum().' - '.$facture->getCompte().' - '.$facture->getTotalTTC().'€';
			}
		}

		foreach($remiseCheque->getCheques() as $cheque){
			foreach($cheque->getPieces() as  $piece){
				if($piece->getFacture()){
					$facture = $piece->getFacture();
					$arr_factures['F'.$facture->getId()] = $facture->getNum().' - '.$facture->getCompte().' - '.$facture->getTotalTTC().'€';
				}
			}
		}

		$avoirsRepo = $em->getRepository('App:Compta\Avoir');
		$arr_avoirs = array();
		$arr_avoirs_tmp = $avoirsRepo->findForCompany('FOURNISSEUR', $this->getUser()->getCompany());
		foreach($arr_avoirs as $avoir){
			$arr_avoirs['A'.$avoir->getId()] = $avoir->getNum().' - '.$avoir->getCompte().' - '.$avoir->getTotalTTC();
		}

		$arr_pieces = array(
			'FACTURES' => $arr_factures,
			'AVOIRS FOURNISSEURS' => $arr_avoirs
		);

        $form = $this->createForm(RemiseChequeType::class, $remiseCheque, array(
            'companyId' => $this->getUser()->getCompany()->getId(),
            'arr_cheque_pieces' => $arr_pieces,
        ));

		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$data = $form->getData();
			$arr_cheques = $form['cheques'];

			//clear cheques collection
			foreach($remiseCheque->getCheques() as $oldCheque){
				$pieces = $oldCheque->getPieces();
				foreach($pieces as $piece){
					if($piece->getFacture()){
						$piece->getFacture()->setEtat('SENT');
					}
				}
				$pieces->clear();
				// $remiseCheque->removeCheque($oldCheque);
				// $em->remove($oldCheque);
			}

			$i = 0;
			foreach($arr_cheques as $form_cheque){

				$cheque = $data->getCheques()[$i];

				if( $form_cheque['autre']->getData() ){
					$od = new OperationDiverse();
					$od->setDate(new \DateTime(date('Y-m-d')));
					$od->setLibelle($form_cheque['libelle']->getData());
					$od->setCodeJournal('OD');
					$od->setCompteComptable($form_cheque['compteComptable']->getData());
					$od->setDebit($form_cheque['montant']->getData());
					$em->persist($od);

					$od = new OperationDiverse();
					$od->setDate(new \DateTime(date('Y-m-d')));
					$od->setLibelle($form_cheque['libelle']->getData());
					$od->setCodeJournal('OD');
					$od->setCompteComptable($form_cheque['compteComptableTiers']->getData());
					$od->setCredit($form_cheque['montant']->getData());
					$em->persist($od);

					$cheque->setEmetteur($form_cheque['emetteur']->getData());

					$chequePiece = new ChequePiece();
					$chequePiece->setCheque($cheque);
					$chequePiece->setOperationDiverse($od);
					$cheque->addPiece($chequePiece);

				} else {
					$arr_pieces_id = $form_cheque['select']->getData();
					foreach($arr_pieces_id as $s_id){
						$chequePiece = new ChequePiece();
						
						$chequePiece->setCheque($cheque);
						$type = substr($s_id,0,1);
						$num = substr($s_id,1);

						if($type == 'F'){
							$facture = $repoFactures->find($num);
							$facture->setEtat('PAID');
							$em->persist($facture);
							$chequePiece->setFacture($facture);
						} else if($type == 'A'){
							$avoir = $avoirsRepo->find($num);
							$chequePiece->setAvoir($avoir);
						}
						$cheque->addPiece($chequePiece);
					}
				}
				$i++;
			}


			$remiseCheque->setDateEdition(new \DateTime(date('Y-m-d')));
			$remiseCheque->setUserEdition($this->getUser());

			$em->persist($remiseCheque);
			$em->flush();

			return $this->redirect($this->generateUrl(
					'compta_remise_cheque_liste'
			));
		}

		return $this->render('compta/remise-cheque/compta_remise_cheque_editer.html.twig', array(
				'form' => $form->createView(),
				'remiseCheque' => $remiseCheque
		));
	}

	/**
	 * @Route("/compta/remise-cheque/exporter/{id}",
	 *   name="compta_remise_cheque_exporter",
	 *   options={"expose"=true}
	 * )
	 */
	public function remiseChequeExporterAction(RemiseCheque $remiseCheque)
	{
		$html = $this->renderView('compta/remise-cheque/compta_remise_cheque_exporter.html.twig',array(
				'remiseCheque' => $remiseCheque,
		));

		$filename = $remiseCheque->getNum().'.pdf';

		//$filename = $facture->getNum().'.'.$nomClient.'.pdf';
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

}
