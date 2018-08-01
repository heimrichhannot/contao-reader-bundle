<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ReaderBundle\ConfigElementType\Syndication;

use Contao\StringUtil;
use Contao\System;
use HeimrichHannot\ReaderBundle\ConfigElementType\Syndication\Link\DefaultLink;
use HeimrichHannot\ReaderBundle\ConfigElementType\Syndication\Link\LinkInterface;

class MailSyndication extends AbstractSyndication
{
    /**
     * {@inheritdoc}
     */
    public function generate(): LinkInterface
    {
        $link = new DefaultLink();
        $link->setCssClass('mail');
        $link->setTitle('huh.reader.element.title.mail');
        $link->setContent('huh.reader.element.title.mail');
        $link->setHref(
            sprintf('mailto:?subject=%s&body=%s',
                rawurlencode(StringUtil::decodeEntities(System::getContainer()->get('translator')->trans($this->readerConfigElement->mailSubjectLabel, ['%title%' => $this->getTitle(), '%url' => $this->getUrl()]))),
                rawurlencode(StringUtil::decodeEntities(System::getContainer()->get('translator')->trans($this->readerConfigElement->mailBodyLabel, ['%title%' => $this->getTitle(), '%url%' => $this->getUrl()])))
            )
        );

        return $link;
    }

    /**
     * {@inheritdoc}
     */
    public function isEnabled(): bool
    {
        return true === (bool) $this->readerConfigElement->syndicationMail;
    }
}
