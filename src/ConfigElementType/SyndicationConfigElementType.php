<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ReaderBundle\ConfigElementType;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\System;
use HeimrichHannot\ReaderBundle\ConfigElementType\Syndication\AbstractSyndication;
use HeimrichHannot\ReaderBundle\Item\ItemInterface;
use HeimrichHannot\ReaderBundle\Model\ReaderConfigElementModel;

class SyndicationConfigElementType implements ConfigElementType
{
    /**
     * @var ContaoFrameworkInterface
     */
    protected $framework;

    public function __construct(ContaoFrameworkInterface $framework)
    {
        $this->framework = $framework;
    }

    public function addToItemData(ItemInterface $item, ReaderConfigElementModel $readerConfigElement)
    {
        $syndications = [];

        foreach (System::getContainer()->get('huh.reader.choice.syndication')->getCachedChoices() as $key => $class) {
            if (!class_exists($class)) {
                continue;
            }

            $r = new \ReflectionClass($class);

            if (!$r->isSubclassOf(AbstractSyndication::class)) {
                continue;
            }

            /**
             * @var AbstractSyndication
             */
            $syndication = new $class($item, $readerConfigElement);

            if (!$syndication->isEnabled()) {
                continue;
            }

            $syndications[$key] = $syndication->generate();
        }

        $item->setFormattedValue(
            $readerConfigElement->name,
            $item->getManager()->getTwig()->render(System::getContainer()->get('huh.utils.template')->getTemplate($readerConfigElement->syndicationTemplate),
                ['links' => $syndications]
            )
        );
    }
}
