<?php

namespace App\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * SettingsRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class CompanyRepository extends EntityRepository
{
	public function custom_count(){
		$result = $this->createQueryBuilder('c')
		->select('COUNT(c)')
		->getQuery()
		->getSingleScalarResult();

		return $result;
	}

	public function countActives(){
	
		$result = $this->createQueryBuilder('c')
		 ->select('COUNT(DISTINCT c.id)')
		->innerJoin('App\Entity\User', 'u', 'WITH', 'c.id = u.company')
		->where('u.lastLogin >= :lastLogin')
		->setParameter('lastLogin', date('Y-m-d', strtotime('-7 days')))
		->getQuery()
		->getSingleScalarResult();

		return $result;
	}
}