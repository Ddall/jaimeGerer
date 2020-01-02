<?php
/**
 * jaimeGerer - CompteComptableServiceTrait.php
 * Created by Allan.
 */

namespace App\Util\DependancyInjectionTrait;


use App\Controller\Compta\CompteComptableController;

trait CompteComptableServiceTrait {

    /**
     * @var CompteComptableController|null
     */
    protected $compteComptableService;

    /**
     * @return CompteComptableController|null
     */
    public function getCompteComptableService(): ?CompteComptableController {
        return $this->compteComptableService;
    }

    /**
     * @required
     * @param CompteComptableController|null $compteComptableService
     * @return CompteComptableServiceTrait
     */
    public function setCompteComptableService(?CompteComptableController $compteComptableService): self {
        $this->compteComptableService = $compteComptableService;
        return $this;
    }

}
