<?php

namespace AppBundle\Entity\Compta;

use Doctrine\ORM\EntityRepository;

/**
 * DepenseRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class DepenseRepository extends EntityRepository
{
	public function count($company){
		$result = $this->createQueryBuilder('d')
		->select('COUNT(d)')
		->leftJoin('AppBundle\Entity\CRM\Compte', 'co', 'WITH', 'co.id = d.compte')
		->where('co.company = :company')
		->setParameter('company', $company)
		->getQuery()
		->getSingleScalarResult();

		return $result;
	}

	/**
	 * @param $company
	 * @param $length
	 * @param $start
	 * @param $orderBy
	 * @param $dir
	 * @param $search
	 * @param string $dateRange
     * @return array
     */
	public function findForList($company, $length, $start, $orderBy, $dir, $search, $dateRange = ''){
		$qb = $this->createQueryBuilder('d')
		->select('d.id', 'd.date', 'd.etat', 'c.nom as compte_nom', 'c.id as compte_id', 'd.libelle', 'd.num')
		->leftJoin('AppBundle\Entity\CRM\Compte', 'c', 'WITH', 'c.id = d.compte')
		->leftJoin('AppBundle\Entity\Compta\LigneDepense', 'l', 'WITH', 'd.id = l.depense');
		$qb->where('c.company = :company')
		->setParameter('company', $company);
		if( is_array($dateRange) ){
			$qb->andWhere('d.date >= :dateDebut')
				->setParameter('dateDebut', \DateTime::createFromFormat('D M d Y H:i:s e+', $dateRange['start']))
				->andWhere('d.date <= :dateFin')
				->setParameter('dateFin', \DateTime::createFromFormat('D M d Y H:i:s e+', $dateRange['end']));
		}
		if($search != ""){
			$qb->andWhere('c.nom LIKE :search OR d.libelle LIKE :search OR l.montant LIKE :search')
			->setParameter('search', '%'.$search.'%');
		}


		$qb->setMaxResults($length)
		->setFirstResult($start);

		if($orderBy == 'compte_nom'){
			$qb->addOrderBy('c.nom', $dir);
		} else {
			$qb->addOrderBy('d.'.$orderBy, $dir);
		}

		return $qb->getQuery()->getResult();
	}

	public function countForList($company, $search, $dateRange = ''){
		$qb = $this->createQueryBuilder('d')
		->select('COUNT(d)')
		->leftJoin('AppBundle\Entity\CRM\Compte', 'c', 'WITH', 'c.id = d.compte')
		->leftJoin('AppBundle\Entity\Compta\LigneDepense', 'l', 'WITH', 'd.id = l.depense')
		->where('c.company = :company')
		->setParameter('company', $company);
		if( is_array($dateRange) ){
			$qb->andWhere('d.date >= :dateDebut')
				->setParameter('dateDebut', \DateTime::createFromFormat('D M d Y H:i:s e+', $dateRange['start']))
				->andWhere('d.date <= :dateFin')
				->setParameter('dateFin', \DateTime::createFromFormat('D M d Y H:i:s e+', $dateRange['end']));
		}

		if($search != ""){
			$qb->andWhere('c.nom LIKE :search OR d.libelle LIKE :search OR l.montant LIKE :search')
			->setParameter('search', '%'.$search.'%');
		}

		return $qb->getQuery()->getSingleScalarResult();
	}

	public function findForCompany($company, $dateRange = null){
		$qb = $this->createQueryBuilder('d')
		->leftJoin('AppBundle\Entity\CRM\Compte', 'c', 'WITH', 'c.id = d.compte')
		->leftJoin('AppBundle\Entity\Compta\LigneDepense', 'l', 'WITH', 'd.id = l.depense')
		->where('c.company = :company')
		->setParameter('company', $company)
		->orderBy('c.nom', 'ASC')
		->addOrderBy('l.montant', 'ASC')
		;

		if( is_array($dateRange) ){
			$dateStart = $dateRange['start'] instanceof \DateTime ? $dateRange['start'] :
								\DateTime::createFromFormat('D M d Y H:i:s e+', $dateRange['start']) ;
			$dateEnd = $dateRange['end'] instanceof \DateTime ? $dateRange['end'] :
							\DateTime::createFromFormat('D M d Y H:i:s e+', $dateRange['end']) ;
			$qb->andWhere('d.date >= :dateDebut')
				->setParameter('dateDebut', $dateStart)
				->andWhere('d.date <= :dateFin')
				->setParameter('dateFin', $dateEnd);
		}

		return $qb->getQuery()->getResult();
	}

	public function findRetard($compte){

		$queryBuilder  = $this->_em->createQueryBuilder();

		$subQueryBuilder = $this->_em->createQueryBuilder();
		$subQueryBuilder->select('IDENTITY(r.depense)')
		->from('AppBundle\Entity\Compta\Rapprochement', 'r')
		->where('r.depense = d.id');

		$query = $this->createQueryBuilder('d')
		->where('d.compte = :compte')
		->andWhere('d.date <= :now')
		->setParameter('compte', $compte)
		->setParameter('now', new \DateTime('yesterday'));

		$query->andWhere($queryBuilder->expr()->not($queryBuilder->expr()->exists($subQueryBuilder->getDQL())));

		$query->addOrderBy('d.id', 'ASC');

		return $query->getQuery()->getResult();
	}

	public function findForPeriodeEngagement($company, $mois, $annee){

		$query = $this->createQueryBuilder('d')
		->leftJoin('AppBundle\Entity\CRM\Compte', 'c', 'WITH', 'c.id = d.compte')
		->where('c.company = :company')
		->andWhere('d.date >= :first')
		->andWhere('d.date <= :last')
		->setParameter('company', $company)
		->setParameter('first', $annee.'-'.$mois.'-01')
		->setParameter('last',  $annee.'-'.$mois.'-31');

		$result = $query->getQuery()->getResult();

		return $result;
	}

	public function findMaxNumForYear($year, $company){
		$query = $this->createQueryBuilder('d')
			->select('MAX(d.num)')
			->leftJoin('AppBundle\Entity\CRM\Compte', 'c', 'WITH', 'c.id = d.compte')
			->where('c.company = :company')
			->andWhere('d.dateCreation >= :first')
			->andWhere('d.dateCreation <= :last')
			->setParameter('company', $company)
			->setParameter('first', $year.'-01-01')
			->setParameter('last', $year.'-12-31');

		$result = $query->getQuery()->getSingleScalarResult();

		return $result;
	}

	public function findForCompanyByYear($company, $year){
		$qb = $this->createQueryBuilder('d')
		->leftJoin('AppBundle\Entity\CRM\Compte', 'c', 'WITH', 'c.id = d.compte')
		->where('c.company = :company')
		->andWhere('d.date >= :first')
		->andWhere('d.date <= :last')
		->setParameter('company', $company)
		->setParameter('first', $year.'-01-01')
		->setParameter('last', $year.'-12-31')
		->orderBy('d.date', 'ASC');

		return $qb->getQuery()->getResult();
	}

	public function findForCompanyByYearAndMonth($company, $year, $month){
		$qb = $this->createQueryBuilder('d')
		->leftJoin('AppBundle\Entity\CRM\Compte', 'c', 'WITH', 'c.id = d.compte')
		->where('c.company = :company')
		->andWhere('d.date >= :first')
		->andWhere('d.date <= :last')
		->setParameter('company', $company)
		->setParameter('first', $year.'-'.$month.'-01')
		->setParameter('last', $year.'-'.$month.'-31')
		->orderBy('d.date', 'ASC');

		return $qb->getQuery()->getResult();
	}

	public function findNoRapprochement($company){

		$queryBuilder  = $this->_em->createQueryBuilder();
		$subQueryBuilder = $this->_em->createQueryBuilder();

		$subQueryBuilder->select('IDENTITY(r.depense)')
			->from('AppBundle\Entity\Compta\Rapprochement', 'r')
			->where('r.depense = d.id ');

		$query = $this->createQueryBuilder('d')
			->leftJoin('AppBundle\Entity\CRM\Compte', 'c', 'WITH', 'c.id = d.compte')
			->where('c.company = :company')
			->andWhere($queryBuilder->expr()->not($queryBuilder->expr()->exists($subQueryBuilder->getDQL())))
			->setParameter('company', $company)
			->orderBy('d.num', 'ASC');

		return $query;
	}

	public function resultNoRapprochement($company){
		return $this->findNoRapprochement($company)
			->getQuery()
			->getResult();
	}





	public function findDepensesRetardByCompany($company){
		$queryBuilder  = $this->_em->createQueryBuilder();

		$subQueryBuilder = $this->_em->createQueryBuilder();
		$subQueryBuilder->select('IDENTITY(r.depense)')
			->from('AppBundle\Entity\Compta\Rapprochement', 'r')
			->where('r.depense = d.id');

//		$chequeSubQueryBuilder = $this->_em->createQueryBuilder();
//		$chequeSubQueryBuilder->select('IDENTITY(cp.depense)')
//			->from('AppBundle\Entity\Compta\ChequePiece', 'cp')
//			->innerJoin('AppBundle\Entity\Compta\Cheque', 'ch', 'WITH', 'cp.cheque = ch.id')
//			->innerJoin('AppBundle\Entity\Compta\Rapprochement', 'rp', 'WITH', 'rp.remiseCheque = ch.remiseCheque')
//			->where('cp.depense = d.id');

		//ne pas prendre les factures qui ont des avoirs
		$avoirSubQueryBuilder = $this->_em->createQueryBuilder();
		$avoirSubQueryBuilder->select('IDENTITY(a.depense)')
			->from('AppBundle\Entity\Compta\Avoir', 'a')
			->where('a.depense = d.id');

		$query = $this->createQueryBuilder('d')
			->leftJoin('AppBundle\Entity\CRM\Compte', 'c', 'WITH', 'c.id = d.compte')
			->where('c.company = :company')
			->andWhere('d.dateConditionReglement <= :now')
			//->andWhere('d.compta = :compta')
			->setParameter('company', $company)
			//->setParameter('compta', true)
			->setParameter('now', new \DateTime('yesterday'));;
		//$query->andWhere('d.type = :type')
		//	->setParameter('type', 'FACTURE');

		$query->andWhere($queryBuilder->expr()->not($queryBuilder->expr()->exists($subQueryBuilder->getDQL())));
		//$query->andWhere($queryBuilder->expr()->not($queryBuilder->expr()->exists($chequeSubQueryBuilder->getDQL())));
		$query->andWhere($queryBuilder->expr()->not($queryBuilder->expr()->exists($avoirSubQueryBuilder->getDQL())));

		$query->addOrderBy('d.libelle', 'ASC');

		return $query->getQuery()->getResult();
	}


	public function findForListRetard($company, $length, $start, $orderBy, $dir, $search){

		$queryBuilder  = $this->_em->createQueryBuilder();

		$subQueryBuilder = $this->_em->createQueryBuilder();
		$subQueryBuilder->select('IDENTITY(r.depense)')
			->from('AppBundle\Entity\Compta\Rapprochement', 'r')
			->where('r.depense = d.id');

		//ne pas prendre les factures qui ont des avoirs
		$avoirSubQueryBuilder = $this->_em->createQueryBuilder();
		$avoirSubQueryBuilder->select('IDENTITY(a.facture)')
			->from('AppBundle\Entity\Compta\Avoir', 'a')
			->where('a.depense = d.id');

		$query = $this->createQueryBuilder('d');
		$query->select('d.id', 'd.libelle', 'd.num', 'd.dateCreation', 'c.nom as compte_nom', 'c.id as compte_id', 'd.dateConditionReglement')
			->leftJoin('AppBundle\Entity\CRM\Compte', 'c', 'WITH', 'c.id = d.compte')
			->where('c.company = :company')
			->andWhere('d.dateConditionReglement <= :now')
			->setParameter('company', $company)
			->setParameter('now', new \DateTime('yesterday'));

		if($search != ""){
			$query->andWhere('d.libelle LIKE :search or d.num LIKE :search or c.nom LIKE :search')
				->setParameter('search', '%'.$search.'%');
		}

		$query->andWhere($queryBuilder->expr()->not($queryBuilder->expr()->exists($subQueryBuilder->getDQL())));
		$query->andWhere($queryBuilder->expr()->not($queryBuilder->expr()->exists($avoirSubQueryBuilder->getDQL())));

		$query->setMaxResults($length)
			->setFirstResult($start)
			->addOrderBy('d.'.$orderBy, $dir);

		return $query->getQuery()->getResult();
	}

	public function countForListRetard($company,$search){

		$queryBuilder  = $this->_em->createQueryBuilder();

		$subQueryBuilder = $this->_em->createQueryBuilder();
		$subQueryBuilder->select('IDENTITY(r.depense)')
			->from('AppBundle\Entity\Compta\Rapprochement', 'r')
			->where('r.depense = d.id');

//		$chequeSubQueryBuilder = $this->_em->createQueryBuilder();
//		$chequeSubQueryBuilder->select('IDENTITY(cp.facture)')
//			->from('AppBundle\Entity\Compta\ChequePiece', 'cp')
//			->innerJoin('AppBundle\Entity\Compta\Cheque', 'ch', 'WITH', 'cp.cheque = ch.id')
//			->innerJoin('AppBundle\Entity\Compta\Rapprochement', 'rp', 'WITH', 'rp.remiseCheque = ch.remiseCheque')
//			->where('cp.facture = d.id');

		$query = $this->createQueryBuilder('d');
		$query->select('COUNT(d)')
			->leftJoin('AppBundle\Entity\CRM\Compte', 'c', 'WITH', 'c.id = d.compte')
			->where('c.company = :company')
			->andWhere('d.dateConditionReglement <= :now')
			->setParameter('company', $company)
			->setParameter('now', new \DateTime('yesterday'));

//		if($compta != null){
//			$query->andWhere('d.compta = :compta')
//				->setParameter('compta', $compta);
//		}

		if($search != ""){
			$query->andWhere('d.libelle LIKE :search or d.num LIKE :search or c.nom LIKE :search')
				->setParameter('search', '%'.$search.'%');
		}
//
//		$query->andWhere('d.type = :type')
//			->setParameter('type', $type);

		$query->andWhere($queryBuilder->expr()->not($queryBuilder->expr()->exists($subQueryBuilder->getDQL())));
		//$query->andWhere($queryBuilder->expr()->not($queryBuilder->expr()->exists($chequeSubQueryBuilder->getDQL())));

		return $query->getQuery()->getSingleScalarResult();
	}

	public function findByNumFournisseurAndCompany($numFournisseur, $company){

		$qb = $this->createQueryBuilder('d')
		->leftJoin('AppBundle\Entity\CRM\Compte', 'c', 'WITH', 'c.id = d.compte')
		->where('c.company = :company')
		->andWhere('d.numFournisseur = :numFournisseur')
		->setParameter('company', $company)
		->setParameter('numFournisseur', $numFournisseur)
		;

		return $qb->getQuery()->getResult();
	}


}
