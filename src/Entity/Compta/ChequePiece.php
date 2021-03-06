<?php

namespace App\Entity\Compta;

use Doctrine\ORM\Mapping as ORM;

/**
 * ChequePiece
 *
 * @ORM\Table(name="cheque_piece")
 * @ORM\Entity(repositoryClass="App\Entity\Compta\ChequePieceRepository")
 */
class ChequePiece
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
     * @ORM\ManyToOne(targetEntity="App\Entity\Compta\Cheque", inversedBy="pieces")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $cheque;
    
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Compta\Avoir")
     * @ORM\JoinColumn(nullable=true)
     */
    private $avoir;
    
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\CRM\DocumentPrix")
     * @ORM\JoinColumn(nullable=true)
     */
    private $facture;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Compta\OperationDiverse")
     * @ORM\JoinColumn(nullable=true)
     */
    private $operationDiverse;

    /**
     * @var float
     *
     * @ORM\Column(name="autre_montant", type="float", nullable=true)
     */
    private $autreMontant;

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
     * Set cheque
     *
     * @param \App\Entity\Compta\Cheque $cheque
     * @return ChequePiece
     */
    public function setCheque(\App\Entity\Compta\Cheque $cheque)
    {
        $this->cheque = $cheque;

        return $this;
    }

    /**
     * Get cheque
     *
     * @return \App\Entity\Compta\Cheque
     */
    public function getCheque()
    {
        return $this->cheque;
    }

    /**
     * Set avoir
     *
     * @param \App\Entity\Compta\Avoir $avoir
     * @return ChequePiece
     */
    public function setAvoir(\App\Entity\Compta\Avoir $avoir)
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

    /**
     * Set facture
     *
     * @param \App\Entity\CRM\DocumentPrix $facture
     * @return ChequePiece
     */
    public function setFacture(\App\Entity\CRM\DocumentPrix $facture)
    {
        $this->facture = $facture;

        return $this;
    }

    /**
     * Get facture
     *
     * @return \App\Entity\CRM\DocumentPrix
     */
    public function getFacture()
    {
        return $this->facture;
    }


    /**
     * Set autreMontant
     *
     * @param float $autreMontant
     * @return ChequePiece
     */
    public function setAutreMontant($autreMontant)
    {
        $this->autreMontant = $autreMontant;

        return $this;
    }

    /**
     * Get autreMontant
     *
     * @return float 
     */
    public function getAutreMontant()
    {
        return $this->autreMontant;
    }

    /**
     * Set operationDiverse
     *
     * @param \App\Entity\Compta\OperationDiverse $operationDiverse
     * @return ChequePiece
     */
    public function setOperationDiverse(\App\Entity\Compta\OperationDiverse $operationDiverse = null)
    {
        $this->operationDiverse = $operationDiverse;

        return $this;
    }

    /**
     * Get operationDiverse
     *
     * @return \App\Entity\Compta\OperationDiverse
     */
    public function getOperationDiverse()
    {
        return $this->operationDiverse;
    }
}
