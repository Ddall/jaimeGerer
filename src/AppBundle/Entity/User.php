<?php
namespace AppBundle\Entity;

//use FOS\UserBundle\Model\User as BaseUser;
use Sonata\UserBundle\Entity\BaseUser;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Entity\UserRepository")
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
     * @var
     *
     * @ORM\ManyToMany(targetEntity="Group", inversedBy="users")
     * @ORM\JoinTable(name="users_groups")
     */
    protected $groups;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Company")
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
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Compta\CompteComptable")
     * @ORM\JoinColumn(nullable=true)
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
     * @param \AppBundle\Entity\Company $company
     * @return User
     */
    public function setCompany(\AppBundle\Entity\Company $company = null)
    {
        $this->company = $company;

        return $this;
    }

    /**
     * Get company
     *
     * @return \AppBundle\Entity\Company
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
     * @param \AppBundle\Entity\Compta\CompteComptable $compteComptableNoteFrais
     * @return User
     */
    public function setCompteComptableNoteFrais(\AppBundle\Entity\Compta\CompteComptable $compteComptableNoteFrais = null)
    {
        $this->compteComptableNoteFrais = $compteComptableNoteFrais;

        return $this;
    }

    /**
     * Get compteComptableNoteFrais
     *
     * @return \AppBundle\Entity\Compta\CompteComptable 
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
        return '/web/upload/avatar/'.$this->id.'/'.$this->avatar;
    }
}
