<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ReaderBundle\ConfigElementType\Syndication;

use Contao\CoreBundle\Exception\ResponseException;
use Contao\System;
use HeimrichHannot\ReaderBundle\ConfigElementType\Syndication\Link\DefaultLink;
use HeimrichHannot\ReaderBundle\ConfigElementType\Syndication\Link\LinkInterface;
use HeimrichHannot\ReaderBundle\Item\ItemInterface;
use HeimrichHannot\ReaderBundle\Model\ReaderConfigElementModel;
use HeimrichHannot\UtilsBundle\Ics\IcsUtil;
use Symfony\Component\HttpFoundation\Response;

class IcsSyndication extends AbstractSyndication
{
    public function __construct(ItemInterface $item, ReaderConfigElementModel $readerConfigElement)
    {
        parent::__construct($item, $readerConfigElement);

        if (System::getContainer()->get('huh.request')->query->get($readerConfigElement->name)) {
            $this->exportIcs($item);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function generate(): LinkInterface
    {
        $link = new DefaultLink();
        $link->setCssClass('ics');
        $link->setRel('nofollow');
        $link->setTitle('huh.reader.element.title.ics');
        $link->setContent('huh.reader.element.title.ics');
        $link->setHref(System::getContainer()->get('huh.utils.url')->addQueryString($this->readerConfigElement->name.'=1', $this->getUrl()));

        return $link;
    }

    public function exportIcs(ItemInterface $item)
    {
        $readerConfigElement = $this->readerConfigElement;

        $icsData = System::getContainer()->get(IcsUtil::class)->generateIcs([
            'title' => $readerConfigElement->syndicationIcsTitleField && $item->getFormattedValue($readerConfigElement->syndicationIcsTitleField),
            'description' => $readerConfigElement->syndicationIcsDescriptionField && $item->getFormattedValue($readerConfigElement->syndicationIcsDescriptionField),
            'street' => $readerConfigElement->syndicationIcsStreetField && $item->getFormattedValue($readerConfigElement->syndicationIcsStreetField),
            'postal' => $readerConfigElement->syndicationIcsPostalField && $item->getFormattedValue($readerConfigElement->syndicationIcsPostalField),
            'city' => $readerConfigElement->syndicationIcsCityField && $item->getFormattedValue($readerConfigElement->syndicationIcsCityField),
            'country' => $readerConfigElement->syndicationIcsCountryField && $item->getFormattedValue($readerConfigElement->syndicationIcsCountryField),
            'location' => $readerConfigElement->syndicationIcsLocationField && $item->getFormattedValue($readerConfigElement->syndicationIcsLocationField),
            'url' => $readerConfigElement->syndicationIcsUrlField && $item->getFormattedValue($readerConfigElement->syndicationIcsUrlField),
            'startDate' => $readerConfigElement->syndicationIcsStartDateField && $item->getRawValue($readerConfigElement->syndicationIcsStartDateField),
            'endDate' => $readerConfigElement->syndicationIcsEndDateField && $item->getRawValue($readerConfigElement->syndicationIcsEndDateField),
            'addTime' => $readerConfigElement->syndicationIcsAddTime && $item->getRawValue($readerConfigElement->syndicationIcsAddTimeField),
            'startTime' => $readerConfigElement->syndicationIcsStartTimeField && $item->getRawValue($readerConfigElement->syndicationIcsStartTimeField),
            'endTime' => $readerConfigElement->syndicationIcsEndTimeField && $item->getRawValue($readerConfigElement->syndicationIcsEndTimeField),
        ]);

        if (!$icsData) {
            return;
        }

        $response = new Response($icsData);
        $response->headers->set('Content-Type', 'text/calendar');

        throw new ResponseException($response);
    }

    /**
     * {@inheritdoc}
     */
    public function isEnabled(): bool
    {
        return true === (bool) $this->readerConfigElement->syndicationIcs;
    }
}
