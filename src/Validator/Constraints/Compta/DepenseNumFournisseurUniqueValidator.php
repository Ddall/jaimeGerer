<?php
namespace App\Validator\Constraints\Compta;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class DepenseNumFournisseurUniqueValidator extends ConstraintValidator
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
  	{
  	    $this->entityManager = $entityManager;
  	}

    public function validate($depense, Constraint $constraint)
    {
      if($depense->getNumFournisseur()){
        $repo = $this->entityManager->getRepository('App:Compta\Depense');
        $arr_existing = $repo->findByNumFournisseurAndCompany($depense->getNumFournisseur(), $depense->getUserCreation()->getCompany());

        if(count($arr_existing) > 0) {
          $this->context
            ->buildViolation($constraint->message)
            ->atPath('foo')
            ->addViolation();
        }
      }
  	}

}
