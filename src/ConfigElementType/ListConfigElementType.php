<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ReaderBundle\ConfigElementType;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\ModuleModel;
use Contao\StringUtil;
use HeimrichHannot\ReaderBundle\Item\ItemInterface;
use HeimrichHannot\ReaderBundle\Model\ReaderConfigElementModel;

class ListConfigElementType implements ConfigElementType
{
    /**
     * @var ContaoFrameworkInterface
     */
    protected $framework;

    public function __construct(ContaoFrameworkInterface $framework)
    {
        $this->framework = $framework;
    }

    public function addToTemplateData(ItemInterface $item, array &$templateData, ReaderConfigElementModel $readerConfigElement)
    {
        $module = ModuleModel::findById($readerConfigElement->listModule);

        if (null === $module) {
            return;
        }

        $listModule = new \HeimrichHannot\ListBundle\Module\ModuleList($module);
        $filterConfig = $listModule->getFilterConfig();
        $filter = StringUtil::deserialize($readerConfigElement->initialFilter, true);

        if (!isset($filter[0]['filterElement']) || !isset($filter[0]['selector'])) {
            return;
        }

        $filterConfig->addContextualValue($filter[0]['filterElement'], $item->getRaw()[$filter[0]['selector']]);
        $filterConfig->initQueryBuilder();
        $templateData['list'][$readerConfigElement->listName] = $listModule->generate();
    }
}
