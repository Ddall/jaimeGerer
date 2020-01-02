<?php
/**
 * jaimeGerer - DevisServiceTrait.php
 * Created by Allan.
 */

namespace App\Util\DependancyInjectionTrait;


use App\Service\CRM\DevisService;

trait DevisServiceTrait {

    /**
     * @var DevisService|null
     */
    protected $devisService;

    /**
     * @return DevisService|null
     */
    public function getDevisService(): ?DevisService {
        return $this->devisService;
    }

    /**
     * @required
     * @param DevisService|null $devisService
     * @return DevisServiceTrait
     */
    public function setDevisService(?DevisService $devisService): self {
        $this->devisService = $devisService;
        return $this;
    }


}
