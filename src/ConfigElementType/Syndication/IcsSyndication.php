<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
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
use Jsvrcek\ICS\CalendarExport;
use Jsvrcek\ICS\CalendarStream;
use Jsvrcek\ICS\Model\Calendar;
use Jsvrcek\ICS\Model\CalendarEvent;
use Jsvrcek\ICS\Model\Description\Location;
use Jsvrcek\ICS\Utility\Formatter;
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

        // prepare data
        $addTime = $readerConfigElement->syndicationIcsAddTime && $item->getRawValue($readerConfigElement->syndicationIcsAddTimeField);
        $end = null;

        if ($addTime && $item->getRawValue($readerConfigElement->syndicationIcsStartTimeField)) {
            $start = (new \DateTime())->setTimestamp($item->getRawValue($readerConfigElement->syndicationIcsStartTimeField));
        } else {
            $start = (new \DateTime())->setTimestamp($item->getRawValue($readerConfigElement->syndicationIcsStartDateField));
            $start->setTime(0, 0, 0);
        }

        if ($readerConfigElement->syndicationIcsEndDateField && $item->getRawValue($readerConfigElement->syndicationIcsEndDateField)) {
            // workaround for allday events
            $end = (new \DateTime())->setTimestamp($item->getRawValue($readerConfigElement->syndicationIcsEndDateField) + ($addTime ? 0 : 86400));
            $end->setTime(0, 0, 0);
        }

        if ($addTime && $readerConfigElement->syndicationIcsEndTimeField && $item->getRawValue($readerConfigElement->syndicationIcsEndTimeField)) {
            $end = (new \DateTime())->setTimestamp($item->getRawValue($readerConfigElement->syndicationIcsEndTimeField));
        }

        // create an event
        $event = new CalendarEvent();

        $event->setAllDay(!$addTime);
        $event->setStart($start);

        if (null !== $end) {
            $event->setEnd($end);
        }

        if ($readerConfigElement->syndicationIcsTitleField && $item->getFormattedValue($readerConfigElement->syndicationIcsTitleField)) {
            $event->setSummary(strip_tags($item->getFormattedValue($readerConfigElement->syndicationIcsTitleField)));
        }

        if ($readerConfigElement->syndicationIcsDescriptionField && $item->getFormattedValue($readerConfigElement->syndicationIcsDescriptionField)) {
            // preserve linebreaks
            $description = preg_replace('@<br\s*/?>@i', "\n", $item->getFormattedValue($readerConfigElement->syndicationIcsDescriptionField));
            $description = preg_replace('@</p>\s*<p>@i', "\n\n", $description);

            $event->setDescription(strip_tags($description));
        }

        if ($readerConfigElement->syndicationIcsLocationField && $item->getFormattedValue($readerConfigElement->syndicationIcsLocationField)) {
            $location = new Location();

            $location->setName($item->getFormattedValue($readerConfigElement->syndicationIcsLocationField));
            $location->setLanguage($GLOBALS['TL_LANGUAGE']);

            $event->addLocation($location);
        }

        if ($readerConfigElement->syndicationIcsUrlField && $item->getFormattedValue($readerConfigElement->syndicationIcsUrlField)) {
            $url = $item->getFormattedValue($readerConfigElement->syndicationIcsUrlField);
        } else {
            $url = $this->getUrl();
        }

        $event->setUrl(System::getContainer()->get('huh.utils.url')->removeQueryString([$readerConfigElement->name], $url));

        // create a calendar
        $calendar = new Calendar();

        $calendar->setTimezone(new \DateTimeZone(\Config::get('timeZone')));
        $calendar->addEvent($event);

        $calendarExport = new CalendarExport(new CalendarStream(), new Formatter());

        // store to ics
        $calendarExport->addCalendar($calendar);

        $response = new Response($calendarExport->getStream());
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
