<?php

namespace App\Entity\JaimeProgresser;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\JaimeProgresser\MissionRepository")
 */
class Mission
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\CRM\Opportunite")
     * @ORM\JoinColumn(nullable=false)
     */
    private $actionCommerciale;


    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\JaimeProgresser\TypeMission", inversedBy="missions")
     * @ORM\JoinColumn(nullable=false)
     */
    private $typeMission;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\JaimeProgresser\Theme", inversedBy="missions")
     */
    private $themes;

    /**
     * @ORM\Column(type="boolean")
     */
    private $archive;

    public function __construct()
    {
        $this->themes = new ArrayCollection();
        $this->archive = false;
    }

    /**
     * Set actionCommerciale
     *
     * @param \App\Entity\CRM\Opportunite $actionCommerciale
     * @return Temps
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

    public function __toString(){
        return $this->actionCommerciale->getNom();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTypeMission(): ?TypeMission
    {
        return $this->typeMission;
    }

    public function setTypeMission(?TypeMission $typeMission): self
    {
        $this->typeMission = $typeMission;

        return $this;
    }

    /**
     * @return Collection|Theme[]
     */
    public function getThemes(): Collection
    {
        return $this->themes;
    }

    public function addTheme(Theme $theme): self
    {
        if (!$this->themes->contains($theme)) {
            $this->themes[] = $theme;
        }

        return $this;
    }

    public function removeTheme(Theme $theme): self
    {
        if ($this->themes->contains($theme)) {
            $this->themes->removeElement($theme);
        }

        return $this;
    }

    public function getArchive(): ?bool
    {
        return $this->archive;
    }

    public function setArchive(bool $archive): self
    {
        $this->archive = $archive;

        return $this;
    }

    public function getClient(){
        return $this->actionCommerciale->getCompte();
    }
}
