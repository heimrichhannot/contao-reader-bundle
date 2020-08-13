<?php
/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @author  Thomas Körner <t.koerner@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */


namespace HeimrichHannot\ReaderBundle\EventListener;

use Contao\Input;
use HeimrichHannot\UtilsBundle\PdfCreator\AbstractPdfCreator;
use HeimrichHannot\UtilsBundle\PdfCreator\PdfCreatorFactory;

/**
 * @Hook("parseFrontendTemplate")
 */
class ParseFrontendTemplateListener
{
    public function __invoke(string $buffer, string $template): string
    {
        return $buffer;


//        if ('fe_page' === substr($template, 0, 7)) {
//            if ('1' == Input::get('doprint')) {
//                PdfCreatorFactory::createInstance('mpdf')
//                    ->setHtmlContent($buffer)
//                    ->setMediaType('print')
//                    ->setOutputMode(AbstractPdfCreator::OUTPUT_MODE_DOWNLOAD)
//                    ->render()
//                ;


//                System::getContainer()->get('huh.utils.pdf.writer')->mergeConfig(['CSSselectMedia' => 'print'])->setHtml($buffer)->generate();


//                    ->mergeConfig($this->getConfig())
//                    ->setHtml($this->compile())
//                    ->addFontDirectories(StringUtil::trimsplit(',', $this->readerConfigElement->syndicationPdfFontDirectories))
//                    ->setFileName($this->getFileName());
//
//                if (null !== ($masterTemplatePath = System::getContainer()->get('huh.utils.file')->getPathFromUuid($this->readerConfigElement->syndicationPdfMasterTemplate))) {
//                    $pdf->setTemplate($masterTemplatePath);
//                }
//
//                $pdf->generate($this->download);
//            }
//        }

//        return $buffer;
    }
}