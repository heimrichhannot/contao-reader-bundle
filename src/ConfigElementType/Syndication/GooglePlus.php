<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ReaderBundle\ConfigElementType\Syndication;

use HeimrichHannot\ReaderBundle\ConfigElementType\Syndication\Link\DefaultLink;
use HeimrichHannot\ReaderBundle\ConfigElementType\Syndication\Link\LinkInterface;

class GooglePlus extends AbstractSyndication
{
    /**
     * {@inheritdoc}
     */
    public function generate(): LinkInterface
    {
        $link = new DefaultLink();
        $link->setCssClass('google-plus');
        $link->setRel('nofollow');
        $link->setTitle('huh.reader.element.title.googleplus');
        $link->setContent('huh.reader.element.title.googleplus');
        $link->setTarget('_blank');
        $link->setOnClick('window.open(this.href,\'\',\'width=520,height=450,modal=yes,left=100,top=50,location=no,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no\');return false');
        $link->setHref(sprintf('https://plus.google.com/share?url=%s&amp', $this->getUrl()));

        return $link;
    }

    /**
     * {@inheritdoc}
     */
    public function isEnabled(): bool
    {
        return true === (bool) $this->readerConfigElement->syndicationGooglePlus;
    }
}
