<?php

namespace App\Service\Compta;

use App\Entity\Compta\PrevTableauBord;
use App\Service\LegacyExcelFactory;
use App\Service\UtilsService;
use Doctrine\ORM\EntityManagerInterface;


class TableauBordService{

  protected $em;
  protected $utilsService;
  protected $phpExcel;
  protected $arr_postes;
  protected $arr_couts_marginaux;
  protected $ccAutoroute;
  protected $ccCarburant;
  protected $ccResaSalle;
  protected $ccStages;
  protected $ccFormationPersonnel;
  protected $ccUrssaf;
  protected $ccMalakoffMederic;
  protected $arr_totaux;
  protected $arr_depenses_next_year;
  protected $arr_repartitionCoutsParCompteComptable;

    /**
     * TableauBordService constructor.
     */
  public function __construct(EntityManagerInterface $em, UtilsService $utilsService, LegacyExcelFactory $phpExcel)
  {
    $this->em = $em;
    $this->utilsService = $utilsService;
    $this->phpExcel = $phpExcel;

    $this->arr_postes = array(
      'actions_commerciales' => 'Actions commerciales',
      'ca_mois' => 'CA sur le mois',
      'couts_marginaux' => 'Coûts marginaux',
      'couts_exploitation' => 'Coûts d\'exploitation',
      'dotation_amortissements' => 'Dotation aux amortissements'
    );

    $this->arr_couts_marginaux = array(
      'sous_traitance' => 'Sous-traitance',
      'couts_deplacements' => 'Coûts de déplacements',
      'reservation_salles' => 'Réservations de salles'
    );

    $this->arr_couts_exploitation = array(
      'communication_impression' => 'Communication et impression',
      'loyers' => 'Loyers',
      'petit_equipement_fournitures' => 'Petit équipement et fournitures',
      'autres_couts' => 'Autres coûts',
      'couts_bancaires' => 'Coûts bancaires',
      'formation_personnel' => 'Formation du personnel',
      'comptable_admin' => 'Comptable et autres coûts administratifs',
      'dons' => 'Dons',
      'taxes' => 'Taxes',
      'ajustement_tva' => 'Ajustement TVA',
      'salaires_nets' => 'Salaires nets',
      'vie' => 'V.I.E.',
      'charges' => 'Charges',
      'stages' => 'Gratifications de stages',
      'variation_cp' => 'Variations CP + charges',
    );

    $this->arr_totaux = array(
      'prev' => array(),
      'accurate' => array(),
      'predictif' => array()
    );

  }

  private function _initCoutsParCompteComptable($company){

    $compteComptableRepo = $this->em->getRepository('App:Compta\CompteComptable');
    $this->ccAutoroute = $compteComptableRepo->searchOneByNameAndNum('autoroute', $company, 6);
    $this->ccCarburant = $compteComptableRepo->searchOneByNameAndNum('carburant', $company, 6);
    $this->ccResaSalle = $compteComptableRepo->searchOneByNameAndNum('location immobilière ponctuelle ', $company, 6);
    $this->ccStages = $compteComptableRepo->searchOneByNameAndNum('stage', $company, 6);
    $this->ccFormationPersonnel = $compteComptableRepo->searchOneByNameAndNum('formation du personnel', $company, 6);
    $this->ccUrssaf = $compteComptableRepo->searchOneByNameAndNum('Cotisations à l\'URSSAF', $company, 6);
    $this->ccMalakoffMederic = $compteComptableRepo->searchOneByNameAndNum('Malakoff Mederic', $company, 4);

    $this->arr_repartitionCoutsParCompteComptable = array();

    $this->arr_repartitionCoutsParCompteComptable = array(
        'couts_deplacements' => array('6251'),
        'communication_impression' => array('623', '626'),
        'loyers' => array('6132'),
        'petit_equipement_fournitures' => array('6064'),
        'couts_bancaires' => array('627', '6616'),
        'formation_personnel' => array('6333', '6228'),
        'comptable_admin' => array('6226'),
        'dons' => array('6238', '6713'),
        'taxes' => array(),
        'ajustement_tva' => array(),
        'salaires_nets' => array('6410'),
        'vie' => array('622626'),
        'charges' => array('64'),
        'stages' => array(),
        'variation_cp' => array(),
      );

      if($this->ccAutoroute){
        $this->arr_repartitionCoutsParCompteComptable['couts_deplacements'][] = $this->ccAutoroute->getNum();
      }
      if($this->ccCarburant){
        $this->arr_repartitionCoutsParCompteComptable['couts_deplacements'][] = $this->ccCarburant->getNum();
      }
      if($this->ccResaSalle){
        $this->arr_repartitionCoutsParCompteComptable['reservation_salles'][] = $this->ccResaSalle->getNum();
      }
      if($this->ccFormationPersonnel){
        $this->arr_repartitionCoutsParCompteComptable['formation_personnel'][] = $this->ccFormationPersonnel->getNum();
      }
      if($this->ccStages){
        $this->arr_repartitionCoutsParCompteComptable['stages'][] = $this->ccStages->getNum();
      }

  }

  public function creerTableauBord($year, $company){

    $this->_initCoutsParCompteComptable($company);

    $tableauPrevisonnel = $this->creerTableauPrevisionnel($year, $company);
    $tableauAccurate = $this->creerTableauAccurate($year, $company);
    $tableauPredictif = $this->creerTableauPredictif($tableauPrevisonnel, $tableauAccurate, $year);

    return array(
        'arr_prev' => $tableauPrevisonnel,
        'arr_accurate' => $tableauAccurate,
        'arr_predictif' => $tableauPredictif,
        'arr_postes' => $this->arr_postes,
        'arr_couts_marginaux' => $this->arr_couts_marginaux,
        'arr_couts_exploitation' => $this->arr_couts_exploitation,
        'arr_totaux' => $this->arr_totaux,
    );

  }

  private function creerTableauPrevisionnel($year, $company){

    $tableauPrevisonnel = array();
    foreach($this->arr_postes as $poste => $label){
      if($poste == 'actions_commerciales' || $poste == 'ca_mois'){
        $tableauPrevisonnel[$poste] = $this->creerTableauPosteAnalytiquePrevisionnel($year, $company, $poste);
      } else if($poste == 'couts_marginaux' || $poste == 'couts_exploitation'){
        $tableauPrevisonnel[$poste] = $this->creerTableauCoutsPrevisionnel($year, $company, $poste);
      } else if($poste == 'dotation_amortissements' ){
        $tableauPrevisonnel[$poste] = $this->creerTableauDotationAmortissementsPrevisionnel($year, $company);
      }

    }
    $this->calculMargeBrute('prev');
    $this->calculResultatExploitation('prev', $year);
    $this->calculRatioResultatCA('prev');

    return $tableauPrevisonnel;
  }

  private function creerTableauAccurate($year, $company){

    $tableauAccurate = array();
    $tableauAccurate['actions_commerciales'] = $this->creerTableauActionsCommercialesAccurate($year, $company);
    $tableauAccurate['ca_mois'] = $this->creerTableauCAMoisAccurate($year, $company);
    $tableauAccurate['couts_marginaux'] = $this->creerTableauCoutsMarginauxAccurate($year, $company);
    $tableauAccurate['couts_exploitation'] = $this->creerTableauCoutsExploitationAccurate($year, $company);
    $tableauAccurate['dotation_amortissements'] = $this->creerTableauDotationAmortissementsAccurate($year, $company);

    $this->calculMargeBrute('accurate');
    $this->calculResultatExploitation('accurate', $year);
    $this->calculRatioResultatCA('accurate');

    return $tableauAccurate;
  }

