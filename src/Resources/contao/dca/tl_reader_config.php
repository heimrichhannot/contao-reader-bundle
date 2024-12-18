<?php

/*
 * Copyright (c) 2023 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

use Contao\DC_Table;
use HeimrichHannot\ReaderBundle\Backend\ReaderConfig;
use HeimrichHannot\ReaderBundle\DataContainer\ReaderConfigContainer;

\Contao\Controller::loadDataContainer('tl_module');
\Contao\System::loadLanguageFile('tl_module');

$GLOBALS['TL_DCA']['tl_reader_config'] = [
    'config' => [
        'dataContainer' => DC_Table::class,
        'ptable' => 'tl_reader_config',
        'ctable' => ['tl_reader_config_element'],
        'enableVersioning' => true,
        'onload_callback' => [
            ['huh.reader.backend.reader-config', 'modifyPalette'],
            ['huh.reader.backend.reader-config', 'flattenPaletteForSubEntities'],
        ],
        'onsubmit_callback' => [
            ['huh.utils.dca', 'setDateAdded'],
        ],
        'oncopy_callback' => [
            ['huh.utils.dca', 'setDateAddedOnCopy'],
        ],
        'sql' => [
            'keys' => [
                'id' => 'primary',
            ],
        ],
    ],
    'list' => [
        'label' => [
            'fields' => ['title'],
            'format' => '%s',
        ],
        'sorting' => [
            'mode' => 5,
            'fields' => ['title'],
            'headerFields' => ['title'],
            'panelLayout' => 'filter;sort,search,limit',
            'paste_button_callback' => [ReaderConfigContainer::class, 'pasteReaderConfig'],
        ],
        'global_operations' => [
            'toggleNodes' => [
                'label' => &$GLOBALS['TL_LANG']['MSC']['toggleAll'],
                'href' => 'ptg=all',
                'class' => 'header_toggle',
            ],
            'sortAlphabetically' => [
                'label' => &$GLOBALS['TL_LANG']['tl_reader_config']['sortAlphabetically'],
                'href' => 'key=sort_alphabetically',
                'class' => 'header_toggle',
                'button_callback' => [ReaderConfigContainer::class, 'sortAlphabetically'],
            ],
            'all' => [
                'label' => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href' => 'act=select',
                'class' => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset();"',
            ],
        ],
        'operations' => [
            'edit' => [
                'label' => &$GLOBALS['TL_LANG']['tl_reader_config']['edit'],
                'href' => 'table=tl_reader_config_element',
                'icon' => 'edit.svg',
                'button_callback' => ['huh.reader.backend.reader-config', 'edit'],
            ],
            'editheader' => [
                'label' => &$GLOBALS['TL_LANG']['tl_reader_config']['editheader'],
                'href' => 'act=edit',
                'icon' => 'header.svg',
                'button_callback' => ['huh.reader.backend.reader-config', 'editHeader'],
            ],
            'copy' => [
                'label' => &$GLOBALS['TL_LANG']['tl_reader_config']['copy'],
                'href' => 'act=copy',
                'icon' => 'copy.gif',
            ],
            'copyChilds' => [
                'label' => &$GLOBALS['TL_LANG']['tl_reader_config']['copyChilds'],
                'href' => 'act=paste&amp;mode=copy&amp;childs=1',
                'icon' => 'copychilds.gif',
                'attributes' => 'onclick="Backend.getScrollOffset()"',
            ],
            'cut' => [
                'label' => &$GLOBALS['TL_LANG']['tl_reader_config']['cut'],
                'href' => 'act=paste&amp;mode=cut',
                'icon' => 'cut.svg',
                'attributes' => 'onclick="Backend.getScrollOffset()"',
            ],
            'delete' => [
                'label' => &$GLOBALS['TL_LANG']['tl_reader_config']['delete'],
                'href' => 'act=delete',
                'icon' => 'delete.gif',
                'attributes' => 'onclick="if(!confirm(\''.($GLOBALS['TL_LANG']['MSC']['deleteConfirm'] ?? null)
                    .'\'))return false;Backend.getScrollOffset()"',
            ],
            'show' => [
                'label' => &$GLOBALS['TL_LANG']['tl_reader_config']['show'],
                'href' => 'act=show',
                'icon' => 'show.gif',
            ],
        ],
    ],
    'palettes' => [
        '__selector__' => [
            'limitFormattedFields',
            'itemRetrievalMode',
            'hideUnpublishedItems',
            'addStartAndStop',
            'addShowConditions',
            'addFieldDependentRedirect',
            'addOverview',
            'overviewMode',
            'customJumpToOverviewLabel',
        ],
        'default' => '{general_legend},title;'
            .'{filter_legend},dataContainer,filter,evaluateFilter;'
            .'{config_legend},manager,item,itemRetrievalMode,hideUnpublishedItems;'
            .'{fields_legend},limitFormattedFields;'
            .'{security_legend},addShowConditions;'
            .'{jumpto_legend},addFieldDependentRedirect,addOverview,disable404;'
            .'{misc_legend},headTags;'
            .'{extensions_legend},addDcMultilingualSupport,addMultilingualFieldsSupport;'
            .'{template_legend},itemTemplate;',
    ],
    'subpalettes' => [
        'limitFormattedFields' => 'formattedFields',
        'itemRetrievalMode_'.ReaderConfig::ITEM_RETRIEVAL_MODE_AUTO_ITEM => 'itemRetrievalAutoItemField',
        'itemRetrievalMode_'.ReaderConfig::ITEM_RETRIEVAL_MODE_FIELD_CONDITIONS => 'itemRetrievalFieldConditions',
        'hideUnpublishedItems' => 'publishedField,invertPublishedField,addStartAndStop',
        'addStartAndStop' => 'startField,stopField',
        'addShowConditions' => 'showFieldConditions',
        'addFieldDependentRedirect' => 'fieldDependentJumpTo,redirectFieldConditions',
        'addOverview' => 'overviewMode,jumpToOverviewMultilingual,customJumpToOverviewLabel',
        'overviewMode_jumpTo' => 'jumpToOverview',
        'customJumpToOverviewLabel' => 'jumpToOverviewLabel',
    ],
    'fields' => [
        'id' => [
            'sql' => 'int(10) unsigned NOT NULL auto_increment',
            'eval' => ['notOverridable' => true],
        ],
        'tstamp' => [
            'label' => &$GLOBALS['TL_LANG']['tl_reader_config']['tstamp'],
            'eval' => ['notOverridable' => true],
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'sorting' => [
            'eval' => ['notOverridable' => true],
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'dateAdded' => [
            'label' => &$GLOBALS['TL_LANG']['MSC']['dateAdded'],
            'sorting' => true,
            'flag' => 6,
            'eval' => ['rgxp' => 'datim', 'doNotCopy' => true, 'notOverridable' => true],
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        // general
        'title' => [
            'label' => &$GLOBALS['TL_LANG']['tl_reader_config']['title'],
            'exclude' => true,
            'search' => true,
            'sorting' => true,
            'flag' => 1,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'tl_class' => 'w50', 'notOverridable' => true],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'pid' => [
            'label' => &$GLOBALS['TL_LANG']['tl_reader_config']['pid'],
            'exclude' => true,
            'filter' => true,
            'inputType' => 'select',
            'sorting' => true,
            'options_callback' => function (DataContainer $dc) {
                return \Contao\System::getContainer()->get('huh.reader.choice.parent-reader-config')->getCachedChoices(
                    [
                        'id' => $dc->id,
                    ]
                );
            },
            'eval' => ['tl_class' => 'w50', 'includeBlankOption' => true, 'chosen' => true, 'notOverridable' => true, 'submitOnChange' => true],
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        // config
        'dataContainer' => [
            'inputType' => 'select',
            'label' => &$GLOBALS['TL_LANG']['tl_reader_config']['dataContainer'],
            'options_callback' => ['huh.utils.choice.data_container', 'getChoices'],
            'eval' => [
                'chosen' => true,
                'submitOnChange' => true,
                'includeBlankOption' => true,
                'tl_class' => 'w50',
                'mandatory' => true,
                'notOverridable' => true,
            ],
            'exclude' => true,
            'sql' => "varchar(128) NOT NULL default ''",
        ],
        // filter
        'filter' => [
            'label' => &$GLOBALS['TL_LANG']['tl_reader_config']['filter'],
            'exclude' => true,
            'inputType' => 'select',
            'foreignKey' => 'tl_filter_config.title',
            'relation' => ['type' => 'belongsTo', 'load' => 'eager'],
            'eval' => ['tl_class' => 'w50 clr', 'includeBlankOption' => true, 'chosen' => true],
            'sql' => "int(10) NOT NULL default '0'",
        ],
        'evaluateFilter' => [
            'exclude' => true,
            'inputType' => 'checkbox',
            'default' => '1',
            'eval' => ['tl_class' => 'w50 clr'],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'manager' => [
            'inputType' => 'select',
            'label' => &$GLOBALS['TL_LANG']['tl_reader_config']['manager'],
            'options_callback' => ['huh.reader.choice.manager', 'getChoices'],
            'eval' => [
                'chosen' => true,
                'includeBlankOption' => true,
                'tl_class' => 'clr w50',
                'mandatory' => true,
            ],
            'exclude' => true,
            'sql' => "varchar(128) NOT NULL default 'default'",
        ],
        'item' => [
            'inputType' => 'select',
            'label' => &$GLOBALS['TL_LANG']['tl_reader_config']['item'],
            'options_callback' => ['huh.reader.choice.item', 'getChoices'],
            'eval' => [
                'chosen' => true,
                'includeBlankOption' => true,
                'mandatory' => true,
                'tl_class' => 'w50',
            ],
            'exclude' => true,
            'sql' => "varchar(128) NOT NULL default 'default'",
        ],
        'limitFormattedFields' => [
            'label' => &$GLOBALS['TL_LANG']['tl_reader_config']['limitFormattedFields'],
            'exclude' => true,
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'w50', 'submitOnChange' => true],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'formattedFields' => [
            'label' => &$GLOBALS['TL_LANG']['tl_reader_config']['formattedFields'],
            'inputType' => 'checkboxWizard',
            'options_callback' => function (DataContainer $dc) {
                return $dc->activeRecord->id > 0 ? System::getContainer()->get('huh.reader.util.reader-config-util')->getFields($dc->activeRecord->id) : [];
            },
            'exclude' => true,
            'eval' => ['multiple' => true, 'includeBlankOption' => true, 'tl_class' => 'w50 clr autoheight'],
            'sql' => 'blob NULL',
        ],
        'itemRetrievalMode' => [
            'label' => &$GLOBALS['TL_LANG']['tl_reader_config']['itemRetrievalMode'],
            'exclude' => true,
            'filter' => true,
            'inputType' => 'select',
            'options' => ReaderConfig::ITEM_RETRIEVAL_MODES,
            'reference' => &$GLOBALS['TL_LANG']['tl_reader_config']['reference'],
            'eval' => ['tl_class' => 'w50 clr', 'mandatory' => true, 'includeBlankOption' => true, 'submitOnChange' => true],
            'sql' => "varchar(64) NOT NULL default ''",
        ],
        'itemRetrievalAutoItemField' => [
            'label' => &$GLOBALS['TL_LANG']['tl_reader_config']['itemRetrievalAutoItemField'],
            'exclude' => true,
            'filter' => true,
            'inputType' => 'select',
            'options_callback' => static function (DataContainer $dc) {
                return !empty($dc->activeRecord->id) ? System::getContainer()->get('huh.reader.util.reader-config-util')->getFields($dc->activeRecord->id) : [];
            },
            'eval' => ['tl_class' => 'w50', 'mandatory' => true, 'includeBlankOption' => true, 'chosen' => true],
            'sql' => "varchar(64) NOT NULL default ''",
        ],
        'hideUnpublishedItems' => [
            'label' => &$GLOBALS['TL_LANG']['tl_reader_config']['hideUnpublishedItems'],
            'exclude' => true,
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'w50 clr', 'submitOnChange' => true],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'publishedField' => [
            'label' => &$GLOBALS['TL_LANG']['tl_reader_config']['publishedField'],
            'exclude' => true,
            'inputType' => 'select',
            'options_callback' => function (DataContainer $dc) {
                return System::getContainer()->get('huh.reader.util.reader-config-util')->getCheckboxFields($dc);
            },
            'eval' => ['maxlength' => 32, 'tl_class' => 'w50', 'includeBlankOption' => true, 'chosen' => true, 'mandatory' => true],
            'sql' => "varchar(32) NOT NULL default ''",
        ],
        'invertPublishedField' => [
            'label' => &$GLOBALS['TL_LANG']['tl_reader_config']['invertPublishedField'],
            'exclude' => true,
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'w50'],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'addStartAndStop' => [
            'label' => &$GLOBALS['TL_LANG']['tl_reader_config']['addStartAndStop'],
            'exclude' => true,
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'w50', 'submitOnChange' => true],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'startField' => [
            'label' => &$GLOBALS['TL_LANG']['tl_reader_config']['startField'],
            'exclude' => true,
            'filter' => true,
            'inputType' => 'select',
            'options_callback' => static function (Contao\DataContainer $dc) {
                return !empty($dc->activeRecord->id) ? System::getContainer()->get('huh.reader.util.reader-config-util')->getFields($dc->activeRecord->id) : [];
            },
            'eval' => ['chosen' => true, 'includeBlankOption' => true, 'tl_class' => 'w50', 'mandatory' => true],
            'sql' => "varchar(64) NOT NULL default ''",
        ],
        'stopField' => [
            'label' => &$GLOBALS['TL_LANG']['tl_reader_config']['stopField'],
            'exclude' => true,
            'filter' => true,
            'inputType' => 'select',
            'options_callback' => static function (Contao\DataContainer $dc) {
                return !empty($dc->activeRecord->id) ? System::getContainer()->get('huh.reader.util.reader-config-util')->getFields($dc->activeRecord->id) : [];
            },
            'eval' => ['chosen' => true, 'includeBlankOption' => true, 'tl_class' => 'w50', 'mandatory' => true],
            'sql' => "varchar(64) NOT NULL default ''",
        ],
        // security
        'addShowConditions' => [
            'label' => &$GLOBALS['TL_LANG']['tl_reader_config']['addShowConditions'],
            'exclude' => true,
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'w50', 'submitOnChange' => true],
            'sql' => "char(1) NOT NULL default ''",
        ],
        // jump to
        'addFieldDependentRedirect' => [
            'label' => &$GLOBALS['TL_LANG']['tl_reader_config']['addFieldDependentRedirect'],
            'exclude' => true,
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'w50', 'submitOnChange' => true],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'fieldDependentJumpTo' => [
            'label' => &$GLOBALS['TL_LANG']['tl_reader_config']['fieldDependentJumpTo'],
            'exclude' => true,
            'inputType' => 'pageTree',
            'foreignKey' => 'tl_page.title',
            'eval' => ['fieldType' => 'radio', 'mandatory' => true],
            'sql' => "int(10) unsigned NOT NULL default '0'",
            'relation' => ['type' => 'hasOne', 'load' => 'eager'],
        ],
        // misc
        'headTags' => [
            'label' => &$GLOBALS['TL_LANG']['tl_reader_config']['headTags'],
            'inputType' => 'multiColumnEditor',
            'eval' => [
                'multiColumnEditor' => [
                    'sortable' => false,
                    'fields' => [
                        'service' => [
                            'label' => &$GLOBALS['TL_LANG']['tl_reader_config']['headTags_service'],
                            'inputType' => 'select',
                            'options' => ['title', 'meta_description' => 'Meta description'],
                            'eval' => ['groupStyle' => 'width:50%', 'includeBlankOption' => true, 'decodeEntities' => true, 'chosen' => true],
                        ],
                        'pattern' => [
                            'label' => &$GLOBALS['TL_LANG']['tl_reader_config']['headTags_pattern'],
                            'inputType' => 'text',
                            'eval' => ['groupStyle' => 'width:40%'],
                        ],
                    ],
                ],
            ],
            'sql' => 'blob NULL',
        ],
        // template
        'itemTemplate' => [
            'label' => &$GLOBALS['TL_LANG']['tl_reader_config']['itemTemplate'],
            'exclude' => true,
            'inputType' => 'select',
            'options_callback' => [ReaderConfigContainer::class, 'onItemTemplateOptionsCallback'],
            'eval' => ['tl_class' => 'long clr', 'includeBlankOption' => true, 'chosen' => true],
            'sql' => "varchar(128) NOT NULL default ''",
        ],
        'addOverview' => [
            'label' => &$GLOBALS['TL_LANG']['tl_reader_config']['addOverview'],
            'exclude' => true,
            'inputType' => 'checkbox',
            'search' => true,
            'eval' => ['tl_class' => 'clr w50', 'submitOnChange' => true],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'overviewMode' => [
            'label' => &$GLOBALS['TL_LANG']['tl_reader_config']['overviewMode'],
            'exclude' => true,
            'inputType' => 'select',
            'options' => ['history', 'jumpTo'],
            'reference' => &$GLOBALS['TL_LANG']['tl_reader_config']['overviewMode']['reference'],
            'eval' => ['tl_class' => 'clr w50', 'submitOnChange' => true, 'includeBlankOption' => true, 'mandatory' => true],
            'sql' => "varchar(32) NOT NULL default ''",
        ],
        'jumpToOverview' => [
            'label' => &$GLOBALS['TL_LANG']['tl_reader_config']['jumpToOverview'],
            'exclude' => true,
            'inputType' => 'pageTree',
            'foreignKey' => 'tl_page.title',
            'eval' => ['fieldType' => 'radio', 'tl_class' => 'clr', 'mandatory' => true],
            'sql' => "int(10) unsigned NOT NULL default '0'",
            'relation' => ['type' => 'hasOne', 'load' => 'eager'],
        ],
        'customJumpToOverviewLabel' => [
            'label' => &$GLOBALS['TL_LANG']['tl_reader_config']['customJumpToOverviewLabel'],
            'exclude' => true,
            'inputType' => 'checkbox',
            'eval' => ['submitOnChange' => true, 'tl_class' => 'clr w50'],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'jumpToOverviewLabel' => [
            'label' => &$GLOBALS['TL_LANG']['tl_reader_config']['jumpToOverviewLabel'],
            'exclude' => true,
            'inputType' => 'select',
            'options_callback' => function (DataContainer $dc) {
                return \Contao\System::getContainer()->get('huh.utils.choice.message')->getCachedChoices('huh.reader.label.overview');
            },
            'eval' => ['chosen' => true, 'mandatory' => true, 'maxlength' => 128, 'includeBlankOption' => true, 'tl_class' => 'w50'],
            'sql' => "varchar(128) NOT NULL default ''",
        ],
        'jumpToOverviewMultilingual' => [
            'inputType' => 'multiColumnEditor',
            'eval' => [
                'tl_class' => 'long clr',
                'multiColumnEditor' => [
                    'minRowCount' => 0,
                    'fields' => [
                        'language' => [
                            'label' => &$GLOBALS['TL_LANG']['tl_reader_config']['jumpToOverviewMultilingual']['language'],
                            'inputType' => 'select',
                            'options_callback' => [ReaderConfigContainer::class, 'onJumpToOverviewMultilingualOptionsCallback'],
                            'eval' => ['tl_class' => 'w50', 'mandatory' => true, 'includeBlankOption' => true, 'chosen' => true, 'groupStyle' => 'width: 400px;'],
                        ],
                        'jumpTo' => [
                            'label' => &$GLOBALS['TL_LANG']['tl_reader_config']['jumpToOverviewMultilingual']['jumpTo'],
                            'inputType' => 'pageTree',
                            'foreignKey' => 'tl_page.title',
                            'eval' => ['fieldType' => 'radio', 'tl_class' => 'w50', 'mandatory' => true],
                            'relation' => ['type' => 'hasOne', 'load' => 'lazy'],
                        ],
                    ],
                ],
            ],
            'sql' => 'blob NULL',
        ],
        'disable404' => [
            'label' => &$GLOBALS['TL_LANG']['tl_reader_config']['disable404'],
            'exclude' => true,
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'clr w50'],
            'sql' => "char(1) NOT NULL default ''",
        ],
    ],
];

$dca = &$GLOBALS['TL_DCA']['tl_reader_config'];

\Contao\System::getContainer()->get('huh.entity_filter.manager')->addFilterToDca(
    'itemRetrievalFieldConditions',
    'tl_reader_config',
    '' // set in modifyPalette
);

\Contao\System::getContainer()->get('huh.entity_filter.manager')->addFilterToDca(
    'showFieldConditions',
    'tl_reader_config',
    '' // set in modifyPalette
);

\Contao\System::getContainer()->get('huh.entity_filter.manager')->addFilterToDca(
    'redirectFieldConditions',
    'tl_reader_config',
    '' // set in modifyPalette
);

if (System::getContainer()->get('huh.utils.container')->isBundleActive('Terminal42\DcMultilingualBundle\Terminal42DcMultilingualBundle')) {
    $dca['fields'] = array_merge(is_array($dca['fields']) ? $dca['fields'] : [], [
        'addDcMultilingualSupport' => [
            'label' => &$GLOBALS['TL_LANG']['tl_reader_config']['addDcMultilingualSupport'],
            'exclude' => true,
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'w50'],
            'sql' => "char(1) NOT NULL default ''",
        ],
    ]);
}

if (class_exists('HeimrichHannot\MultilingualFieldsBundle\HeimrichHannotMultilingualFieldsBundle')) {
    $dca['fields'] = array_merge(is_array($dca['fields']) ? $dca['fields'] : [], [
        'addMultilingualFieldsSupport' => [
            'label' => &$GLOBALS['TL_LANG']['tl_reader_config']['addMultilingualFieldsSupport'],
            'exclude' => true,
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'w50'],
            'sql' => "char(1) NOT NULL default ''",
        ],
    ]);
}

ReaderConfig::addOverridableFields();
