<?php
/**
 * jaimeGerer - UtilsServiceTrait.php
 * Created by Allan.
 */

namespace App\Util\DependancyInjectionTrait;


use App\Service\UtilsService;

trait UtilsServiceTrait {

    /**
     * @var UtilsService|null
     */
    protected $utilsService;

    /**
     * @return UtilsService|null
     */
    public function getUtilsService(): ?UtilsService {
        return $this->utilsService;
    }

    /**
     * @required
     * @param UtilsService|null $utilsService
     * @return UtilsServiceTrait
     */
    public function setUtilsService(?UtilsService $utilsService): self {
        $this->utilsService = $utilsService;
        return $this;
    }


}
