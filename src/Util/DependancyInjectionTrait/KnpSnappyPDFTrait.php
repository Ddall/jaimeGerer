<?php
/**
 * jaimeGerer - KnpSnappyPDFTrait.php
 * Created by Allan.
 */

namespace App\Util\DependancyInjectionTrait;


use Knp\Snappy\Pdf;

trait KnpSnappyPDFTrait {

    /**
     * @var Pdf
     */
    protected $knpSnappyPdf;

    /**
     * @return Pdf
     */
    public function getKnpSnappyPdf(): Pdf {
        return $this->knpSnappyPdf;
    }

    /**
     * @required
     * @param Pdf $knpSnappyPdf
     * @return KnpSnappyPDFTrait
     */
    public function setKnpSnappyPdf(Pdf $knpSnappyPdf): self {
        $this->knpSnappyPdf = $knpSnappyPdf;
        return $this;
    }


}
