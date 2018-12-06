<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ReaderBundle\EventListener;

use Contao\System;
use HeimrichHannot\FilterBundle\Event\AdjustFilterValueEvent;
use HeimrichHannot\ReaderBundle\Backend\FilterConfigElement;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class AdjustFilterValueEventListener
{
    public function onAdjustFilterValue(AdjustFilterValueEvent $event, string $eventName, EventDispatcherInterface $dispatcher)
    {
        $element = $event->getElement();

        if (FilterConfigElement::ALTERNATIVE_SOURCE_READER_BUNDLE_ENTITY !== $element->alternativeValueSource ||
            null === ($readerConfig = System::getContainer()->get('huh.utils.model')->findModelInstanceByPk(
                'tl_reader_config', $element->readerConfig
            )) || !$element->readerField) {
            return;
        }

        if (System::getContainer()->get('huh.utils.container')->isBackend()) {
            return;
        }

        $instance = System::getContainer()->get('huh.reader.manager.reader')->retrieveItem();

        $value = $instance->{$element->readerField};
        $value = array_filter(!\is_array($value) ? explode(',', $value) : $value);

        $event->setValue($value);
    }
}
