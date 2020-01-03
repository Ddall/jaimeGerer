<?php

namespace App\Service;

use App\Entity\Settings;
use Doctrine\ORM\EntityManagerInterface;

class NumService {

    protected $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function getNextNum($type, $company){

        $settingsRepository = $this->em->getRepository('App:Settings');

        switch($type){
            case 'DEPENSE':

                //find num
                $settingsNum = $settingsRepository->findOneBy(array(
                    'module' => 'COMPTA',
                    'parametre' => 'NUMERO_DEPENSE',
                    'company'=> $company
                ));

                //initialize num if it doens't exist
                if(is_null($settingsNum) || (is_countable($settingsNum) && count($settingsNum) == 0)) {
                    $settingsNum = new Settings();
                    $settingsNum->setModule('COMPTA');
                    $settingsNum->setParametre('NUMERO_DEPENSE');
                    $settingsNum->setHelpText('Le numéro de dépense courant - ne pas modifier si vous n\'êtes pas sûr de ce que vous faites !');
                    $settingsNum->setTitre('Numéro de dépense');
                    $settingsNum->setType('NUM');
                    $settingsNum->setNoTVA(false);
                    $settingsNum->setCategorie('DEPENSE');
                    $settingsNum->setCompany($company);
                    $settingsNum->setValeur(1);
                }

                //find prefix
                $prefixe = 'D-'.date('Y').'-';
                break;
        }

        $currentNum = $settingsNum->getValeur();


        if($currentNum < 10){
            $prefixe.='00';
        } else if ($currentNum < 100){
            $prefixe.='0';
        }

        $arr_num = array(
            'prefixe' => $prefixe,
            'num' => $currentNum
        );

        return $arr_num;
    }


    public function getDepenseNum($company, $depense){

        $settingsRepository = $this->em->getRepository('App:Settings');

        $settingsNum = $settingsRepository->findOneBy(array(
            'module' => 'COMPTA',
            'parametre' => 'NUMERO_DEPENSE',
            'company'=> $company
        ));

        if(is_null($settingsNum) || (is_countable($settingsNum) && count($settingsNum) == 0)) {
            $settingsNum = new Settings();
            $settingsNum->setModule('COMPTA');
            $settingsNum->setParametre('NUMERO_DEPENSE');
            $settingsNum->setHelpText('Le numéro de dépense courant - ne pas modifier si vous n\'êtes pas sûr de ce que vous faites !');
            $settingsNum->setTitre('Numéro de dépense');
            $settingsNum->setType('NUM');
            $settingsNum->setNoTVA(false);
            $settingsNum->setCategorie('DEPENSE');
            $settingsNum->setCompany($company);
            $currentNum = 1;
        } else {
            $currentNum = $settingsNum->getValeur();
        }

        $depenseYear = $depense->getDate()->format('Y');
        if($depenseYear != date("Y")){

            //si la dépense est antidatée, récupérer le dernier numéro de dépense de l'année concernée
            $prefixe = 'D-'.$depenseYear.'-';
            $depenseRepository = $this->em->getRepository('App:Compta\Depense');
            $lastNum = $depenseRepository->findMaxNumForYear('DEPENSE', $depenseYear, $company);
            $lastNum = substr($lastNum, 7);
            $lastNum++;

            $num = $lastNum;

        } else {

            $prefixe = 'D-'.date('Y').'-';
            if($currentNum < 10){
                $prefixe.='00';
            } else if ($currentNum < 100){
                $prefixe.='0';
            }

            $num = $currentNum;
        }

        $arr_num = array(
            'prefixe' => $prefixe,
            'num' => $num
        );

        return $arr_num;
    }

    /**
     * @upgrade-notes removed unused return Response();
     */
    public function updateDepenseNum($company, $num){

        $settingsRepository = $this->em->getRepository('App:Settings');
        $settingsNum = $settingsRepository->findOneBy(array(
            'module' => 'COMPTA',
            'parametre' => 'NUMERO_DEPENSE',
            'company'=> $company
        ));

        //mise à jour du numéro de depense
        $settingsNum->setValeur($num);
        $this->em->persist($settingsNum);
        $this->em->flush();
    }

    public function getNumEcriture($company){

        $settingsRepository = $this->em->getRepository('App:Settings');

        $settingsNum = $settingsRepository->findOneBy(array(
            'module' => 'COMPTA',
            'parametre' => 'NUMERO_ECRITURE',
            'company'=> $company
        ));

        if(is_null($settingsNum) || (is_countable($settingsNum) && count($settingsNum) == 0)) {
            $settingsNum = new Settings();
            $settingsNum->setModule('COMPTA');
            $settingsNum->setParametre('NUMERO_ECRITURE');
            $settingsNum->setHelpText('Le numéro d\'écriture courant - ne pas modifier si vous n\'êtes pas sûr de ce que vous faites !');
            $settingsNum->setTitre('Numéro d\'écriture');
            $settingsNum->setType('NUM');
            $settingsNum->setNoTVA(false);
            $settingsNum->setCategorie('JOURNAUX');
            $settingsNum->setCompany($company);
            $num = 1;
        } else {
            $num = $settingsNum->getValeur();
        }

        return $num;
    }

    /**
     * @upgrade-notes removed unused return Response();
     */
    public function updateNumEcriture($company, $num){

        $settingsRepository = $this->em->getRepository('App:Settings');
        $settingsNum = $settingsRepository->findOneBy(array(
            'module' => 'COMPTA',
            'parametre' => 'NUMERO_ECRITURE',
            'company'=> $company
        ));

        //mise à jour du numéro de depense
        $settingsNum->setValeur($num);
        $this->em->persist($settingsNum);
        $this->em->flush();
    }


}
