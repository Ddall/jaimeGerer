<?php

namespace App\Entity\CRM;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Impulsion
 *
 * @ORM\Table(name="impulsion")
 * @ORM\Entity(repositoryClass="App\Entity\CRM\ImpulsionRepository")
 */
class Impulsion
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
     * @ORM\ManyToOne(targetEntity="App\Entity\CRM\Contact")
     * @ORM\JoinColumn(nullable=false)
     * @Assert\NotBlank()
     */
    private $contact;
    
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;
    
    /**
     * @var integer
     *
     * @ORM\Column(name="delaiNum", type="integer", nullable=true)
     */
    private $delaiNum;
    
    /**
     * @var integer
     *
     * @ORM\Column(name="delaiUnit", type="string", length=10, nullable=true)
     */
    private $delaiUnit;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_creation", type="date")
     */
    private $dateCreation;

     /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime")
     */
    private $date;

     /**
     * @var string
     *
     * @ORM\Column(name="methodeContact", type="string", length=255)
     */
    private $methodeContact;

    /**
     * @var string
     *
     * @ORM\Column(name="infos", type="text")
     */
    private $infos;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\CRM\PriseContact")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private $priseContact;
    
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
     * Set delai
     *
     * @param integer $delai
     * @return Impulsion
     */
    public function setDelai($delai)
    {
        $this->delai = $delai;

        return $this;
    }

    /**
     * Get delai
     *
     * @return integer 
     */
    public function getDelai()
    {
        return $this->delai;
    }

    /**
     * Set contact
     *
     * @param unknown_type $contact
     * @return Impulsion
     */
    public function setContact($contact)
    {
        $this->contact = $contact;

        return $this;
    }

    /**
     * Get contact
     *
     * @return unknown_type 
     */
    public function getContact()
    {
        return $this->contact;
    }

    /**
     * Set user
     *
     * @param \App\Entity\User $user
     * @return Impulsion
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

    /**
     * Set delaiNum
     *
     * @param integer $delaiNum
     * @return Impulsion
     */
    public function setDelaiNum($delaiNum)
    {
        $this->delaiNum = $delaiNum;

        return $this;
    }

    /**
     * Get delaiNum
     *
     * @return integer 
     */
    public function getDelaiNum()
    {
        return $this->delaiNum;
    }

    /**
     * Set delaiUnit
     *
     * @param string $delaiUnit
     * @return Impulsion
     */
    public function setDelaiUnit($delaiUnit)
    {
        $this->delaiUnit = $delaiUnit;

        return $this;
    }

    /**
     * Get delaiUnit
     *
     * @return string 
     */
    public function getDelaiUnit()
    {
        return $this->delaiUnit;
    }

    public function getDelaiJours(){
    	
    	$mult = 1;
    	if($this->delaiUnit == 'WEEK'){
    		$mult = 7;
    	} else if($this->delaiUnit == 'MONTH'){
    		$mult = 30;
    	}
    	
    	return $this->delaiNum * $mult;
    }
    
    public function getEcheance(){
   		
    	$delaiJours = $this->getDelaiJours().' days';  	
   		
    	if(count($this->contact->getPriseContacts()) > 0){
    		$lastContact = clone($this->contact->getPriseContacts()[0]->getDate());
    	} else {
    		$lastContact = clone($this->dateCreation);
    	}
   		$echeance = $lastContact->add(date_interval_create_from_date_string($delaiJours));

   		return $echeance;
    }
    
    public function getTempsRestant(){
        	
    	$echeance = $this->getEcheance();

    	$today  = new \DateTime();
    	
		$diff = $today->diff($echeance);

		if($diff->format('%R%a') < 0){
			return $diff->format('%R%a');
		}
			
		return $diff->format('%d');
    }

    /**
     * Set dateCreation
     *
     * @param \DateTime $dateCreation
     * @return Impulsion
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
     * Set date
     *
     * @param \DateTime $date
     * @return Impulsion
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
     * Set methodeContact
     *
     * @param string $methodeContact
     * @return Impulsion
     */
    public function setMethodeContact($methodeContact)
    {
        $this->methodeContact = $methodeContact;

        return $this;
    }

    /**
     * Get methodeContact
     *
     * @return string 
     */
    public function getMethodeContact()
    {
        return $this->methodeContact;
    }

    /**
     * Set infos
     *
     * @param string $infos
     * @return Impulsion
     */
    public function setInfos($infos)
    {
        $this->infos = $infos;

        return $this;
    }

    /**
     * Get infos
     *
     * @return string 
     */
    public function getInfos()
    {
        return $this->infos;
    }

    public function getRetard(){

        $today = new \DateTime('today');
        $diff = $today->diff($this->date);

        if($diff->format("%R%a") > 0){
            return 0;
        }
        return $diff->format("%a");
    }

    /**
     * Set priseContact
     *
     * @param \App\Entity\CRM\PriseContact $priseContact
     * @return Impulsion
     */
    public function setPriseContact(\App\Entity\CRM\PriseContact $priseContact = null)
    {
        $this->priseContact = $priseContact;

        return $this;
    }

    /**
     * Get priseContact
     *
     * @return \App\Entity\CRM\PriseContact 
     */
    public function getPriseContact()
    {
        return $this->priseContact;
    }
}
