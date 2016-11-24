<?php

/*
 * @package    agitation/pdf-bundle
 * @link       http://github.com/agitation/pdf-bundle
 * @author     Alexander Günsche
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\PdfBundle\Command;

use FontLib\Font;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class FontsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName("agit:pdf:fonts")
            ->setDescription("Installs PDF fonts to be used the project")
            ->addArgument("fontname", InputArgument::REQUIRED, "name of the font, e.g. for referencing in CSS")
            ->addArgument("normal", InputArgument::REQUIRED, "path to “normal” font variant")
            ->addArgument("bold", InputArgument::OPTIONAL, "path to “bold” font variant")
            ->addArgument("italic", InputArgument::OPTIONAL, "path to “italic” font variant")
            ->addArgument("bold_italic", InputArgument::OPTIONAL, "path to “bold italic” font variant");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $filesystem = new Filesystem();
        $dompdf = $this->getContainer()->get("agit.pdf")->getRenderer();
        $fontMetrics = $dompdf->getFontMetrics();
        $name = trim(preg_replace("|[^a-z0-9\.\-\_]|i", "", $input->getArgument("fontname")), ".-_");
        $targetDir = $dompdf->getOptions()->get("fontDir");
        $entries = [];

        foreach (["normal", "bold", "italic", "bold_italic"] as $variant) {
            $fileName = $input->getArgument($variant);
            $sourcePath = realpath($fileName);
            $filesystem->mkdir($targetDir);

            if (! $fileName || ! $sourcePath) {
                continue;
            }

            $targetBasePath = "$targetDir/$name.$variant";
            $extension = strrchr($sourcePath, ".") ?: "";
            $targetPath = $targetBasePath . $extension;
            $filesystem->copy($sourcePath, $targetPath, true);

            $font = Font::load($targetPath);
            $font->saveAdobeFontMetrics("$targetBasePath.ufm");
            $font->close();

            $entries[$variant] = $targetBasePath;
        }

        $fontMetrics->setFontFamily($name, $entries);
        $fontMetrics->saveFontFamilies();
    }
}
