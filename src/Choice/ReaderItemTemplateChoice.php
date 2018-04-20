<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ReaderBundle\Choice;

use Contao\System;
use HeimrichHannot\UtilsBundle\Choice\AbstractChoice;

class ReaderItemTemplateChoice extends AbstractChoice
{
    /**
     * @return array
     */
    protected function collect()
    {
        $choices = [];

        $config = System::getContainer()->getParameter('huh.reader');

        if (isset($config['reader']['templates']['item_prefixes'])) {
            $choices = System::getContainer()->get('huh.utils.choice.twig_template')->setContext($config['reader']['templates']['item_prefixes'])->getCachedChoices();
        }

        if (isset($config['reader']['templates']['item'])) {
            foreach ($config['reader']['templates']['item'] as $template) {
                $choices[$template['name']] = $template['template'];
            }
        }

        asort($choices);

        return $choices;
    }
}
