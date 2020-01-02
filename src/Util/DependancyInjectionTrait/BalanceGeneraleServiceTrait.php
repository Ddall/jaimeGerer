<?php
/**
 * jaimeGerer - BalanceGeneraleServiceTrait.php
 * Created by Allan.
 */

namespace App\Util\DependancyInjectionTrait;


use App\Service\Compta\BalanceGeneraleService;

trait BalanceGeneraleServiceTrait {

    /**
     * @var BalanceGeneraleService|null
     */
    protected $balanceGeneraleService;

    /**
     * @return BalanceGeneraleService|null
     */
    public function getBalanceGeneraleService(): ?BalanceGeneraleService {
        return $this->balanceGeneraleService;
    }

    /**
     * @required
     * @param BalanceGeneraleService|null $balanceGeneraleService
     * @return BalanceGeneraleServiceTrait
     */
    public function setBalanceGeneraleService(?BalanceGeneraleService $balanceGeneraleService): self {
        $this->balanceGeneraleService = $balanceGeneraleService;
        return $this;
    }


}
