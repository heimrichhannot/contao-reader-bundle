<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ReaderBundle\ConfigElementType\Syndication\PdfReader;

use Contao\Controller;
use Contao\StringUtil;
use Contao\System;

class DefaultPdfReader extends AbstractPdfReader
{
    /**
     * {@inheritdoc}
     */
    public function getFileName(): string
    {
        return StringUtil::standardize($this->getSyndication()->getTitle()).'.pdf';
    }

    /**
     * {@inheritdoc}
     */
    protected function compile(): string
    {
        $data = $this->item->jsonSerialize();

        $data['isRTL'] = 'rtl' === $GLOBALS['TL_LANG']['MSC']['textDirection'];
        $data['language'] = $GLOBALS['TL_LANGUAGE'];
        $data['charset'] = \Config::get('characterSet');
        $data['base'] = \Environment::get('base');
        $data['title'] = $this->getSyndication()->getTitle();

        $result = $this->item->getManager()->getTwig()->render(System::getContainer()->get('huh.utils.template')->getTemplate($this->readerConfigElement->syndicationPdfTemplate), $data);

        $result = StringUtil::restoreBasicEntities($result);
        $result = Controller::replaceInsertTags($result);

        return $result;
    }
}
