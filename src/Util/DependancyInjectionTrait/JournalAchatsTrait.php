<?php
/**
 * jaimeGerer - JournalAchatsTrait.php
 * Created by Allan.
 */

namespace App\Util\DependancyInjectionTrait;


use App\Controller\Compta\JournalAchatsController;

trait JournalAchatsTrait {

    /**
     * @var JournalAchatsController|null
     */
    protected $journalAchatService;

    /**
     * @return JournalAchatsController|null
     */
    public function getJournalAchatService(): ?JournalAchatsController {
        return $this->journalAchatService;
    }

    /**
     * @required
     * @param JournalAchatsController|null $journalAchatService
     * @return JournalAchatsTrait
     */
    public function setJournalAchatService(?JournalAchatsController $journalAchatService): self {
        $this->journalAchatService = $journalAchatService;
        return $this;
    }


}
