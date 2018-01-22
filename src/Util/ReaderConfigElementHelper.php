<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ReaderBundle\Util;

use Contao\DataContainer;
use Contao\System;

class ReaderConfigElementHelper
{
    public static function getFields(DataContainer $dc)
    {
        if (!$dc->id || null === ($readerConfigElement = System::getContainer()->get('huh.reader.reader-config-element-registry')->findByPk($dc->id))) {
            return [];
        }

        if (!$dc->id
            || null === ($readerConfig = System::getContainer()->get('huh.reader.reader-config-registry')->findByPk($readerConfigElement->pid))
        ) {
            return [];
        }

        return System::getContainer()->get('huh.utils.choice.field')->getCachedChoices(
            [
                'dataContainer' => $readerConfig->dataContainer,
            ]
        );
    }

    public static function getCheckboxFields(DataContainer $dc)
    {
        if (!$dc->id || null === ($readerConfigElement = System::getContainer()->get('huh.reader.reader-config-element-registry')->findByPk($dc->id))) {
            return [];
        }

        if (!$dc->id
            || null === ($readerConfig = System::getContainer()->get('huh.reader.reader-config-registry')->findByPk($readerConfigElement->pid))
        ) {
            return [];
        }

        return System::getContainer()->get('huh.utils.choice.field')->getCachedChoices(
            [
                'dataContainer' => $readerConfig->dataContainer,
                'inputType' => ['checkbox'],
            ]
        );
    }
}
