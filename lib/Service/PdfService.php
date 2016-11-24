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
    public function __construct($appDir)
    {
        $this->appDir = $appDir;
    }

    public function getRenderer()
    {
        $options = new Options([
            "fontDir"                   => $this->appDir . "/Resources/pdf/fonts",
            "fontCache"                 => $this->appDir . "/Resources/pdf/fonts",
            "isFontSubsettingEnabled"   => true
        ]);

        return new Dompdf($options);
    }
}
