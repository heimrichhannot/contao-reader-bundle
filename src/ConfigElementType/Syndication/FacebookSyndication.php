<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ReaderBundle\ConfigElementType\Syndication;

use HeimrichHannot\ReaderBundle\ConfigElementType\Syndication\Link\DefaultLink;
use HeimrichHannot\ReaderBundle\ConfigElementType\Syndication\Link\LinkInterface;

class FacebookSyndication extends AbstractSyndication
{
    /**
     * {@inheritdoc}
     */
    public function generate(): LinkInterface
    {
        $link = new DefaultLink();
        $link->setCssClass('facebook');
        $link->setRel('nofollow');
        $link->setTitle('huh.reader.element.title.facebook');
        $link->setContent('huh.reader.element.title.facebook');
        $link->setTarget('_blank');
        $link->setOnClick('window.open(this.href,\'\',\'width=640,height=380,modal=yes,left=100,top=50,location=no,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no\');return false');
        $link->setHref(sprintf('https://www.facebook.com/sharer/sharer.php?u=%s&amp;t=%s', $this->getUrl(), $this->getTitle()));

        return $link;
    }

    /**
     * {@inheritdoc}
     */
    public function isEnabled(): bool
    {
        return true === (bool) $this->readerConfigElement->syndicationFacebook;
    }
}
