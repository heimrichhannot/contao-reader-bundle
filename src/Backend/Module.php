<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ReaderBundle\Backend;

use Contao\DataContainer;
use Contao\System;
use HeimrichHannot\ReaderBundle\Module\ModuleReader;

class Module
{
    /**
     * @deprecated constant MODULE_READER is deprecated and will be removed in future version. Use ModuleReader::TYPE instead.
     */
    const MODULE_READER = ModuleReader::TYPE;

    /**
     * @param DataContainer $dc
     *
     * @return array
     *
     * @todo Move to EventListener
     */
    public function getAllReaderDataContainerFields(DataContainer $dc)
    {
        if (!$dc->id || null === ($readerConfigElement = System::getContainer()->get('huh.reader.reader-config-element-registry')->findByPk($dc->id))) {
            return [];
        }
    }
}
