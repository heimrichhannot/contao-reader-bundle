<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ReaderBundle\ConfigElementType;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\Model;
use Contao\System;
use HeimrichHannot\FilterBundle\Config\FilterConfig;
use HeimrichHannot\ReaderBundle\Exception\InvalidReaderAndListFilterDataContainerException;
use HeimrichHannot\ReaderBundle\Item\ItemInterface;
use HeimrichHannot\ReaderBundle\Model\ReaderConfigElementModel;

class NavigationConfigElementType implements ReaderConfigElementTypeInterface
{
    /**
     * @var ContaoFrameworkInterface
     */
    protected $framework;

    public function __construct(ContaoFrameworkInterface $framework)
    {
        $this->framework = $framework;
    }

    /**
     * {@inheritdoc}
     */
    public function addToItemData(ItemInterface $item, ReaderConfigElementModel $readerConfigElement)
    {
        if (!$readerConfigElement->listConfig) {
            $items = $this->getReaderFilterNavigationItems($item, $readerConfigElement);
        } else {
            $items = $this->getListFilterNavigationItems($item, $readerConfigElement);
        }

        if (null === $items) {
            return;
        }

        $item->setFormattedValue(
            $readerConfigElement->name,
            $item->getManager()->getTwig()->render(System::getContainer()->get('huh.utils.template')->getTemplate($readerConfigElement->navigationTemplate),
                ['items' => $items]
            )
        );
    }

    /**
     * Return the reader config element type alias.
     *
     * @return string
     */
    public static function getType(): string
    {
        return 'navigation';
    }

    /**
     * Return the reader config element type palette.
     *
     * @return string
     */
    public function getPalette(): string
    {
        return '{config_legend},name,navigationTemplate,previousLabel,nextLabel,previousTitle,nextTitle,sortingField,sortingDirection,listConfig,infiniteNavigation;';
    }

    /**
     * Update the item data.
     *
     * @param ReaderConfigElementData $configElementData
     */
    public function addToReaderItemData(ReaderConfigElementData $configElementData): void
    {
        $this->addToItemData($configElementData->getItem(), $configElementData->getReaderConfigElement());
    }

    /**
     * Render reader filter based navigation.
     *
     * @param ItemInterface            $item
     * @param ReaderConfigElementModel $readerConfigElement
     *
     * @return array|null
     */
    protected function getReaderFilterNavigationItems(ItemInterface $item, ReaderConfigElementModel $readerConfigElement): ?array
    {
        if (null === ($readerConfig = System::getContainer()->get('huh.reader.reader-config-registry')->findByPk($readerConfigElement->pid))) {
            return null;
        }

        if (null === ($filterConfig = System::getContainer()->get('huh.filter.manager')->findById($readerConfig->filter, false))) {
            return null;
        }

        return $this->getItems($filterConfig, $item, $readerConfigElement, $readerConfigElement->sortingField, $readerConfigElement->sortingDirection);
    }

    /**
     * Render list filter based navigation.
     *
     * @param ItemInterface            $item
     * @param ReaderConfigElementModel $readerConfigElement
     *
     * @return array|null
     */
    protected function getListFilterNavigationItems(ItemInterface $item, ReaderConfigElementModel $readerConfigElement): ?array
    {
        if (null === ($listConfig = System::getContainer()->get('huh.list.list-config-registry')->computeListConfig($readerConfigElement->listConfig))) {
            return null;
        }

        if (null === ($filterConfig = System::getContainer()->get('huh.filter.manager')->findById($listConfig->filter, false))) {
            return null;
        }

        if ($item->getDataContainer() !== $filterConfig->getFilter()['dataContainer']) {
            throw new InvalidReaderAndListFilterDataContainerException(sprintf('Item (%s) and filter (%s) dataContainer of list do not match.', $item->getDataContainer(), $filterConfig->getFilter()['dataContainer']));
        }

        $filterConfig->initQueryBuilder();

        return $this->getItems($filterConfig, $item, $readerConfigElement, $readerConfigElement->sortingField, $readerConfigElement->sortingDirection);
    }

