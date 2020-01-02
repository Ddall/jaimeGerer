<?php

namespace App\Entity\Compta;

use Doctrine\ORM\Mapping as ORM;

/**
 * AffectationDiverse
 *
 * @ORM\Table(name="affectation_diverse")
 * @ORM\Entity(repositoryClass="App\Entity\Compta\AffectationDiverseRepository")
 */
class AffectationDiverse
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
     * @ORM\Column(name="type", type="string", length=5)
     */
    private $type;

    /**
     * @var string
     *
     * @ORM\Column(name="nom", type="string", length=255)
     */
    private $nom;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Compta\Rapprochement", mappedBy="affectationDiverse")
     */
    private $rapprochements;
    
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Compta\CompteComptable")
     * @ORM\JoinColumn(nullable=false)
     */
    private $compteComptable;
    
      /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Company")
     * @ORM\JoinColumn(nullable=true)
     */
     private $company;
    
     /**
      * @var boolean
      *
      * @ORM\Column(name="recurrent", type="boolean", nullable=true)
      */
     private $recurrent;
     
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
     * Set type
     *
     * @param string $type
     * @return AffectationDiverse
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string 
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set nom
     *
     * @param string $nom
     * @return AffectationDiverse
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
     * Constructor
     */
    public function __construct()
    {
        $this->rapprochements = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add rapprochements
     *
     * @param \App\Entity\Compta\Rapprochement $rapprochement
     * @return AffectationDiverse
     */
    public function addRapprochement(\App\Entity\Compta\Rapprochement $rapprochement)
    {
    	$rapprochement->setAffectationDiverse($this);
        $this->rapprochements[] = $rapprochement;

        return $this;
    }

    /**
     * Remove rapprochements
     *
     * @param \App\Entity\Compta\Rapprochement $rapprochements
     */
    public function removeRapprochement(\App\Entity\Compta\Rapprochement $rapprochements)
    {
        $this->rapprochements->removeElement($rapprochements);
    }

    /**
     * Get rapprochements
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getRapprochements()
    {
        return $this->rapprochements;
    }

    /**
     * Set compteComptable
     *
     * @param \App\Entity\Compta\CompteComptable $compteComptable
     * @return AffectationDiverse
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
    
    public function __toString(){
    	return $this->nom;
    }

    /**
     * Set company
     *
     * @param \App\Entity\Company $company
     * @return AffectationDiverse
     */
    public function setCompany(\App\Entity\Company $company)
    {
        $this->company = $company;

        return $this;
    }

    /**
     * Get company
     *
     * @return \App\Entity\Company
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * Set recurrent
     *
     * @param boolean $recurrent
     * @return AffectationDiverse
     */
    public function setRecurrent($recurrent)
    {
        $this->recurrent = $recurrent;

        return $this;
    }

    /**
     * Get recurrent
     *
     * @return boolean 
     */
    public function getRecurrent()
    {
        return $this->recurrent;
    }
}
