<?php

namespace AppBundle\Entity\CRM;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * DocumentPrix
 *
 * @ORM\Table(name="documentprix")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\CRM\DocumentPrixRepository")
 */
class DocumentPrix
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
     * @ORM\Column(name="objet", type="string", length=255)
     * @Assert\NotBlank()
     */
    private $objet;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=255)
     * @Assert\NotBlank()
     */
    private $type;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="dateValidite", type="date")
     * @Assert\NotBlank()
     */
    private $dateValidite;

    /**
     * @var string
     *
     * @ORM\Column(name="adresse", type="string", length=255, nullable=true)
     */
    private $adresse;

    /**
     * @var string
     *
     * @ORM\Column(name="ville", type="string", length=255, nullable=true)
     */
    private $ville;

    /**
     * @var string
     *
     * @ORM\Column(name="code_postal", type="string", length=255, nullable=true)
     */
    private $codePostal;

    /**
     * @var string
     *
     * @ORM\Column(name="region", type="string", length=255, nullable=true)
     */
    private $region;

    /**
     * @var string
     *
     * @ORM\Column(name="pays", type="string", length=255, nullable=true)
     */
    private $pays;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="etatDevis", type="string", length=20, nullable=true)
     */
    private $etatDevis;

    /**
     * @var string
     *
     * @ORM\Column(name="etat", type="string", length=20)
     */
    private $etat = "DRAFT";

    /**
     * @var float
     *
     * @ORM\Column(name="remise", type="float", nullable=true)
     */
    private $remise;

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
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User")
     * @ORM\JoinColumn(nullable=false)
     */
    private $userCreation;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User")
     * @ORM\JoinColumn(nullable=true)
     */
    private $userEdition;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User")
     * @ORM\JoinColumn(nullable=false)
     */
    private $userGestion;

    /**
     * @var decimal
     *
     * @ORM\Column(name="taxe", type="decimal", scale=2, nullable=true)
     */
    private $taxe;

    /**
     * @var decimal
     *
     * @ORM\Column(name="taxePercent", type="decimal", scale=4, nullable=true)
     */
    private $taxePercent;

    /**
     * @var decimal
     *
     * @ORM\Column(name="facturationBelgePercent", type="decimal", scale=4, nullable=true)
     */
    private $facturationBelgePercent;

    /**
     * @var string
     *
     * @ORM\Column(name="num", type="string", length=50, nullable=false)
     */
    private $num;

    /**
     * @var string
     *
     * @ORM\Column(name="numBCInterne", type="string", length=50, nullable=true)
     */
    private $numBCInterne;

    /**
     * @var string
     *
     * @ORM\Column(name="numBCCLient", type="string", length=50, nullable=true)
     */
    private $numBCClient;

    /**
     * @var string
     *
     * @ORM\Column(name="cgv", type="text", nullable=true)
     */
    private $cgv;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\CRM\Compte")
     * @ORM\JoinColumn(nullable=false)
     * @Assert\NotBlank()
     */
    private $compte;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\CRM\Contact")
     * @ORM\JoinColumn(nullable=true)
     */
    private $contact;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\CRM\DocumentPrix")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private $devis;

    /**
    *
    * @ORM\OneToMany(targetEntity="AppBundle\Entity\CRM\Produit", mappedBy="documentPrix", cascade={"persist", "remove"}, orphanRemoval=true)
    *
    */
    private $produits;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Settings")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private $analytique;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Compta\Rapprochement", mappedBy="facture")
     */
    private $rapprochements;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Compta\Avoir", mappedBy="facture")
     */
    private $avoirs;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Compta\Relance", mappedBy="facture")
     */
    private $relances;

    /**
     * @var boolean
     *
     * @ORM\Column(name="compta", type="boolean", nullable=true)
     */
    private $compta;

    /**
   * @ORM\OneToOne(targetEntity="AppBundle\Entity\CRM\Opportunite", mappedBy="devis")
   * @ORM\JoinColumn(nullable=true)
   */
  private $opportunite;

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
     * Set objet
     *
     * @param string $objet
     * @return DocumentPrix
     */
    public function setObjet($objet)
    {
        $this->objet = $objet;

        return $this;
    }

    /**
     * Get objet
     *
     * @return string
     */
    public function getObjet()
    {
        return $this->objet;
    }

    /**
     * Set dateValidite
     *
     * @param \DateTime $dateValidite
     * @return DocumentPrix
     */
    public function setDateValidite($dateValidite)
    {
        $this->dateValidite = $dateValidite;

        return $this;
    }

    /**
     * Get dateValidite
     *
     * @return \DateTime
     */
    public function getDateValidite()
    {
        return $this->dateValidite;
    }

    /**
     * Set adresse
     *
     * @param string $adresse
     * @return DocumentPrix
     */
    public function setAdresse($adresse)
    {
        $this->adresse = $adresse;

        return $this;
    }

    /**
     * Get adresse
     *
     * @return string
     */
    public function getAdresse()
    {
        return $this->adresse;
    }

    /**
     * Set ville
     *
     * @param string $ville
     * @return DocumentPrix
     */
    public function setVille($ville)
    {
        $this->ville = $ville;

        return $this;
    }

    /**
     * Get ville
     *
     * @return string
     */
    public function getVille()
    {
        return $this->ville;
    }

    /**
     * Set codePostal
     *
     * @param string $codePostal
     * @return DocumentPrix
     */
    public function setCodePostal($codePostal)
    {
        $this->codePostal = $codePostal;

        return $this;
    }

    /**
     * Get codePostal
     *
     * @return string
     */
    public function getCodePostal()
    {
        return $this->codePostal;
    }

    /**
     * Set region
     *
     * @param string $region
     * @return DocumentPrix
     */
    public function setRegion($region)
    {
        $this->region = $region;

        return $this;
    }

    /**
     * Get region
     *
     * @return string
     */
    public function getRegion()
    {
        return $this->region;
    }

    /**
     * Set pays
     *
     * @param string $pays
     * @return DocumentPrix
     */
    public function setPays($pays)
    {
        $this->pays = $pays;

        return $this;
    }

    /**
     * Get pays
     *
     * @return string
     */
    public function getPays()
    {
        return $this->pays;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return DocumentPrix
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
     * Set remise
     *
     * @param float $remise
     * @return DocumentPrix
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
     * Set dateCreation
     *
     * @param \DateTime $dateCreation
     * @return DocumentPrix
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
     * @return DocumentPrix
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
     * Set userCreation
     *
     * @param \AppBundle\Entity\User $userCreation
     * @return DocumentPrix
     */
    public function setUserCreation(\AppBundle\Entity\User $userCreation)
    {
        $this->userCreation = $userCreation;

        return $this;
    }

    /**
     * Get userCreation
     *
     * @return \AppBundle\Entity\User
     */
    public function getUserCreation()
    {
        return $this->userCreation;
    }

    /**
     * Set userEdition
     *
     * @param \AppBundle\Entity\User $userEdition
     * @return DocumentPrix
     */
    public function setUserEdition(\AppBundle\Entity\User $userEdition = null)
    {
        $this->userEdition = $userEdition;

        return $this;
    }

    /**
     * Get userEdition
     *
     * @return \AppBundle\Entity\User
     */
    public function getUserEdition()
    {
        return $this->userEdition;
    }

    /**
     * Set userGestion
     *
     * @param \AppBundle\Entity\User $userGestion
     * @return DocumentPrix
     */
    public function setUserGestion(\AppBundle\Entity\User $userGestion)
    {
        $this->userGestion = $userGestion;

        return $this;
    }

    /**
     * Get userGestion
     *
     * @return \AppBundle\Entity\User
     */
    public function getUserGestion()
    {
        return $this->userGestion;
    }

    /**
     * Set compte
     *
     * @param \AppBundle\Entity\CRM\Compte $compte
     * @return DocumentPrix
     */
    public function setCompte($compte)
    {
        $this->compte = $compte;

        return $this;
    }

    /**
     * Get compte
     *
     * @return \AppBundle\Entity\CRM\Compte
     */
    public function getCompte()
    {
        return $this->compte;
    }

    /**
     * Set contact
     *
     * @param \AppBundle\Entity\CRM\Contact $contact
     * @return DocumentPrix
     */
    public function setContact($contact)
    {
        $this->contact = $contact;

        return $this;
    }

    /**
     * Get contact
     *
     * @return \AppBundle\Entity\CRM\Contact
     */
    public function getContact()
    {
        return $this->contact;
    }

    /**
     * Set num
     *
     * @param string $num
     * @return DocumentPrix
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
     * Constructor
     */
    public function __construct($companyId=null, $type=null, $em=null)
    {


    	$this->produits = new ArrayCollection();
    	$this->avoirs = new ArrayCollection();
    	$this->relances = new ArrayCollection();
    	$this->rapprochements = new ArrayCollection();

    	if($type!= null){
    		$this->type = $type;
    	}
    	if($this->cgv == null && $em!=null){
    		$settingsRepository = $em->getRepository('AppBundle:Settings');
    		$cgv = $settingsRepository->findOneBy(array('module' => 'CRM', 'parametre' => 'CGV_'.$this->type, 'company' => $companyId));

    		if($cgv){
    			$this->cgv = $cgv->getValeur();
    		}
    	}

    	if($this->dateCreation == null){
    		$this->dateCreation = new \DateTime(date('Y-m-d'));
    	}

    	if($this->dateValidite == null){
    		$this->dateValidite = new \DateTime(date('Y-m-d', strtotime("+1 month", strtotime(date('Y-m-d')))));
    	}

    }

    public function __toString()
    {
    	return $this->getNum().' - '.$this->getCompte()->getNom().' : '.$this->getTotalHT().'€ HT';
    }

    public function getTotaux(){

    	$sous_total = 0;
    	$ht = 0;
    	$ttc = 0;

     	if(count($this->getProduits()) != 0){
	    	foreach($this->getProduits() as $produit){
	    		$total_produit = $produit->getTarifUnitaire()*$produit->getQuantite()-$produit->getRemise();
	    		$sous_total+=$total_produit;
	    	}

	    	if($this->remise != null){
	    		$ht = $sous_total-$this->remise;
	    	} else {
	    		$ht = $sous_total;
	    	}

            if($this->hasTypeProduit("Panorama")){
                $facturationBelge = $ht*$this->facturationBelgePercent;
                $ht+= $facturationBelge;
            }  

	    	$ttc = $ht+$this->taxe;
    	}
    	$arr_totaux = array();
    	$arr_totaux['sous_total'] = $sous_total;
    	$arr_totaux['HT'] = round($ht, 2);
    	$arr_totaux['TTC'] = round($ttc, 2);

    	return $arr_totaux;
    }


    /**
     * Set taxe
     *
     * @param float $taxe
     * @return DocumentPrix
     */
    public function setTaxe($taxe)
    {
        $this->taxe = $taxe;

        return $this;
    }

    /**
     * Get taxe
     *
     * @return float
     */
    public function getTaxe()
    {
        return $this->taxe;
    }

    /**
     * Add produits
     *
     * @param \AppBundle:CRM\Produit $produit
     * @return DocumentPrix
     */
    public function addProduit(\AppBundle\Entity\CRM\Produit $produit)
    {
    	$produit->setDocumentPrix($this);
        $this->produits[] = $produit;

        return $this;
    }

    /**
     * Remove produits
     *
     * @param \AppBundle:CRM\Produit $produits
     */
    public function removeProduit(\AppBundle\Entity\CRM\Produit $produits)
    {
        $this->produits->removeElement($produits);
    }

    /**
     * Get produits
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getProduits()
    {
        return $this->produits;
    }

    /**
     * Set taxePercent
     *
     * @param float $taxePercent
     * @return DocumentPrix
     */
    public function setTaxePercent($taxePercent)
    {
        $this->taxePercent = $taxePercent;

        return $this;
    }

    /**
     * Get taxePercent
     *
     * @return float
     */
    public function getTaxePercent()
    {
        return $this->taxePercent;
    }

    public function getSousTotal(){
    	$sousTotal = 0;
    	foreach($this->produits as $produit){
    		$sousTotal+= $produit->getTotal();
    	}
    	return $sousTotal;
    }

    public function getTotalHT(){
    	$sousTotal = 0;
    	foreach($this->produits as $produit){
    		$sousTotal+= $produit->getTotal();
    	}

    	$totalHT = $sousTotal - $this->remise;

        if($this->hasTypeProduit("Panorama")){
            $facturationBelge = $totalHT*$this->facturationBelgePercent;
            $totalHT+= $facturationBelge;

        } 
        
    	return round($totalHT, 2);
    }

    public function getTotalTTC(){
    	$sousTotal = 0;
    	foreach($this->produits as $produit){
    		$sousTotal+= $produit->getTotal();
    	}

    	$totalHT = $sousTotal - $this->remise;

        if($this->hasTypeProduit("Panorama")){
            $facturationBelge = $totalHT*$this->facturationBelgePercent;
            $totalTTC = $totalHT + $facturationBelge;
        } else {
            $totalTTC = $totalHT+$this->taxe;
        }

    	return round($totalTTC, 2);
    }

      public function getTotalTTCAsInt(){
        $sousTotal = 0;
        foreach($this->produits as $produit){
            $sousTotal+= $produit->getTotal();
        }

        $totalHT = $sousTotal - $this->remise;

        if($this->hasTypeProduit("Panorama")){
            $totalTTC = $totalHT+$this->getFacturationBelge();
        } else {
            $totalTTC = $totalHT+$this->taxe;
        }

        return intval($totalTTC*100);
    }

    public function getTotalHTMoinsAvoirs(){
      return $this->getTotalHT()-$this->getTotalAvoirsHT();
    }

    public function getTotalTTCMoinsAvoirs(){
      return $this->getTotalTTC()-$this->getTotalAvoirs();
    }

    public function getTotalRapproche(){

    	$total = 0;
   	 	foreach($this->rapprochements as $rapprochement){
    		$total+= $rapprochement->getMouvementBancaire()->getMontant();
    	}
    	return $total;
    }

    public function getTotalRapprocheAsInt(){

        $total = 0;
        foreach($this->rapprochements as $rapprochement){
            $total+= $rapprochement->getMouvementBancaire()->getMontant()*100;
        }
        return intval($total);
    }


    public function getTotalAvoirs(){

    	$total = 0;
    	foreach($this->avoirs as $avoir){
    		$total+= $avoir->getTotalTTC();
    	}
    	return $total;
    }

    public function getTotalAvoirsHT(){

      $total = 0;
      foreach($this->avoirs as $avoir){
        $total+= $avoir->getTotalHT();
      }
      return $total;
    }

    /**
     * Set cgv
     *
     * @param string $cgv
     * @return DocumentPrix
     */
    public function setCgv($cgv)
    {
        $this->cgv = $cgv;

        return $this;
    }

    /**
     * Get cgv
     *
     * @return string
     */
    public function getCgv()
    {
        return $this->cgv;
    }

    /**
     * Set type
     *
     * @param string $type
     * @return DocumentPrix
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
     * Set numBCInterne
     *
     * @param string $numBCInterne
     * @return DocumentPrix
     */
    public function setNumBCInterne($numBCInterne)
    {
        $this->numBCInterne = $numBCInterne;

        return $this;
    }

    /**
     * Get numBCInterne
     *
     * @return string
     */
    public function getNumBCInterne()
    {
        return $this->numBCInterne;
    }

    /**
     * Set numBCClient
     *
     * @param string $numBCClient
     * @return DocumentPrix
     */
    public function setNumBCClient($numBCClient)
    {
        $this->numBCClient = $numBCClient;

        return $this;
    }

    /**
     * Get numBCClient
     *
     * @return string
     */
    public function getNumBCClient()
    {
        return $this->numBCClient;
    }

    /**
     * Set devis
     *
     * @param \AppBundle\Entity\CRM\DocumentPrix $devis
     * @return DocumentPrix
     */
    public function setDevis(\AppBundle\Entity\CRM\DocumentPrix $devis = null)
    {
        $this->devis = $devis;

        return $this;
    }

    /**
     * Get devis
     *
     * @return \AppBundle\Entity\CRM\DocumentPrix
     */
    public function getDevis()
    {
        return $this->devis;
    }

    /**
     * Set etatDevis
     *
     * @param string $etatDevis
     * @return DocumentPrix
     */
    public function setEtatDevis($etatDevis)
    {
        $this->etatDevis = $etatDevis;

        return $this;
    }

    /**
     * Get etatDevis
     *
     * @return string
     */
    public function getEtatDevis()
    {
        return $this->etatDevis;
    }

    /**
     * Set etat
     *
     * @param string $etat
     * @return DocumentPrix
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
     * Get formatted etat
     *
     * @return string
     */
    public function getFormattedEtat()
    {
    	$s_etat = '';

    	switch($this->etat){

    		case 'DRAFT':
    			$s_etat = 'Brouillon';
    			break;
    		case 'READY':
    			$s_etat = 'Validé';
    			if($this->type == 'FACTURE'){
    				$s_etat.='e';
    			}
    			break;
    		case 'SENT':
    			$s_etat = 'Envoyé';
    			if($this->type == 'FACTURE'){
    				$s_etat.='e';
    			}
    			break;
    		case 'WON':
    			$s_etat = 'Gagné';
    			break;
    		case 'LOST':
    			$s_etat = 'Perdu';
    			break;
    		default:
    			$s_etat = 'Inconnu';
    			break;
    	}

    	return $s_etat;
    }

    public function win(){
      $this->etat = "WON";
    }

    public function isWon(){
      if($this->etat == "WON"){
        return true;
      }
      return false;
    }

    public function lose(){
      $this->etat = "LOST";
    }

    public function isLost(){
      if($this->etat == "LOST"){
        return true;
      }
      return false;
    }


    public function __clone() {

    	if ($this->id) {

    		$this->setDateCreation(new \DateTime(date('Y-m-d')));

    		$this->setDateEdition(null);
    		$this->setUserEdition(null);

    		$this->setDateValidite(new \DateTime(date('Y-m-d', strtotime("+1 month", strtotime(date('Y-m-d'))))));

    		$this->setEtat("DRAFT");

    		$this->setId(null);

    		$produits = $this->getProduits();

    		$this->produits = new ArrayCollection();
    		foreach ($produits as $produit) {
    			$cloneProduit = clone $produit;
    			$cloneProduit->setDocumentPrix($this);
    			$this->produits->add($cloneProduit);
    		}
            
    	 }
    }

  	public function setId($id) {
  		$this->id = $id;
  		return $this;
  	}

  	public function getAnalytique() {
  		return $this->analytique;
  	}

  	public function setAnalytique($analytique) {
  		$this->analytique = $analytique;
  		return $this;
  	}


	/**
	 * Add rapprochements
	 *
	 * @param \AppBundle\Entity\Compta\Rapprochement $rapprochements
	 * @return DocumentPrix
	 */
	public function addRapprochement(\AppBundle\Entity\Compta\Rapprochement $rapprochement)
	{
		$rapprochement->setFacture($this);
		$this->rapprochements[] = $rapprochement;

		return $this;
	}

	/**
	 * Remove rapprochements
	 *
	 * @param \AppBundle\Entity\Compta\Rapprochement $rapprochements
	 */
	public function removeRapprochement(\AppBundle\Entity\Compta\Rapprochement $rapprochement)
	{
		$this->rapprochements->removeElement($rapprochement);
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
     * Add avoirs
     *
     * @param \AppBundle\Entity\Compta\Avoir $avoir
     * @return DocumentPrix
     */
    public function addAvoir(\AppBundle\Entity\Compta\Avoir $avoir)
    {
    	  $avoir->setFacture(this);
        $this->avoirs[] = $avoir;

        return $this;
    }

    /**
     * Remove avoirs
     *
     * @param \AppBundle\Entity\Compta\Avoir $avoirs
     */
    public function removeAvoir(\AppBundle\Entity\Compta\Avoir $avoirs)
    {
        $this->avoirs->removeElement($avoirs);
    }

    /**
     * Get avoirs
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAvoirs()
    {
        return $this->avoirs;
    }

    /**
     * Set compta
     *
     * @param boolean $compta
     * @return DocumentPrix
     */
    public function setCompta($compta)
    {
        $this->compta = $compta;

        return $this;
    }

    /**
     * Get compta
     *
     * @return boolean
     */
    public function getCompta()
    {
        return $this->compta;
    }

    /**
     * Add relance
     *
     * @param \AppBundle\Entity\Compta\Relance $relance
     * @return DocumentPrix
     */
    public function addRelance(\AppBundle\Entity\Compta\Relance $relance)
    {
    	$relance->setFacture(this);
        $this->relances[] = $relance;
        return $this;
    }

    /**
     * Remove relance
     *
     * @param \AppBundle\Entity\Compta\Relance $relance
     */
    public function removeRelance(\AppBundle\Entity\Compta\Relance $relance)
    {
        $this->relances->removeElement($relance);
    }

    /**
     * Get relances
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getRelances()
    {
        return $this->relances;
    }

    public function getRelancesRetard(){
      $arr_relances = array();
      foreach($this->relances as $relance){
        if($relance->getObjet() == 'RETARD'){
          $arr_relances[] = $relance;
        }
      }
      return $arr_relances;
    }

    public function getRelancesEcheance(){
      $arr_relances = array();
      foreach($this->relances as $relance){
        if($relance->getObjet() == 'ECHEANCE'){
          $arr_relances[] = $relance;
        }
      }
      return $arr_relances;
    }

    /**
     * Set opportunite
     *
     * @param \AppBundle\Entity\CRM\Opportunite $opportunite
     * @return DocumentPrix
     */
    public function setOpportunite(\AppBundle\Entity\CRM\Opportunite $opportunite = null)
    {
        $this->opportunite = $opportunite;

        return $this;
    }

    /**
     * Get opportunite
     *
     * @return \AppBundle\Entity\CRM\Opportunite
     */
    public function getOpportunite()
    {
        return $this->opportunite;
    }


    public function hasTypeProduit($type){

      foreach($this->produits as $produit){
        if($produit->getType()){
            if( strtoupper($produit->getType()->getValeur()) == strtoupper($type) ){
                return true;
            }
        }
        
      }

      return false;

    }

    /**
     * Set facturationBelgePercent
     *
     * @param string $facturationBelgePercent
     * @return DocumentPrix
     */
    public function setFacturationBelgePercent($facturationBelgePercent)
    {
        $this->facturationBelgePercent = $facturationBelgePercent;

        return $this;
    }

    /**
     * Get facturationBelgePercent
     *
     * @return string
     */
    public function getFacturationBelgePercent()
    {
        return $this->facturationBelgePercent;
    }

    /**
     * Get facturationBelge
     */
    public function getFacturationBelge()
    {
        $sousTotal = 0;
        foreach($this->produits as $produit){
            $sousTotal+= $produit->getTotal();
        }

        $totalHT = $sousTotal - $this->remise;
        return $totalHT*$this->facturationBelgePercent;
    }
}
