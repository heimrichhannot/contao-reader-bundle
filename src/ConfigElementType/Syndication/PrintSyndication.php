<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ReaderBundle\ConfigElementType\Syndication;

use Contao\System;
use HeimrichHannot\ReaderBundle\ConfigElementType\Syndication\Link\DefaultLink;
use HeimrichHannot\ReaderBundle\ConfigElementType\Syndication\Link\LinkInterface;
use HeimrichHannot\ReaderBundle\Item\ItemInterface;
use HeimrichHannot\ReaderBundle\Model\ReaderConfigElementModel;

class PrintSyndication extends AbstractSyndication
{
    const PRINT_QUERY_PARAM = 'rp';
    const PRINT_DEBUG_QUERY_PARAM = 'debug';

    /**
     * AbstractSyndication constructor.
     */
    public function __construct(ItemInterface $item, ReaderConfigElementModel $readerConfigElement)
    {
        parent::__construct($item, $readerConfigElement);

        if ((int) $readerConfigElement->id === (int) System::getContainer()->get('huh.request')->query->get(static::PRINT_QUERY_PARAM)) {
            $this->print();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function generate(): LinkInterface
    {
        $link = new DefaultLink();
        $link->setCssClass('print');
        $link->setTitle('huh.reader.element.title.print');
        $link->setContent('huh.reader.element.title.print');
        $link->setTarget('_blank');
        $link->setHref(System::getContainer()->get('huh.utils.url')->addQueryString(static::PRINT_QUERY_PARAM.'='.$this->readerConfigElement->id, $this->getUrl()));

        return $link;
    }

    /**
     * Print current item based on reader config element inside custom page layout, open print dialog and close browser tab on close.
     */
    public function print()
    {
        $data = $this->item->jsonSerialize();
        $data['isRTL'] = 'rtl' === $GLOBALS['TL_LANG']['MSC']['textDirection'];
        $data['language'] = $GLOBALS['TL_LANGUAGE'];
        $data['charset'] = \Config::get('characterSet');
        $data['base'] = \Environment::get('base');
        $data['onload'] = sprintf('window.print();%s', (bool) System::getContainer()->get('huh.request')->query->get(static::PRINT_DEBUG_QUERY_PARAM) ? '' : 'setTimeout(window.close, 0);');
        $data['title'] = $this->getTitle();

        die(System::getContainer()->get('huh.utils.string')->replaceInsertTags(
            $this->item->getManager()->getTwig()->render(System::getContainer()->get('huh.utils.template')->getTemplate(
                $this->readerConfigElement->syndicationPrintTemplate),
                $data))
        );
    }

    /**
     * {@inheritdoc}
     */
    public function isEnabled(): bool
    {
        return true === (bool) $this->readerConfigElement->syndicationPrint;
    }
}
