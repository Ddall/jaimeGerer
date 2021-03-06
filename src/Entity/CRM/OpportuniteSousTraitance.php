<?php

namespace App\Entity\CRM;

use Doctrine\ORM\Mapping as ORM;

/**
 * OpportuniteSousTraitance
 *
 * @ORM\Table(name="opportunite_sous_traitance")
 * @ORM\Entity(repositoryClass="App\Entity\CRM\OpportuniteSousTraitanceRepository")
 */
class OpportuniteSousTraitance
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
     * @ORM\Column(name="sousTraitant", type="string", length=255)
     */
    private $sousTraitant;

    /**
     * @var string
     *
     * @ORM\Column(name="typeForfait", type="string", length=255)
     */
    private $typeForfait;

    /**
     * @var integer
     *
     * @ORM\Column(name="montantGlobal", type="integer", nullable=true)
     */
    private $montantGlobal = null;

    /**
     * @var string
     *
     * @ORM\Column(name="tarifJour", type="integer", nullable=true)
     */
    private $tarifJour = null;

    /**
     * @var integer
     *
     * @ORM\Column(name="nbJours", type="integer", nullable=true)
     */
    private $nbJours = null;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\CRM\Opportunite", inversedBy="opportuniteSousTraitances")
     * @ORM\JoinColumn(nullable=false)
     */
    private $opportunite;

    /**
    * @ORM\OneToMany(targetEntity="App\Entity\Compta\DepenseSousTraitance", mappedBy="sousTraitance")
    */
    private $depenses;


    /**
    *
    * @ORM\OneToMany(targetEntity="App\Entity\CRM\SousTraitanceRepartition", mappedBy="opportuniteSousTraitance", cascade={"persist", "remove"}, orphanRemoval=true)
    *
    */
    private $repartitions;

    /**
     * @var boolean
     *
     * @ORM\Column(name="frais_refacturables", type="boolean", nullable=false)
     */
    private $fraisRefacturables = false;

    /**
     * @var string
     *
     * @ORM\Column(name="ht_prix_net", type="string", nullable=false)
     */
    private $htPrixNet = 'HT';


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->depenses = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set sousTraitant
     *
     * @param string $sousTraitant
     * @return OpportuniteSousTraitance
     */
    public function setSousTraitant($sousTraitant)
    {
        $this->sousTraitant = $sousTraitant;

        return $this;
    }

    /**
     * Get sousTraitant
     *
     * @return string
     */
    public function getSousTraitant()
    {
        return $this->sousTraitant;
    }

    /**
     * Set montantGlobal
     *
     * @param integer $montantGlobal
     * @return OpportuniteSousTraitance
     */
    public function setMontantGlobal($montantGlobal)
    {
        $this->montantGlobal = $montantGlobal;

        return $this;
    }

    /**
     * Get montantGlobal
     *
     * @return integer
     */
    public function getMontantGlobal()
    {
        return $this->montantGlobal;
    }

    /**
     * Set tarifJour
     *
     * @param integer $tarifJour
     * @return OpportuniteSousTraitance
     */
    public function setTarifJour($tarifJour)
    {
        $this->tarifJour = $tarifJour;

        return $this;
    }

    /**
     * Get tarifJour
     *
     * @return integer
     */
    public function getTarifJour()
    {
        return $this->tarifJour;
    }

    /**
     * Set nbJours
     *
     * @param integer $nbJours
     * @return OpportuniteSousTraitance
     */
    public function setNbJours($nbJours)
    {
        $this->nbJours = $nbJours;

        return $this;
    }

    /**
     * Get nbJours
     *
     * @return integer
     */
    public function getNbJours()
    {
        return $this->nbJours;
    }

    /**
     * Set opportunite
     *
     * @param \App\Entity\CRM\Opportunite $opportunite
     * @return OpportuniteSousTraitance
     */
    public function setOpportunite(\App\Entity\CRM\Opportunite $opportunite)
    {
        $this->opportunite = $opportunite;

        return $this;
    }

    /**
     * Get opportunite
     *
     * @return \App\Entity\CRM\Opportunite
     */
    public function getOpportunite()
    {
        return $this->opportunite;
    }

    /**
     * Set typeForfait
     *
     * @param string $typeForfait
     * @return OpportuniteSousTraitance
     */
    public function setTypeForfait($typeForfait)
    {
        $this->typeForfait = $typeForfait;

        return $this;
    }

    /**
     * Get typeForfait
     *
     * @return string
     */
    public function getTypeForfait()
    {
        return $this->typeForfait;
    }

    /**
     * Get montant monetaire
     *
     * @return float
     */
    public function getMontantMonetaire()
    {
        $montant = 0;
        
        if($this->typeForfait == "GLOBAL"){
           $montant+= ($this->montantGlobal/100);
        } else {
            $montant+= ($this->nbJours*$this->tarifJour/100);
        }
        
        return $montant;
    }

    /**
     * Get montant montaire
     *
     * @return integer
     */
    public function setMontantMonetaire($montant)
    {
      if($this->typeForfait == "GLOBAL"){
        return $this->montantGlobal = $montant*100;
      }
      return $this->tarifJour = $montant*100;
    }

    /**
     * Get montant global monetaire
     *
     * @return float
     */
    public function getMontantGlobalMonetaire()
    {
        return $this->montantGlobal/100;
    }

    /**
     * Get montant global montaire
     *
     * @return integer
     */
    public function setMontantGlobalMonetaire($montant)
    {
      return $this->montantGlobal = $montant*100;
    }

    /**
     * Get tarif jour monetaire monetaire
     *
     * @return float
     */
    public function getTarifJourMonetaire()
    {
        return $this->tarifJour/100;
    }

    /**
     * Get tarif jour montaire
     *
     * @return integer
     */
    public function setTarifJourMonetaire($montant)
    {
      return $this->tarifJour = $montant*100;
    }


    public function __toString(){
      return $this->opportunite->getNom().' - '.$this->sousTraitant;
    }


    public function getTotalFacture(){
      $totalFacture = 0;
      foreach($this->depenses as $depense){
        $totalFacture+=$depense->getMontantMonetaire();
      }
      return $totalFacture;
    }

    public function getResteAFacturer(){
      $reste = $this->getMontantMonetaireAvecFrais()-$this->getTotalFacture();
      return $reste;
    }

    public function getNomEtMontant(){
        $str=  $this->sousTraitant.' - '.
              $this->opportunite->getCompte()->getNom().' - '.$this->opportunite->getNom().' : '.
              $this->getMontantMonetaireAvecFrais().'€ ';

        if($this->htPrixNet){
            $str.=$this->htPrixNet;
        }

        $str.=' (reste à facturer : '.
              $this->getResteAFacturer().' € ';

        if($this->htPrixNet){
            $str.=$this->htPrixNet;
        }

        $str.=')';

        return $str;
    }

    /**
     * Add repartitions
     *
     * @param \App\Entity\CRM\SousTraitanceRepartition $repartitions
     * @return OpportuniteSousTraitance
     */
    public function addRepartition(\App\Entity\CRM\SousTraitanceRepartition $repartitions)
    {
        $this->repartitions[] = $repartitions;
        $repartitions->setOpportuniteSousTraitance($this);

        return $this;
    }

    /**
     * Remove repartitions
     *
     * @param \App\Entity\CRM\SousTraitanceRepartition $repartitions
     */
    public function removeRepartition(\App\Entity\CRM\SousTraitanceRepartition $repartitions)
    {
        $this->repartitions->removeElement($repartitions);
    }

    /**
     * Get repartitions
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getRepartitions()
    {
        return $this->repartitions;
    }

    /**
     * Add depenses
     *
     * @param \App\Entity\Compta\DepenseSousTraitance $depenses
     * @return OpportuniteSousTraitance
     */
    public function addDepense(\App\Entity\Compta\DepenseSousTraitance $depenses)
    {
        $this->depenses[] = $depenses;

        return $this;
    }

    /**
     * Remove depenses
     *
     * @param \App\Entity\Compta\DepenseSousTraitance $depenses
     */
    public function removeDepense(\App\Entity\Compta\DepenseSousTraitance $depenses)
    {
        $this->depenses->removeElement($depenses);
    }

    /**
     * Get depenses
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getDepenses()
    {
        return $this->depenses;
    }

    /**
     * Set fraisRefacturables
     *
     * @param boolean $fraisRefacturables
     * @return OpportuniteSousTraitance
     */
    public function setFraisRefacturables($fraisRefacturables)
    {
        $this->fraisRefacturables = $fraisRefacturables;

        return $this;
    }

    /**
     * Get fraisRefacturables
     *
     * @return boolean 
     */
    public function getFraisRefacturables()
    {
        return $this->fraisRefacturables;
    }

    public function getMontantFraisMonetaire(){

        $frais = 0;
        foreach($this->repartitions as $repartition){
            $frais+= $repartition->getFrais();
        }
        return $frais/100;
    }

    public function getMontantMonetaireAvecFrais(){
        return $this->getMontantMonetaire() + $this->getMontantFraisMonetaire();
    }

    /**
     * Set htPrixNet
     *
     * @param string $htPrixNet
     * @return OpportuniteSousTraitance
     */
    public function setHtPrixNet($htPrixNet)
    {
        $this->htPrixNet = $htPrixNet;

        return $this;
    }

    /**
     * Get htPrixNet
     *
     * @return string 
     */
    public function getHtPrixNet()
    {
        return $this->htPrixNet;
    }
}
