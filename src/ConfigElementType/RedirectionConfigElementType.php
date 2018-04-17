<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ReaderBundle\ConfigElementType;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\Database;
use Contao\PageModel;
use Contao\StringUtil;
use Contao\System;
use HeimrichHannot\ReaderBundle\Item\ItemInterface;
use HeimrichHannot\ReaderBundle\Model\ReaderConfigElementModel;

class RedirectionConfigElementType implements ConfigElementType
{
    /**
     * @var ContaoFrameworkInterface
     */
    protected $framework;

    public function __construct(ContaoFrameworkInterface $framework)
    {
        $this->framework = $framework;
    }

    public function addToItemData(ItemInterface $item, ReaderConfigElementModel $readerConfigElement)
    {
        $item->setFormattedValue($readerConfigElement->name, false);

        if (!$this->checkPermission($readerConfigElement, $item)) {
            return;
        }
        /** @var PageModel $pageModel */
        $pageModel = $this->framework->getAdapter(PageModel::class)->findPublishedById($readerConfigElement->redirection);
        if (null === $pageModel) {
            return;
        }
        $url = $pageModel->getFrontendUrl();

        if ($readerConfigElement->addRedirectParam) {
            $redirectParams = StringUtil::deserialize($readerConfigElement->redirectParams, true);
            foreach ($redirectParams as $redirectParam) {
                $param = $redirectParam['field'];
                $url = System::getContainer()->get('huh.utils.url')->addQueryString($param.'='.$item->{$param}, $url);
            }
        }

        $item->setFormattedValue($readerConfigElement->name, $url);
    }

    public function checkPermission(ReaderConfigElementModel $readerConfigElement, ItemInterface $item)
    {
        $allowed = true;

        if ($readerConfigElement->addRedirectConditions) {
            $itemConditions = StringUtil::deserialize($readerConfigElement->showRedirectConditions, true);

            if (empty($itemConditions)) {
                return false;
            }

            if (null === ($readerConfig = System::getContainer()->get('huh.reader.reader-config-registry')->findByPk($readerConfigElement->pid))) {
                return false;
            }

            list($whereCondition, $values) = System::getContainer()->get('huh.entity_filter.backend.entity_filter')->computeSqlCondition($itemConditions, $readerConfig->dataContainer);

            $statement = $this->framework->createInstance(Database::class)->prepare("SELECT * FROM $readerConfig->dataContainer WHERE ($whereCondition) AND $readerConfig->dataContainer.id=?");

            $result = call_user_func_array([$statement, 'execute'], array_merge($values, [$item->id]));

            if ($result->numRows < 1) {
                $allowed = false;
            }
        }

        return $allowed;
    }
}
