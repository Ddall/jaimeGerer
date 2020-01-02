<?php

namespace App\Entity\CRM;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Produit
 *
 * @ORM\Table(name="produit")
 * @ORM\Entity(repositoryClass="App\Entity\CRM\ProduitRepository")
 */
class Produit
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
     * @ORM\Column(name="nom", type="string", length=255)
     * @Assert\NotBlank()
     */
    private $nom;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * @var float
     *
     * @ORM\Column(name="tarifUnitaire", type="float")
     * @Assert\NotBlank()
     */
    private $tarifUnitaire;

    /**
     * @var float
     *
     * @ORM\Column(name="quantite", type="float")
     * @Assert\NotBlank()
     */
    private $quantite;

    /**
     * @var float
     *
     * @ORM\Column(name="remise", type="float", nullable=true)
     */
    private $remise;
    
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\CRM\DocumentPrix", inversedBy="produits")
     * @ORM\JoinColumn(nullable=true, onDelete="CASCADE")
     * @Assert\NotBlank()
     */
    private $documentPrix;
    
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Settings")
     * @ORM\JoinColumn(nullable=true)
     */
    private $type;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\CRM\Frais", inversedBy="produit")
     * @ORM\JoinColumn(nullable=true)
     */
    private $frais;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\NDF\Recu", inversedBy="produit")
     * @ORM\JoinColumn(nullable=true)
     */
    private $recu;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\CRM\SousTraitanceRepartition", inversedBy="produit")
     * @ORM\JoinColumn(nullable=true)
     */
    private $sousTraitanceRepartition;


    public function __toString(){
        return $this->nom.' : '.number_format($this->getTotal(), 2, ',', ' ').' €';
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
     * Set nom
     *
     * @param string $nom
     * @return Produit
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
     * Set description
     *
     * @param string $description
     * @return Produit
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string 
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set tarifUnitaire
     *
     * @param float $tarifUnitaire
     * @return Produit
     */
    public function setTarifUnitaire($tarifUnitaire)
    {
        $this->tarifUnitaire = $tarifUnitaire;

        return $this;
    }

    /**
     * Get tarifUnitaire
     *
     * @return float 
     */
    public function getTarifUnitaire()
    {
        return $this->tarifUnitaire;
    }

    /**
     * Set quantite
     *
     * @param float $quantite
     * @return Produit
     */
    public function setQuantite($quantite)
    {
        $this->quantite = $quantite;

        return $this;
    }

    /**
     * Get quantite
     *
     * @return float 
     */
    public function getQuantite()
    {
        return $this->quantite;
    }

    /**
     * Set remise
     *
     * @param float $remise
     * @return Produit
     */
    public function setRemise($remise)
    {
        $this->remise = $remise;

        return $this;
    }

    /**
     * Get remise
     *
     * @return float 
     */
    public function getRemise()
    {
        return $this->remise;
    }

    /**
     * Set type
     *
     * @param \App\Entity\Settings $type
     * @return Produit
     */
    public function setType(\App\Entity\Settings $type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return \App\Entity\Settings
     */
    public function getType()
    {
        return $this->type;
    }
    
    public function getMontant(){
    	return round($this->tarifUnitaire*$this->quantite, 2);
    }
    
    public function getTotal(){
    	return round($this->tarifUnitaire*$this->quantite-$this->remise, 2);
    }

    /**
     * Set documentPrix
     *
     * @param \App\Entity\CRM\DocumentPrix $documentPrix
     * @return Produit
     */
    public function setDocumentPrix(\App\Entity\CRM\DocumentPrix $documentPrix)
    {
        $this->documentPrix = $documentPrix;
        return $this;
    }

    /**
     * Get documentPrix
     *
     * @return \App\Entity\CRM\DocumentPrix
     */
    public function getDocumentPrix()
    {
        return $this->documentPrix;
    }
    
    public function __clone() {
    	if ($this->id) {
    		$this->setId(null);
    	}
    }
    
	public function setId($id) {
		$this->id = $id;
		return $this;
	}
	

    /**
     * Set frais
     *
     * @param boolean $frais
     * @return Produit
     */
    public function setFrais($frais)
    {
        $frais->setProduit($this);        
        $this->frais = $frais;
        return $this;
    }

    /**
     * Get frais
     *
     * @return boolean 
     */
    public function getFrais()
    {
        return $this->frais;
    }


    /**
     * Set recu
     *
     * @param \App\Entity\NDF\Recu $recu
     * @return Produit
     */
    public function setRecu(\App\Entity\NDF\Recu $recu = null)
    {
        $recu->setProduit($this); 
        $this->recu = $recu;

        return $this;
    }

    /**
     * Get recu
     *
     * @return \App\Entity\NDF\Recu
     */
    public function getRecu()
    {
        return $this->recu;
    }

    /**
     * Set sousTraitanceRepartition
     *
     * @param \App\Entity\CRM\SousTraitanceRepartition $sousTraitanceRepartition
     * @return Produit
     */
    public function setSousTraitanceRepartition(\App\Entity\CRM\SousTraitanceRepartition $sousTraitanceRepartition = null)
    {
        $sousTraitanceRepartition->setProduit($this); 
        $this->sousTraitanceRepartition = $sousTraitanceRepartition;

        return $this;
    }

    /**
     * Get sousTraitanceRepartition
     *
     * @return \App\Entity\CRM\SousTraitanceRepartition
     */
    public function getSousTraitanceRepartition()
    {
        return $this->sousTraitanceRepartition;
    }
}
