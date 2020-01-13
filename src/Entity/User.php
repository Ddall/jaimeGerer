<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\User as BaseUser;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Entity\UserRepository")
 * @ORM\Table(name="fos_user")
 */
class User extends BaseUser
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Company")
     * @ORM\JoinColumn(nullable=true)
     */
    private $company;

    /**
     * @var string
     *
     * @ORM\Column(name="receipt_bank_id", type="string", length=255, nullable=true)
     */
    private $receiptBankId;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Compta\CompteComptable")
     * @ORM\JoinColumn(nullable=true, name="compteComptableNoteFrais_id")
     */
    private $compteComptableNoteFrais;

    /**
     * @var string
     *
     * @ORM\Column(name="signature", type="string", length=255, nullable=true)
     */
    private $signature;

    //for signature upload
    /**
     * @Assert\Image(
     *  maxSize="1M",
     *  minHeight="50",
     *  maxHeight="300",
     *  minWidth="50",
     *  maxWidth="300" )
     */
    private $file;
    private $tempFilename;

    /**
     * @var string
     *
     * @ORM\Column(name="avatar", type="string", length=255, nullable=true)
     */
    private $avatar;

    /**
    * @var boolean
    *
    * @ORM\Column(name="compet_com", type="boolean", nullable=true)
    */
    private $competCom = false;

    /**
    * @var integer
    *
    * @ORM\Column(name="taux_horaire", type="integer", nullable=true)
    */
    private $tauxHoraire;

    /**
     * @var string
     *
     * @ORM\Column(name="marque_voiture", type="string", length=255, nullable=true)
     */
    private $marqueVoiture;

    /**
     * @var string
     *
     * @ORM\Column(name="modele_voiture", type="string", length=255, nullable=true)
     */
    private $modeleVoiture;

    /**
     * @var string
     *
     * @ORM\Column(name="immatriculation_voiture", type="string", length=255, nullable=true)
     */
    private $immatriculation_voiture;

    /**
     * @var integer
     *
     * @ORM\Column(name="puissance_voiture", type="integer", length=255, nullable=true)
     */
    private $puissance_voiture;

    /**
     * @var string
     *
     * @ORM\Column(name="firstname", type="string", length=255)
     */
    private $firstname;

    /**
     * @var string
     *
     * @ORM\Column(name="lastname", type="string", length=255)
     */
    private $lastname;


    public function __construct()
    {
        parent::__construct();
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
     * Set id
     *
     * @param integer $id 
     * @return Company
     */
    public function setId($id)
    {
        return $this->id = $id;
        return $this;
    }

    public function __toString()
    {
    	return $this->getFirstname().' '.$this->getLastname();
    }

    public function getParent()
    {
    	return 'SonataUserBundle';
    }

    /**
     * Set company
     *
     * @param \App\Entity\Company $company
     * @return User
     */
    public function setCompany(\App\Entity\Company $company = null)
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

	public function setEmail($email){
	    parent::setEmail($email);
	    $this->setUsername($email);
	}

    /**
     * Set receiptBankId
     *
     * @param string $receiptBankId
     * @return User
     */
    public function setReceiptBankId($receiptBankId)
    {
        $this->receiptBankId = $receiptBankId;

        return $this;
    }

    /**
     * Get receiptBankId
     *
     * @return string
     */
    public function getReceiptBankId()
    {
        return $this->receiptBankId;
    }

    /**
     * Set compteComptableNoteFrais
     *
     * @param \App\Entity\Compta\CompteComptable $compteComptableNoteFrais
     * @return User
     */
    public function setCompteComptableNoteFrais(\App\Entity\Compta\CompteComptable $compteComptableNoteFrais = null)
    {
        $this->compteComptableNoteFrais = $compteComptableNoteFrais;

        return $this;
    }

    /**
     * Get compteComptableNoteFrais
     *
     * @return \App\Entity\Compta\CompteComptable
     */
    public function getCompteComptableNoteFrais()
    {
        return $this->compteComptableNoteFrais;
    }

    /**
     * Set signature
     *
     * @param string $signature
     * @return User
     */
    public function setSignature($signature)
    {
        $this->signature = $signature;

        return $this;
    }

    /**
     * Get signature
     *
     * @return string 
     */
    public function getSignature()
    {
        return $this->signature;
    }

    /**
     * Set avatar
     *
     * @param string $avatar
     * @return User
     */
    public function setAvatar($avatar)
    {
        $this->avatar = $avatar;

        return $this;
    }

    /**
     * Get avatar
     *
     * @return string 
     */
    public function getAvatar()
    {
        return $this->avatar;
    }

    public function getAvatarPath(){
        return '/public/upload/avatar/'.$this->id.'/'.$this->avatar;
    }

    /**
     * Set competCom
     *
     * @param boolean $competCom
     * @return User
     */
    public function setCompetCom($competCom)
    {
        $this->competCom = $competCom;

        return $this;
    }

    /**
     * Get competCom
     *
     * @return boolean 
     */
    public function getCompetCom()
    {
        return $this->competCom;
    }

    /**
     * Set tauxHoraire
     *
     * @param integer $tauxHoraire
     * @return User
     */
    public function setTauxHoraire($tauxHoraire)
    {
        $this->tauxHoraire = $tauxHoraire;

        return $this;
    }

    /**
     * Get tauxHoraire
     *
     * @return integer 
     */
    public function getTauxHoraire()
    {
        return $this->tauxHoraire;
    }

    /**
     * Set marqueVoiture
     *
     * @param string $marqueVoiture
     * @return User
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
     * Set modeleVoiture
     *
     * @param string $modeleVoiture
     * @return User
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
     * Set immatriculation_voiture
     *
     * @param string $immatriculationVoiture
     * @return User
     */
    public function setImmatriculationVoiture($immatriculationVoiture)
    {
        $this->immatriculation_voiture = $immatriculationVoiture;

        return $this;
    }

    /**
     * Get immatriculation_voiture
     *
     * @return string 
     */
    public function getImmatriculationVoiture()
    {
        return $this->immatriculation_voiture;
    }



    /**
     * Set puissance_voiture
     *
     * @param integer $puissanceVoiture
     * @return User
     */
    public function setPuissanceVoiture($puissanceVoiture)
    {
        $this->puissance_voiture = $puissanceVoiture;

        return $this;
    }

    /**
     * Get puissance_voiture
     *
     * @return integer 
     */
    public function getPuissanceVoiture()
    {
        return $this->puissance_voiture;
    }

    /**
     * Set firstname
     *
     * @param string $firstname
     * @return User
     */
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;

        return $this;
    }

    /**
     * Get firstname
     *
     * @return string 
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * Set lastname
     *
     * @param string $lastname
     * @return User
     */
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;

        return $this;
    }

    /**
     * Get lastname
     *
     * @return string 
     */
    public function getLastname()
    {
        return $this->lastname;
    }
}
