<?php
/**
 * jaimeGerer - TableauTresorerieServiceTrait.php
 * Created by Allan.
 */

namespace App\Util\DependancyInjectionTrait;


use App\Service\Compta\TableauTresorerieService;

trait TableauTresorerieServiceTrait {

    /**
     * @var TableauTresorerieService|null
     */
    protected $tableauTresorerieService;

    /**
     * @return TableauTresorerieService|null
     */
    public function getTableauTresorerieService(): ?TableauTresorerieService {
        return $this->tableauTresorerieService;
    }

    /**
     * @required
     * @param TableauTresorerieService|null $tableauTresorerieService
     * @return TableauTresorerieServiceTrait
     */
    public function setTableauTresorerieService(?TableauTresorerieService $tableauTresorerieService): self {
        $this->tableauTresorerieService = $tableauTresorerieService;
        return $this;
    }

}
