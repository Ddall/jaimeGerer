<?php

namespace App\Service\Compta;

use App\Entity\Compta\OperationDiverse;
use App\Service\NumService;
use Doctrine\ORM\EntityManagerInterface;

class OperationDiverseService {

  protected $em;
  protected $compteComptableService;
  protected $numService;


  public function __construct(EntityManagerInterface $em, CompteComptableService $compteComptableService, NumService $numService)
  {
    $this->em = $em;
    $this->compteComptableService = $compteComptableService;
    $this->numService = $numService;
  }

  public function corrigerAffectationAvecOD($ligneJournal, $compteChoisi){

    $numEcriture = $this->numService->getNumEcriture($compteChoisi->getCompany());

    //ecriture d'une ligne Opération Diverse au compte choisi
    $od =  new OperationDiverse();
    $od->setDate(new \DateTime(date('Y-m-d')));

    $libelle = "";
    $codeJournal = $ligneJournal->getCodeJournal();
    if($codeJournal == 'VE'){
      if($ligneJournal->getFacture() != null){
        $libelle = 'Facture '.$ligneJournal->getFacture()->getNum();
        $od->setFacture($ligneJournal->getFacture());
      } elseif($ligneJournal->getAvoir() != null){
        $libelle = 'Avoir '.$ligneJournal->getAvoir()->getNum();
        $od->setAvoir($ligneJournal->getAvoir());
      }
    } elseif($codeJournal == 'AC'){
      if($ligneJournal->getDepense() != null){
        $libelle = 'Dépense '.$ligneJournal->getDepense()->getNum();
        $od->setDepense($ligneJournal->getDepense());
      } elseif($ligneJournal->getAvoir() != null){
        $libelle = 'Avoir '.$ligneJournal->getAvoir()->getNum();
        $od->setAvoir($ligneJournal->getAvoir());
      }
    } elseif($codeJournal != 'OD') {
      $libelle = $ligneJournal->getNom();
    }
    $od->setLibelle($libelle.' - correction depuis compte 471');
    $od->setCodeJournal('OD');
    if($ligneJournal->getDebit() != null){
      $od->setCredit(null);
      $od->setDebit($ligneJournal->getDebit());
    } else {
      $od->setDebit(null);
      $od->setCredit($ligneJournal->getCredit());
    }
    $od->setCompteComptable($compteChoisi);
    $od->setNumEcriture($numEcriture);
    $this->em->persist($od);


    //ecriture d'une ligne Opération Diverse au compte 471
    $compteAttente =   $this->compteComptableService->getCompteAttente($compteChoisi->getCompany());
    $od = new OperationDiverse();
    $od->setDate(new \DateTime(date('Y-m-d')));
    $od->setLibelle($libelle.' - correction pour compte '.$compteChoisi->getNum());
    $od->setCodeJournal('OD');
    //inverser le debit et le credit
    if($ligneJournal->getDebit() != null){
      $od->setDebit(null);
      $od->setCredit($ligneJournal->getDebit());
    } else {
      $od->setCredit(null);
      $od->setDebit($ligneJournal->getCredit());
    }
    $od->setCompteComptable($compteAttente);
    $od->setNumEcriture($numEcriture);
    $this->em->persist($od);

    $this->em->flush();

    $numEcriture++;
    $this->numService->updateNumEcriture($compteChoisi, $numEcriture);
  }


}