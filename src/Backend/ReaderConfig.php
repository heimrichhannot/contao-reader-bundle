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

    public function modifyPalette(DataContainer $dc)
    {
        if (null !== ($readerConfig = System::getContainer()->get('huh.reader.reader-config-registry')->findByPk($dc->id))) {
            $dca = &$GLOBALS['TL_DCA']['tl_reader_config'];

            $readerConfig = System::getContainer()->get('huh.utils.model')->findRootParentRecursively(
                'parentReaderConfig', 'tl_reader_config', $readerConfig
            );

            if ($readerConfig->dataContainer) {
                foreach (['itemRetrievalFieldConditions', 'showItemConditions', 'redirectFieldConditions'] as $field) {
                    $dca['fields'][$field]['eval']['multiColumnEditor']['table'] = $readerConfig->dataContainer;
                }
            }
        }
    }

    public static function addOverridableFields()
    {
        $dca = &$GLOBALS['TL_DCA']['tl_reader_config'];

        $overridableFields = [];

        foreach ($dca['fields'] as $field => $data) {
            if (isset($data['eval']['notOverridable'])) {
                continue;
            }

            $overridableFields[] = $field;
        }

        System::getContainer()->get('huh.utils.dca')->addOverridableFields(
            $overridableFields,
            'tl_reader_config',
            'tl_reader_config',
            [
                'checkboxDcaEvalOverride' => [
                    'tl_class' => 'w50 clr',
                ],
            ]
        );
    }

    public static function flattenPaletteForSubEntities(DataContainer $dc)
    {
        if (null !== ($readerConfig = System::getContainer()->get('huh.reader.reader-config-registry')->findByPk($dc->id))) {
            if ($readerConfig->parentReaderConfig) {
                $dca = &$GLOBALS['TL_DCA']['tl_reader_config'];

                $overridableFields = [];

                foreach ($dca['fields'] as $field => $data) {
                    if (isset($data['eval']['notOverridable']) || isset($data['eval']['isOverrideSelector'])) {
                        continue;
                    }

                    $overridableFields[] = $field;
                }

                System::getContainer()->get('huh.utils.dca')->flattenPaletteForSubEntities('tl_reader_config', $overridableFields);

                // remove data container
                unset($dca['fields']['dataContainer']);
            }
        }
    }

    public function edit($row, $href, $label, $title, $icon, $attributes)
    {
        if ($row['parentReaderConfig']) {
            return '';
        }

        return sprintf('<a href="%s" title="%s" class="edit">%s</a>', $this->addToUrl($href.'&amp;id='.$row['id']), $title, Image::getHtml($icon, $label));
    }

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

    /**
     * @param array
     * @param string
     * @param object
     * @param string
     *
     * @return string
     */
    public function generateLabel($row, $label, $dca, $attributes)
    {
        if ($row['parentReaderConfig']) {
            if (null !== ($readerConfig = System::getContainer()->get('huh.reader.reader-config-registry')->findByPk($row['parentReaderConfig']))) {
                $label .= '<span style="padding-left:3px;color:#b3b3b3;">['.$GLOBALS['TL_LANG']['MSC']['readerBundle']['parentConfig'].': '.$readerConfig->title.']</span>';
            }
        }

        return $label;
    }
}
