<?php
/**
 * jaimeGerer - JournalBanqueTrait.php
 * Created by Allan.
 */

namespace App\Util\DependancyInjectionTrait;


use App\Controller\Compta\JournalBanqueController;

trait JournalBanqueTrait {

    /**
     * @var JournalBanqueController|null
     */
    protected $journalBanqueService;

    /**
     * @return JournalBanqueController|null
     */
    public function getJournalBanqueService(): ?JournalBanqueController {
        return $this->journalBanqueService;
    }

    /**
     * @required
     * @param JournalBanqueController|null $journalBanqueService
     * @return JournalBanqueTrait
     */
    public function setJournalBanqueService(?JournalBanqueController $journalBanqueService): self {
        $this->journalBanqueService = $journalBanqueService;
        return $this;
    }


}
