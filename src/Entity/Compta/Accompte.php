<?php

namespace App\Entity\Compta;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Accompte
 *
 * @ORM\Table(name="accompte")
 * @ORM\Entity(repositoryClass="App\Entity\Compta\AccompteRepository")
 */
class Accompte
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
     * @var float
     *
     * @ORM\Column(name="montant", type="float")
     */
    private $montant;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="date")
     */
    private $date;
    
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\CRM\DocumentPrix")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     * @Assert\NotBlank()
     */
    private $devis;
    
    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Compta\Rapprochement", mappedBy="accompte")
     */
    private $rapprochements;


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
     * Set montant
     *
     * @param float $montant
     * @return Accompte
     */
    public function setMontant($montant)
    {
        $this->montant = $montant;

        return $this;
    }

    /**
     * Get montant
     *
     * @return float 
     */
    public function getMontant()
    {
        return $this->montant;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     * @return Accompte
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
     * Set devis
     *
     * @param \App\Entity\CRM\DocumentPrix $devis
     * @return Accompte
     */
    public function setDevis(\App\Entity\CRM\DocumentPrix $devis)
    {
        $this->devis = $devis;

        return $this;
    }

    /**
     * Get devis
     *
     * @return \App\Entity\CRM\DocumentPrix
     */
    public function getDevis()
    {
        return $this->devis;
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
     * @return Accompte
     */
    public function addRapprochement(\App\Entity\Compta\Rapprochement $rapprochement)
    {
    	$rapprochement->setAccompte($this);
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
    
    public function getTotalRapproche(){
    		
    	$total = 0;
    	foreach($this->rapprochements as $rapprochement){
    		$total+= $rapprochement->getMouvementBancaire()->getMontant();
    	}
    	return ($total);
    }
    
    public function getTotalTTC(){
    	return $this->montant;
    }
   
    public function __toString(){
    	return $this->devis->getNum().' : '.$this->montant.' €';
    }
}
