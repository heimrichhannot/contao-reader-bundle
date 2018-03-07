<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ReaderBundle\Choice;

use Contao\System;
use HeimrichHannot\UtilsBundle\Choice\AbstractChoice;

class ItemChoice extends AbstractChoice
{
    /**
     * @return array
     */
    protected function collect()
    {
        $choices = [];

        $config = System::getContainer()->getParameter('huh.reader');

        if (!isset($config['reader']['items'])) {
            return $choices;
        }

        foreach ($config['reader']['items'] as $manager) {
            $choices[$manager['name']] = $manager['class'];
        }

        asort($choices);

        return $choices;
    }
}
