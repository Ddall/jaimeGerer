<?php
/**
 * jaimeGerer - EntityManagerTrait.php
 * Created by Allan.
 */

namespace App\Util\DependancyInjectionTrait;


use Doctrine\ORM\EntityManagerInterface;

class EntityManagerTrait {

    /**
     * @var EntityManagerInterface
     */
    protected $em;
    protected $manager;
    protected $entityManager;

    /**
     * @required
     * @param EntityManagerInterface $em
     * @return EntityManagerTrait
     */
    public function setEntityManager(EntityManagerInterface $entityManager): self {
        $this->em = $entityManager;
        $this->manager = $entityManager;
        $this->entityManager = $entityManager;
        return $this;
    }

}
