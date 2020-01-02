<?php

namespace App\Service\Compta;

use Doctrine\ORM\EntityManagerInterface;

class DepenseService  {

  protected $em;

  public function __construct(EntityManagerInterface $em)
  {
    $this->em = $em;
  }


}
