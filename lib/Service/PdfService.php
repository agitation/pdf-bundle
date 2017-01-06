<?php

/*
 * @package    agitation/pdf-bundle
 * @link       http://github.com/agitation/pdf-bundle
 * @author     Alexander Günsche
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\PdfBundle\Service;

use Dompdf\Dompdf;
use Dompdf\Options;

class PdfService
{
    private $cacheDir;

    public function __construct($cacheDir)
    {
        $this->cacheDir = $cacheDir;
    }

    public function getRenderer()
    {
        $cacheDir = $this->cacheDir . "/agit/pdf";

        $options = new Options([
            "fontDir"                   => $cacheDir,
            "fontCache"                 => $cacheDir,
            "isFontSubsettingEnabled"   => true
        ]);

        return new Dompdf($options);
    }
}
