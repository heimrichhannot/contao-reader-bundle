<?php

/*
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ReaderBundle\Backend;

use Contao\BackendUser;
use Contao\Controller;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\DataContainer;
use Contao\Image;
use Contao\StringUtil;
use Contao\System;
use HeimrichHannot\ReaderBundle\Registry\ReaderConfigRegistry;
use HeimrichHannot\UtilsBundle\Dca\DcaUtil;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;

class ReaderConfig
{
    const ITEM_RETRIEVAL_MODE_AUTO_ITEM = 'auto_item';
    const ITEM_RETRIEVAL_MODE_FIELD_CONDITIONS = 'field_conditions';

    const ITEM_RETRIEVAL_MODES = [
        self::ITEM_RETRIEVAL_MODE_AUTO_ITEM,
        self::ITEM_RETRIEVAL_MODE_FIELD_CONDITIONS,
    ];

    /** @var ContaoFrameworkInterface */
    protected $framework;

    /** @var ReaderConfigRegistry */
    protected $readerConfigRegistry;

    /** @var ModelUtil */
    protected $modelUtil;

    /** @var DcaUtil */
    protected $dcaUtil;

    public function __construct(ContaoFrameworkInterface $framework, ReaderConfigRegistry $readerConfigRegistry, ModelUtil $modelUtil, DcaUtil $dcaUtil)
    {
        $this->framework = $framework;
        $this->readerConfigRegistry = $readerConfigRegistry;
        $this->modelUtil = $modelUtil;
        $this->dcaUtil = $dcaUtil;
    }

    public function modifyPalette(DataContainer $dc)
    {
        if (null !== ($readerConfig = $this->readerConfigRegistry->findByPk($dc->id))) {
            $dca = &$GLOBALS['TL_DCA']['tl_reader_config'];

            $readerConfig = $this->modelUtil->findRootParentRecursively(
                'parentReaderConfig', 'tl_reader_config', $readerConfig
            );

            if ($readerConfig->dataContainer) {
                foreach (['itemRetrievalFieldConditions', 'showFieldConditions', 'redirectFieldConditions'] as $field) {
                    $dca['fields'][$field]['eval']['multiColumnEditor']['table'] = $readerConfig->dataContainer;
                }
            }
        }
    }

    /**
     * static because else db field won't get added by install tool.
     */
    public static function addOverridableFields()
    {
        $dca = &$GLOBALS['TL_DCA']['tl_reader_config'];

        $overridableFields = [];

        foreach ($dca['fields'] as $field => $data) {
            $overrideFieldname = 'override'.ucfirst($field);

            if (isset($data['eval']['notOverridable']) || isset($dca['fields'][$overrideFieldname]) ||
                isset($data['eval']['isOverrideSelector'])) {
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

    public function flattenPaletteForSubEntities(DataContainer $dc)
    {
        if (null !== ($readerConfig = $this->readerConfigRegistry->findByPk($dc->id))) {
            if ($readerConfig->parentReaderConfig) {
                $dca = &$GLOBALS['TL_DCA']['tl_reader_config'];

                $overridableFields = [];

                foreach ($dca['fields'] as $field => $data) {
                    if (isset($data['eval']['notOverridable']) || isset($data['eval']['isOverrideSelector'])) {
                        continue;
                    }

                    $overridableFields[] = $field;
                }

                $this->dcaUtil->flattenPaletteForSubEntities('tl_reader_config', $overridableFields);

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

        return sprintf('<a href="%s" title="%s" class="edit">%s</a>', Controller::addToUrl($href.'&amp;id='.$row['id']), $title, Image::getHtml($icon, $label));
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
            ? '<a href="'.Controller::addToUrl($href.'&amp;id='.$row['id'].'&amp;rt='.REQUEST_TOKEN).'" title="'.StringUtil::specialchars($title).'"'.$attributes
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
            if (null !== ($readerConfig = $this->readerConfigRegistry->findByPk($row['parentReaderConfig']))) {
                $label .= '<span style="padding-left:3px;color:#b3b3b3;">['.$GLOBALS['TL_LANG']['MSC']['readerBundle']['parentConfig'].': '.$readerConfig->title.']</span>';
            }
        }

        return $label;
    }
}
