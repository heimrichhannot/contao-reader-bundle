<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ReaderBundle\Event;

use HeimrichHannot\ReaderBundle\Item\ItemInterface;
use HeimrichHannot\ReaderBundle\Model\ReaderConfigModel;
use Symfony\Component\EventDispatcher\Event;

class ReaderAfterRenderEvent extends Event
{
    const NAME = 'huh.reader.event.reader_after_render';

    /**
     * @var string
     */
    protected $rendered;

    /**
     * @var mixed
     */
    protected $templateData;

    /**
     * @var ItemInterface
     */
    protected $item;

    /**
     * @var ReaderConfigModel
     */
    protected $readerConfig;

    public function __construct(string $rendered, $templateData, ItemInterface $item, ReaderConfigModel $readerConfig)
    {
        $this->rendered = $rendered;
        $this->templateData = $templateData;
        $this->item = $item;
        $this->readerConfig = $readerConfig;
    }

    public function getRendered(): string
    {
        return $this->rendered;
    }

    public function setRendered(string $rendered): void
    {
        $this->rendered = $rendered;
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
}
