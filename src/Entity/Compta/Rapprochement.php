<?php

namespace App\Entity\Compta;

use Doctrine\ORM\Mapping as ORM;

/**
 * Rapprochement
 *
 * @ORM\Table(name="rapprochement")
 * @ORM\Entity(repositoryClass="App\Entity\Compta\RapprochementRepository")
 */
class Rapprochement
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
     * @ORM\ManyToOne(targetEntity="App\Entity\Compta\MouvementBancaire", inversedBy="rapprochements")
     * @ORM\JoinColumn(nullable=false)
     */
    private $mouvementBancaire;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Compta\Depense", inversedBy="rapprochements")
     * @ORM\JoinColumn(nullable=true)
     */
    private $depense;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\CRM\DocumentPrix", inversedBy="rapprochements")
     * @ORM\JoinColumn(nullable=true)
     */
    private $facture;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Compta\Accompte", inversedBy="rapprochements")
     * @ORM\JoinColumn(nullable=true)
     */
    private $accompte;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Compta\Avoir", inversedBy="rapprochements")
     * @ORM\JoinColumn(nullable=true)
     */
    private $avoir;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Compta\RemiseCheque", inversedBy="rapprochements")
     * @ORM\JoinColumn(nullable=true)
     */
    private $remiseCheque;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Compta\AffectationDiverse", inversedBy="rapprochements")
     * @ORM\JoinColumn(nullable=true)
     */
    private $affectationDiverse;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\NDF\NoteFrais", inversedBy="rapprochements")
     * @ORM\JoinColumn(nullable=true)
     */
    private $noteFrais;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="date")
     */
    private $date;


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
     * Set mouvementBancaire
     *
     * @param \App\Entity\Compta\MouvementBancaire $mouvementBancaire
     * @return Rapprochement
     */
    public function setMouvementBancaire(\App\Entity\Compta\MouvementBancaire $mouvementBancaire)
    {
        $this->mouvementBancaire = $mouvementBancaire;

        return $this;
    }

    /**
     * Get mouvementBancaire
     *
     * @return \App\Entity\Compta\MouvementBancaire
     */
    public function getMouvementBancaire()
    {
        return $this->mouvementBancaire;
    }

    /**
     * Set depense
     *
     * @param \App\Entity\Compta\Depense $depense
     * @return Rapprochement
     */
    public function setDepense(\App\Entity\Compta\Depense $depense = null)
    {
        $this->depense = $depense;

        return $this;
    }

    /**
     * Get depense
     *
     * @return \App\Entity\Compta\Depense
     */
    public function getDepense()
    {
        return $this->depense;
    }

    /**
     * Set facture
     *
     * @param \App\Entity\CRM\DocumentPrix $facture
     * @return Rapprochement
     */
    public function setFacture(\App\Entity\CRM\DocumentPrix $facture = null)
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
     * Set accompte
     *
     * @param \App\Entity\Compta\Accompte $accompte
     * @return Rapprochement
     */
    public function setAccompte(\App\Entity\Compta\Accompte $accompte = null)
    {
        $this->accompte = $accompte;

        return $this;
    }

    /**
     * Get accompte
     *
     * @return \App\Entity\Compta\Accompte
     */
    public function getAccompte()
    {
        return $this->accompte;
    }

    /**
     * Set avoir
     *
     * @param \App\Entity\Compta\Avoir $avoir
     * @return Rapprochement
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

    /**
     * Set remiseCheque
     *
     * @param \App\Entity\Compta\RemiseCheque $remiseCheque
     * @return Rapprochement
     */
    public function setRemiseCheque(\App\Entity\Compta\RemiseCheque $remiseCheque = null)
    {
        $this->remiseCheque = $remiseCheque;

        return $this;
    }

    /**
     * Get remiseCheque
     *
     * @return \App\Entity\Compta\RemiseCheque
     */
    public function getRemiseCheque()
    {
        return $this->remiseCheque;
    }

    /**
     * Set affectationDiverse
     *
     * @param \App\Entity\Compta\AffectationDiverse $affectationDiverse
     * @return Rapprochement
     */
    public function setAffectationDiverse(\App\Entity\Compta\AffectationDiverse $affectationDiverse = null)
    {
        $this->affectationDiverse = $affectationDiverse;

        return $this;
    }

    /**
     * Get affectationDiverse
     *
     * @return \App\Entity\Compta\AffectationDiverse
     */
    public function getAffectationDiverse()
    {
        return $this->affectationDiverse;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     * @return Rapprochement
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
     * Set noteFrais
     *
     * @param \App\Entity\NDF\NoteFrais $noteFrais
     * @return Rapprochement
     */
    public function setNoteFrais(\App\Entity\NDF\NoteFrais $noteFrais = null)
    {
        $this->noteFrais = $noteFrais;

        return $this;
    }

    /**
     * Get noteFrais
     *
     * @return \App\Entity\NDF\NoteFrais
     */
    public function getNoteFrais()
    {
        return $this->noteFrais;
    }
}
