<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ReaderBundle\Event;

use HeimrichHannot\ReaderBundle\Item\ItemInterface;
use HeimrichHannot\ReaderBundle\Model\ReaderConfigModel;
use Symfony\Contracts\EventDispatcher\Event;

class ReaderBeforeRenderEvent extends Event
{
    const NAME = 'huh.reader.event.reader_before_render';

    /**
     * @var ItemInterface
     */
    protected $item;

    /**
     * @var ReaderConfigModel
     */
    protected $readerConfig;

    /**
     * @var mixed
     */
    private $templateData;

    public function __construct($templateData, ItemInterface $item, ReaderConfigModel $readerConfig)
    {
        $this->item = $item;
        $this->readerConfig = $readerConfig;
        $this->templateData = $templateData;
    }

    public function getItem(): ItemInterface
    {
        return $this->item;
    }

    public function setItem(ItemInterface $item): void
    {
        $this->item = $item;
    }

    public function getReaderConfig(): ReaderConfigModel
    {
        return $this->readerConfig;
    }

    public function setReaderConfig(ReaderConfigModel $readerConfig): void
    {
        $this->readerConfig = $readerConfig;
    }

    /**
     * @return mixed
     */
    public function getTemplateData()
    {
        return $this->templateData;
    }

    /**
     * @param mixed $templateData
     */
    public function setTemplateData($templateData): void
    {
        $this->templateData = $templateData;
    }
}
