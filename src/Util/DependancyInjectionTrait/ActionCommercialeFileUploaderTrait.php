<?php
/**
 * jaimeGerer - ActionCommercialeFileUploaderTrait.php
 * Created by Allan.
 */

namespace App\Util\DependancyInjectionTrait;


use App\Service\CRM\ActionCommercialeFileUploader;

trait ActionCommercialeFileUploaderTrait {

    /**
     * @var ActionCommercialeFileUploader|null
     */
    protected $actionCommercialeFileUploader;

    /**
     * @return ActionCommercialeFileUploader|null
     */
    public function getActionCommercialeFileUploader(): ?ActionCommercialeFileUploader {
        return $this->actionCommercialeFileUploader;
    }

    /**
     * @required
     * @param ActionCommercialeFileUploader|null $actionCommercialeFileUploader
     * @return ActionCommercialeFileUploaderTrait
     */
    public function setActionCommercialeFileUploader(?ActionCommercialeFileUploader $actionCommercialeFileUploader): self {
        $this->actionCommercialeFileUploader = $actionCommercialeFileUploader;
        return $this;
    }


}
