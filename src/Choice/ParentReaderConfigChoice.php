<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ReaderBundle\Choice;

use Contao\System;
use HeimrichHannot\UtilsBundle\Choice\AbstractChoice;

class ParentReaderConfigChoice extends AbstractChoice
{
    /**
     * @return array
     */
    protected function collect()
    {
        $id = $this->getContext()['id'];

        if (!$id
            || null === ($readerConfigs = System::getContainer()->get('huh.reader.reader-config-registry')->findBy(
                [
                    'tl_reader_config.id != ?',
                ],
                [
                    $id,
                ]
            ))
        ) {
            return [];
        }

        $choices = array_combine(
            $readerConfigs->fetchEach('id'),
            $readerConfigs->fetchEach('title')
        );

        asort($choices);

        return $choices;
    }
}
