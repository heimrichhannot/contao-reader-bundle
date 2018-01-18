<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0+
 */

namespace HeimrichHannot\ReaderBundle\Backend;

use Contao\Backend;
use Contao\BackendUser;
use Contao\DataContainer;
use Contao\Image;
use Contao\StringUtil;
use Contao\System;

class ReaderConfig extends Backend
{
    /**
     * Return the edit header button.
     *
     * @param array  $row
     * @param string $href
     * @param string $label
     * @param string $title
     * @param string $icon
     * @param string $attributes
     *
     * @return string
     */
    public function editHeader($row, $href, $label, $title, $icon, $attributes)
    {
        return BackendUser::getInstance()->canEditFieldsOf('tl_reader_config')
            ? '<a href="'.$this->addToUrl($href.'&amp;id='.$row['id']).'" title="'.StringUtil::specialchars($title).'"'.$attributes
              .'>'.Image::getHtml($icon, $label).'</a> '
            : Image::getHtml(
                preg_replace('/\.svg$/i', '_.svg', $icon)
            ).' ';
    }

    public function modifyPalette(DataContainer $dc)
    {
        if (null !== ($readerConfig = System::getContainer()->get('huh.utils.model')->findModelInstanceByPk('tl_reader_config', $dc->id))) {
            $dca = &$GLOBALS['TL_DCA']['tl_reader_config'];

            if ($readerConfig->dataContainer) {
                $dca['fields']['showConditions']['eval']['multiColumnEditor']['table'] = $readerConfig->dataContainer;
            }
        }
    }
}
