<?php
/**
 * jaimeGerer - FactureServiceTrait.php
 * Created by Allan.
 */

namespace App\Util\DependancyInjectionTrait;


use App\Service\CRM\FactureService;

trait FactureServiceTrait {

    /**
     * @var FactureService|null
     */
    protected $factureService;

    /**
     * @return FactureService|null
     */
    public function getFactureService(): ?FactureService {
        return $this->factureService;
    }

    /**
     * @required
     * @param FactureService|null $factureService
     * @return FactureServiceTrait
     */
    public function setFactureService(?FactureService $factureService): self {
        $this->factureService = $factureService;
        return $this;
    }

}
