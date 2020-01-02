<?php
/**
 * jaimeGerer - ReleveBancaireServiceTrait.php
 * Created by Allan.
 */

namespace App\Util\DependancyInjectionTrait;


use App\Service\Compta\ReleveBancaireService;

trait ReleveBancaireServiceTrait {

    /**
     * @var ReleveBancaireService|null
     */
    protected $releveBancaireService;

    /**
     * @return ReleveBancaireService|null
     */
    public function getReleveBancaireService(): ?ReleveBancaireService {
        return $this->releveBancaireService;
    }

    /**
     * @required
     * @param ReleveBancaireService|null $releveBancaireService
     * @return ReleveBancaireServiceTrait
     */
    public function setReleveBancaireService(?ReleveBancaireService $releveBancaireService): self {
        $this->releveBancaireService = $releveBancaireService;
        return $this;
    }


}
