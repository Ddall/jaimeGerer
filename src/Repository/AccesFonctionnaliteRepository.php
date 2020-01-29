<?php

namespace App\Repository;

use App\Entity\AccesFonctionnalite;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method AccesFonctionnalite|null find($id, $lockMode = null, $lockVersion = null)
 * @method AccesFonctionnalite|null findOneBy(array $criteria, array $orderBy = null)
 * @method AccesFonctionnalite[]    findAll()
 * @method AccesFonctionnalite[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AccesFonctionnaliteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AccesFonctionnalite::class);
    }

    // /**
    //  * @return AccesFonctionnalite[] Returns an array of AccesFonctionnalite objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?AccesFonctionnalite
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
