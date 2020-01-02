<?php
/**
 * jaimeGerer - TableauTVAServiceTrait.php
 * Created by Allan.
 */

namespace App\Util\DependancyInjectionTrait;


use App\Service\Compta\TableauTVAService;

trait TableauTVAServiceTrait {

    /**
     * @var TableauTVAService|null
     */
    protected $tableauTVAService;

    /**
     * @return TableauTVAService|null
     */
    public function getTableauTVAService(): ?TableauTVAService {
        return $this->tableauTVAService;
    }

    /**
     * @required
     * @param TableauTVAService|null $tableauTVAService
     * @return TableauTVAServiceTrait
     */
    public function setTableauTVAService(?TableauTVAService $tableauTVAService): self {
        $this->tableauTVAService = $tableauTVAService;
        return $this;
    }

}
