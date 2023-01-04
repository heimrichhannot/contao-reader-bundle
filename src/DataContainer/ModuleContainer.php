<?php

/*
 * Copyright (c) 2023 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ReaderBundle\DataContainer;

use Contao\Controller;
use Contao\CoreBundle\ServiceAnnotation\Callback;
use Contao\DataContainer;
use Contao\Image;
use Contao\StringUtil;
use HeimrichHannot\UtilsBundle\Util\Utils;

class ModuleContainer
{
    private Utils $utils;

    public function __construct(Utils $utils)
    {
        $this->utils = $utils;
    }

    /**
     * @Callback(table="tl_module", target="fields.readerConfig.wizard")
     */
    public function onReaderConfigWizardCallback(DataContainer $dc): string
    {
        Controller::loadLanguageFile('tl_reader_config');

        $href = $this->utils->routing()->generateBackendRoute([
            'do' => 'reader_configs',
            'act' => 'edit',
            'id' => $dc->value,
            'popup' => '1',
            'nb' => '1',
        ], true, false);

        $title = sprintf(StringUtil::specialchars($GLOBALS['TL_LANG']['tl_reader_config']['edit'][1]), $dc->value);
        $onClick = 'Backend.openModalIframe({\'title\':\''.StringUtil::specialchars(str_replace("'", "\\'", sprintf(
                ($GLOBALS['TL_LANG']['tl_reader_config']['edit'][1] ?? 'Edit reader config ID %s'),
                $dc->value
            ))).'\',\'url\':this.href});return false';
        $image = Image::getHtml('alias.svg', ($GLOBALS['TL_LANG']['tl_reader_config']['edit'][0] ?? ''));

        return ($dc->value < 1) ? '' : sprintf(
            '<a href="%s" title="%s" onclick="%s">%s</a>',
            $href, $title, $onClick, $image
            );
    }
}