    /**
     * Get the navigation items.
     *
     * @param FilterConfig             $filterConfig
     * @param ItemInterface            $item
     * @param ReaderConfigElementModel $readerConfigElement
     * @param string                   $sortingField
     * @param string                   $sortingDirection
     *
     * @return array
     */
    protected function getItems(FilterConfig $filterConfig, ItemInterface $item, ReaderConfigElementModel $readerConfigElement, ?string $sortingField, ?string $sortingDirection): array
    {
        $items = [];

        $filterConfig->initQueryBuilder();
        $queryBuilder = $filterConfig->getQueryBuilder();

        $itemModelClass = Model::getClassFromTable($item->getDataContainer());
        $pk = 'id';

        if (class_exists($itemModelClass)) {
            /**
             * @var Model
             */
            $itemModel = new $itemModelClass();
            $pk = $itemModel::getPk();
        }

        // previous
        $queryBuilderPrev = clone $queryBuilder;
        $queryBuilderPrev->andWhere($queryBuilderPrev->expr()->neq($item->getDataContainer().'.'.$pk, ':entityId'));
        $queryBuilderPrev->setParameter('entityId', $item->{$pk});

        if (\HeimrichHannot\ListBundle\Backend\ListConfig::SORTING_DIRECTION_DESC == $sortingDirection) {
            $queryBuilderPrev->andWhere($queryBuilderPrev->expr()->gte($item->getDataContainer().'.'.$sortingField, ':sortingValue'));
        } else {
            $queryBuilderPrev->andWhere($queryBuilderPrev->expr()->lte($item->getDataContainer().'.'.$sortingField, ':sortingValue'));
            $queryBuilderPrev->orderBy($item->getDataContainer().'.'.$sortingField, \HeimrichHannot\ListBundle\Backend\ListConfig::SORTING_DIRECTION_DESC);
        }

        $queryBuilderPrev->setParameter('sortingValue', $item->{$sortingField});

        // next
        $queryBuilderNext = clone $queryBuilder;
        $queryBuilderNext->andWhere($queryBuilderNext->expr()->neq($item->getDataContainer().'.'.$pk, ':entityId'));
        $queryBuilderNext->setParameter('entityId', $item->{$pk});

        if (\HeimrichHannot\ListBundle\Backend\ListConfig::SORTING_DIRECTION_DESC == $sortingDirection) {
            $queryBuilderNext->andWhere($queryBuilderNext->expr()->lte($item->getDataContainer().'.'.$sortingField, ':sortingValue'));
            $queryBuilderNext->orderBy($item->getDataContainer().'.'.$sortingField, $sortingDirection);
        } else {
            $queryBuilderNext->andWhere($queryBuilderNext->expr()->gte($item->getDataContainer().'.'.$sortingField, ':sortingValue'));
        }

        $queryBuilderNext->setParameter('sortingValue', $item->{$sortingField});

        if (false === ($previous = $queryBuilderPrev->select('*')->setMaxResults(1)->execute()->fetch()) && $readerConfigElement->infiniteNavigation) {
            // previous infinite
            $queryBuilderPrev = clone $queryBuilder;
            $queryBuilderPrev->select('*');
            $queryBuilderPrev->andWhere($queryBuilderPrev->expr()->neq($item->getDataContainer().'.'.$pk, ':entityId'));
            $queryBuilderPrev->setParameter('entityId', $item->{$pk});
            $queryBuilderPrev->orderBy($item->getDataContainer().'.'.$sortingField, \HeimrichHannot\ListBundle\Backend\ListConfig::SORTING_DIRECTION_DESC == $sortingDirection ? \HeimrichHannot\ListBundle\Backend\ListConfig::SORTING_DIRECTION_ASC : \HeimrichHannot\ListBundle\Backend\ListConfig::SORTING_DIRECTION_DESC);
            $queryBuilderPrev->setParameter('sortingValue', $item->{$sortingField});
            $previous = $queryBuilderPrev->select('*')->setMaxResults(1)->execute()->fetch();
        }

        if (false !== $previous) {
            $prevItem = clone $item;
            $prevItem->setRaw($previous);
            $items['previous'] = [
                'item' => $prevItem,
                'label' => $readerConfigElement->previousLabel,
                'class' => 'prev',
                'url' => $prevItem->getDetailsUrl(),
            ];

            if ($readerConfigElement->previousTitle) {
                $items['previous']['title'] = $readerConfigElement->previousTitle;
            }
        }

        if (false === ($next = $queryBuilderNext->select('*')->setMaxResults(1)->execute()->fetch()) && $readerConfigElement->infiniteNavigation) {
            // next infinite
            $queryBuilderNext = clone $queryBuilder;
            $queryBuilderNext->andWhere($queryBuilderNext->expr()->neq($item->getDataContainer().'.'.$pk, ':entityId'));
            $queryBuilderNext->setParameter('entityId', $item->{$pk});
            $queryBuilderNext->orderBy($item->getDataContainer().'.'.$sortingField, $sortingDirection);
            $queryBuilderNext->setParameter('sortingValue', $item->{$sortingField});
            $next = $queryBuilderNext->select('*')->setMaxResults(1)->execute()->fetch();
        }

        if (false !== $next) {
            $nextItem = clone $item;
            $nextItem->setRaw($next);
            $items['next'] = [
                'item' => $nextItem,
                'label' => $readerConfigElement->nextLabel,
                'class' => 'next',
                'url' => $nextItem->getDetailsUrl(),
            ];

            if ($readerConfigElement->nextTitle) {
                $items['next']['title'] = $readerConfigElement->nextTitle;
            }
        }

        return $items;
    }
}
