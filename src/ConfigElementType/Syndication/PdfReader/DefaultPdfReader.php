<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ReaderBundle\ConfigElementType\Syndication\PdfReader;

use Contao\Config;
use Contao\Controller;
use Contao\Environment;
use Contao\LayoutModel;
use Contao\PageModel;
use Contao\StringUtil;
use Contao\System;
use HeimrichHannot\TwigSupportBundle\Filesystem\TwigTemplateLocator;

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
        $data['charset'] = Config::get('characterSet');
        $data['base'] = Environment::get('base');
        $data['title'] = $this->getSyndication()->getTitle();

        /* @var PageModel $objPage */
        global $objPage;

        if (System::getContainer()->has('huh.encore.asset.template') && $objPage) {
            $layout = LayoutModel::findById($objPage->layout);

            if ($layout) {
                $templateAssets = System::getContainer()->get('huh.encore.asset.template')->createInstance($objPage, $layout);
                $data['encoreStylesheets'] = $templateAssets->linkTags();
            }
        }

        $result = $this->item->getManager()->getTwig()->render(System::getContainer()->get(TwigTemplateLocator::class)->getTemplatePath($this->readerConfigElement->syndicationPdfTemplate), $data);

        $result = StringUtil::restoreBasicEntities($result);
        $result = Controller::replaceInsertTags($result);

        return $result;
    }
}