  private function creerTableauPredictif($tableauPrevisonnel, $tableauAccurate, $year){
    $tableauPredictif = array();
    $moisDebutPredictif = $this->getMoisDebutPredictif('n');

    $tableauPredictif['actions_commerciales'] = $tableauPrevisonnel['actions_commerciales'];
    $tableauPredictif['ca_mois'] = $tableauAccurate['ca_mois'];
    $tableauPredictif['couts_marginaux'] = $tableauPrevisonnel['couts_marginaux'];
    $tableauPredictif['couts_marginaux']['sous_traitance'] = $tableauAccurate['couts_marginaux']['sous_traitance'];
    $tableauPredictif['couts_exploitation'] = $tableauPrevisonnel['couts_exploitation'];
    $tableauPredictif['dotation_amortissements'] = $tableauPrevisonnel['dotation_amortissements'];

    $this->arr_totaux['predictif']['actions_commerciales'] = $this->arr_totaux['prev']['actions_commerciales'];

    $this->arr_totaux['predictif']['ca_mois'] = $this->arr_totaux['accurate']['ca_mois'];
    $this->arr_totaux['predictif']['ca_mois_repartition'] = $this->arr_totaux['accurate']['ca_mois_repartition'];
    // $this->arr_totaux['predictif']['ca_mois']['total'] = 0;
    //  foreach($tableauPredictif['ca_mois'] as $sous_poste => $arr){
    //   $tableauPredictif['ca_mois'][$sous_poste]['total'] = 0;
    //   for($mois = $moisDebutPredictif; $mois <=12; $mois++){
    //     $tableauPredictif['ca_mois'][$sous_poste]['total']+= $tableauPredictif['ca_mois'][$sous_poste][$mois]['val'];
    //     // $this->arr_totaux['predictif']['ca_mois']['total']+= $tableauPredictif['ca_mois'][$sous_poste][$mois]['val'];
    //   }
    // }
    $this->arr_totaux['predictif']['couts_marginaux'] = $this->arr_totaux['prev']['couts_marginaux'];
    $this->arr_totaux['predictif']['couts_marginaux']['total'] = 0;
    for($mois = 1; $mois <=12; $mois++){
      $this->arr_totaux['predictif']['couts_marginaux'][$mois] = $tableauPredictif['couts_marginaux']['sous_traitance'][$mois]['val'] + $tableauPredictif['couts_marginaux']['couts_deplacements'][$mois] + $tableauPredictif['couts_marginaux']['reservation_salles'][$mois];
      $this->arr_totaux['predictif']['couts_marginaux']['total']+= $this->arr_totaux['predictif']['couts_marginaux'][$mois];
    }
   
    // 
    // foreach($tableauPredictif['couts_marginaux'] as $sous_poste => $arr){
    //   $tableauPredictif['couts_marginaux'][$sous_poste]['total'] = 0;
    //   for($mois = $moisDebutPredictif; $mois <=12; $mois++){
    //     if( "sous_traitance" == $sous_poste ){
    //       $tableauPredictif['couts_marginaux'][$sous_poste]['total']+= $tableauPredictif['couts_marginaux'][$sous_poste][$mois]['val'];
    //       $this->arr_totaux['predictif']['couts_marginaux']['total']+= $tableauPredictif['couts_marginaux'][$sous_poste][$mois]['val'];
    //     } else {
    //       $tableauPredictif['couts_marginaux'][$sous_poste]['total']+= $tableauPredictif['couts_marginaux'][$sous_poste][$mois];
    //       $this->arr_totaux['predictif']['couts_marginaux']['total']+= $tableauPredictif['couts_marginaux'][$sous_poste][$mois];
    //     }
    //   }
    // }

    $this->arr_totaux['predictif']['couts_exploitation'] = $this->arr_totaux['prev']['couts_exploitation'];
    // $this->arr_totaux['predictif']['couts_exploitation']['total'] = 0;
    // foreach($tableauPredictif['couts_exploitation'] as $sous_poste => $arr){
    //   $tableauPredictif['couts_exploitation'][$sous_poste]['total'] = 0;
    //   for($mois = $moisDebutPredictif; $mois <=12; $mois++){
    //     $tableauPredictif['couts_exploitation'][$sous_poste]['total']+= $tableauPredictif['couts_exploitation'][$sous_poste][$mois];
    //     $this->arr_totaux['predictif']['couts_exploitation']['total']+= $tableauPredictif['couts_exploitation'][$sous_poste][$mois];
    //   }
    // }


    $this->arr_totaux['predictif']['dotation_amortissements'] = $this->arr_totaux['prev']['dotation_amortissements'];
    // $this->arr_totaux['predictif']['dotation_amortissements']['total'] = 0;
    // for($mois = $moisDebutPredictif; $mois <=12; $mois++){
    //   $this->arr_totaux['predictif']['dotation_amortissements']['total']+= $tableauPredictif['dotation_amortissements']['dotation_amortissements'][$mois];
    // }

    $this->calculMargeBrute('predictif');
    $this->calculResultatExploitation('predictif', $year);
    $this->calculRatioResultatCA('predictif');

    return $tableauPredictif;
  }

    private function creerTableauPosteAnalytiquePrevisionnel($year, $company, $poste){

        $settingsRepo = $this->em->getRepository('App:Settings');
        $arr_analytiques = $settingsRepo->findBy(array(
          'company' => $company,
          'parametre' => 'analytique',
          'module' => 'CRM'
        ));

        $prevRepo = $this->em->getRepository('App:Compta\PrevTableauBord');

        $arr_prev = array();
        $this->arr_totaux['prev'][$poste] = array();
        $this->arr_totaux['prev'][$poste]['total'] = 0;
        $this->arr_totaux['prev'][$poste.'_repartition'] = array();
        $this->arr_totaux['prev'][$poste.'_repartition']['total']['public'] = 0;
        $this->arr_totaux['prev'][$poste.'_repartition']['total']['prive'] = 0;

        foreach($arr_analytiques as $analytique){
            $arr_prev[$analytique->getValeur().'_prive'] = array();
            $arr_prev[$analytique->getValeur().'_prive']['total'] = 0;
            $arr_prev[$analytique->getValeur().'_public'] = array();
            $arr_prev[$analytique->getValeur().'_public']['total'] = 0;

            for($i = 1; $i<=12; $i++){
                $arr_prev[$analytique->getValeur().'_prive'][$i] = 0;
                $arr_prev[$analytique->getValeur().'_public'][$i] = 0;

                if( !array_key_exists($i, $this->arr_totaux['prev'][$poste]) ){
                    $this->arr_totaux['prev'][$poste][$i] = 0;
                    $this->arr_totaux['prev'][$poste.'_repartition'][$i]['public'] = 0;
                    $this->arr_totaux['prev'][$poste.'_repartition'][$i]['prive'] = 0;
                }

                $arr_saved = $prevRepo->findBy(array(
                    'company' => $company,
                    'poste' => $poste,
                    'annee' => $year,
                    'mois' => $i,
                    'analytique' => $analytique,
                    'priveOrPublic' => 'PRIVE'
                ));

                foreach($arr_saved as $prevPrive){
                    $arr_prev[$analytique->getValeur().'_prive'][$i]+= $prevPrive->getMontantMonetaire();
                    $arr_prev[$analytique->getValeur().'_prive']['total']+= $prevPrive->getMontantMonetaire();
                    $this->arr_totaux['prev'][$poste][$i]+= $prevPrive->getMontantMonetaire();
                    $this->arr_totaux['prev'][$poste]['total']+= $prevPrive->getMontantMonetaire();
                    $this->arr_totaux['prev'][$poste.'_repartition'][$i]['prive']+= $prevPrive->getMontantMonetaire();
                    $this->arr_totaux['prev'][$poste.'_repartition']['total']['prive']+= $prevPrive->getMontantMonetaire();
                }

                $arr_saved = $prevRepo->findBy(array(
                    'company' => $company,
                    'poste' => $poste,
                    'annee' => $year,
                    'mois' => $i,
                    'analytique' => $analytique,
                    'priveOrPublic' => 'PUBLIC'
                ));

                foreach($arr_saved as $prevPublic){
                    $arr_prev[$analytique->getValeur().'_public'][$i]+= $prevPublic->getMontantMonetaire();
                    $arr_prev[$analytique->getValeur().'_public']['total']+= $prevPublic->getMontantMonetaire();
                    $this->arr_totaux['prev'][$poste][$i]+= $prevPublic->getMontantMonetaire();
                    $this->arr_totaux['prev'][$poste]['total']+= $prevPublic->getMontantMonetaire();
                    $this->arr_totaux['prev'][$poste.'_repartition'][$i]['public']+= $prevPublic->getMontantMonetaire();
                    $this->arr_totaux['prev'][$poste.'_repartition']['total']['public']+= $prevPublic->getMontantMonetaire();
                }
            }
        }

         if('ca_mois' == $poste){
            $arr_prev['frais_public'] = array();
            $arr_prev['frais_public']['total'] = 0;

            $arr_prev['frais_prive'] = array();
            $arr_prev['frais_prive']['total'] = 0;

            for($i = 1; $i<=12; $i++){
                $arr_prev['frais_public'][$i] = 0;
                $arr_prev['frais_prive'][$i] = 0;

                $arr_saved = $prevRepo->findBy(array(
                    'company' => $company,
                    'poste' => $poste,
                    'annee' => $year,
                    'mois' => $i,
                    'analytique' => null,
                    'priveOrPublic' => 'PRIVE',
                    'frais' => true
                ));

                foreach($arr_saved as $prevPrive){
                    $arr_prev['frais_prive'][$i]+= $prevPrive->getMontantMonetaire();
                    $arr_prev['frais_prive']['total']+= $prevPrive->getMontantMonetaire();
                    $this->arr_totaux['prev'][$poste][$i]+= $prevPrive->getMontantMonetaire();
                    $this->arr_totaux['prev'][$poste]['total']+= $prevPrive->getMontantMonetaire();
                    $this->arr_totaux['prev'][$poste.'_repartition'][$i]['prive']+= $prevPrive->getMontantMonetaire();
                    $this->arr_totaux['prev'][$poste.'_repartition']['total']['prive']+= $prevPrive->getMontantMonetaire();
                }

                $arr_saved = $prevRepo->findBy(array(
                    'company' => $company,
                    'poste' => $poste,
                    'annee' => $year,
                    'mois' => $i,
                    'analytique' => null,
                    'priveOrPublic' => 'PUBLIC',
                    'frais' => true
                ));

                foreach($arr_saved as $prevPublic){
                    $arr_prev['frais_public'][$i]+= $prevPublic->getMontantMonetaire();
                    $arr_prev['frais_public']['total']+= $prevPublic->getMontantMonetaire();
                    $this->arr_totaux['prev'][$poste][$i]+= $prevPublic->getMontantMonetaire();
                    $this->arr_totaux['prev'][$poste]['total']+= $prevPublic->getMontantMonetaire();
                    $this->arr_totaux['prev'][$poste.'_repartition'][$i]['public']+= $prevPublic->getMontantMonetaire();
                    $this->arr_totaux['prev'][$poste.'_repartition']['total']['public']+= $prevPublic->getMontantMonetaire();
                }
            }

            
        }
        return $arr_prev;
    }

