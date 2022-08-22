<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ReaderBundle\EventListener;

use Contao\Database;
use Contao\StringUtil;
use Contao\System;
use HeimrichHannot\FilterBundle\Event\AdjustFilterValueEvent;
use HeimrichHannot\ReaderBundle\Backend\FilterConfigElement;
use HeimrichHannot\UtilsBundle\Util\Utils;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AdjustFilterValueEventListener implements EventSubscriberInterface
{
    /**
     * @var Utils
     */
    private $utils;

    public function __construct(Utils $utils)
    {
        $this->utils = $utils;
    }

    public static function getSubscribedEvents()
    {
        return [
            AdjustFilterValueEvent::NAME => 'onAdjustFilterValue',
        ];
    }

    public function onAdjustFilterValue(AdjustFilterValueEvent $event)
    {
        $element = $event->getElement();
        $dca = $event->getDca();
        $table = $event->getConfig()->getFilter()['dataContainer'];

        if (FilterConfigElement::ALTERNATIVE_SOURCE_READER_BUNDLE_ENTITY !== $element->alternativeValueSource ||
            null === ($readerConfig = System::getContainer()->get('huh.utils.model')->findModelInstanceByPk(
                'tl_reader_config', $element->readerConfig
            )) || !$element->readerField) {
            return;
        }

        if (!$this->utils->container()->isFrontend() || $this->utils->container()->isFrontendCron()) {
            return;
        }

        $instance = System::getContainer()->get('huh.reader.manager.reader')->retrieveItem();
        $value = null;

        // necessary for getSearchablePages hook
        if (null === $instance) {
            return;
        }

        if ($dca['eval']['isCategoryField'] ?? false) {
            $value = [];

            $associations = System::getContainer()->get('huh.categories.manager')->findAssociationsByParentTableAndEntityAndField(
                $table, $instance->getRawValue('id'), $element->field
            );

            if (null !== $associations) {
                $value = $associations->fetchEach('category');
            }
        } elseif ('cfgTags' === ($dca['inputType'] ?? '')) {
            $value = [];
            $relationTable = $dca['relation']['relationTable'];
            $idName = str_replace('tl_', '', $table).'_id';

            $associations = Database::getInstance()->prepare(
                "SELECT cfg_tag_id FROM $relationTable WHERE $idName = ?"
            )->execute($instance->getRawValue('id'));

            if ($associations->numRows > 0) {
                while ($associations->next()) {
                    $value[] = $associations->cfg_tag_id;
                }
            }
        } else {
            $value = $instance->{$element->readerField};
            $value = StringUtil::deserialize($value);
            $value = array_filter(!\is_array($value) ? explode(',', $value) : $value);
        }

        $event->setValue($value);
    }
}
