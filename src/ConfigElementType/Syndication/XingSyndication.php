<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ReaderBundle\ConfigElementType\Syndication;

use HeimrichHannot\ReaderBundle\ConfigElementType\Syndication\Link\DefaultLink;
use HeimrichHannot\ReaderBundle\ConfigElementType\Syndication\Link\LinkInterface;

class XingSyndication extends AbstractSyndication
{
    /**
     * {@inheritdoc}
     */
    public function generate(): LinkInterface
    {
        $link = new DefaultLink();
        $link->setCssClass('xing');
        $link->setRel('nofollow');
        $link->setTitle('huh.reader.element.title.xing');
        $link->setContent('huh.reader.element.title.xing');
        $link->setTarget('_blank');
        $link->setOnClick('window.open(this.href,\'\',\'width=460,height=460,modal=yes,left=100,top=50,location=no,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no\');return false');
        $link->setHref(sprintf('https://www.xing.com/social_plugins/share/new?sc_p=xing-share&h=1&contao-defas-bundle.es6.jsurl=%s', rawurlencode($this->getUrl())));

        return $link;
    }

    /**
     * {@inheritdoc}
     */
    public function isEnabled(): bool
    {
        return true === (bool) $this->readerConfigElement->syndicationXing;
    }
}
