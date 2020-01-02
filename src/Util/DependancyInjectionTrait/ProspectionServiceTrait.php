<?php
/**
 * jaimeGerer - ProspectionServiceTrait.php
 * Created by Allan.
 */

namespace App\Util\DependancyInjectionTrait;


use App\Service\CRM\ProspectionService;

trait ProspectionServiceTrait {

    /**
     * @var ProspectionService|null
     */
    protected $prospectionService;

    /**
     * @return ProspectionService|null
     */
    public function getProspectionService(): ?ProspectionService {
        return $this->prospectionService;
    }

    /**
     * @required
     * @param ProspectionService|null $prospectionService
     * @return ProspectionServiceTrait
     */
    public function setProspectionService(?ProspectionService $prospectionService): self {
        $this->prospectionService = $prospectionService;
        return $this;
    }


}
