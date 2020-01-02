<?php
/**
 * jaimeGerer - GrandLivreServiceTrait.php
 * Created by Allan.
 */

namespace App\Util\DependancyInjectionTrait;


use App\Service\Compta\GrandLivreService;

trait GrandLivreServiceTrait {

    /**
     * @var GrandLivreService|null
     */
    protected $grandLivreService;

    /**
     * @return GrandLivreService|null
     */
    public function getGrandLivreService(): ?GrandLivreService {
        return $this->grandLivreService;
    }

    /**
     * @required
     * @param GrandLivreService|null $grandLivreService
     * @return GrandLivreServiceTrait
     */
    public function setGrandLivreService(?GrandLivreService $grandLivreService): self {
        $this->grandLivreService = $grandLivreService;
        return $this;
    }

}
