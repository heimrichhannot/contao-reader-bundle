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
        $pdf = System::getContainer()->get('huh.utils.pdf_writer')
            ->mergeConfig($this->getConfig())
            ->setHtml($this->compile())
            ->addFontDirectories(StringUtil::trimsplit(',', $this->readerConfigElement->syndicationPdfFontDirectories))
            ->setFileName($this->getFileName());

        if (null !== ($masterTemplatePath = System::getContainer()->get('huh.utils.file')->getPathFromUuid($this->readerConfigElement->syndicationPdfMasterTemplate))) {
            $pdf->setTemplate($masterTemplatePath);
        }

        $pdf->generate($this->download);
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

    /**
     * Get the config.
     */
    protected function getConfig(): array
    {
        $margins = StringUtil::deserialize($this->readerConfigElement->syndicationPdfPageMargin, true);

        $config = [
            'mode' => \Config::get('characterSet'),
            'format' => 'A4',
            'orientation' => 'P',
        ];

        if (!empty($margins['top'])) {
            $config['margin_top'] = $margins['top'];
        }

        if (!empty($margins['right'])) {
            $config['margin_right'] = $margins['right'];
        }

        if (!empty($margins['bottom'])) {
            $config['margin_bottom'] = $margins['bottom'];
        }

        if (!empty($margins['left'])) {
            $config['margin_left'] = $margins['left'];
        }

        return $config;
    }
}
