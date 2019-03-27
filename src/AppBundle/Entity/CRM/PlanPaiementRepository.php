<?php

namespace AppBundle\Entity\CRM;

use Doctrine\ORM\EntityRepository;

/**
 * PlanPaiementRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class PlanPaiementRepository extends EntityRepository
{

	public function findTimespan($timespan, $user){
		$today = date('Y-m-d').'%';

		$query = $this->createQueryBuilder('p')
		->join('AppBundle:CRM\Opportunite', 'o', 'WITH', 'p.actionCommerciale = o.id')
		->andWhere('o.userGestion = :user')
		->andWhere('p.facture IS NULL');

		if('today' == $timespan){
			$query->andWhere('p.date LIKE :today');
		}

		if('week' == $timespan){
			$sunday = date('Y-m-d', strtotime('next sunday'));
			$query
				->andWhere('p.date > :today')
				->andWhere('p.date <= :sunday')
				->setParameter('sunday', $sunday);
		}

		if('late' == $timespan){
			$query 
				->andWhere('p.date < :today');
		}


		$result = $query
		->setParameter('today', $today)
		->setParameter('user', $user)
		->getQuery()
		->getResult();

		return $result;
	}

}
