<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ReaderBundle\ConfigElementType;

use Contao\Controller;
use Contao\Model;
use HeimrichHannot\ReaderBundle\DataContainer\ReaderConfigElementContainer;
use HeimrichHannot\ReaderBundle\Item\ItemInterface;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;
use HeimrichHannot\UtilsBundle\String\StringUtil;

class RelatedConfigElementType implements ReaderConfigElementTypeInterface
{
    /**
     * @var StringUtil
     */
    private $stringUtil;
    /**
     * @var ModelUtil
     */
    private $modelUtil;

    public function __construct(StringUtil $stringUtil, ModelUtil $modelUtil)
    {
        $this->stringUtil = $stringUtil;
        $this->modelUtil = $modelUtil;
    }

    /**
     * Return the config element type alias.
     */
    public static function getType(): string
    {
        return 'related';
    }

    /**
     * Return the config element type palette.
     */
    public function getPalette(): string
    {
        return '{config_legend},relatedExplanation,relatedListModule,relatedCriteriaExplanation,relatedCriteria;';
    }

    /**
     * Update the item data.
     */
    public function addToReaderItemData(ReaderConfigElementData $configElementData): void
    {
        $readerConfigElement = $configElementData->getReaderConfigElement();
        $item = $configElementData->getItem();

        $item->setFormattedValue(
            $readerConfigElement->templateVariable ?: 'relatedItems',
            $this->renderRelated($readerConfigElement, $item)
        );

        $configElementData->setItem($item);
    }

    protected function renderRelated(Model $configElement, ItemInterface $item): ?string
    {
        $GLOBALS['HUH_LIST_RELATED'] = [];

        $this->applyTagsFilter($configElement, $item);

        $result = Controller::getFrontendModule($configElement->relatedListModule);

        unset($GLOBALS['HUH_LIST_RELATED']);

        return $result;
    }

    protected function applyTagsFilter(Model $configElement, ItemInterface $item)
    {
        if (!class_exists('\Codefog\TagsBundle\CodefogTagsBundle') || !$configElement->tagsField) {
            return;
        }

        $criteria = \Contao\StringUtil::deserialize($configElement->relatedCriteria, true);

        if (empty($criteria)) {
            return;
        }

        if (\in_array(ReaderConfigElementContainer::RELATED_CRITERIUM_TAGS, $criteria)) {
            $GLOBALS['HUH_LIST_RELATED'][ReaderConfigElementContainer::RELATED_CRITERIUM_TAGS] = [
                'field' => $configElement->tagsField,
                'item' => $item,
            ];
        }
    }
}
