<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ReaderBundle\ConfigElementType\Syndication;

use Contao\Environment;
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
        $link->setHref(sprintf('whatsapp://send?text=%s%s', $this->getDescription() ? (rawurlencode($this->getDescription()).'%0A%0A') : '', rawurlencode($this->getUrl())));
        $link->addAttribute('data-action', 'share/whatsapp/share');

        return $link;
    }

    /**
     * {@inheritdoc}
     */
    public function isEnabled(): bool
    {
        /**
         * @var Environment
         */
        $environment = $this->item->getManager()->getFramework()->getAdapter(Environment::class);

        return true === (bool) $this->readerConfigElement->syndicationWhatsApp && $environment->get('agent')->mobile;
    }
}
