<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ReaderBundle\ConfigElementType;

use Contao\Controller;
use Contao\ModuleModel;
use Contao\StringUtil;
use HeimrichHannot\FilterBundle\Manager\FilterManager;
use HeimrichHannot\ListBundle\Event\ListCompileEvent;
use HeimrichHannot\ReaderBundle\Item\ItemInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class ListConfigElementType implements ReaderConfigElementTypeInterface
{
    /** @var EventDispatcher */
    private EventDispatcherInterface                        $eventDispatcher;
    private FilterManager                                   $filterManager;
    private ?ItemInterface $item = null;
    private ?array $filter = null;

    public function __construct(FilterManager $filterManager, EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->filterManager = $filterManager;
    }

    /**
     * Update the item data.
     */
    public function addToReaderItemData(ReaderConfigElementData $configElementData): void
    {
        $moduleModel = ModuleModel::findById($configElementData->getReaderConfigElement()->listModule);

        if (!$moduleModel) {
            return;
        }

        $filter = StringUtil::deserialize($configElementData->getReaderConfigElement()->initialFilter, true);

        if (!isset($filter[0]['filterElement']) || !isset($filter[0]['selector'])) {
            return;
        }

        $item = $configElementData->getItem();

        $this->item = $item;
        $this->filter = $filter;
        $this->eventDispatcher->addListener(ListCompileEvent::NAME, [$this, 'prepareFilter']);

        $lists = $item->getFormattedValue('list') ?? [];

        $item->setFormattedValue(
            'list',
            array_merge($lists, [$configElementData->getReaderConfigElement()->listName => Controller::getFrontendModule($moduleModel->id)]
            )
        );

        $this->eventDispatcher->removeListener(ListCompileEvent::NAME, [$this, 'prepareFilter']);
        $this->item = null;
        $this->filter = null;
    }

    public function prepareFilter(ListCompileEvent $event): void
    {
        if (!$this->item || !$this->filter) {
            return;
        }
        $filterId = $event->getListConfig()->filter;

        if (!$filterId) {
            return;
        }
        $filterConfig = $this->filterManager->findById($filterId);

        if (!$filterConfig) {
            return;
        }

        $filterConfig->addContextualValue($this->filter[0]['filterElement'], $this->item->getRawValue($this->filter[0]['selector']));
        $filterConfig->initQueryBuilder();
    }

    /**
     * Return the reader config element type alias.
     */
    public static function getType(): string
    {
        return 'list';
    }

    /**
     * Return the reader config element type palette.
     */
    public function getPalette(): string
    {
        return '{config_legend},listName,listModule,initialFilter;';
    }
}