  private function creerTableauCoutsPrevisionnel($year, $company, $poste){

    $prevRepo = $this->em->getRepository('App:Compta\PrevTableauBord');

    $arr_prev = array();
    $this->arr_totaux['prev'][$poste] = array();
    $this->arr_totaux['prev'][$poste]['total'] = 0;

    $arr_couts = $this->arr_couts_marginaux;
    if($poste == 'couts_exploitation'){
      $arr_couts = $this->arr_couts_exploitation;
    }

    foreach($arr_couts as $cout => $label){
      $arr_prev[$cout] = array();
      $arr_prev[$cout]['total'] = 0;

      for($i = 1; $i<=12; $i++){
        if( !array_key_exists($i, $this->arr_totaux['prev'][$poste]) ){
          $this->arr_totaux['prev'][$poste][$i] = 0;
        }
        $arr_prev[$cout][$i] = 0;

        $arr_saved = $prevRepo->findBy(array(
          'company' => $company,
          'poste' => $cout,
          'annee' => $year,
          'mois' => $i,
        ));

        foreach($arr_saved as $prev){
          $arr_prev[$cout][$i]+= $prev->getMontantMonetaire();
          $this->arr_totaux['prev'][$poste][$i]+= $prev->getMontantMonetaire();
          $arr_prev[$cout]['total']+= $prev->getMontantMonetaire();
          $this->arr_totaux['prev'][$poste]['total']+= $prev->getMontantMonetaire();
        }
      }
    }

    return $arr_prev;
  }

  private function creerTableauDotationAmortissementsPrevisionnel($year, $company){

    $prevRepo = $this->em->getRepository('App:Compta\PrevTableauBord');

    $arr_prev = array();
    $this->arr_totaux['prev']['dotation_amortissements'] = array();
    $this->arr_totaux['prev']['dotation_amortissements']['total'] = 0;

    for($i = 1; $i<=12; $i++){
      if( !array_key_exists($i, $this->arr_totaux['prev']['dotation_amortissements']) ){
        $this->arr_totaux['prev']['dotation_amortissements'] [$i] = 0;
        $arr_prev['dotation_amortissements'][$i] = 0;
      }

      $arr_saved = $prevRepo->findBy(array(
        'company' => $company,
        'poste' => 'dotation_amortissements',
        'annee' => $year,
        'mois' => $i,
      ));

      foreach($arr_saved as $prev){
        $arr_prev['dotation_amortissements'][$i]+= $prev->getMontantMonetaire();
        $this->arr_totaux['prev']['dotation_amortissements'][$i] = $prev->getMontantMonetaire();
        $this->arr_totaux['prev']['dotation_amortissements']['total']+= $prev->getMontantMonetaire();
      }
  }

    return $arr_prev;
  }

    private function creerTableauActionsCommercialesAccurate($year, $company){

        $opportuniteRepo = $this->em->getRepository('App:CRM\Opportunite');
        $arr_opportunite = $opportuniteRepo->findForCompanyByYear($company, $year);

        $settingsRepo = $this->em->getRepository('App:Settings');
        $arr_analytiques = $settingsRepo->findBy(array(
            'company' => $company,
            'parametre' => 'analytique',
            'module' => 'CRM'
        ));

        $arr_details = array();
        foreach($arr_analytiques as $analytique){
            $arr_details[$analytique->getValeur().'_prive'] = array();
            $arr_details[$analytique->getValeur().'_prive']['total'] = 0;

            $arr_details[$analytique->getValeur().'_public'] = array();
            $arr_details[$analytique->getValeur().'_public']['total'] = 0;
            for($i = 1; $i<=12; $i++){
                $arr_details[$analytique->getValeur().'_prive'][$i]['val'] = 0;
                $arr_details[$analytique->getValeur().'_public'][$i]['val'] = 0;
            }
        }

        $this->arr_totaux['accurate']['actions_commerciales'] = array();
        $this->arr_totaux['accurate']['actions_commerciales_repartition'] = array();
        for($i = 1; $i<=12; $i++){
            $this->arr_totaux['accurate']['actions_commerciales'][$i] = 0;
            $this->arr_totaux['accurate']['actions_commerciales_repartition'][$i]['public'] = 0;
            $this->arr_totaux['accurate']['actions_commerciales_repartition'][$i]['prive'] = 0;
        }
        $this->arr_totaux['accurate']['actions_commerciales']['total'] = 0;
        $this->arr_totaux['accurate']['actions_commerciales_repartition']['total']['public'] = 0;
        $this->arr_totaux['accurate']['actions_commerciales_repartition']['total']['prive'] = 0;

        foreach($arr_opportunite as $opportunite){
            $month = $opportunite->getDate()->format('n');
            if($opportunite->isSecteurPublic()){
                $arr_details[$opportunite->getAnalytique()->getValeur().'_public'][$month]['val']+= $opportunite->getMontant();
                $arr_details[$opportunite->getAnalytique()->getValeur().'_public'][$month]['details']['opportunites'][] = $opportunite;
                $arr_details[$opportunite->getAnalytique()->getValeur().'_public']['total']+= $opportunite->getMontant();
                $this->arr_totaux['accurate']['actions_commerciales_repartition'][$month]['public']+= $opportunite->getMontant();
                $this->arr_totaux['accurate']['actions_commerciales_repartition']['total']['public']+= $opportunite->getMontant();
            } else {
                $arr_details[$opportunite->getAnalytique()->getValeur().'_prive'][$month]['val']+= $opportunite->getMontant();
                $arr_details[$opportunite->getAnalytique()->getValeur().'_prive'][$month]['details']['opportunites'][] = $opportunite;
                $arr_details[$opportunite->getAnalytique()->getValeur().'_prive']['total']+= $opportunite->getMontant();
                $this->arr_totaux['accurate']['actions_commerciales_repartition'][$month]['prive']+= $opportunite->getMontant();
                $this->arr_totaux['accurate']['actions_commerciales_repartition']['total']['prive']+= $opportunite->getMontant();
            }
      
            $this->arr_totaux['accurate']['actions_commerciales'][$month]+= $opportunite->getMontant();
            $this->arr_totaux['accurate']['actions_commerciales']['total']+= $opportunite->getMontant();
      
        }

        return $arr_details;

    }

