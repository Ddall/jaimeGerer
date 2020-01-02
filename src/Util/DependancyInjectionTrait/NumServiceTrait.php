<?php
/**
 * jaimeGerer - NumServiceTrait.php
 * Created by Allan.
 */

namespace App\Util\DependancyInjectionTrait;


use App\Service\NumService;

trait NumServiceTrait {

    /**
     * @var NumService|null
     */
    protected $numService;

    /**
     * @return NumService|null
     */
    public function getNumService(): ?NumService {
        return $this->numService;
    }

    /**
     * @required
     * @param NumService|null $numService
     * @return NumServiceTrait
     */
    public function setNumService(?NumService $numService): self {
        $this->numService = $numService;
        return $this;
    }




}
