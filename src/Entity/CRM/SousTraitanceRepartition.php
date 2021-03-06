<?php

namespace App\Entity\CRM;

use Doctrine\ORM\Mapping as ORM;

/**
 * SousTraitanceRepartition
 *
 * @ORM\Table(name="sous_traitance_repartition")
 * @ORM\Entity(repositoryClass="App\Entity\CRM\SousTraitanceRepartitionRepository")
 */
class SousTraitanceRepartition
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
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime")
     */
    private $date;

    /**
     * @var integer
     *
     * @ORM\Column(name="montant", type="integer")
     */
    private $montant;

    /**
     * @var integer
     *
     * @ORM\Column(name="frais", type="integer", nullable=true)
     */
    private $frais = null;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\CRM\OpportuniteSousTraitance", inversedBy="repartitions")
     * @ORM\JoinColumn(nullable=false)
     */
    private $opportuniteSousTraitance;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\CRM\Produit", mappedBy="sousTraitanceRepartition")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL"))
     */
    private $produit;


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
     * Set date
     *
     * @param \DateTime $date
     * @return SousTraitanceRepartition
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
     * Set montant
     *
     * @param integer $montant
     * @return SousTraitanceRepartition
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
     * Set opportuniteSousTraitance
     *
     * @param \App\Entity\CRM\OpportuniteSousTraitance $opportuniteSousTraitance
     * @return SousTraitanceRepartition
     */
    public function setOpportuniteSousTraitance(\App\Entity\CRM\OpportuniteSousTraitance $opportuniteSousTraitance)
    {
        $this->opportuniteSousTraitance = $opportuniteSousTraitance;

        return $this;
    }

    /**
     * Get opportuniteSousTraitance
     *
     * @return \App\Entity\CRM\OpportuniteSousTraitance
     */
    public function getOpportuniteSousTraitance()
    {
        return $this->opportuniteSousTraitance;
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
     * Set montant monetaire
     *
     * @return integer
     */
    public function setMontantMonetaire($montant)
    {
        return $this->montant = $montant*100;
    }

    /**
     * Set frais
     *
     * @param integer $frais
     * @return SousTraitanceRepartition
     */
    public function setFrais($frais)
    {
        $this->frais = $frais;

        return $this;
    }

    /**
     * Get frais
     *
     * @return integer 
     */
    public function getFrais()
    {
        return $this->frais;
    }

    /**
     * Get frais monetaire
     *
     * @return float
     */
    public function getFraisMonetaire()
    {
        return $this->frais/100;
    }

    /**
     * Set frais monetaire
     *
     * @return integer
     */
    public function setFraisMonetaire($frais)
    {
        return $this->frais = $frais*100;
    }

    /**
     * Set produit
     *
     * @param \App\Entity\CRM\Produit $produit
     * @return SousTraitanceRepartition
     */
    public function setProduit(\App\Entity\CRM\Produit $produit = null)
    {
        $this->produit = $produit;

        return $this;
    }

    /**
     * Get produit
     *
     * @return \App\Entity\CRM\Produit
     */
    public function getProduit()
    {
        return $this->produit;
    }
}
