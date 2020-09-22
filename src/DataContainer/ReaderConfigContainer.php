<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ReaderBundle\DataContainer;

use HeimrichHannot\TwigSupportBundle\Filesystem\TwigTemplateLocator;

class ReaderConfigContainer
{
    /**
     * @var array
     */
    protected $bundleConfig;
    /**
     * @var TwigTemplateLocator
     */
    protected $templateLocator;

    /**
     * ReaderConfigContainer constructor.
     */
    public function __construct(array $bundleConfig, TwigTemplateLocator $templateLocator)
    {
        $this->bundleConfig = $bundleConfig;
        $this->templateLocator = $templateLocator;
    }

    public function onItemTemplateOptionsCallback()
    {
        $choices = [];

        if (isset($this->bundleConfig['templates']['item_prefixes'])) {
            $choices = $this->templateLocator->getTemplateGroup($this->bundleConfig['templates']['item_prefixes']);
        }

        if (isset($this->bundleConfig['templates']['item'])) {
            foreach ($this->bundleConfig['templates']['item'] as $template) {
                if (!isset($template['template']) || !isset($template['name'])) {
                    continue;
                }
                // remove duplicates returned by `huh.utils.choice.twig_template`
                if (false !== ($idx = array_search($template['template'], $choices, true))) {
                    unset($choices[$idx]);
                }

                $choices[$template['name']] = $template['template'].' (Yaml)';
            }
        }

        asort($choices);

        return $choices;
    }
}
