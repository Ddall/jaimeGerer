<?php

namespace App\Entity\NDF;

use Doctrine\ORM\Mapping as ORM;

/**
 * Recu
 *
 * @ORM\Table(name="recu")
 * @ORM\Entity(repositoryClass="App\Entity\RecuRepository")
 */
class Recu
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
     * @ORM\Column(name="file", type="string", length=255, nullable=true)
     */
    private $file;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="date", nullable=true)
     */
    private $date;

    /**
     * @var string
     *
     * @ORM\Column(name="fournisseur", type="string", length=255, nullable=true)
     */
    private $fournisseur;

    /**
     * @var float
     *
     * @ORM\Column(name="montantHT", type="float", nullable=true)
     */
    private $montantHT;

    /**
     * @var float
     *
     * @ORM\Column(name="tva", type="float", nullable=true)
     */
    private $tva;

    /**
     * @var float
     *
     * @ORM\Column(name="montantTTC", type="float", nullable=true)
     */
    private $montantTTC;

    /**
     * @var string
     *
     * @ORM\Column(name="etat", type="string", length=10, nullable=true)
     */
    private $etat;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Settings")
     * @ORM\JoinColumn(nullable=true)
     */
    private $analytique;

     /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Compta\CompteComptable")
     * @ORM\JoinColumn(nullable=true)
     */
    private $compteComptable;

    /**
    * @ORM\OneToOne(targetEntity="App\Entity\Compta\LigneDepense", cascade={"remove"})
    * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
    */
   private $ligneDepense;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="dateCreation", type="date")
     */
    private $dateCreation;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="dateEdition", type="date", nullable=true)
     */
    private $dateEdition;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(nullable=false)
     */
    private $userCreation;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(nullable=true)
     */
    private $userEdition;

    /**
     * @var string
     *
     * @ORM\Column(name="libelle", type="string", length=255, nullable=true)
     */
    private $libelle;


    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\CRM\Opportunite", inversedBy="recus")
     * @ORM\JoinColumn(nullable=true)
     */
    private $actionCommerciale = null;

    /**
     * @var boolean
     *
     * @ORM\Column(name="refacturable", type="boolean", nullable=false)
     */
    private $refacturable = false;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\CRM\Produit", mappedBy="recu")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private $produit;

    /**
     * @var string
     *
     * @ORM\Column(name="trajet", type="string", length=255, nullable=true)
     */
    private $trajet;

    /**
     * @var integer
     *
     * @ORM\Column(name="distance", type="integer", nullable=true)
     */
    private $distance;

    /**
     * @var boolean
     *
     * @ORM\Column(name="deplacementVoiture", type="boolean", nullable=false)
     */
    private $deplacementVoiture = false;

    /**
     * @var string
     *
     * @ORM\Column(name="modele_voiture", type="string", length=255, nullable=true)
     */
    private $modeleVoiture;

    /**
     * @var integer
     *
     * @ORM\Column(name="puissance_voiture", type="integer", nullable=true)
     */
    private $puissanceVoiture;

    /**
     * @var string
     *
     * @ORM\Column(name="marque_voiture", type="string", length=255, nullable=true)
     */
    private $marqueVoiture;

    /**
     * @var string
     *
     * @ORM\Column(name="immatriculation_voiture", type="string", length=255, nullable=true)
     */
    private $immatriculationVoiture;

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
     * Set file
     *
     * @param string $file
     * @return Recu
     */
    public function setFile($file)
    {
        $this->file = $file;

        return $this;
    }

    /**
     * Get file
     *
     * @return string
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     * @return Recu
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
     * Set fournisseur
     *
     * @param string $fournisseur
     * @return Recu
     */
    public function setFournisseur($fournisseur)
    {
        $this->fournisseur = $fournisseur;

        return $this;
    }

    /**
     * Get fournisseur
     *
     * @return string
     */
    public function getFournisseur()
    {
        return $this->fournisseur;
    }

    /**
     * Set montantHT
     *
     * @param float $montantHT
     * @return Recu
     */
    public function setMontantHT($montantHT)
    {
        $this->montantHT = $montantHT;

        return $this;
    }

    /**
     * Get montantHT
     *
     * @return float
     */
    public function getMontantHT()
    {
        return $this->montantHT;
    }

    /**
     * Set tva
     *
     * @param float $tva
     * @return Recu
     */
    public function setTva($tva)
    {
        $this->tva = $tva;

        return $this;
    }

    /**
     * Get tva
     *
     * @return float
     */
    public function getTva()
    {
        return $this->tva;
    }

    /**
     * Set montantTTC
     *
     * @param float $montantTTC
     * @return Recu
     */
    public function setMontantTTC($montantTTC)
    {
        $this->montantTTC = $montantTTC;

        return $this;
    }

    /**
     * Get montantTTC
     *
     * @return float
     */
    public function getMontantTTC()
    {
        return $this->montantTTC;
    }

    /**
     * Set etat
     *
     * @param string $etat
     * @return Recu
     */
    public function setEtat($etat)
    {
        $this->etat = $etat;

        return $this;
    }

    /**
     * Get etat
     *
     * @return string
     */
    public function getEtat()
    {
        return $this->etat;
    }

    /**
     * Set dateCreation
     *
     * @param \DateTime $dateCreation
     * @return Recu
     */
    public function setDateCreation($dateCreation)
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }

    /**
     * Get dateCreation
     *
     * @return \DateTime
     */
    public function getDateCreation()
    {
        return $this->dateCreation;
    }

    /**
     * Set dateEdition
     *
     * @param \DateTime $dateEdition
     * @return Recu
     */
    public function setDateEdition($dateEdition)
    {
        $this->dateEdition = $dateEdition;

        return $this;
    }

    /**
     * Get dateEdition
     *
     * @return \DateTime
     */
    public function getDateEdition()
    {
        return $this->dateEdition;
    }

    /**
     * Set analytique
     *
     * @param \App\Entity\Settings $analytique
     * @return Recu
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
     * Set compteComptable
     *
     * @param \App\Entity\Compta\CompteComptable $compteComptable
     * @return Recu
     */
    public function setCompteComptable(\App\Entity\Compta\CompteComptable $compteComptable = null)
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
     * Set userCreation
     *
     * @param \App\Entity\User $userCreation
     * @return Recu
     */
    public function setUserCreation(\App\Entity\User $userCreation)
    {
        $this->userCreation = $userCreation;

        return $this;
    }

    /**
     * Get userCreation
     *
     * @return \App\Entity\User
     */
    public function getUserCreation()
    {
        return $this->userCreation;
    }

    /**
     * Set userEdition
     *
     * @param \App\Entity\User $userEdition
     * @return Recu
     */
    public function setUserEdition(\App\Entity\User $userEdition = null)
    {
        $this->userEdition = $userEdition;

        return $this;
    }

    /**
     * Get userEdition
     *
     * @return \App\Entity\User
     */
    public function getUserEdition()
    {
        return $this->userEdition;
    }

    /**
     * Set user
     *
     * @param \App\Entity\User $user
     * @return Recu
     */
    public function setUser(\App\Entity\User $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \App\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    public function getPath(){
      return 'public/upload/ndf/'.$this->user->getCompany()->getId().'/'.$this->user->getId();
    }

    public function getFileType(){
		    return pathinfo($this->getPath().'/'.$this->file, PATHINFO_EXTENSION);
    }

    public function __toString(){
        if($this->deplacementVoiture === true){
             return $this->date->format("d/m/Y").' | Trajet '.$this->getTrajet().' | '.$this->getMontantTTC().' €';
        }
       return $this->date->format("d/m/Y").' | '.$this->getFournisseur().' | '.$this->getMontantTTC().' €';
    }


    /**
     * Set ligneDepense
     *
     * @param \App\Entity\Compta\LigneDepense $ligneDepense
     * @return Recu
     */
    public function setLigneDepense(\App\Entity\Compta\LigneDepense $ligneDepense = null)
    {
        $this->ligneDepense = $ligneDepense;

        return $this;
    }

    /**
     * Get ligneDepense
     *
     * @return \App\Entity\Compta\LigneDepense
     */
    public function getLigneDepense()
    {
        return $this->ligneDepense;
    }

    /**
     * Set libelle
     *
     * @param string $libelle
     * @return Recu
     */
    public function setLibelle($libelle)
    {
        $this->libelle = $libelle;

        return $this;
    }

    /**
     * Get libelle
     *
     * @return string 
     */
    public function getLibelle()
    {
        return $this->libelle;
    }

    /**
     * Set refacturable
     *
     * @param boolean $refacturable
     * @return Recu
     */
    public function setRefacturable($refacturable)
    {
        $this->refacturable = $refacturable;

        return $this;
    }

    /**
     * Get refacturable
     *
     * @return boolean 
     */
    public function getRefacturable()
    {
        return $this->refacturable;
    }

    /**
     * Set actionCommerciale
     *
     * @param \App\Entity\CRM\Opportunite $actionCommerciale
     * @return Recu
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
     * Set actionCommerciale to null
     *
     * @return Recu
     */
    public function removeActionCommerciale()
    {
        $this->actionCommerciale = null;

        return $this;
    }

    /**
     * Set produit
     *
     * @param \App\Entity\CRM\Produit $produit
     * @return Recu
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

    /**
     * Set trajet
     *
     * @param string $trajet
     * @return Recu
     */
    public function setTrajet($trajet)
    {
        $this->trajet = $trajet;

        return $this;
    }

    /**
     * Get trajet
     *
     * @return string 
     */
    public function getTrajet()
    {
        return $this->trajet;
    }

    /**
     * Set distance
     *
     * @param integer $distance
     * @return Recu
     */
    public function setDistance($distance)
    {
        $this->distance = $distance;

        return $this;
    }

    /**
     * Get distance
     *
     * @return integer 
     */
    public function getDistance()
    {
        return $this->distance;
    }

    /**
     * Set deplacementVoiture
     *
     * @param boolean $deplacementVoiture
     * @return Recu
     */
    public function setDeplacementVoiture($deplacementVoiture)
    {
        $this->deplacementVoiture = $deplacementVoiture;

        return $this;
    }

    /**
     * Get deplacementVoiture
     *
     * @return boolean 
     */
    public function getDeplacementVoiture()
    {
        return $this->deplacementVoiture;
    }

    /**
     * Set modeleVoiture
     *
     * @param string $modeleVoiture
     * @return Recu
     */
    public function setModeleVoiture($modeleVoiture)
    {
        $this->modeleVoiture = $modeleVoiture;

        return $this;
    }

    /**
     * Get modeleVoiture
     *
     * @return string 
     */
    public function getModeleVoiture()
    {
        return $this->modeleVoiture;
    }

    /**
     * Set puissanceVoiture
     *
     * @param integer $puissanceVoiture
     * @return Recu
     */
    public function setPuissanceVoiture($puissanceVoiture)
    {
        $this->puissanceVoiture = $puissanceVoiture;

        return $this;
    }

    /**
     * Get puissanceVoiture
     *
     * @return integer 
     */
    public function getPuissanceVoiture()
    {
        return $this->puissanceVoiture;
    }

    /**
     * Set marqueVoiture
     *
     * @param string $marqueVoiture
     * @return Recu
     */
    public function setMarqueVoiture($marqueVoiture)
    {
        $this->marqueVoiture = $marqueVoiture;

        return $this;
    }

    /**
     * Get marqueVoiture
     *
     * @return string 
     */
    public function getMarqueVoiture()
    {
        return $this->marqueVoiture;
    }

    /**
     * Set immatriculationVoiture
     *
     * @param string $immatriculationVoiture
     * @return Recu
     */
    public function setImmatriculationVoiture($immatriculationVoiture)
    {
        $this->immatriculationVoiture = $immatriculationVoiture;

        return $this;
    }

    /**
     * Get immatriculationVoiture
     *
     * @return string 
     */
    public function getImmatriculationVoiture()
    {
        return $this->immatriculationVoiture;
    }
}
