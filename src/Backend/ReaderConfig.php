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
        $arrayUtil = System::getContainer()->get('huh.utils.array');

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

        // palette
        // remove data container
        unset($dca['fields']['dataContainer']);

        foreach ($overridableFields as $field) {
            if ($dca['fields'][$field]['eval']['submitOnChange'] === true) {
                unset($dca['fields'][$field]['eval']['submitOnChange']);

                if (in_array($field, $dca['palettes']['__selector__'], true)) {
                    // flatten concatenated type selectors
                    foreach ($dca['subpalettes'] as $selector => $subPaletteFields) {
                        if (false !== strpos($selector, $field.'_')) {
                            if ($dca['subpalettes'][$selector]) {
                                $subPaletteFields = explode(',', $dca['subpalettes'][$selector]);

                                foreach (array_reverse($subPaletteFields) as $subPaletteField) {
                                    $dca['palettes']['default'] = str_replace($field, $field.','.$subPaletteField, $dca['palettes']['default']);
                                }
                            }

                            // remove nested field in order to avoid its normal "selector" behavior
                            $arrayUtil->removeValue($field, $dca['palettes']['__selector__']);
                            unset($dca['subpalettes'][$selector]);
                        }
                    }

                    // flatten sub palettes
                    if (isset($dca['subpalettes'][$field]) && $dca['subpalettes'][$field]) {
                        $subPaletteFields = explode(',', $dca['subpalettes'][$field]);

                        foreach (array_reverse($subPaletteFields) as $subPaletteField) {
                            $dca['palettes']['default'] = str_replace($field, $field.','.$subPaletteField, $dca['palettes']['default']);
                        }

                        // remove nested field in order to avoid its normal "selector" behavior
                        $arrayUtil->removeValue($field, $dca['palettes']['__selector__']);
                        unset($dca['subpalettes'][$field]);
                    }
                }
            }

            $dca['palettes']['default'] = str_replace($field, 'override'.ucfirst($field), $dca['palettes']['default']);
        }

        // sub palettes
//        foreach ($overridableFields as $field)
//        {
//            foreach ($dca['subpalettes'] as $selector => $subPalettePields)
//            {
//                if ($selector == 'override' . ucfirst($field))
//                {
//                    continue;
//                }
//
//                $dca['subpalettes'][$selector] = str_replace($field, 'override' . ucfirst($field), $subPalettePields);
//            }
//        }

//        echo '<pre>';
//        var_dump(array_keys($dca['fields']));
//        var_dump($dca['palettes']);
//        var_dump($dca['subpalettes']);
//        echo '</pre>';
//        die();
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
}