    private function creerTableauCAMoisAccurate($year, $company){

        $opportuniteRepartitionRepo = $this->em->getRepository('App:CRM\OpportuniteRepartition');

        $settingsRepo = $this->em->getRepository('App:Settings');
        $arr_analytiques = $settingsRepo->findBy(array(
          'company' => $company,
          'parametre' => 'analytique',
          'module' => 'CRM'
        ));

        $arr_details = array();
        foreach($arr_analytiques as $analytique){
            $arr_details[$analytique->getValeur().'_public'] = array();
            $arr_details[$analytique->getValeur().'_public']['total'] = 0;

            $arr_details[$analytique->getValeur().'_prive'] = array();
            $arr_details[$analytique->getValeur().'_prive']['total'] = 0;
            for($i = 1; $i<=12; $i++){
                $arr_details[$analytique->getValeur().'_public'][$i]['val'] = 0;
                $arr_details[$analytique->getValeur().'_prive'][$i]['val'] = 0;
            }
            $arr_details[$analytique->getValeur().'_public']['opportunites'] = $this->creerTableauOpportunitesWonAccurate($year, $company, $analytique, 'PUBLIC');
            $arr_details[$analytique->getValeur().'_prive']['opportunites'] = $this->creerTableauOpportunitesWonAccurate($year, $company, $analytique, 'PRIVE');


        }

        $arr_details['frais_public'] = array();
        $arr_details['frais_public']['total'] = 0;

        $arr_details['frais_prive'] = array();
        $arr_details['frais_prive']['total'] = 0;

        for($i = 1; $i<=12; $i++){
            $arr_details['frais_public'][$i]['val'] = 0;
            $arr_details['frais_prive'][$i]['val'] = 0;
        }

        $this->arr_totaux['accurate']['ca_mois'] = array();
        $this->arr_totaux['accurate']['ca_mois_repartition'] = array();
        for($i = 1; $i<=12; $i++){
            $this->arr_totaux['accurate']['ca_mois'][$i] = 0;
            $this->arr_totaux['accurate']['ca_mois_repartition'][$i]['public'] = 0;
            $this->arr_totaux['accurate']['ca_mois_repartition'][$i]['prive'] = 0;
        }
        $this->arr_totaux['accurate']['ca_mois']['total'] = 0;
        $this->arr_totaux['accurate']['ca_mois_repartition']['total']['public'] = 0;
        $this->arr_totaux['accurate']['ca_mois_repartition']['total']['prive'] = 0;

        $arr_opportuniteRepartitions = $opportuniteRepartitionRepo->findForCompanyByYear($company, $year);
        foreach($arr_opportuniteRepartitions as $opportuniteRepartition){
            $month = $opportuniteRepartition->getDate()->format('n');

            if($opportuniteRepartition->getOpportunite()->isSecteurPublic()){
                $arr_details[$opportuniteRepartition->getOpportunite()->getAnalytique()->getValeur().'_public'][$month]['val']+= $opportuniteRepartition->getMontantMonetaire();
                $arr_details[$opportuniteRepartition->getOpportunite()->getAnalytique()->getValeur().'_public']['total']+= $opportuniteRepartition->getMontantMonetaire();
                $this->arr_totaux['accurate']['ca_mois_repartition'][$month]['public']+= $opportuniteRepartition->getMontantMonetaire();
                $this->arr_totaux['accurate']['ca_mois_repartition']['total']['public']+= $opportuniteRepartition->getMontantMonetaire();
            } else {
                $arr_details[$opportuniteRepartition->getOpportunite()->getAnalytique()->getValeur().'_prive'][$month]['val']+= $opportuniteRepartition->getMontantMonetaire();
                $arr_details[$opportuniteRepartition->getOpportunite()->getAnalytique()->getValeur().'_prive']['total']+= $opportuniteRepartition->getMontantMonetaire();
                $this->arr_totaux['accurate']['ca_mois_repartition'][$month]['prive']+= $opportuniteRepartition->getMontantMonetaire();
                $this->arr_totaux['accurate']['ca_mois_repartition']['total']['prive']+= $opportuniteRepartition->getMontantMonetaire();
            }

            $this->arr_totaux['accurate']['ca_mois'][$month]+= $opportuniteRepartition->getMontantMonetaire();
            $this->arr_totaux['accurate']['ca_mois']['total']+= $opportuniteRepartition->getMontantMonetaire();
        }

        $fraisRepo = $this->em->getRepository('App:CRM\Frais');
        $arr_frais = $fraisRepo->findForCompanyByYear($company, $year);
        foreach($arr_frais as $frais){
            $month = $frais->getDate()->format('n');

            if($frais->getActionCommerciale()->isSecteurPublic()){
                $arr_details['frais_public'][$month]['val']+= $frais->getMontantHT();
                $arr_details['frais_public']['total']+= $frais->getMontantHT();
                
                $this->arr_totaux['accurate']['ca_mois_repartition'][$month]['public']+= $frais->getMontantHT();
                $this->arr_totaux['accurate']['ca_mois_repartition']['total']['public']+= $frais->getMontantHT();
            } else {
                $arr_details['frais_prive'][$month]['val']+= $frais->getMontantHT();
                $arr_details['frais_prive']['total']+= $frais->getMontantHT();
                $this->arr_totaux['accurate']['ca_mois_repartition'][$month]['prive']+= $frais->getMontantHT();
                $this->arr_totaux['accurate']['ca_mois_repartition']['total']['prive']+= $frais->getMontantHT();
            }

            $this->arr_totaux['accurate']['ca_mois'][$month]+= $frais->getMontantHT();
            $this->arr_totaux['accurate']['ca_mois']['total']+= $frais->getMontantHT();
        }

        $recuRepo = $this->em->getRepository('App:NDF\Recu');
        $arr_recus = $recuRepo->findRefacturablesForCompanyByYear($company, $year);
        foreach($arr_recus as $recu){
            $month = $recu->getDate()->format('n');

            if($recu->getActionCommerciale()->isSecteurPublic()){
                $arr_details['frais_public'][$month]['val']+= $recu->getMontantHT();
                $arr_details['frais_public']['total']+= $recu->getMontantHT();
                
                $this->arr_totaux['accurate']['ca_mois_repartition'][$month]['public']+= $recu->getMontantHT();
                $this->arr_totaux['accurate']['ca_mois_repartition']['total']['public']+= $recu->getMontantHT();
            } else {
                $arr_details['frais_prive'][$month]['val']+= $recu->getMontantHT();
                $arr_details['frais_prive']['total']+= $recu->getMontantHT();
                $this->arr_totaux['accurate']['ca_mois_repartition'][$month]['prive']+= $recu->getMontantHT();
                $this->arr_totaux['accurate']['ca_mois_repartition']['total']['prive']+= $recu->getMontantHT();
            }

            $this->arr_totaux['accurate']['ca_mois'][$month]+= $recu->getMontantHT();
            $this->arr_totaux['accurate']['ca_mois']['total']+= $recu->getMontantHT();
        }

        $sousTraitanceRepartitionRepo = $this->em->getRepository('App:CRM\SousTraitanceRepartition');
        $arr_sousTraitancesRepartitions = $sousTraitanceRepartitionRepo->findForCompanyByYearHavingFrais($company, $year);
        foreach($arr_sousTraitancesRepartitions as $sousTraitanceRepartition){
            $month = $sousTraitanceRepartition->getDate()->format('n');

            if($sousTraitanceRepartition->getOpportuniteSousTraitance()->getOpportunite()->isSecteurPublic()){
                $arr_details['frais_public'][$month]['val']+= $sousTraitanceRepartition->getFraisMonetaire();
                $arr_details['frais_public']['total']+= $sousTraitanceRepartition->getFraisMonetaire();
                
                $this->arr_totaux['accurate']['ca_mois_repartition'][$month]['public']+= $sousTraitanceRepartition->getFraisMonetaire();
                $this->arr_totaux['accurate']['ca_mois_repartition']['total']['public']+= $sousTraitanceRepartition->getFraisMonetaire();
            } else {
                $arr_details['frais_prive'][$month]['val']+= $sousTraitanceRepartition->getFraisMonetaire();
                $arr_details['frais_prive']['total']+= $sousTraitanceRepartition->getFraisMonetaire();
                $this->arr_totaux['accurate']['ca_mois_repartition'][$month]['prive']+= $sousTraitanceRepartition->getFraisMonetaire();
                $this->arr_totaux['accurate']['ca_mois_repartition']['total']['prive']+= $sousTraitanceRepartition->getFraisMonetaire();
            }

            $this->arr_totaux['accurate']['ca_mois'][$month]+= $sousTraitanceRepartition->getFraisMonetaire();
            $this->arr_totaux['accurate']['ca_mois']['total']+= $sousTraitanceRepartition->getFraisMonetaire();
        }

        return $arr_details;

    }

