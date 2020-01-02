<?php

namespace App\Entity\CRM;

use Doctrine\ORM\Mapping as ORM;

/**
 * BonCommande
 *
 * @ORM\Table(name="bon_commande")
 * @ORM\Entity(repositoryClass="App\Entity\CRM\BonCommandeRepository")
 */
class BonCommande
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
     * @ORM\Column(name="num", type="string", length=255)
     */
    private $num;

    /**
     * @var integer
     *
     * @ORM\Column(name="montant", type="integer")
     */
    private $montant;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\CRM\Opportunite", inversedBy="bonsCommande")
     * @ORM\JoinColumn(nullable=false)
     */
    private $actionCommerciale;


    /**
     * @ORM\OneToMany(targetEntity="App\Entity\CRM\DocumentPrix", mappedBy="bonCommande")
     * @ORM\JoinColumn(nullable=true)
     */
    private $factures;

    /**
     * @var boolean
     *
     * @ORM\Column(name="frais_refacturables", type="boolean", nullable=false)
     */
    private $fraisRefacturables = false;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->factures = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return BonCommande
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
     * Set montant
     *
     * @param integer $montant
     * @return BonCommande
     */
    public function setMontant($montant)
    {
        $this->montant = $montant;

        return $this;
    }

    /**
     * Get montant
     *
     * @return integer 
     */
    public function getMontant()
    {
        return $this->montant;
    }

    /**
     * Get montant monetaire
     *
     * @return float
     */
    public function getMontantMonetaire()
    {
        return $this->montant/100;
    }

    /**
     * Get montant montaire
     *
     * @return integer
     */
    public function setMontantMonetaire($montant)
    {
        return $this->montant = $montant*100;
    }

    /**
     * Set actionCommerciale
     *
     * @param \App\Entity\CRM\Opportunite $actionCommerciale
     * @return BonCommande
     */
    public function setActionCommerciale(\App\Entity\CRM\Opportunite $actionCommerciale)
    {
        $this->actionCommerciale = $actionCommerciale;

        return $this;
    }

    /**
     * Get actionCommerciale
     *
     * @return \App\Entity\CRM\Opportunite
     */
    public function getActionCommerciale()
    {
        return $this->actionCommerciale;
    }

   
    /**
     * Get total facture
     *
     * @return \App\Entity\CRM\DocumentPrix
     */
    public function getTotalFacture()
    {
        $montant = 0;
        foreach($this->factures as $facture){
            if(false == $facture->getFactureFrais()){
                $montant+= intval(strval($facture->getTotalHTMoinsAvoirs()*100));
            }
        }
        return $montant;
       
    }

    /**
     * Get total facture monetaire
     *
     * @return \App\Entity\CRM\DocumentPrix
     */
    public function getTotalFactureMonetaire()
    {
        $montant = 0;
        foreach($this->factures as $facture){
            if(false == $facture->getFactureFrais()){
                $montant+= $facture->getTotalHTMoinsAvoirs();
            }
        }
        return $montant;
       
    }
    
    /**
     * Add factures
     *
     * @param \App\Entity\CRM\DocumentPrix $factures
     * @return BonCommande
     */
    public function addFacture(\App\Entity\CRM\DocumentPrix $factures)
    {
        $this->factures[] = $factures;

        return $this;
    }

    /**
     * Remove factures
     *
     * @param \App\Entity\CRM\DocumentPrix $factures
     */
    public function removeFacture(\App\Entity\CRM\DocumentPrix $factures)
    {
        $this->factures->removeElement($factures);
    }

    /**
     * Get factures
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getFactures()
    {
        return $this->factures;
    }


    /**
     * Set fraisRefacturables
     *
     * @param boolean $fraisRefacturables
     * @return BonCommande
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

    /**
     * Get factures de frais
     *
     * @return \App\Entity\CRM\DocumentPrix
     */
    public function getFacturesFrais()
    {
        $facturesFrais = array();
        foreach($this->factures as $facture){
            if(true === $facture->getFactureFrais()){
                $facturesFrais[] = $facture;
            }
        }
        return $facturesFrais;
       
    }

    public function getTotalFrais(){
        $total = 0;
        foreach($this->factures as $facture){
            if(true === $facture->getFactureFrais()){
                $total+= $facture->getTotalHT();
            }
        }
        return $total;
    }

    public function addMontantMonetaire($toAdd){
        $toAdd = $toAdd*100;
        $this->montant= $this->montant+$toAdd;
    }
}
