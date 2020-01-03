<?php

namespace App\Entity\CRM;

use Doctrine\ORM\EntityRepository;

/**
 * SousTraitanceRepartitionRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class SousTraitanceRepartitionRepository extends EntityRepository
{
    public function findForCompanyByYear($company, $year){

        $qb = $this->createQueryBuilder('r')
          ->innerJoin('r.opportuniteSousTraitance', 's')
          ->innerJoin('s.opportunite', 'o')
          ->innerJoin('o.compte', 'c')
          ->where('c.company = :company')
          ->andWhere('r.date >= :start')
          ->andWhere('r.date <= :end')
          ->setParameter('company', $company)
          ->setParameter('start', $year.'-01-01')
          ->setParameter('end',  $year.'-12-31');

        return $qb->getQuery()->getResult();
    }

    public function findForCompanyByYearHavingFrais($company, $year){

        $qb = $this->createQueryBuilder('r')
          ->innerJoin('r.opportuniteSousTraitance', 's')
          ->innerJoin('s.opportunite', 'o')
          ->innerJoin('o.compte', 'c')
          ->where('c.company = :company')
          ->andWhere('r.date >= :start')
          ->andWhere('r.date <= :end')
          ->andWhere('r.frais IS NOT NULL')
          ->setParameter('company', $company)
          ->setParameter('start', $year.'-01-01')
          ->setParameter('end',  $year.'-12-31');

        return $qb->getQuery()->getResult();
    }
}