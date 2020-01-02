<?php
/**
 * jaimeGerer - MailgunServiceTrait.php
 * Created by Allan.
 */

namespace App\Util\DependancyInjectionTrait;


use App\Service\Emailing\MailgunService;

trait MailgunServiceTrait {

    /**
     * @var MailgunService|null
     */
    protected $mailgunService;

    /**
     * @return MailgunService|null
     */
    public function getMailgunService(): ?MailgunService {
        return $this->mailgunService;
    }

    /**
     * @required
     * @param MailgunService|null $mailgunService
     * @return MailgunServiceTrait
     */
    public function setMailgunService(?MailgunService $mailgunService): self {
        $this->mailgunService = $mailgunService;
        return $this;
    }


}
