<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
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
    const ITEM_RETRIEVAL_MODE_AUTO_ITEM = 'auto_item';
    const ITEM_RETRIEVAL_MODE_FIELD_CONDITIONS = 'field_conditions';

    const ITEM_RETRIEVAL_MODES = [
        self::ITEM_RETRIEVAL_MODE_AUTO_ITEM,
        self::ITEM_RETRIEVAL_MODE_FIELD_CONDITIONS,
    ];

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
                foreach (['itemRetrievalFieldConditions', 'showItemConditions', 'redirectFieldConditions'] as $field) {
                    $dca['fields'][$field]['eval']['multiColumnEditor']['table'] = $readerConfig->dataContainer;
                }
            }
        }
    }
}
