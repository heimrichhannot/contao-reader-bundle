<?php

/*
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ReaderBundle\ConfigElementType\Syndication;

use HeimrichHannot\ReaderBundle\ConfigElementType\Syndication\Link\DefaultLink;
use HeimrichHannot\ReaderBundle\ConfigElementType\Syndication\Link\LinkInterface;

class WhatsAppSyndication extends AbstractSyndication
{
    /**
     * {@inheritdoc}
     */
    public function generate(): LinkInterface
    {
        $link = new DefaultLink();
        $link->setCssClass('whatsapp');
        $link->setRel('nofollow');
        $link->setTitle('huh.reader.element.title.whatsapp');
        $link->setContent('huh.reader.element.title.whatsapp');
        $link->setHref(sprintf('https://wa.me/?text=%s%s', $this->getDescription() ? (rawurlencode($this->getDescription()).'%0A%0A') : '', rawurlencode($this->getUrl())));
        $link->addAttribute('data-action', 'share/whatsapp/share');

        return $link;
    }

    /**
     * {@inheritdoc}
     */
    public function isEnabled(): bool
    {
        return true === (bool) $this->readerConfigElement->syndicationWhatsApp;
    }
}
