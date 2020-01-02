<?php
/**
 * jaimeGerer - LettrageServiceTrait.php
 * Created by Allan.
 */

namespace App\Util\DependancyInjectionTrait;


use App\Service\Compta\LettrageService;

trait LettrageServiceTrait {

    /**
     * @var LettrageService|null
     */
    protected $lettrageService;

    /**
     * @return LettrageService|null
     */
    public function getLettrageService(): ?LettrageService {
        return $this->lettrageService;
    }

    /**
     * @required
     * @param LettrageService|null $lettrageService
     * @return LettrageServiceTrait
     */
    public function setLettrageService(?LettrageService $lettrageService): self {
        $this->lettrageService = $lettrageService;
        return $this;
    }

}
