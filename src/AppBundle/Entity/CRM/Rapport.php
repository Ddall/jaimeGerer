<?php

namespace AppBundle\Entity\CRM;

use Doctrine\ORM\Mapping as ORM;

/**
 * Rapport
 *
 * @ORM\Table(name="rapport")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\CRM\RapportRepository")
 */
class Rapport
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
     * @ORM\Column(name="type", type="string", length=255)
     */
    private $type;

    /**
     * @var string
     *
     * @ORM\Column(name="module", type="string", length=255)
     */
    private $module;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="dateCreation", type="date")
     */
    private $dateCreation;

    /**
     * @var string
     *
     * @ORM\Column(name="nom", type="string", length=255)
     */
    private $nom;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="data", type="text", nullable=true)
     */
    private $data;
    
    /**
     * @var string
     *
     * @ORM\Column(name="cols", type="text", nullable=true)
     */
    private $cols;

    
    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User")
     * @ORM\JoinColumn(nullable=false)
     */
    private $userCreation;

    /**
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\CRM\RapportFilter", mappedBy="rapport", cascade={"persist", "remove"}, orphanRemoval=true)
     *
     */
    private $filtres;
    
    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Company")
     * @ORM\JoinColumn(nullable=true)
     */
    private $company = null;


     /**
     * @var boolean
     *
     * @ORM\Column(name="emailing", type="boolean", nullable=true)
     */
    private $emailing = null;

    /**
     * @var boolean
     *
     * @ORM\Column(name="exclude_warnings", type="boolean", nullable=true)
     */
    private $excludeWarnings = null;
    
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
     * @return Liste
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
     * Set module
     *
     * @param string $module
     * @return Liste
     */
    public function setModule($module)
    {
        $this->module = $module;

        return $this;
    }

    /**
     * Get module
     *
     * @return string 
     */
    public function getModule()
    {
        return $this->module;
    }

    /**
     * Set dateCreation
     *
     * @param \DateTime $dateCreation
     * @return Liste
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
     * Set nom
     *
     * @param string $nom
     * @return Liste
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
     * @return Liste
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
     * Set data
     *
     * @param string $data
     * @return Liste
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Get data
     *
     * @return string 
     */
    public function getData()
    {
        return $this->data;
    }
    
    /**
     * Set userCreation
     *
     * @param \AppBundle\Entity\User $userCreation
     * @return Contact
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
     * Set cols
     *
     * @param string $cols
     * @return Rapport
     */
    public function setCols($cols)
    {
        $this->cols = $cols;

        return $this;
    }

    /**
     * Get cols
     *
     * @return string 
     */
    public function getCols()
    {
        return $this->cols;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->filtres = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add filtres
     *
     * @param \AppBundle\Entity\CRM\RapportFilter $filtres
     * @return Rapport
     */
    public function addFiltre(\AppBundle\Entity\CRM\RapportFilter $filtres)
    {
        $filtres->setRapport($this);
        $this->filtres[] = $filtres;

        return $this;
    }

    /**
     * Remove filtres
     *
     * @param \AppBundle\Entity\CRM\RapportFilter $filtres
     */
    public function removeFiltre(\AppBundle\Entity\CRM\RapportFilter $filtres)
    {
        $this->filtres->removeElement($filtres);
    }

    /**
     * Get filtres
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getFiltres()
    {
        return $this->filtres;
    }
	public function getCompany() {
		return $this->company;
	}
	public function setCompany($company) {
		$this->company = $company;
		return $this;
	}
	

    /**
     * Set emailing
     *
     * @param boolean $emailing
     * @return Rapport
     */
    public function setEmailing($emailing)
    {
        $this->emailing = $emailing;

        return $this;
    }

    /**
     * Get emailing
     *
     * @return boolean 
     */
    public function getEmailing()
    {
        return $this->emailing;
    }

    /**
     * Set excludeWarnings
     *
     * @param boolean $excludeWarnings
     * @return Rapport
     */
    public function setExcludeWarnings($excludeWarnings)
    {
        $this->excludeWarnings = $excludeWarnings;

        return $this;
    }

    /**
     * Get excludeWarnings
     *
     * @return boolean 
     */
    public function getExcludeWarnings()
    {
        return $this->excludeWarnings;
    }
}
