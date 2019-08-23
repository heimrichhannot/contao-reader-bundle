<?php

/*
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ReaderBundle\ConfigElementType;

use HeimrichHannot\ReaderBundle\Item\ItemInterface;
use HeimrichHannot\ReaderBundle\Model\ReaderConfigElementModel;

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
     *
     * @param ItemInterface            $item
     * @param ReaderConfigElementModel $readerConfigElement
     */
    public function __construct(ItemInterface $item, ReaderConfigElementModel $readerConfigElement)
    {
        $this->item = $item;
        $this->readerConfigElement = $readerConfigElement;
    }

    /**
     * @return ItemInterface
     */
    public function getItem(): ItemInterface
    {
        return $this->item;
    }

    /**
     * @param ItemInterface $item
     */
    public function setItem(ItemInterface $item): void
    {
        $this->item = $item;
    }

    /**
     * @return ReaderConfigElementModel
     */
    public function getReaderConfigElement(): ReaderConfigElementModel
    {
        return $this->readerConfigElement;
    }

    /**
     * @param ReaderConfigElementModel $readerConfigElement
     */
    public function setReaderConfigElement(ReaderConfigElementModel $readerConfigElement): void
    {
        $this->readerConfigElement = $readerConfigElement;
    }
}
