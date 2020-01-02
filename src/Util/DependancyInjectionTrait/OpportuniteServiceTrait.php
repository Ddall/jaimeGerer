<?php
/**
 * jaimeGerer - OpportuniteServiceTrait.php
 * Created by Allan.
 */

namespace App\Util\DependancyInjectionTrait;


use App\Service\CRM\OpportuniteService;

trait OpportuniteServiceTrait {

    /**
     * @var OpportuniteService|null
     */
    protected $opportuniteService;

    /**
     * @return OpportuniteService|null
     */
    public function getOpportuniteService(): ?OpportuniteService {
        return $this->opportuniteService;
    }

    /**
     * @required
     * @param OpportuniteService|null $opportuniteService
     * @return OpportuniteServiceTrait
     */
    public function setOpportuniteService(?OpportuniteService $opportuniteService): self {
        $this->opportuniteService = $opportuniteService;
        return $this;
    }

}
