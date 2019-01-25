<?php

namespace AppBundle\Entity\Emailing;

use Doctrine\ORM\EntityRepository;

/**
 * CampagneRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class CampagneRepository extends EntityRepository
{
	public function count($company){
		$result = $this->createQueryBuilder('c')
		->select('COUNT(c)')
		->leftJoin('AppBundle\Entity\User', 'u', 'WITH', 'u.id = c.userCreation')
		->where('u.company = :company')
		->setParameter('company', $company)
		->getQuery()
		->getSingleScalarResult();
		
		return $result;
	}
	
	public function findForList($company, $length, $start, $orderBy, $dir, $search){
		$qb = $this->createQueryBuilder('c')
			->select('DISTINCT(c.id) as id', 'c.nom', 'c.dateCreation', 'c.objet', 'c.nomRapport', 'CONCAT(u.firstname,\' \',u.lastname) as user', 'COUNT(cc.id) AS nbContacts', 'c.etat')
			->leftJoin('AppBundle\Entity\User', 'u', 'WITH', 'u.id = c.userCreation')
			->leftJoin('AppBundle\Entity\Emailing\CampagneContact', 'cc', 'WITH', 'cc.campagne = c.id')
			->where('u.company = :company')
			->setParameter('company', $company);
		
		if($search != ""){
			$search = trim($search);
			$qb->andWhere('c.nom LIKE :search')
			->setParameter('search', '%'.$search.'%');
		}
		
		$qb->setMaxResults($length)
	        ->setFirstResult($start)
	        ->addOrderBy('c.'.$orderBy, $dir);
	
		return $qb->getQuery()->getResult();
	}
	
	public function countForList($company, $search){
		$qb = $this->createQueryBuilder('c')
		->select('COUNT(c)')
		->leftJoin('AppBundle\Entity\User', 'u', 'WITH', 'u.id = c.userCreation')
		->where('u.company = :company')
		->setParameter('company', $company);
		
		if($search != ""){
				
			$qb->andWhere('c.nom LIKE :search')
			->setParameter('search', '%'.$search.'%');
		}
		
		return $qb->getQuery()->getSingleScalarResult();
	}
		
	public function findForListStats($userCreation, $length, $start, $orderBy, $dir, $search){
		$qb = $this->createQueryBuilder('c')
			->select('c.id', 'c.nom', 'c.dateCreation', 'c.objet')
			->where('c.userCreation = :userCreation')
			->setParameter('userCreation', $userCreation);
		
		if($search != ""){
			$search = trim($search);
			$qb->andWhere('c.nom LIKE :search')
			->setParameter('search', '%'.$search.'%');
		}
		
		$qb->setMaxResults($length)
	        ->setFirstResult($start)
	        ->addOrderBy('c.'.$orderBy, $dir);
	
		return $qb->getQuery()->getResult();
	}
	
	public function countForListStats($userCreation, $search){
		$qb = $this->createQueryBuilder('c')
		->select('COUNT(c)')
		->where('c.userCreation = :userCreation')
		->andWhere('c.envoyee = :envoyee')
		->setParameter('envoyee', true)
		->setParameter('userCreation', $userCreation);
		
		if($search != ""){
				
			$qb->andWhere('c.nom LIKE :search')
			->setParameter('search', '%'.$search.'%');
		}
		
		return $qb->getQuery()->getSingleScalarResult();
	}
		
	public function findAllExcept($id = 0){

		$qb = $this->createQueryBuilder('u');
		$qb->where('u.id != :identifier')
		   ->setParameter('identifier', $id);

		return $qb->getQuery()
			  ->getResult();
	}

	public function createQueryAndGetResult($arr_filters, $userCreation){
		
		$query = $this->createQueryBuilder('c');
		
		$index=0;
		
		foreach($arr_filters as $filter){
			
			$champ = $filter->getChamp();
			$action = $filter->getAction();
			$andor = $filter->getAndor();
			
			if($action == 'EMPTY'){
				if($index == 0){
					$query->where('c.'.$champ.' IS NULL' );
				} else {
					if($andor == 'AND'){
						$query->andWhere('c.'.$champ.' IS NULL' );
					} else{
						$query->orWhere('c.'.$champ.' IS NULL' );
					}
				}
			} else if($action == 'NOT_EMPTY'){
				if($index == 0){
					$query->where('c.'.$champ.' IS NOT NULL' );
				} else {
					if($andor == 'AND'){
						$query->andWhere('c.'.$champ.' IS NOT NULL' );
					} else{
						$query->orWhere('c.'.$champ.' IS NOT NULL' );
					}
				}
			} else {

				$operateur = 'LIKE';
				
				if($action == 'NOT_EQUALS' || $action == 'NOT_CONTAINS'){
					$operateur = 'NOT LIKE';
				}
				
				$arr_valeurs = explode(',', $filter->getValeur());
				$where = '';
				
				for($i=0; $i<count($arr_valeurs); $i++){
			
					$param = ':valeur'.$index.$i;
					
					$val = '';
					if($action == 'EQUALS' || $action == 'NOT_EQUALS'){
						$val = $arr_valeurs[$i];
					} elseif($action == 'CONTAINS' || $action == 'NOT_CONTAINS'){
						$val = '%'.$arr_valeurs[$i].'%';
					} elseif($action == 'BEGINS_WITH'){
						$val = $arr_valeurs[$i].'%';
					} elseif($action == 'ENDS_WITH'){
						$val = '%'.$arr_valeurs[$i];
					}
					
					if($i != 0){
						$where.=' OR ';
					}
					$where.= 'c.'.$champ.' '.$operateur.' '.$param;
					$query->setParameter($param, $val);

				}
				
				if($index == 0){
					$query->where($where);
				} else {
					if($andor == 'AND'){
						$query->andWhere($where);
					} else{
						$query->orWhere($where);
					}
				}
				
			}
			$index++;
		}
		
		$query->andWhere('c.userCreation = :userCreation')
		->setParameter('userCreation', $userCreation);
		
		$result = $query->getQuery()->getResult();

		return $result;
	}

}
