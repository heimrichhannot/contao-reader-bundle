<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ReaderBundle\Util;

use Contao\Config;
use Contao\DataContainer;
use Contao\System;

class ReaderConfigHelper
{
    public static function getFields(DataContainer $dc)
    {
        if (!$dc->id || null === ($readerConfig = System::getContainer()->get('huh.reader.reader-config-registry')->findByPk($dc->id))) {
            return [];
        }

        $readerConfig = System::getContainer()->get('huh.utils.model')->findRootParentRecursively(
            'parentReaderConfig', 'tl_reader_config', $readerConfig
        );

        return \Contao\System::getContainer()->get('huh.utils.choice.field')->getCachedChoices(
            [
                'dataContainer' => System::getContainer()->get('huh.utils.dca')->getOverridableProperty('dataContainer', [
                    $readerConfig,
                ]),
            ]
        );
    }

    public static function getTextFields(DataContainer $dc)
    {
        if (!$dc->id || null === ($readerConfig = System::getContainer()->get('huh.reader.reader-config-registry')->findByPk($dc->id))) {
            return [];
        }

        $readerConfig = System::getContainer()->get('huh.utils.model')->findRootParentRecursively(
            'parentReaderConfig', 'tl_reader_config', $readerConfig
        );

        return \Contao\System::getContainer()->get('huh.utils.choice.field')->getCachedChoices(
            [
                'dataContainer' => $readerConfig->dataContainer,
                'inputTypes' => ['text'],
            ]
        );
    }

    public static function getCheckboxFields(DataContainer $dc)
    {
        if (!$dc->id || null === ($readerConfig = System::getContainer()->get('huh.reader.reader-config-registry')->findByPk($dc->id))) {
            return [];
        }

        $readerConfig = System::getContainer()->get('huh.utils.model')->findRootParentRecursively(
            'parentReaderConfig', 'tl_reader_config', $readerConfig
        );

        return \Contao\System::getContainer()->get('huh.utils.choice.field')->getCachedChoices(
            [
                'dataContainer' => $readerConfig->dataContainer,
                'inputTypes' => ['checkbox'],
            ]
        );
    }
}
