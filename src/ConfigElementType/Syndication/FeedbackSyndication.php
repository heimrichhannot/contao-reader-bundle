<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ReaderBundle\ConfigElementType\Syndication;

use Contao\StringUtil;
use Contao\System;
use HeimrichHannot\ReaderBundle\ConfigElementType\Syndication\Link\DefaultLink;
use HeimrichHannot\ReaderBundle\ConfigElementType\Syndication\Link\LinkInterface;

class FeedbackSyndication extends AbstractSyndication
{
    /**
     * {@inheritdoc}
     */
    public function generate(): LinkInterface
    {
        $link = new DefaultLink();
        $link->setCssClass('feedback');
        $link->setTitle('huh.reader.element.title.feedback');
        $link->setContent('huh.reader.element.title.feedback');
        $link->setHref(
            sprintf('mailto:%s?subject=%s&body=%s',
                $this->readerConfigElement->feedbackEmail,
                rawurlencode(StringUtil::decodeEntities(System::getContainer()->get('translator')->trans($this->readerConfigElement->feedbackSubjectLabel, ['%title%' => $this->getTitle(), '%url' => $this->getUrl()]))),
                rawurlencode(StringUtil::decodeEntities(System::getContainer()->get('translator')->trans($this->readerConfigElement->feedbackBodyLabel, ['%title%' => $this->getTitle(), '%url%' => $this->getUrl()])))
            )
        );

        return $link;
    }

    /**
     * {@inheritdoc}
     */
    public function isEnabled(): bool
    {
        return true === (bool) $this->readerConfigElement->syndicationFeedback;
    }
}
