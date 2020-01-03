<?php

namespace App\Entity\Compta;

use Doctrine\ORM\EntityRepository;

/**
 * RemiseChequeRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class RemiseChequeRepository extends EntityRepository
{
	public function findForCompany($company){
		$qb = $this->createQueryBuilder('r')
		->leftJoin('App\Entity\Compta\CompteBancaire', 'c', 'WITH', 'c.id = r.compteBancaire')
		->where('c.company = :company')
		->setParameter('company', $company)
		->addOrderBy('r.date', 'DESC');

		return $qb->getQuery()->getResult();
	}
	
	public function custom_count($company){
		$result = $this->createQueryBuilder('r')
		->select('COUNT(r)')
		->leftJoin('App\Entity\Compta\CompteBancaire', 'c', 'WITH', 'c.id = r.compteBancaire')
		->where('c.company = :company')
		->setParameter('company', $company)
		->getQuery()
		->getSingleScalarResult();
	
		return $result;
	}
	
	public function findForList($company, $length, $start, $orderBy, $dir, $search){
		$qb = $this->createQueryBuilder('r')
		->select('r.id', 'r.date', 'r.num')
		->leftJoin('App\Entity\Compta\CompteBancaire', 'c', 'WITH', 'c.id = r.compteBancaire')
		->where('c.company = :company')
		->setParameter('company', $company);
	
		$qb->setMaxResults($length)
		->setFirstResult($start)
		->addOrderBy('r.'.$orderBy, $dir);
	
		return $qb->getQuery()->getResult();
	}
	
	public function countForList($company, $search){
		$qb = $this->createQueryBuilder('r')
		->select('COUNT(r)')
		->leftJoin('App\Entity\Compta\CompteBancaire', 'c', 'WITH', 'c.id = r.compteBancaire')
		->where('c.company = :company')
		->setParameter('company', $company);
	
		if($search != ""){
	
		}
	
		return $qb->getQuery()->getSingleScalarResult();
	}
}