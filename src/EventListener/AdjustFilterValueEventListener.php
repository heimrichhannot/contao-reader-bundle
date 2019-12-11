<?php

/*
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ReaderBundle\EventListener;

use Contao\Database;
use Contao\StringUtil;
use Contao\System;
use HeimrichHannot\FilterBundle\Event\AdjustFilterValueEvent;
use HeimrichHannot\ReaderBundle\Backend\FilterConfigElement;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class AdjustFilterValueEventListener
{
    public function onAdjustFilterValue(AdjustFilterValueEvent $event, string $eventName, EventDispatcherInterface $dispatcher)
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

        if (System::getContainer()->get('huh.utils.container')->isBackend() || System::getContainer()->get('huh.utils.container')->isFrontendCron()) {
            return;
        }

        $instance = System::getContainer()->get('huh.reader.manager.reader')->retrieveItem();
        $value = null;

        if ($dca['eval']['isCategoryField']) {
            $value = [];

            $associations = System::getContainer()->get('huh.categories.manager')->findAssociationsByParentTableAndEntityAndField(
                $table, $instance->getRawValue('id'), $element->field
            );

            if (null !== $associations) {
                $value = $associations->fetchEach('category');
            }
        } elseif ('cfgTags' === $dca['inputType']) {
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
