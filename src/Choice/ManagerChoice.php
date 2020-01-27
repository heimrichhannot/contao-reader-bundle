<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ReaderBundle\Choice;

use Contao\System;
use HeimrichHannot\UtilsBundle\Choice\AbstractChoice;

class ManagerChoice extends AbstractChoice
{
    /**
     * @return array
     */
    protected function collect()
    {
        $choices = [];

        $config = System::getContainer()->getParameter('huh.reader');

        if (!isset($config['reader']['managers'])) {
            return $choices;
        }

        foreach ($config['reader']['managers'] as $manager) {
            if (!isset($manager['name']) || !isset($manager['id'])) {
                continue;
            }
            $choices[$manager['name']] = $manager['id'];
        }

        asort($choices);

        return $choices;
    }
}
