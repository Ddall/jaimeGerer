<?php

namespace App\Service\TimeTracker;


use Doctrine\ORM\EntityManagerInterface;

class TimeTrackerService {

    protected $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function getDataChartTempsParMois($company, $year){

        $tempsRepo = $this->em->getRepository('App:TimeTracker\Temps');
       
        $arr_data = array();

        for($i = 1; $i<=12; $i++){
            $arr_data[$i] = 0;
        }

        $arr_temps = $tempsRepo->findForCompanyByYear($company, $year);
       
        foreach($arr_temps as $temps){
            $arr_data[$temps->getDate()->format('n')]+= $temps->getDuree();
        }
        
        return $arr_data;
    }

    public function getDataChartTempsParAnnee($company){

        $activationRepo = $this->em->getRepository('App:SettingsActivationOutil');
        $activation = $activationRepo->findOneBy(array(
            'company' => $company,
            'outil' => 'CRM'
        ));
        $yearActivation = $activation->getDate()->format('Y');
        $currentYear = date('Y');

        $tempsRepo = $this->em->getRepository('App:TimeTracker\Temps');
       
        $arr_data = array();

        for($i = $yearActivation; $i<=$currentYear; $i++){
            $arr_data[$i] = 0;
            $arr_temps = $tempsRepo->findForCompanyByYear($company, $i);
            foreach($arr_temps as $temps){
                $arr_data[$i]+= $temps->getDuree();
            }
        }
        
        return $arr_data;
    }

}
