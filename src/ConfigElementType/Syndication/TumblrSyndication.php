<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ReaderBundle\ConfigElementType\Syndication;

use HeimrichHannot\ReaderBundle\ConfigElementType\Syndication\Link\DefaultLink;
use HeimrichHannot\ReaderBundle\ConfigElementType\Syndication\Link\LinkInterface;

class TumblrSyndication extends AbstractSyndication
{
    /**
     * {@inheritdoc}
     */
    public function generate(): LinkInterface
    {
        $link = new DefaultLink();
        $link->setCssClass('tumblr');
        $link->setRel('nofollow');
        $link->setTitle('huh.reader.element.title.tumblr');
        $link->setContent('huh.reader.element.title.tumblr');
        $link->setTarget('_blank');
        $link->setOnClick('window.open(this.href,\'\',\'width=800,height=450,modal=yes,left=100,top=50,location=no,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no\');return false');
        $link->setHref(sprintf('http://www.tumblr.com/share?v=3&u=%s&t=%s&s=%s', rawurlencode($this->getUrl()), rawurlencode($this->getTitle()), rawurlencode($this->getDescription())));

        return $link;
    }

    /**
     * {@inheritdoc}
     */
    public function isEnabled(): bool
    {
        return true === (bool) $this->readerConfigElement->syndicationTumblr;
    }
}