  private function creerTableauCoutsMarginauxAccurate($year, $company){

    $depensesRepo = $this->em->getRepository('App:Compta\Depense');
    $sousTraitanceRepo = $this->em->getRepository('App:CRM\OpportuniteSousTraitance');
   
    $arr_depenses = $depensesRepo->findForCompanyByYear($company, $year);

    $arr_details = array();
    foreach($this->arr_couts_marginaux as $cout => $label){
      $arr_details[$cout] = array();
      for($i = 1; $i<=12; $i++){
        $arr_details[$cout][$i]['val'] = 0;
        $arr_details[$cout][$i]['details'] = array('lignes_depenses');
      }
       $arr_details[$cout]['total'] = 0;
    }

    

    $this->arr_totaux['accurate']['couts_marginaux'] = array();
    $this->arr_totaux['accurate']['couts_marginaux']['total'] = 0;
    for($i = 1; $i<=12; $i++){
      $this->arr_totaux['accurate']['couts_marginaux'][$i] = 0;
    }

    foreach($arr_depenses as $depense){

      $month = $depense->getDate()->format('n');

      if( count($depense->getSousTraitances()) ){
        continue;
      }

      foreach($depense->getLignes() as $ligne){

       if( $this->utilsService->startsWith($ligne->getCompteComptable(), '6251') ||
         ( $this->ccAutoroute && $ligne->getCompteComptable()->getId() == $this->ccAutoroute->getId() )  ||
         ( $this->ccCarburant && $ligne->getCompteComptable()->getId() == $this->ccCarburant->getId() )
       ){
           $arr_details['couts_deplacements'][$month]['val']+= $ligne->getMontant();
           $arr_details['couts_deplacements'][$month]['details']['lignes_depenses'][$ligne->getId()] = $ligne;

           $this->arr_totaux['accurate']['couts_marginaux'][$month]+= $ligne->getMontant();
           $arr_details['couts_deplacements']['total']+= $ligne->getMontant();

        } else if( $this->ccResaSalle && $ligne->getCompteComptable()->getId() == $this->ccResaSalle->getId() ){

           $arr_details['reservation_salles'][$month]['val']+= $ligne->getMontant();
           $arr_details['reservation_salles'][$month]['details']['lignes_depenses'][$ligne->getId()] = $ligne;

           $this->arr_totaux['accurate']['couts_marginaux'][$month]+= $ligne->getMontant();
           $arr_details['reservation_salles']['total']+= $ligne->getMontant();
        }
      }

    }

    $arr_details['sous_traitance']['sous_traitances'] = $this->creerTableauOpportunitesSousTraitanceAccurate($year, $company);
    foreach($arr_details['sous_traitance']['sous_traitances'] as $sousTraitance){
      foreach($sousTraitance->getRepartitions() as $repartition){
        $month = $repartition->getDate()->format('n');
        $yearSousTraitance = $repartition->getDate()->format('Y');
        if($yearSousTraitance == $year ){
            $arr_details['sous_traitance'][$month]['val']+= $repartition->getMontantMonetaire();
            $this->arr_totaux['accurate']['couts_marginaux'][$month]+= $repartition->getMontantMonetaire();
            $arr_details['sous_traitance']['total']+= $repartition->getMontantMonetaire();
        }
      }
    }

    for($month = 1; $month <= 12 ; $month++){
      $this->arr_totaux['accurate']['couts_marginaux']['total']+=$this->arr_totaux['accurate']['couts_marginaux'][$month];
    }

    return $arr_details;

  }

  private function creerTableauCoutsExploitationAccurate($year, $company){

     $depensesRepo = $this->em->getRepository('App:Compta\Depense');
     $sousTraitanceRepo = $this->em->getRepository('App:CRM\OpportuniteSousTraitance');
     $compteComptableRepo = $this->em->getRepository('App:Compta\CompteComptable');

    $arr_details = array();
    foreach($this->arr_couts_exploitation as $cout => $label){
      $arr_details[$cout] = array();
      for($i = 1; $i<=12; $i++){
        $arr_details[$cout][$i]['val'] = 0;
        $arr_details[$cout][$i]['details'] = array(
          'depenses' => array(),
          'lignes_depenses' => array(),
          'affectations_diverses' => array()
        );
        $arr_details[$cout]['total']= 0;
      }
    }

    $this->arr_totaux['accurate']['couts_exploitation'] = array();
    $this->arr_totaux['accurate']['couts_exploitation']['total'] = 0;
    for($i = 1; $i<=12; $i++){
      $this->arr_totaux['accurate']['couts_exploitation'][$i] = 0;
    }
   
    $arr_depenses = $depensesRepo->findForCompanyByYear($company, $year);

    foreach($arr_depenses as $depense){

      $month = $depense->getDate()->format('n');

      //on ne prend pas les dépenses correspondant à de la sous-traitance car elles vont dans les coûts marginaux
      if( count($depense->getSousTraitances()) ){
        continue;
      }

      foreach($depense->getLignes() as $ligne){
          $arr_details = $this->repartitionCoutsExploitationParPoste(
            $arr_details,
            $ligne->getCompteComptable(),
            $month,
            $ligne->getMontant(),
            $depense,
            null,
            $ligne
          );
      }
    }

    //récupérer le montant des charges de mars de l'année +1 pour les lisser sur les mois d'octobre, novembre et décembre
   /* $arr_depenses_next_year = $depensesRepo->findForCompanyByYear($company, $year+1);
    foreach($arr_depenses_next_year as $depense){

      $month = $depense->getDate()->format('n');

      //mois de janvier : répartir sur octobre, novembre et décembre
       if($depense->getDate()->format('n') <= 3){
         foreach($depense->getLignes() as $ligne){

           if( $this->utilsService->startsWith($ligne->getCompteComptable()->getNum(), '645') ||
             ( $this->ccUrssaf && $ligne->getCompteComptable()->getId() == $this->ccUrssaf->getId() ) ||
             ( $this->ccMalakoffMederic && $ligne->getCompteComptable()->getId() == $this->ccMalakoffMederic->getId() )
           ) {
             $montant = $ligne->getMontant();

             $tiers = $montant / 3;

             $arr_details['charges'][12]['val']+= $tiers;
             $this->arr_totaux['accurate']['couts_exploitation'][12]+= $tiers;
             $arr_details['charges']['total']+= $tiers;

             if($month <= 2){
               $arr_details['charges'][11]['val']+= $tiers;
               $this->arr_totaux['accurate']['couts_exploitation'][11]+= $tiers;
               $arr_details['charges']['total']+= $tiers;
             }

            if($month == 1){
              $arr_details['charges'][10]['val']+= $tiers;
              $this->arr_totaux['accurate']['couts_exploitation'][10]+= $tiers;
              $arr_details['charges']['total']+= $tiers;
            }
           }
         }
       }
     }*/

    $rapprochementRepo = $this->em->getRepository('App:Compta\Rapprochement');
    $arr_rapprochements = $rapprochementRepo->findTableauBord($company, $year);

    foreach($arr_rapprochements as $rapprochement){
      $month = $rapprochement->getMouvementBancaire()->getDate()->format('n');

      if($rapprochement->getAffectationDiverse()){

        $compteComptable = $rapprochement->getAffectationDiverse()->getCompteComptable();

        if( $this->utilsService->startsWith($compteComptable->getNum(), '6') || $this->utilsService->startsWith($compteComptable->getNum(), '4') ) {
            $arr_details = $this->repartitionCoutsExploitationParPoste(
              $arr_details,
              $compteComptable,
              $month,
              -$rapprochement->getMouvementBancaire()->getMontant(),
              null,
              $rapprochement,
              null
            );
        }

      }
    }

    //récupérer le montant des charges de mars de l'année +1 pour les lisser sur les mois d'octobre, novembre et décembre
   /* $arr_rapprochements_next_year = $rapprochementRepo->findTableauBord($company, $year+1);
    foreach($arr_rapprochements_next_year as $rapprochement){
      $month = $rapprochement->getMouvementBancaire()->getDate()->format('n');

      if($rapprochement->getAffectationDiverse() && $month <=3){
        $cc = $rapprochement->getAffectationDiverse()->getCompteComptable();

        if(  $this->utilsService->startsWith($cc->getNum(), '645') ||
          ( $this->ccUrssaf && $cc->getId() == $this->ccUrssaf->getId() ) ||
          ( $this->ccMalakoffMederic && $cc->getId() == $this->ccMalakoffMederic->getId() )
        ) {
          $montant = -$rapprochement->getMouvementBancaire()->getMontant();

          $tiers = $montant / 3;

          $arr_details['charges'][12]['val']+= $tiers;
          $this->arr_totaux['accurate']['couts_exploitation'][12]+= $tiers;
          $arr_details['charges']['total']+= $tiers;

          if($month <= 2){
            $arr_details['charges'][11]['val']+= $tiers;
            $this->arr_totaux['accurate']['couts_exploitation'][11]+= $tiers;
            $arr_details['charges']['total']+= $tiers;
          }

          if($month == 1){
            $arr_details['charges'][10]['val']+= $tiers;
            $this->arr_totaux['accurate']['couts_exploitation'][10]+= $tiers;
            $arr_details['charges']['total']+= $tiers;
          }

        }

      }
    }*/

    $prevRepo = $this->em->getRepository('App:Compta\PrevTableauBord');
    // ajustement tva
    $postesCopiePrevisionnel = array(
      'ajustement_tva',
      'variation_cp',
      'taxes',
      'loyers',
      'salaires_nets',
      'stages',
      'vie',
      'charges'
    );

    for($mois = 1; $mois<=12; $mois++){
      foreach($postesCopiePrevisionnel as $poste){

        $prev = $prevRepo->findOneBy(array(
          'annee' => $year,
          'mois' => $mois,
          'poste' => $poste,
          'company' => $company,
          'analytique' => null
        ));

        if($prev){
          $arr_details[$poste][$mois]['val'] = $prev->getMontantMonetaire();
          $this->arr_totaux['accurate']['couts_exploitation'][$mois]+= $prev->getMontantMonetaire();
          $arr_details[$poste]['total']+= $prev->getMontantMonetaire();
        } else {
          $arr_details[$poste][$mois]['val'] = 0;
        }

      }
    }

    for($mois = 1; $mois<=12; $mois++){
       $this->arr_totaux['accurate']['couts_exploitation']['total']+= $this->arr_totaux['accurate']['couts_exploitation'][$mois];
    }


    return $arr_details;

  }

