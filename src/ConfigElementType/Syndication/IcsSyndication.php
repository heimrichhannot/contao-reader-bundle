<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
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
use Jsvrcek\ICS\Utility\Formatter;
use Symfony\Component\HttpFoundation\Response;

class IcsSyndication extends AbstractSyndication
{
    /**
     * @param ItemInterface            $item
     * @param ReaderConfigElementModel $readerConfigElement
     */
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
        $link->setHref(System::getContainer()->get('huh.utils.url')->addQueryString($this->readerConfigElement->name.'=1', $this->getUrl()));

        return $link;
    }

    public function exportIcs(ItemInterface $item)
    {
        $container = System::getContainer();
        $readerConfigElement = $this->readerConfigElement;

        // prepare data
        $addTime = $readerConfigElement->syndicationIcsAddTime && $item->{$readerConfigElement->syndicationIcsAddTimeField};
        $end = null;

        if ($addTime && $item->{$readerConfigElement->syndicationIcsStartTimeField}) {
            $start = new \DateTime($item->{$readerConfigElement->syndicationIcsStartTimeField});
        } else {
            $start = new \DateTime($item->{$readerConfigElement->syndicationIcsStartDateField});
            $start->setTime(0, 0, 0);
        }

        if ($readerConfigElement->syndicationIcsEndDateField && $item->{$readerConfigElement->syndicationIcsEndDateField}) {
            if ($addTime && $item->{$readerConfigElement->syndicationIcsEndTimeField}) {
                $end = new \DateTime($item->{$readerConfigElement->syndicationIcsEndTimeField});
            } else {
                $end = new \DateTime($item->{$readerConfigElement->syndicationIcsEndDateField});
                $end->setTime(0, 0, 0);
            }
        }

        // create an event
        $event = new CalendarEvent();

        $event->setStart($start);

        if (null !== $end) {
            $event->setEnd($end);
        }

        if ($readerConfigElement->syndicationIcsTitleField && $item->{$readerConfigElement->syndicationIcsTitleField}) {
            $event->setSummary(strip_tags($item->{$readerConfigElement->syndicationIcsTitleField}));
        }

        if ($readerConfigElement->syndicationIcsDescriptionField && $item->{$readerConfigElement->syndicationIcsDescriptionField}) {
            // preserve linebreaks
            $description = preg_replace('@<br\s*/?>@i', "\n", $item->{$readerConfigElement->syndicationIcsDescriptionField});

            $event->setDescription(strip_tags($description));
        }

//        $event->setUrl()

        // create a calendar
        $calendar = new Calendar();
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
