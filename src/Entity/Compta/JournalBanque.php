<?php

namespace App\Entity\Compta;

use Doctrine\ORM\Mapping as ORM;

/**
 * JournalBanque
 *
 * @ORM\Table(name="journal_banque",
 *  uniqueConstraints={@ORM\UniqueConstraint(name="ligne_unique_debit", columns={"mouvementBancaire_id", "debit", "compteComptable_id", "nom"}),
 *  @ORM\UniqueConstraint(name="ligne_unique_credit", columns={"mouvementBancaire_id", "credit", "compteComptable_id", "nom"})},
 *  )
 * @ORM\Entity(repositoryClass="App\Entity\Compta\JournalBanqueRepository")
 */
class JournalBanque
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="codeJournal", type="string", length=255)
     */
    private $codeJournal;

    /**
     * @var float
     *
     * @ORM\Column(name="debit", type="float", nullable=true)
     */
    private $debit;

    /**
     * @var float
     *
     * @ORM\Column(name="credit", type="float", nullable=true)
     */
    private $credit;

    /**
     * @var string
     *
     * @ORM\Column(name="modePaiement", type="string", length=255, nullable=true)
     */
    private $modePaiement;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="date", nullable=true)
     */
    private $date;

    /**
     * @var string
     *
     * @ORM\Column(name="nom", type="string", length=255)
     */
    private $nom;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Compta\CompteComptable", inversedBy="journalBanque")
     * @ORM\JoinColumn(nullable=false, name="compteComptable_id")
     */
    private $compteComptable;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Settings")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private $analytique;

     /**
     * @var string
     * 
     * @ORM\Column(name="stringAnalytique", type="string", length=255, nullable=true)
     */
    private $stringAnalytique;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\CRM\DocumentPrix", cascade={"persist"})
     * @ORM\JoinColumn(nullable=true, unique=false)
     */
    private $facture;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Compta\Depense", cascade={"persist"})
     * @ORM\JoinColumn(nullable=true, unique=false)
     */
    private $depense;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Compta\Avoir", cascade={"persist"})
     * @ORM\JoinColumn(nullable=true, unique=false)
     */
    private $avoir;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Compta\MouvementBancaire")
     * @ORM\JoinColumn(nullable=true, unique=false, name="mouvementBancaire_id")
     */
    private $mouvementBancaire;

    /**
     * @var string
     *
     * @ORM\Column(name="lettrage", type="string", length=100, nullable=true)
     */
    private $lettrage;

    /**
     * @var string
     *
     * @ORM\Column(name="num_ecriture", type="string", length=50, nullable=true)
     */
    private $numEcriture;

    /**
     * @var string
     *
     * @ORM\Column(name="commentaire", type="string", length=255, nullable=true)
     */
    private $commentaire;


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
     * Set codeJournal
     *
     * @param string $codeJournal
     * @return JournalBanque
     */
    public function setCodeJournal($codeJournal)
    {
        $this->codeJournal = $codeJournal;

        return $this;
    }

    /**
     * Get codeJournal
     *
     * @return string
     */
    public function getCodeJournal()
    {
        return $this->codeJournal;
    }

    /**
     * Set debit
     *
     * @param float $debit
     * @return JournalBanque
     */
    public function setDebit($debit)
    {
        $this->debit = $debit;

        return $this;
    }

    /**
     * Get debit
     *
     * @return float
     */
    public function getDebit()
    {
        return $this->debit;
    }

    /**
     * Set credit
     *
     * @param float $credit
     * @return JournalBanque
     */
    public function setCredit($credit)
    {
        $this->credit = $credit;

        return $this;
    }

    /**
     * Get credit
     *
     * @return float
     */
    public function getCredit()
    {
        return $this->credit;
    }

    /**
     * Set modePaiement
     *
     * @param string $modePaiement
     * @return JournalBanque
     */
    public function setModePaiement($modePaiement)
    {
        $this->modePaiement = $modePaiement;

        return $this;
    }

    /**
     * Get modePaiement
     *
     * @return string
     */
    public function getModePaiement()
    {
        return $this->modePaiement;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     * @return JournalBanque
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDateCreation()
    {
        return $this->date;
    }

    /**
     * Set nom
     *
     * @param string $nom
     * @return JournalBanque
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

    /**
     * Set compteComptable
     *
     * @param \App\Entity\Compta\CompteComptable $compteComptable
     * @return JournalBanque
     */
    public function setCompteComptable(\App\Entity\Compta\CompteComptable $compteComptable)
    {
        $this->compteComptable = $compteComptable;

        return $this;
    }

    /**
     * Get compteComptable
     *
     * @return \App\Entity\Compta\CompteComptable
     */
    public function getCompteComptable()
    {
        return $this->compteComptable;
    }

    /**
     * Set analytique
     *
     * @param \App\Entity\Settings $analytique
     * @return JournalBanque
     */
    public function setAnalytique(\App\Entity\Settings $analytique = null)
    {
        $this->analytique = $analytique;

        return $this;
    }

    /**
     * Get analytique
     *
     * @return \App\Entity\Settings
     */
    public function getAnalytique()
    {
        return $this->analytique;
    }

    /**
     * Set facture
     *
     * @param \App\Entity\CRM\DocumentPrix $facture
     * @return JournalBanque
     */
    public function setFacture(\App\Entity\CRM\DocumentPrix $facture = null)
    {
        $this->facture = $facture;

        return $this;
    }

    /**
     * Get facture
     *
     * @return \App\Entity\CRM\DocumentPrix
     */
    public function getFacture()
    {
        return $this->facture;
    }

    /**
     * Set depense
     *
     * @param \App\Entity\Compta\Depense $depense
     * @return JournalBanque
     */
    public function setDepense(\App\Entity\Compta\Depense $depense = null)
    {
        $this->depense = $depense;

        return $this;
    }

    /**
     * Get depense
     *
     * @return \App\Entity\Compta\Depense
     */
    public function getDepense()
    {
        return $this->depense;
    }

    /**
     * Set mouvementBancaire
     *
     * @param \App\Entity\Compta\MouvementBancaire $mouvementBancaire
     * @return JournalBanque
     */
    public function setMouvementBancaire(\App\Entity\Compta\MouvementBancaire $mouvementBancaire = null)
    {
        $this->mouvementBancaire = $mouvementBancaire;

        return $this;
    }

    /**
     * Get mouvementBancaire
     *
     * @return \App\Entity\Compta\MouvementBancaire
     */
    public function getMouvementBancaire()
    {
        return $this->mouvementBancaire;
    }

    /**
     * Set avoir
     *
     * @param \App\Entity\Compta\Avoir $avoir
     * @return JournalBanque
     */
    public function setAvoir(\App\Entity\Compta\Avoir $avoir = null)
    {
        $this->avoir = $avoir;

        return $this;
    }

    /**
     * Get avoir
     *
     * @return \App\Entity\Compta\Avoir
     */
    public function getAvoir()
    {
        return $this->avoir;
    }

    public function getLibelle(){
      return $this->nom;
    }

    public function getPiece(){
      $num = '';
      foreach($this->mouvementBancaire->getRapprochements() as $rapprochement){
        if($rapprochement->getRemiseCheque() ){
          $num = $rapprochement->getRemiseCheque()->getNum();
        } else if ($rapprochement->getFacture()){
          $num = $rapprochement->getFacture()->getNum();
        } else if ($rapprochement->getDepense()){
          $num = $rapprochement->getDepense()->getNum();
        } else if ($rapprochement->getAvoir()){
          $num = $rapprochement->getAvoir()->getNum();
        } else if ($rapprochement->getAffectationDiverse()){
          $num = $rapprochement->getAffectationDiverse()->__toString();
        } else if ($rapprochement->getNoteFrais()){
          $num = $rapprochement->getNoteFrais()->__toString();
        }
      }
      return $num;
    }

    public function getPieceId(){
      $id = '';
      foreach($this->mouvementBancaire->getRapprochements() as $rapprochement){
        if($rapprochement->getRemiseCheque() ){
          $num = $rapprochement->getRemiseCheque()->getId();
        } else if ($rapprochement->getFacture()){
          $num = $rapprochement->getFacture()->getId();
        } else if ($rapprochement->getDepense()){
          $num = $rapprochement->getDepense()->getId();
        } else if ($rapprochement->getAvoir()){
          $num = $rapprochement->getAvoir()->getNum();
        } else if ($rapprochement->getAffectationDiverse()){
          $num = $rapprochement->getAffectationDiverse()->__toString();
        } else if ($rapprochement->getNoteFrais()){
          $num = $rapprochement->getNoteFrais()->__toString();
        }
      }
      return $id;
    }

    public function getDatePiece(){
        $date = null;
        foreach($this->mouvementBancaire->getRapprochements() as $rapprochement){
            if($rapprochement->getRemiseCheque() ){
                $date = $rapprochement->getRemiseCheque()->getDate();
            } else if ($rapprochement->getFacture()){
                $date = $rapprochement->getFacture()->getDateCreation();
            } else if ($rapprochement->getDepense()){
                $date = $rapprochement->getDepense()->getDate();
            } else if ($rapprochement->getAvoir()){
                $date = $rapprochement->getAvoir()->getDateCreation();
            } else if ($rapprochement->getAffectationDiverse()){
                $date = $rapprochement->getDate();
            } else if ($rapprochement->getNoteFrais()){
                $date = $rapprochement->getNoteFrais()->getDateCreation();
            }
        }
        return $date;
    }


    /**
     * Set lettrage
     *
     * @param string $lettrage
     * @return JournalBanque
     */
    public function setLettrage($lettrage)
    {
        $this->lettrage = $lettrage;

        return $this;
    }

    /**
     * Get lettrage
     *
     * @return string 
     */
    public function getLettrage()
    {
        return $this->lettrage;
    }

    /**
     * Set stringAnalytique
     *
     * @param string $stringAnalytique
     * @return JournalBanque
     */
    public function setStringAnalytique($stringAnalytique)
    {
        $this->stringAnalytique = $stringAnalytique;

        return $this;
    }

    /**
     * Get stringAnalytique
     *
     * @return string 
     */
    public function getStringAnalytique()
    {
        return $this->stringAnalytique;
    }

    /**
     * Set numEcriture
     *
     * @param string $numEcriture
     * @return JournalBanque
     */
    public function setNumEcriture($numEcriture)
    {
        $this->numEcriture = $numEcriture;

        return $this;
    }

    /**
     * Get numEcriture
     *
     * @return string 
     */
    public function getNumEcriture()
    {
        return $this->numEcriture;
    }

    public function getMontant(){
        if($this->debit != null & $this->debit != 0){
            return $this->debit;
        }
        return $this->credit;
    }
    

    /**
     * Set commentaire
     *
     * @param string $commentaire
     * @return JournalBanque
     */
    public function setCommentaire($commentaire)
    {
        $this->commentaire = $commentaire;

        return $this;
    }

    /**
     * Get commentaire
     *
     * @return string 
     */
    public function getCommentaire()
    {
        return $this->commentaire;
    }
}