  private function creerTableauDotationAmortissementsAccurate($year, $company){

    $this->arr_totaux['accurate']['dotation_amortissements'] = array();
    for($i = 1; $i<=12; $i++){
      $this->arr_totaux['accurate']['dotation_amortissements'][$i] = 0;
    }
    $this->arr_totaux['accurate']['dotation_amortissements']['total'] = 0;

    $tableauPrevisonnel = $this->creerTableauDotationAmortissementsPrevisionnel($year, $company);
    $tableauAccurate= array();

    foreach($tableauPrevisonnel['dotation_amortissements'] as $mois => $amortissement){
      $tableauAccurate['dotation_amortissements'][$mois]['val'] = $amortissement;
      $this->arr_totaux['accurate']['dotation_amortissements'][$mois] += $amortissement;
      $this->arr_totaux['accurate']['dotation_amortissements']['total']+= $amortissement;
    }

    return $tableauAccurate;
  }

  private function creerTableauOpportunitesWonAccurate($year, $company, $analytique, $publicOrPrive){

    $opportuniteRepartitionRepo = $this->em->getRepository('App:CRM\OpportuniteRepartition');
    $arr_opportuniteRepartitions = $opportuniteRepartitionRepo->findForCompanyByYearAndAnalytiqueAndPublicOrPrive($company, $year, $analytique, $publicOrPrive);

    $arr_opportunites = array();
    foreach($arr_opportuniteRepartitions as $opportuniteRepartition){
      if(!array_key_exists($opportuniteRepartition->getOpportunite()->getId(), $arr_opportunites)){
        $arr_opportunites[$opportuniteRepartition->getOpportunite()->getId()] = $opportuniteRepartition->getOpportunite();
      }
    }

    return $arr_opportunites;
  }

  private function creerTableauOpportunitesSousTraitanceAccurate($year, $company){

    $sousTraitanceRepartitionRepository = $this->em->getRepository('App:CRM\SousTraitanceRepartition');
    $arr_repartitions = $sousTraitanceRepartitionRepository->findForCompanyByYear($company, $year);

    $arr_sous_traitances = array();
    foreach($arr_repartitions as $repartition){
      if(!array_key_exists($repartition->getOpportuniteSousTraitance()->getId(), $arr_sous_traitances)){
        $arr_sous_traitances[$repartition->getOpportuniteSousTraitance()->getId()] = $repartition->getOpportuniteSousTraitance();
      }
    }

    uasort($arr_sous_traitances, array($this, 'sortArrayAlpha'));
    return $arr_sous_traitances;
  }

