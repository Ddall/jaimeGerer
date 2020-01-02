<?php

namespace App\Entity\Compta;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * CompteComptable
 *
 * @ORM\Table(name="compte_comptable", uniqueConstraints={@ORM\UniqueConstraint(name="num_unique", columns={"num", "company_id"})})
 * @ORM\Entity(repositoryClass="App\Entity\Compta\CompteComptableRepository")
 */
class CompteComptable
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     */
    private $id;

    /**
     * @var string
     *
     *
     * @ORM\Column(name="num", type="string", length=8)
     * @Assert\Length(
     *      max = 8,
     *      maxMessage = "Vous ne pouvez pas utiliser plus de {{ limit }} caractères"
     * )
     */
    private $num;

    /**
     * @var string
     *
     * @ORM\Column(name="nom", type="string", length=255)
     */
    private $nom;


    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Company")
     * @ORM\JoinColumn(nullable=true)
     */
    private $company;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Compta\CompteComptable")
     * @ORM\JoinColumn(nullable=true)
     */
    private $compteTVA;

    /**
     * @var string
     *
     * @ORM\Column(name="report_debit", type="decimal", nullable=true)
     */
    private $reportDebit;

    /**
     * @var string
     *
     * @ORM\Column(name="report_credit", type="decimal", nullable=true)
     */
    private $reportCredit;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Compta\JournalVente", mappedBy="compteComptable")
     */
    private $journalVentes;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Compta\JournalAchat", mappedBy="compteComptable")
     */
    private $journalAchats;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Compta\JournalBanque", mappedBy="compteComptable")
     */
    private $journalBanque;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Compta\OperationDiverse", mappedBy="compteComptable")
     */
    private $operationsDiverses;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->journalVentes = new \Doctrine\Common\Collections\ArrayCollection();
        $this->journalAchats = new \Doctrine\Common\Collections\ArrayCollection();
        $this->journalBanque = new \Doctrine\Common\Collections\ArrayCollection();
        $this->operationsDiverses = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set num
     *
     * @param string $num
     * @return CompteComptable
     */
    public function setNum($num)
    {
        $this->num = $num;

        return $this;
    }

    /**
     * Get num
     *
     * @return string
     */
    public function getNum()
    {
        return $this->num;
    }

    /**
     * Set nom
     *
     * @param string $nom
     * @return CompteComptable
     */
    public function setNom($nom)
    {
        $this->nom = $nom;

        return $this;
    }

    /**
     * Get nom
     *
     * @return string
     */
    public function getNom()
    {
        return $this->nom;
    }
	public function getCompany() {
		return $this->company;
	}
	public function setCompany($company) {
		$this->company = $company;
		return $this;
	}

	public function __toString(){
		return $this->num.' : '.$this->nom;
	}

    public function getNameAndNum(){
        return $this->nom.' ('.$this->num.')';
    }



    /**
     * Set compteTVA
     *
     * @param \App\Entity\Compta\CompteComptable $compteTVA
     * @return CompteComptable
     */
    public function setCompteTVA(\App\Entity\Compta\CompteComptable $compteTVA = null)
    {
        $this->compteTVA = $compteTVA;

        return $this;
    }

    /**
     * Get compteTVA
     *
     * @return \App\Entity\Compta\CompteComptable
     */
    public function getCompteTVA()
    {
        return $this->compteTVA;
    }

    /**
     * Set reportDebit
     *
     * @param string $reportDebit
     * @return CompteComptable
     */
    public function setReportDebit($reportDebit)
    {
        $this->reportDebit = $reportDebit;

        return $this;
    }

    /**
     * Get reportDebit
     *
     * @return string
     */
    public function getReportDebit()
    {
        return $this->reportDebit;
    }

    /**
     * Set reportCredit
     *
     * @param string $reportCredit
     * @return CompteComptable
     */
    public function setReportCredit($reportCredit)
    {
        $this->reportCredit = $reportCredit;

        return $this;
    }

    /**
     * Get reportCredit
     *
     * @return string
     */
    public function getReportCredit()
    {
        return $this->reportCredit;
    }

    /**
     * Add journalVentes
     *
     * @param \App\Entity\Compta\JournalVente $journalVentes
     * @return CompteComptable
     */
    public function addJournalVente(\App\Entity\Compta\JournalVente $journalVentes)
    {
        $this->journalVentes[] = $journalVentes;

        return $this;
    }

    /**
     * Remove journalVentes
     *
     * @param \App\Entity\Compta\JournalVente $journalVentes
     */
    public function removeJournalVente(\App\Entity\Compta\JournalVente $journalVentes)
    {
        $this->journalVentes->removeElement($journalVentes);
    }

    /**
     * Get journalVentes
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getJournalVentes()
    {
        return $this->journalVentes;
    }

    /**
     * Add journalAchats
     *
     * @param \App\Entity\Compta\JournalAchat $journalAchats
     * @return CompteComptable
     */
    public function addJournalAchat(\App\Entity\Compta\JournalAchat $journalAchats)
    {
        $this->journalAchats[] = $journalAchats;

        return $this;
    }

    /**
     * Remove journalAchats
     *
     * @param \App\Entity\Compta\JournalAchat $journalAchats
     */
    public function removeJournalAchat(\App\Entity\Compta\JournalAchat $journalAchats)
    {
        $this->journalAchats->removeElement($journalAchats);
    }

    /**
     * Get journalAchats
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getJournalAchats()
    {
        return $this->journalAchats;
    }

    /**
     * Add journalBanque
     *
     * @param \App\Entity\Compta\JournalBanque $journalBanque
     * @return CompteComptable
     */
    public function addJournalBanque(\App\Entity\Compta\JournalBanque $journalBanque)
    {
        $this->journalBanque[] = $journalBanque;

        return $this;
    }

    /**
     * Remove journalBanque
     *
     * @param \App\Entity\Compta\JournalBanque $journalBanque
     */
    public function removeJournalBanque(\App\Entity\Compta\JournalBanque $journalBanque)
    {
        $this->journalBanque->removeElement($journalBanque);
    }

    /**
     * Get journalBanque
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getJournalBanque()
    {
        return $this->journalBanque;
    }

    /**
     * Add operationsDiverses
     *
     * @param \App\Entity\Compta\OperationDiverse $operationsDiverses
     * @return CompteComptable
     */
    public function addOperationsDiverse(\App\Entity\Compta\OperationDiverse $operationsDiverses)
    {
        $this->operationsDiverses[] = $operationsDiverses;

        return $this;
    }

    /**
     * Remove operationsDiverses
     *
     * @param \App\Entity\Compta\OperationDiverse $operationsDiverses
     */
    public function removeOperationsDiverse(\App\Entity\Compta\OperationDiverse $operationsDiverses)
    {
        $this->operationsDiverses->removeElement($operationsDiverses);
    }

    /**
     * Get operationsDiverses
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getOperationsDiverses()
    {
        return $this->operationsDiverses;
    }

    public function getTotalDebit($periode)
    {

      $dateDebutPeriode = null;
      $dateFinPeriode = null;
      
      switch($periode){
        case 'ANNEE':
          $dateDebutPeriode =  new \DateTime('first day of january');
          $dateFinPeriode = new \DateTime();
          break;

        case 'TRIMESTRE':
          $dateDebutPeriode =  new \DateTime('first day of january');
          $dateFinPeriode = new \DateTime();
          break;

        case 'MOIS':
          $dateDebutPeriode =  new \DateTime('first day of');
          $dateFinPeriode = new \DateTime();
          break;

        case 'ANNEE_PRECEDENTE':
          $dateDebutPeriode =  new \DateTime('first day of january last year');
          $dateFinPeriode = new \DateTime('last day of december last year');
          break;

        case 'ANNEE_COURS_ET_PRECEDENTE':
            $dateDebutPeriode =  new \DateTime('first day of january last year');
            $dateFinPeriode = new \DateTime();
            break;
      }

      $totalDebit = 0;
      foreach($this->journalVentes as $jv){
        if($jv->getDate() >= $dateDebutPeriode && $jv->getDate() <= $dateFinPeriode){
          $totalDebit+=$jv->getDebit();
        }
      }
      foreach($this->journalAchats as $ja){
        if($ja->getDate() >= $dateDebutPeriode && $ja->getDate() <= $dateFinPeriode){
          $totalDebit+=$ja->getDebit();
        }
      }
      foreach($this->journalBanque as $jb){
        if($jb->getDate() >= $dateDebutPeriode && $jb->getDate() <= $dateFinPeriode){
          $totalDebit+=$jb->getDebit();
        }
      }
      foreach($this->operationsDiverses as $od){
        if($od->getDate() >= $dateDebutPeriode && $od->getDate() <= $dateFinPeriode){
          $totalDebit+=$od->getDebit();
        }
      }

      return $totalDebit;

    }

    public function getTotalCredit($periode)
    {
      $dateDebutPeriode;
      $dateFinPeriode;

      switch($periode){
        case 'ANNEE':
          $dateDebutPeriode =  new \DateTime('first day of january');
          $dateFinPeriode = new \DateTime();
          break;

        case 'TRIMESTRE':

          $currentMonth = date('n');
          $month = 'january';
          if ($currentMonth > 3 && $currentMonth <= 6){
            $month = "april";
          } else if ($currentMonth <= 9){
            $month = "july";
          } else {
            $month = "october";
          }

          $dateDebutPeriode =  new \DateTime('first day of '.$month);
          $dateFinPeriode = new \DateTime();
          break;

        case 'MOIS':
          $dateDebutPeriode =  new \DateTime('first day of');
          $dateFinPeriode = new \DateTime();
          break;

           case 'ANNEE_PRECEDENTE':
          $dateDebutPeriode =  new \DateTime('first day of january last year');
          $dateFinPeriode = new \DateTime('last day of december last year');
          break;

        case 'ANNEE_COURS_ET_PRECEDENTE':
            $dateDebutPeriode =  new \DateTime('first day of january last year');
            $dateFinPeriode = new \DateTime();
            break;
      }

      $totalCredit = 0;
      foreach($this->journalVentes as $jv){
        if($jv->getDate() >= $dateDebutPeriode && $jv->getDate() <= $dateFinPeriode){
            $totalCredit+=$jv->getCredit();
        }
      }
      foreach($this->journalAchats as $ja){
        if($ja->getDate() >= $dateDebutPeriode && $ja->getDate() <= $dateFinPeriode){
          $totalCredit+=$ja->getCredit();
        }
      }
      foreach($this->journalBanque as $jb){
        if($jb->getDate() >= $dateDebutPeriode && $jb->getDate() <= $dateFinPeriode){
          $totalCredit+=$jb->getCredit();
        }
      }
      foreach($this->operationsDiverses as $od){
        if($od->getDate() >= $dateDebutPeriode && $od->getDate() <= $dateFinPeriode){
          $totalCredit+=$od->getCredit();
        }
      }

      return $totalCredit;

    }

    public function getSoldeDebiteur($periode){
      return $this->getTotalDebit($periode)-$this->getTotalCredit($periode);
    }

    public function getSoldeCrediteur($periode){
      return $this->getTotalCredit($periode)-$this->getTotalDebit($periode);
    }
}
