<?php

namespace App\Entity\Social;

use Doctrine\ORM\EntityRepository;

/**
 * TableauMerciRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class TableauMerciRepository extends EntityRepository
{

	public function findCurrent(){

		$today = date('Y-m-d');
		$result = $this->createQueryBuilder('t')
		->where('t.dateDebut <= :today')
		->andWhere('t.dateFin >= :today')
		->setParameter('today', $today)
		->getQuery()
		->getOneOrNullResult();
		
		return $result;
	}
}