  private function repartitionCoutsExploitationParPoste($arr_details, $compteComptable, $month, $montant, $depense = null, $rapprochement = null, $ligneDepense=null){


    if( $this->utilsService->startsWith($compteComptable->getNum(), '623') ||
        $this->utilsService->startsWith($compteComptable->getNum(), '626') ){
        // Coûts communication et impression
        $arr_details['communication_impression'][$month]['val']+= $montant;
        $this->arr_totaux['accurate']['couts_exploitation'][$month]+= $montant;
        $arr_details['communication_impression']['total']+= $montant;


        if($depense){
          $arr_details['communication_impression'][$month]['details']['depenses'][$depense->getId()] = $depense;
          $arr_details['communication_impression'][$month]['details']['lignes_depenses'][$ligneDepense->getId()] = $ligneDepense;
        }
        if($rapprochement){
          $arr_details['communication_impression'][$month]['details']['affectation_diverses'][$rapprochement->getId()] = $rapprochement;
        }

     } else if ( $this->utilsService->startsWith($compteComptable->getNum(), '6132') ){
         // Loyers
        // on utilise le previsionnel
         /*$arr_details['loyers'][$month]['val']+= $montant;
         $this->arr_totaux['accurate']['couts_exploitation'][$month]+= $montant;

         if($depense){
           $arr_details['loyers'][$month]['details']['depenses'][$depense->getId()] = $depense;
         }
         if($rapprochement){
           $arr_details['loyers'][$month]['details']['affectation_diverses'][$rapprochement->getId()] = $rapprochement;
         }*/

     } else if ( $this->utilsService->startsWith($compteComptable->getNum(), '6064') ||
                 $this->utilsService->startsWith($compteComptable->getNum(), '6068') ){
         // Petit équipement et fournitures
         $arr_details['petit_equipement_fournitures'][$month]['val']+= $montant;
         $this->arr_totaux['accurate']['couts_exploitation'][$month]+= $montant;
        $arr_details['petit_equipement_fournitures']['total']+= $montant;

         if($depense){
           $arr_details['petit_equipement_fournitures'][$month]['details']['depenses'][$depense->getId()] = $depense;
           $arr_details['petit_equipement_fournitures'][$month]['details']['lignes_depenses'][$ligneDepense->getId()] = $ligneDepense;
         }
         if($rapprochement){
           $arr_details['petit_equipement_fournitures'][$month]['details']['affectation_diverses'][$rapprochement->getId()] = $rapprochement;
         }

     }  else if ( $this->utilsService->startsWith($compteComptable->getNum(), '627') ||
                  $this->utilsService->startsWith($compteComptable->getNum(), '6616') ||
                  $this->utilsService->startsWith($compteComptable->getNum(), '6611') ){
         // Coûts bancaires
         $arr_details['couts_bancaires'][$month]['val']+= $montant;
         $this->arr_totaux['accurate']['couts_exploitation'][$month]+= $montant;
        $arr_details['couts_bancaires']['total']+= $montant;

         if($depense){
           $arr_details['couts_bancaires'][$month]['details']['depenses'][$depense->getId()] = $depense;
           $arr_details['couts_bancaires'][$month]['details']['lignes_depenses'][$ligneDepense->getId()] = $ligneDepense;
         }
         if($rapprochement){
           $arr_details['couts_bancaires'][$month]['details']['affectation_diverses'][$rapprochement->getId()] = $rapprochement;
         }

     } else if ( $this->utilsService->startsWith($compteComptable->getNum(), '6333') ||
          $this->utilsService->startsWith($compteComptable->getNum(), '6228') ||
          ($this->ccFormationPersonnel && $this->ccFormationPersonnel->getId() == $compteComptable->getId())
      ){
         // Formation du personnel
         $arr_details['formation_personnel'][$month]['val']+= $montant;
         $this->arr_totaux['accurate']['couts_exploitation'][$month]+= $montant;
         $arr_details['formation_personnel']['total']+= $montant;

         if($depense){
           $arr_details['formation_personnel'][$month]['details']['depenses'][$depense->getId()] = $depense;
           $arr_details['formation_personnel'][$month]['details']['lignes_depenses'][$ligneDepense->getId()] = $ligneDepense;
         }
         if($rapprochement){
           $arr_details['formation_personnel'][$month]['details']['affectation_diverses'][$rapprochement->getId()] = $rapprochement;
         
         }
        
         

     } else if ( $this->utilsService->startsWith($compteComptable->getNum(), '622626') ){
       // V.I.E.
      /* $arr_details['vie'][$month]['val']+= $montant;
       $this->arr_totaux['accurate']['couts_exploitation'][$month]+= $montant;

       if($depense){
         $arr_details['vie'][$month]['details']['depenses'][$depense->getId()] = $depense;
       }
       if($rapprochement){
         $arr_details['vie'][$month]['details']['affectation_diverses'][$rapprochement->getId()] = $rapprochement;
       }*/

     } else if ( $this->utilsService->startsWith($compteComptable->getNum(), '6226') ){
         // Comptable et autres coûts administratifs
         $arr_details['comptable_admin'][$month]['val']+= $montant;
         $this->arr_totaux['accurate']['couts_exploitation'][$month]+= $montant;
         $arr_details['comptable_admin']['total']+= $montant;


         if($depense){
           $arr_details['comptable_admin'][$month]['details']['depenses'][$depense->getId()] = $depense;
           $arr_details['comptable_admin'][$month]['details']['lignes_depenses'][$ligneDepense->getId()] = $ligneDepense;
         }
         if($rapprochement){
           $arr_details['comptable_admin'][$month]['details']['affectation_diverses'][$rapprochement->getId()] = $rapprochement;
         }

     } else if ( $this->utilsService->startsWith($compteComptable->getNum(), '6238') ||
                 $this->utilsService->startsWith($compteComptable->getNum(), '6713') ){
         // Dons
         $arr_details['dons'][$month]['val']+= $montant;
         $this->arr_totaux['accurate']['couts_exploitation'][$month]+= $montant;
         $arr_details['dons']['total']+= $montant;

         if($depense){
           $arr_details['dons'][$month]['details']['depenses'][$depense->getId()] = $depense;
           $arr_details['dons'][$month]['details']['lignes_depenses'][$ligneDepense->getId()] = $ligneDepense;
         }
         if($rapprochement){
           $arr_details['dons'][$month]['details']['affectation_diverses'][$rapprochement->getId()] = $rapprochement;
         }

     } else if ( $this->ccStages && $compteComptable->getId() == $this->ccStages->getId() ){
         // Gratifications de stages
        // on utilise le previsionnel
         /*$arr_details['stages'][$month]['val']+= $montant;
         $this->arr_totaux['accurate']['couts_exploitation'][$month]+= $montant;

         if($depense){
           $arr_details['stages'][$month]['details']['depenses'][$depense->getId()] = $depense;
         }
         if($rapprochement){
           $arr_details['stages'][$month]['details']['affectation_diverses'][$rapprochement->getId()] = $rapprochement;
         }*/

     } else if ( $this->utilsService->startsWith($compteComptable->getNum(), '6410') ||
                 $this->utilsService->startsWith($compteComptable->getNum(), '421') ){
         // Salaires nets
        // on utilise le previsionnel
        /* $arr_details['salaires_nets'][$month]['val']+= $montant;
         $this->arr_totaux['accurate']['couts_exploitation'][$month]+= $montant;

         if($depense){
           $arr_details['salaires_nets'][$month]['details']['depenses'][$depense->getId()] = $depense;
         }
         if($rapprochement){
           $arr_details['salaires_nets'][$month]['details']['affectation_diverses'][$rapprochement->getId()] = $rapprochement;
         }*/

     } else if ( 
                 $this->utilsService->startsWith($compteComptable->getNum(), '6251') ||
                 ( $this->ccAutoroute && $compteComptable->getId() == $this->ccAutoroute->getId() )  ||
                 ( $this->ccCarburant && $compteComptable->getId() == $this->ccCarburant->getId() ) ||
                 $this->ccResaSalle && $compteComptable->getId() == $this->ccResaSalle->getId()
     ){
        //coûts marginaux, ne pas les re-compter une seconde fois
      
     } else if(  $this->utilsService->startsWith($compteComptable->getNum(), '645') ||
       ( $this->ccUrssaf && $compteComptable->getId() == $this->ccUrssaf->getId() ) ||
       ( $this->ccMalakoffMederic && $compteComptable->getId() == $this->ccMalakoffMederic->getId() ) ||
       ( $this->utilsService->startsWith($compteComptable->getNum(), '431') )
     ) {

      // $arr_details['charges']['total']+= $montant;

      //  //charges à lisser sur les 3 mois précédents
      //  $tiers = round($montant/3);


      //  if($month-3 > 0){
      //    $arr_details['charges'][$month-3]['val']+= $tiers;
      //    $this->arr_totaux['accurate']['couts_exploitation'][$month-3]+= $tiers;
      //    $arr_details['charges']['total']+= $montant;

      //   if($depense){
      //     $arr_details['charges'][$month-3]['details']['lissees']['depenses'][$depense->getId()] = $depense;
      //   }
      //   if($rapprochement){
      //     $arr_details['charges'][$month-3]['details']['lissees']['affectation_diverses'][$rapprochement->getId()] = $rapprochement;
      //   }
      //  }
      //  if($month-2 > 0){
      //    $arr_details['charges'][$month-2]['val']+= $tiers;
      //    $this->arr_totaux['accurate']['couts_exploitation'][$month-2]+= $tiers;

      //    if($depense){
      //      $arr_details['charges'][$month-2]['details']['lissees']['depenses'][$depense->getId()] = $depense;
      //    }
      //    if($rapprochement){
      //      $arr_details['charges'][$month-2]['details']['lissees']['affectation_diverses'][$rapprochement->getId()] = $rapprochement;
      //    }
      //  }
      //  if($month-1 > 0){
      //    $arr_details['charges'][$month-1]['val']+= $tiers;
      //    $this->arr_totaux['accurate']['couts_exploitation'][$month-1]+= $tiers;

      //    if($depense){
      //      $arr_details['charges'][$month-1]['details']['lissees']['depenses'][$depense->getId()] = $depense;
      //    }
      //    if($rapprochement){
      //      $arr_details['charges'][$month-1]['details']['lissees']['affectation_diverses'][$rapprochement->getId()] = $rapprochement;
      //    }

      //  }

     } else if(  $this->utilsService->startsWith($compteComptable->getNum(), '64') ||
                 $this->utilsService->startsWith($compteComptable->getNum(), '6061') ) {

        // //charges
        // $arr_details['charges'][$month]['val']+= $montant;
        // $this->arr_totaux['accurate']['couts_exploitation'][$month]+= $montant;
        // $arr_details['charges']['total']+= $montant;

        // if($depense){
        //   $arr_details['charges'][$month]['details']['depenses'][$depense->getId()] = $depense;
        // }
        // if($rapprochement){
        //   $arr_details['charges'][$month]['details']['affectation_diverses'][$rapprochement->getId()] = $rapprochement;
        // }
      } else {

        //autres coûts

        //ne pas mettre les comptes 4 dans autres coûts, uniquement les comptes 6
        //ne psa prendre 60410500 qui correpond à la sous-traitance FIPHFP
        if ( $this->utilsService->startsWith($compteComptable->getNum(), '6') && $compteComptable->getNum() != '60410500' ) {
          $arr_details['autres_couts'][$month]['val']+= $montant;
          $this->arr_totaux['accurate']['couts_exploitation'][$month]+= $montant;
          $arr_details['autres_couts']['total']+= $montant;

          if($depense){
            $arr_details['autres_couts'][$month]['details']['depenses'][$depense->getId()] = $depense;
            $arr_details['autres_couts'][$month]['details']['lignes_depenses'][$ligneDepense->getId()] = $ligneDepense;
          }
          if($rapprochement){
            $arr_details['autres_couts'][$month]['details']['affectation_diverses'][$rapprochement->getId()] = $rapprochement;
          }

       }
       
     }

     return $arr_details;
  }

