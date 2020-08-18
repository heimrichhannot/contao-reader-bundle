<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ReaderBundle\ConfigElementType\Syndication\PdfReader;

use Contao\StringUtil;
use Contao\System;
use HeimrichHannot\ReaderBundle\ConfigElementType\Syndication\AbstractSyndication;
use HeimrichHannot\ReaderBundle\Item\ItemInterface;
use HeimrichHannot\ReaderBundle\Model\ReaderConfigElementModel;
use HeimrichHannot\UtilsBundle\PdfCreator\AbstractPdfCreator;
use HeimrichHannot\UtilsBundle\PdfCreator\Concrete\MpdfCreator;
use HeimrichHannot\UtilsBundle\PdfCreator\PdfCreatorFactory;

abstract class AbstractPdfReader
{
    /**
     * @var ItemInterface
     */
    protected $item;

    /**
     * @var ReaderConfigElementModel
     */
    protected $readerConfigElement;

    /**
     * @var AbstractSyndication
     */
    protected $syndication;

    /**
     * @var bool
     */
    protected $download = true;

    /**
     * AbstractPdfReader constructor.
     */
    public function __construct(ItemInterface $item, ReaderConfigElementModel $readerConfigElement, AbstractSyndication $syndication)
    {
        $this->item = $item;
        $this->readerConfigElement = $readerConfigElement;
        $this->syndication = $syndication;
    }

    /**
     * Get pdf file name.
     */
    abstract public function getFileName(): string;

    /**
     * Download or directly output the pdf to the browser.
     */
    public function generate()
    {
        /** @var MpdfCreator|AbstractPdfCreator $pdf */
        $pdf = PdfCreatorFactory::createInstance(MpdfCreator::getType());
        $pdf->setHtmlContent($this->compile())
            ->setFilename($this->getFileName())
            ->setFormat('A4')
            ->setOrientation($pdf::ORIENTATION_PORTRAIT)
        ;

        if ($fontDirectories = StringUtil::trimsplit(',', $this->readerConfigElement->syndicationPdfFontDirectories)) {
            $pdf->addFontDirectories($fontDirectories);
        }

        $margins = StringUtil::deserialize($this->readerConfigElement->syndicationPdfPageMargin, true);

        if (!empty($margins)) {
            $pdf->setMargins($margins['top'], $margins['right'], $margins['bottom'], $margins['left']);
        }

        if (null !== ($masterTemplatePath = System::getContainer()->get('huh.utils.file')->getPathFromUuid($this->readerConfigElement->syndicationPdfMasterTemplate))) {
            $pdf->setTemplateFilePath($masterTemplatePath);
        }

        $pdf->setOutputMode($this->download ? $pdf::OUTPUT_MODE_DOWNLOAD : '');

        $pdf->render();

//        $config = [
//            'mode' => \Config::get('characterSet'),
//        ];

//        $pdf->generate($this->download);
    }

    public function getItem(): ItemInterface
    {
        return $this->item;
    }

    public function setItem(ItemInterface $item)
    {
        $this->item = $item;
    }

    public function getReaderConfigElement(): ReaderConfigElementModel
    {
        return $this->readerConfigElement;
    }

    public function setReaderConfigElement(ReaderConfigElementModel $readerConfigElement)
    {
        $this->readerConfigElement = $readerConfigElement;
    }

    public function getSyndication(): AbstractSyndication
    {
        return $this->syndication;
    }

    public function setSyndication(AbstractSyndication $syndication)
    {
        $this->syndication = $syndication;
    }

    public function isDownload(): bool
    {
        return $this->download;
    }

    public function setDownload(bool $download)
    {
        $this->download = $download;
    }

    /**
     * Compile the html.
     */
    abstract protected function compile(): string;
}
