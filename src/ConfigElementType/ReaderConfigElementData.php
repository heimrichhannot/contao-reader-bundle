<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ReaderBundle\ConfigElementType;

use HeimrichHannot\ReaderBundle\Item\ItemInterface;
use HeimrichHannot\ReaderBundle\Model\ReaderConfigElementModel;

/**
 * Class ReaderConfigElementData.
 *
 * @deprecated use ConfigElementTypeData instead
 */
class ReaderConfigElementData
{
    /**
     * @var ItemInterface
     */
    private $item;
    /**
     * @var ReaderConfigElementModel
     */
    private $readerConfigElement;

    /**
     * ReaderConfigElementData constructor.
     */
    public function __construct(ItemInterface $item, ReaderConfigElementModel $readerConfigElement)
    {
        $this->item = $item;
        $this->readerConfigElement = $readerConfigElement;
    }

    public function getItem(): ItemInterface
    {
        return $this->item;
    }

    public function setItem(ItemInterface $item): void
    {
        $this->item = $item;
    }

    public function getReaderConfigElement(): ReaderConfigElementModel
    {
        return $this->readerConfigElement;
    }

    public function setReaderConfigElement(ReaderConfigElementModel $readerConfigElement): void
    {
        $this->readerConfigElement = $readerConfigElement;
    }
}