  public function ajouterPrevisionnel($valeur, $annee, $mois, $poste, $analytiqueVal, $priveOrPublic, $company){

    $analytique = null;

    if($analytiqueVal !== 0 && 'frais' != $analytiqueVal){
      $settingsRepo = $this->em->getRepository('App:Settings');
      $analytique = $settingsRepo->findOneBy(array(
        'company' => $company,
        'parametre' => 'analytique',
        'valeur' => $analytiqueVal,
      ));
    }

    $frais = 0;
    if('frais' == $analytiqueVal){
        $frais = 1;
    }

    $arr_paramsQuery = array(
        'annee' => $annee,
        'mois' => $mois,
        'poste' => $poste,
        'company' => $company,
        'frais' => $frais
    );

    if(null !== $priveOrPublic){
        $arr_paramsQuery['priveOrPublic'] = $priveOrPublic;
    }

    if($analytique){
        $arr_paramsQuery['analytique'] = $analytique;
    }

    $prevRepo = $this->em->getRepository('App:Compta\PrevTableauBord');
    $prev = $prevRepo->findOneBy($arr_paramsQuery);

    if(!$prev){
      $prev = new PrevTableauBord();
      $prev->setAnnee($annee);
      $prev->setMois($mois);
      $prev->setPoste($poste);
      $prev->setCompany($company);
      $prev->setFrais($frais);

      if($analytique){
        $prev->setAnalytique($analytique);
      }
      
      if(null !== $priveOrPublic){
        $prev->setPriveOrPublic(strtoupper($priveOrPublic));
      }
      
    }

    $prev->setMontantMonetaire($valeur);
    $this->em->persist($prev);
    $this->em->flush();

    return $prev;

  }

  private function calculMargeBrute($type){

    for($mois=1; $mois<=12; $mois++){
        $this->arr_totaux[$type]['marge_brute'][$mois] = $this->arr_totaux[$type]['ca_mois'][$mois] - $this->arr_totaux[$type]['couts_marginaux'][$mois];
    }

  }

    private function calculResultatExploitation($type, $year){
    
        $cumul = 0;
        $moisDebutPredictif = $this->getMoisDebutPredictif();
        $currentYear = date('Y');

        for($mois=1; $mois<=12; $mois++){

            if($type == "predictif" && $mois < $moisDebutPredictif && $year == $currentYear ){
                $resultat = $this->arr_totaux['accurate']['marge_brute'][$mois] - $this->arr_totaux['accurate']['couts_exploitation'][$mois] - $this->arr_totaux['accurate']['dotation_amortissements'][$mois];
            } else {
                $resultat = $this->arr_totaux[$type]['marge_brute'][$mois] - $this->arr_totaux[$type]['couts_exploitation'][$mois] - $this->arr_totaux[$type]['dotation_amortissements'][$mois];
            }

            $cumul+=$resultat;

            $this->arr_totaux[$type]['resultat_exploitation'][$mois] = $resultat;
            $this->arr_totaux[$type]['resultat_exploitation_cumule'][$mois] = $cumul;
        }

    }

  private function calculRatioResultatCA($type){

    for($mois=1; $mois<=12; $mois++){
      $ratio = 'N/A';
      if($this->arr_totaux[$type]['ca_mois'][$mois] != 0){
        $ratio =  $this->arr_totaux[$type]['resultat_exploitation'][$mois] / $this->arr_totaux[$type]['ca_mois'][$mois] * 100;
        }
       $this->arr_totaux[$type]['ratio_resultat_ca'][$mois] = $ratio;
    }

  }

  public function calculTotaux($annee, $company){
    $this->_initCoutsParCompteComptable($company);

    $arr_prev = $this->creerTableauPrevisionnel($annee, $company);
    return $this->arr_totaux['prev'];
  }

  public function displayDetails($annee, $mois, $company, $poste, $sousPoste){
    $this->_initCoutsParCompteComptable($company);

    $arr_accurate = $this->creerTableauAccurate($annee, $company);
    return $arr_accurate[$poste][$sousPoste][$mois]['details'];
  }

    /**
     * @upgrade-notes This method cannot work
     * @param $company
     * @return mixed
     */
  public function exportImportPrevisionnelExcel($company){
    $path = __DIR__.'/../../../../public/files/compta/tableau_bord/';
	$fileName = 'fichier_import_tableau_bord_previsionnel.xlsx';

    $objPHPExcel = $this->phpExcel->createPHPExcelObject($path.$fileName); // @todo replace phpexcel
    $tableauPrevisonnel = $this->creerTableauPrevisionnel(date('Y'), $company);

    //actions commerciales
    $row = 3;
    $col = 'B';
    foreach($tableauPrevisonnel as $arr_poste){
      foreach($arr_poste as $arr_sousPoste ){

        for($mois = 1; $mois <= 12; $mois++){
          $montant = $arr_sousPoste[$mois];
          $objPHPExcel->getActiveSheet ()->setCellValue ($col.$row, $montant);
          $col++;
        }

        //next line
        $row++;
        $col = 'B';
      }
      $row++;
    }

    return $objPHPExcel;

  }

  public function importPrevisionnelExcel($company, $objPHPExcel, $year){

    $sheet = $objPHPExcel->getActiveSheet();
    $tableauPrevisonnel = $this->creerTableauPrevisionnel($year, $company);

    $row = 3;
    $col = 1;
    foreach($tableauPrevisonnel as $nomPoste => $arr_poste){
      foreach($arr_poste as $nomSousPostePoste => $arr_sousPoste ){

        for($mois = 1; $mois <= 12; $mois++){

          $montant = $sheet->getCellByColumnAndRow($col, $row)->getCalculatedValue();

          if($nomPoste == 'actions_commerciales' || $nomPoste == 'ca_mois'){

            $arr_sous_poste = explode('_', $nomSousPostePoste);
            $analytique = "";
            $priveOrPublic = $arr_sous_poste[count($arr_sous_poste)-1]; 
            for($i = 0; $i<count($arr_sous_poste)-1; $i++){
                $analytique.=$arr_sous_poste[$i];
            }

            $this->ajouterPrevisionnel($montant, $year, $mois, $nomPoste, $analytique, $priveOrPublic, $company);
          } else {
            $this->ajouterPrevisionnel($montant, $year, $mois, $nomSousPostePoste, null, null, $company);
          }
          $col++;

        }

        //next line
        $row++;
        $col = 1;
      }
      $row++;
    }

  }

  private function sortArrayAlpha($a, $b){
      if ($a->getOpportunite()->getCompte()->getNom() == $b->getOpportunite()->getCompte()->getNom()) {
        return 0;
    }
    return ($a->getOpportunite()->getCompte()->getNom() < $b->getOpportunite()->getCompte()->getNom()) ? -1 : 1;
  }

  public function getMoisDebutPredictif($format = "m"){
    //le predictif commence au mois en cours, sauf si on est avant le 3 du mois (il commence alors au mois précédent)
    $moisDebutPredictif = date($format);
    
    if( date('d') <= 3 ){
      $moisDebutPredictif = $moisDebutPredictif-1;
    }

    return $moisDebutPredictif;
  }


}
