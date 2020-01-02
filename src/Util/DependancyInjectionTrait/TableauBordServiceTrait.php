<?php
/**
 * jaimeGerer - TableauBordServiceTrait.php
 * Created by Allan.
 */

namespace App\Util\DependancyInjectionTrait;


use App\Service\Compta\TableauBordService;

trait TableauBordServiceTrait {

    /**
     * @var TableauBordService|null
     */
    protected $tableauBordService;

    /**
     * @return TableauBordService|null
     */
    public function getTableauBordService(): ?TableauBordService {
        return $this->tableauBordService;
    }

    /**
     * @required
     * @param TableauBordService|null $tableauBordService
     * @return TableauBordServiceTrait
     */
    public function setTableauBordService(?TableauBordService $tableauBordService): self {
        $this->tableauBordService = $tableauBordService;
        return $this;
    }


}
