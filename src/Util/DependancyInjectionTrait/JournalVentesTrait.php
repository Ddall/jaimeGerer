<?php
/**
 * jaimeGerer - JournalVentesTrait.php
 * Created by Allan.
 */

namespace App\Util\DependancyInjectionTrait;


use App\Controller\Compta\JournalVentesController;

trait JournalVentesTrait {

    /**
     * @var JournalVentesController|null
     */
    protected $journalVentesService;

    /**
     * @return JournalVentesController|null
     */
    public function getJournalVentesService(): ?JournalVentesController {
        return $this->journalVentesService;
    }

    /**
     * @required
     * @param JournalVentesController|null $journalVentesService
     * @return JournalVentesTrait
     */
    public function setJournalVentesService(?JournalVentesController $journalVentesService): self {
        $this->journalVentesService = $journalVentesService;
        return $this;
    }



}
