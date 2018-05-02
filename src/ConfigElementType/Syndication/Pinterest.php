<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ReaderBundle\ConfigElementType\Syndication;

use Contao\Image;
use Contao\StringUtil;
use Contao\System;
use HeimrichHannot\ReaderBundle\ConfigElementType\Syndication\Link\DefaultLink;
use HeimrichHannot\ReaderBundle\ConfigElementType\Syndication\Link\LinkInterface;

class Pinterest extends AbstractSyndication
{
    /**
     * {@inheritdoc}
     */
    public function generate(): LinkInterface
    {
        /**
         * @var Image
         */
        $imgSize = StringUtil::deserialize($this->readerConfigElement->imgSize, true);
        $imgPath = System::getContainer()->get('huh.utils.file')->getPathFromUuid($this->item->{$this->readerConfigElement->imageField});

        if (!empty(array_filter($imgSize))) {
            $imgPath = System::getContainer()->get('contao.image.image_factory')->create(
                $imgPath, StringUtil::deserialize($this->readerConfigElement->imgSize, true)
            )->getUrl(System::getContainer()->get('huh.utils.container')->getProjectDir());
        }

        $link = new DefaultLink();
        $link->setCssClass('pinterest');
        $link->setRel('nofollow');
        $link->setTitle('huh.reader.element.title.pinterest');
        $link->setContent('huh.reader.element.title.pinterest');
        $link->setTarget('_blank');
        $link->setOnClick('window.open(this.href,\'\',\'width=460,height=460,modal=yes,left=100,top=50,location=no,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no\');return false');
        $link->setHref(sprintf('http://pinterest.com/pin/create/button/?url=%s&amp;media=%s&amp;description=%s', $this->getUrl(), $imgPath, $this->getDescription()));

        return $link;
    }

    /**
     * {@inheritdoc}
     */
    public function isEnabled(): bool
    {
        $check = (true === (bool) $this->readerConfigElement->syndicationPinterest);

        if (false === $check) {
            return false;
        }

        if (false === (bool) $this->item->{$this->readerConfigElement->imageSelectorField}) {
            return false;
        }

        if (!$this->item->{$this->readerConfigElement->imageField}) {
            return false;
        }

        if (null === System::getContainer()->get('huh.utils.file')->getPathFromUuid($this->item->{$this->readerConfigElement->imageField})) {
            return false;
        }

        return $check;
    }
}
