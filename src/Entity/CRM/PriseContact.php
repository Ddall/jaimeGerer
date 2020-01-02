<?php

namespace App\Entity\CRM;

use Doctrine\ORM\Mapping as ORM;

/**
 * PriseContact
 *
 * @ORM\Table(name="prise_contact")
 * @ORM\Entity
 */
class PriseContact
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
     * @ORM\ManyToOne(targetEntity="App\Entity\CRM\Contact", inversedBy="priseContacts")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private $contact;
    
    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=10)
     */
    private $type;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="date")
     */
    private $date;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text")
     */
    private $description;
    
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\CRM\DocumentPrix")
     * @ORM\JoinColumn(nullable=true)
     */
    private $documentPrix;
    
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Compta\Avoir")
     * @ORM\JoinColumn(nullable=true)
     */
    private $avoir;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @var string
     *
     * @ORM\Column(name="message", type="text", nullable=true)
     * 
     */
    private $message;
    
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
     * @return PriseContact
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
     * Set date
     *
     * @param \DateTime $date
     * @return PriseContact
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
     * Set description
     *
     * @param string $description
     * @return PriseContact
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
     * Set documentPrix
     *
     * @param \App\Entity\CRM\DocumentPrix $documentPrix
     * @return PriseContact
     */
    public function setDocumentPrix(\App\Entity\CRM\DocumentPrix $documentPrix = null)
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

    /**
     * Set contact
     *
     * @param \App\Entity\CRM\Contact $contact
     * @return PriseContact
     */
    public function setContact(\App\Entity\CRM\Contact $contact = null)
    {
        $this->contact = $contact;

        return $this;
    }

    /**
     * Get contact
     *
     * @return \App\Entity\CRM\Contact
     */
    public function getContact()
    {
        return $this->contact;
    }

    /**
     * Set user
     *
     * @param \App\Entity\User $user
     * @return PriseContact
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
     * Set message
     *
     * @param string $message
     * @return PriseContact
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Get message
     *
     * @return string 
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Set avoir
     *
     * @param \App\Entity\Compta\Avoir $avoir
     * @return PriseContact
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
}
