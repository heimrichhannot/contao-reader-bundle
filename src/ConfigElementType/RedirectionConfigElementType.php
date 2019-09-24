<?php

/*
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ReaderBundle\ConfigElementType;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\Database;
use Contao\PageModel;
use Contao\StringUtil;
use Contao\System;
use HeimrichHannot\ReaderBundle\Backend\ReaderConfigElement;
use HeimrichHannot\ReaderBundle\Item\ItemInterface;
use HeimrichHannot\ReaderBundle\Model\ReaderConfigElementModel;

class RedirectionConfigElementType implements ReaderConfigElementTypeInterface
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
     * @param ItemInterface            $item
     * @param ReaderConfigElementModel $readerConfigElement
     *
     * @return void|null
     */
    public function addToItemData(ItemInterface $item, ReaderConfigElementModel $readerConfigElement)
    {
        $item->setFormattedValue($readerConfigElement->name, false);

        if (!$this->checkPermission($readerConfigElement, $item)) {
            return;
        }
        /** @var PageModel $pageModel */
        $pageModel = $this->framework->getAdapter(PageModel::class)->findPublishedById($readerConfigElement->jumpTo);

        if (null === $pageModel) {
            return;
        }

        if ($readerConfigElement->addAutoItem && System::getContainer()->get('huh.request')->hasGet('auto_item')) {
            $url = $pageModel->getFrontendUrl('/'.System::getContainer()->get('huh.request')->getGet('auto_item'));
        } else {
            $url = $pageModel->getFrontendUrl();
        }

        if ($readerConfigElement->addRedirectParam) {
            $redirectParams = StringUtil::deserialize($readerConfigElement->redirectParams, true);

            foreach ($redirectParams as $redirectParam) {
                if (ReaderConfigElement::REDIRECTION_PARAM_TYPE_FIELD_VALUE === $redirectParam['parameterType']) {
                    $param = $redirectParam['field'];
                    $url = System::getContainer()->get('huh.utils.url')->addQueryString($redirectParam['name'].'='.$item->{$param}, $url);
                } elseif (ReaderConfigElement::REDIRECTION_PARAM_TYPE_DEFAULT_VALUE === $redirectParam['parameterType']) {
                    $url = System::getContainer()->get('huh.utils.url')->addQueryString($redirectParam['name'].'='.$redirectParam['defaultValue'], $url);
                }
            }
        }

        $item->setFormattedValue($readerConfigElement->name, $url);
    }

    public function checkPermission(ReaderConfigElementModel $readerConfigElement, ItemInterface $item)
    {
        $allowed = true;

        if ($readerConfigElement->addRedirectConditions) {
            $itemConditions = StringUtil::deserialize($readerConfigElement->redirectConditions, true);

            if (empty($itemConditions)) {
                return false;
            }

            if (null === ($readerConfig = System::getContainer()->get('huh.reader.reader-config-registry')->findByPk($readerConfigElement->pid))) {
                return false;
            }

            list($whereCondition, $values) = System::getContainer()->get('huh.entity_filter.backend.entity_filter')->computeSqlCondition($itemConditions, $readerConfig->dataContainer);

            $statement = $this->framework->createInstance(Database::class)->prepare("SELECT * FROM $readerConfig->dataContainer WHERE ($whereCondition) AND $readerConfig->dataContainer.id=?");

            $result = \call_user_func_array([$statement, 'execute'], array_merge($values, [$item->id]));

            if ($result->numRows < 1) {
                $allowed = false;
            }
        }

        return $allowed;
    }

    /**
     * Return the reader config element type alias.
     *
     * @return string
     */
    public static function getType(): string
    {
        return 'redirection';
    }

    /**
     * Return the reader config element type palette.
     *
     * @return string
     */
    public function getPalette(): string
    {
        return '{config_legend},name,jumpTo,addRedirectConditions,addRedirectParam,addAutoItem;';
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
}
