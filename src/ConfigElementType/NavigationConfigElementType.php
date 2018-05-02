<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
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

class NavigationConfigElementType implements ConfigElementType
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
        if (null === ($listConfig = System::getContainer()->get('huh.list.list-config-registry')->findByPk($readerConfigElement->listConfig))) {
            return null;
        }

        if (null === ($filterConfig = System::getContainer()->get('huh.filter.manager')->findById($listConfig->filter, false))) {
            return null;
        }

        if ($item->getDataContainer() !== $filterConfig->getFilter()['dataContainer']) {
            throw new InvalidReaderAndListFilterDataContainerException(sprintf('Item (%s) and filter (%s) dataContainer of list do not match.', $item->getDataContainer(), $filterConfig->getFilter()['dataContainer']));
        }

        $filterConfig->initQueryBuilder();

        return $this->getItems($filterConfig, $item, $readerConfigElement, $listConfig->sortingField, $listConfig->sortingDirection);
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
    protected function getItems(FilterConfig $filterConfig, ItemInterface $item, ReaderConfigElementModel $readerConfigElement, string $sortingField, string $sortingDirection): array
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
                'title' => $readerConfigElement->previousTitle,
                'class' => 'prev',
                'url' => $prevItem->getDetailsUrl(),
            ];
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
                'title' => $readerConfigElement->nextTitle,
                'class' => 'next',
                'url' => $nextItem->getDetailsUrl(),
            ];
        }

        return $items;
    }
}