<?php

/*
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ReaderBundle\ConfigElementType;

use HeimrichHannot\ReaderBundle\ConfigElementType\Syndication\AbstractSyndication;
use HeimrichHannot\ReaderBundle\Item\ItemInterface;
use HeimrichHannot\ReaderBundle\Model\ReaderConfigElementModel;
use Symfony\Component\DependencyInjection\ContainerInterface;

class SyndicationConfigElementType implements ReaderConfigElementTypeInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function addToItemData(ItemInterface $item, ReaderConfigElementModel $readerConfigElement)
    {
        $syndications = [];

        $config = $this->container->getParameter('huh.reader');

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
            $this->container->get('twig')->render($this->container->get('huh.utils.template')->getTemplate($readerConfigElement->syndicationTemplate),
                ['links' => $syndications]
            )
        );
    }

    /**
     * Return the reader config element type alias.
     *
     * @return string
     */
    public static function getType(): string
    {
        return 'syndication';
    }

    /**
     * Return the reader config element type palette.
     *
     * @return string
     */
    public function getPalette(): string
    {
        return '{config_legend},name,syndicationTemplate,syndicationFacebook,syndicationTwitter,syndicationGooglePlus,syndicationLinkedIn,syndicationXing,syndicationMail,syndicationPdf,syndicationPrint,syndicationIcs,syndicationTumblr,syndicationPinterest,syndicationReddit,syndicationWhatsApp;';
    }

    /**
     * Update the item data.
     *
     * @param ReaderConfigElementData $configElementData
     */
    public function addToReaderItemData(ReaderConfigElementData $configElementData): void
    {
        $this->addToItemData($configElementData->getItem(), $configElementData->getReaderConfigElement());
    }
}
