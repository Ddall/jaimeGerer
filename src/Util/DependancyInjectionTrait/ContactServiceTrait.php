<?php
/**
 * jaimeGerer - ContactServiceTrait.php
 * Created by Allan.
 */

namespace App\Util\DependancyInjectionTrait;


use App\Service\CRM\ContactService;

trait ContactServiceTrait {

    /**
     * @var ContactService|null
     */
    protected $contactService;

    /**
     * @return ContactService|null
     */
    public function getContactService(): ?ContactService {
        return $this->contactService;
    }

    /**
     * @required
     * @param ContactService|null $contactService
     * @return ContactServiceTrait
     */
    public function setContactService(?ContactService $contactService): self {
        $this->contactService = $contactService;
        return $this;
    }


}
