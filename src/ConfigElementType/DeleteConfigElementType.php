<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ReaderBundle\ConfigElementType;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\StringUtil;
use Contao\System;
use HeimrichHannot\ReaderBundle\Backend\ReaderConfigElement;
use HeimrichHannot\ReaderBundle\ConfigElementType\Delete\DefaultDelete;
use HeimrichHannot\ReaderBundle\Item\ItemInterface;
use HeimrichHannot\ReaderBundle\Model\ReaderConfigElementModel;

class DeleteConfigElementType extends RedirectionConfigElementType
{
    /**
     * @var ContaoFrameworkInterface
     */
    protected $framework;

    public function __construct(ContaoFrameworkInterface $framework)
    {
        $this->framework = $framework;
        parent::__construct($framework);
    }

    public function addToItemData(ItemInterface $item, ReaderConfigElementModel $readerConfigElement)
    {
        parent::addToItemData($item, $readerConfigElement);

        if (!$readerConfigElement->addRedirectParam) {
            return;
        }

        $redirectParams = StringUtil::deserialize($readerConfigElement->redirectParams, true);
        $deleteConditions = false;
        $request = System::getContainer()->get('huh.request');

        foreach ($redirectParams as $redirectParam) {
            if (ReaderConfigElement::REDIRECTION_PARAM_TYPE_DEFAULT_VALUE === $redirectParam['parameterType'] && (!$request->hasGet($redirectParam['name']) || $redirectParam['defaultValue'] !== $request->getGet($redirectParam['name']))) {
                $deleteConditions = false;

                break;
            }
            $deleteConditions = true;

            if (ReaderConfigElement::REDIRECTION_PARAM_TYPE_FIELD_VALUE === $redirectParam['parameterType'] && !$request->hasGet($redirectParam['name'])) {
                $deleteConditions = false;

                break;
            }
            $deleteConditions = true;
        }

        if ($deleteConditions) {
            $class = $this->getDeleteClassByName($readerConfigElement->deleteClass);

            if (null === $class) {
                return;
            }
            /**
             * @var DefaultDelete
             */
            $deleteClass = $this->framework->createInstance($class, [$this->framework]);
            $deleteClass->delete($item, $readerConfigElement);
        }
    }

    protected function getDeleteClassByName(string $name)
    {
        $config = System::getContainer()->getParameter('huh.reader');
        $templates = $config['reader']['delete_classes'];

        foreach ($templates as $template) {
            if ($template['name'] == $name) {
                return class_exists($template['class']) ? $template['class'] : null;
            }
        }

        return null;
    }
}
