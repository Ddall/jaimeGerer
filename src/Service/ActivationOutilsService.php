<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;

class ActivationOutilsService {

  protected $em;

    /**
     * ActivationOutilsService constructor.
     *
     * @param EntityManagerInterface $em
     */
  public function __construct(EntityManagerInterface $em)
  {
    $this->em = $em;
  }

  public function isActive($outil, $company){

    $activationSettingsRepo = $this->em->getRepository('App:SettingsActivationOutil');
    $activation = $activationSettingsRepo->findOneBy(array(
      'company' => $company,
      'outil' => $outil
    ));

    if($activation === null){
      return false;
    }

    return true;

  }



}
