<?php

/*
 * @package    agitation/pdf-bundle
 * @link       http://github.com/agitation/pdf-bundle
 * @author     Alexander Günsche
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\PdfBundle\Service;

use Agit\BaseBundle\Exception\InternalErrorException;
use FontLib\Font;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;
use Symfony\Component\HttpKernel\Kernel;

class GenerateFontCache implements CacheWarmerInterface
{
    const AVAILABLE_VARIANTS = ["normal", "italic", "bold", "bold_italic"];

    /**
     * @var Kernel
     */
    private $kernel;

    /**
     * @var PdfService
     */
    private $pdfService;

    /**
     * @var array
     */
    private $fonts;

    public function __construct(Kernel $kernel, PdfService $pdfService, $fonts)
    {
        $this->kernel = $kernel;
        $this->pdfService = $pdfService;
        $this->fonts = $fonts;
    }

    public function warmUp($cacheDir)
    {
        $filesystem = new Filesystem();
        $dompdf = $this->pdfService->getRenderer();
        $fontMetrics = $dompdf->getFontMetrics();
        $targetDir = $dompdf->getOptions()->get("fontDir");
        $filesystem->mkdir($targetDir);

        if (is_array($this->fonts)) {
            foreach ($this->fonts as $name => $variants) {
                if (! is_array($variants)) {
                    throw new InternalErrorException("Expected an array of font variants/files for font $name.");
                }

                $name = trim(preg_replace("|[^a-z0-9\.\-\_]|i", "", $name), ".-_");
                $files = [];

                foreach ($variants as $variant => $path) {
                    if (! in_array($variant, self::AVAILABLE_VARIANTS)) {
                        throw new InternalErrorException("Invalid font variant: $variant.");
                    }

                    $sourcePath = $this->kernel->locateResource("@$path");
                    $targetBasePath = "$targetDir/$name.$variant";
                    $extension = strrchr($sourcePath, ".") ?: "";
                    $targetPath = $targetBasePath . $extension;
                    $filesystem->copy($sourcePath, $targetPath, true);

                    $font = Font::load($targetPath);
                    $font->saveAdobeFontMetrics("$targetBasePath.ufm");
                    $font->close();

                    $files[$variant] = $targetBasePath;
                }

                $fontMetrics->setFontFamily($name, $files);
            }

            $fontMetrics->saveFontFamilies();
        }
    }

    public function isOptional()
    {
        return true;
    }
}
