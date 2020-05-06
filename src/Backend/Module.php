<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ReaderBundle\Backend;

use Contao\DataContainer;
use Contao\ModuleModel;
use Contao\System;

class Module
{
    /**
     * @deprecated constant MODULE_READER is deprecated and will be removed in future version. Use ModuleReader::TYPE instead.
     */
    const MODULE_READER = 'huhreader';

    /**
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

    /**
     * Get fields by.
     *
     * @return array|mixed
     */
    public function getFieldsByListModule(DataContainer $dc)
    {
        if (!$dc->id || null === ($readerConfigElement = System::getContainer()->get('huh.reader.reader-config-element-registry')->findByPk($dc->id))) {
            return [];
        }

        if (null === ($readerConfig = System::getContainer()->get('huh.utils.model')->findModelInstanceByPk('tl_reader_config', $readerConfigElement->pid))) {
            return [];
        }

        if ('' === $readerConfigElement->listModule || null === ($listModule = ModuleModel::findById($readerConfigElement->listModule))) {
            return [];
        }

        if (null === ($listConfig = System::getContainer()->get('huh.list.list-config-registry')->findByPk($listModule->listConfig))) {
            return [];
        }

        if (null !== ($filterConfig = System::getContainer()->get('huh.filter.manager')->findById($listConfig->filter))) {
            $filter = (object) $filterConfig->getFilter();
            $table = $filter->dataContainer;
        } else {
            $table = $readerConfig->dataContainer;
        }

        return System::getContainer()->get('huh.utils.choice.field')->getCachedChoices([
            'dataContainer' => $table,
        ]);
    }
}
