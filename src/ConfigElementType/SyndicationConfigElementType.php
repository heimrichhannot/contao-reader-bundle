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

        $config = System::getContainer()->getParameter('huh.reader');

        if (!isset($config['reader']['syndications'])) {
            return $syndications;
        }

        $types = $config['reader']['syndications'];

        usort($types, function ($a, $b) {
            return (!isset($a['sort']) || !isset($b['sort'])) ? 0 : $a['sort'] - $b['sort'];
        });

        foreach ($types as $type) {
            if (!isset($type['name']) || !isset($type['class']) || !class_exists($type['class'])) {
                continue;
            }

            $r = new \ReflectionClass($type['class']);

            if (!$r->isSubclassOf(AbstractSyndication::class)) {
                continue;
            }

            /**
             * @var AbstractSyndication
             */
            $syndication = new $type['class']($item, $readerConfigElement);

            if (!$syndication->isEnabled()) {
                continue;
            }

            $syndications[$type['name']] = $syndication->generate();
        }

        $item->setFormattedValue(
            $readerConfigElement->name,
            $item->getManager()->getTwig()->render(System::getContainer()->get('huh.utils.template')->getTemplate($readerConfigElement->syndicationTemplate),
                ['links' => $syndications]
            )
        );
    }
}
