<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ReaderBundle\ConfigElementType\Syndication;

use Contao\System;
use HeimrichHannot\ReaderBundle\ConfigElementType\Syndication\Link\DefaultLink;
use HeimrichHannot\ReaderBundle\ConfigElementType\Syndication\Link\LinkInterface;
use HeimrichHannot\ReaderBundle\ConfigElementType\Syndication\PdfReader\AbstractPdfReader;
use HeimrichHannot\ReaderBundle\Exception\InvalidSyndicationPdfReaderException;
use HeimrichHannot\ReaderBundle\Item\ItemInterface;
use HeimrichHannot\ReaderBundle\Model\ReaderConfigElementModel;

class PdfSyndication extends AbstractSyndication
{
    const PRINT_QUERY_PARAM = 'rPdf';

    /**
     * AbstractSyndication constructor.
     */
    public function __construct(ItemInterface $item, ReaderConfigElementModel $readerConfigElement)
    {
        parent::__construct($item, $readerConfigElement);

        if ((int) $readerConfigElement->id === (int) System::getContainer()->get('huh.request')->query->get(static::PRINT_QUERY_PARAM)) {
            $this->print();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function generate(): LinkInterface
    {
        $link = new DefaultLink();
        $link->setCssClass('pdf');
        $link->setTitle('huh.reader.element.title.pdf');
        $link->setContent('huh.reader.element.title.pdf');
        $link->setHref(System::getContainer()->get('huh.utils.url')->addQueryString(static::PRINT_QUERY_PARAM.'='.$this->readerConfigElement->id, $this->getUrl()));

        return $link;
    }

    /**
     * Print current item based on reader config element inside custom page layout and download file.
     */
    public function print()
    {
        $readers = System::getContainer()->get('huh.reader.choice.syndication-pdf-reader')->getCachedChoices();

        if (!isset($readers[$this->readerConfigElement->syndicationPdfReader])) {
            throw new InvalidSyndicationPdfReaderException(sprintf('The selected syndication pdf reader (%s) is invalid. Please use a valid reader provided within huh.reader.syndication_pdf_readers config.', $this->readerConfigElement->syndicationPdfReader));
        }

        $class = $readers[$this->readerConfigElement->syndicationPdfReader];

        /**
         * @var AbstractPdfReader ;
         */
        $reader = new $class($this->item, $this->readerConfigElement, $this);
        $reader->generate();
    }

    /**
     * {@inheritdoc}
     */
    public function isEnabled(): bool
    {
        return true === (bool) $this->readerConfigElement->syndicationPdf;
    }
}
