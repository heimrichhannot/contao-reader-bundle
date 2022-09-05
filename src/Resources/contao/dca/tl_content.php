<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

$dca = &$GLOBALS['TL_DCA']['tl_content'];

$dca['palettes']['related_list_content_element'] = '{type_legend},type,headline;{config_legend},relatedExplanation,readerConfig,relatedListModule,relatedCriteriaExplanation,relatedCriteria;{template_legend:hide},customTpl;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID;{invisible_legend:hide},invisible,start,stop';

$dca['fields']['relatedExplanation'] = [
    'inputType' => 'explanation',
    'eval' => [
        'text' => &$GLOBALS['TL_LANG']['tl_reader_config_element']['relatedExplanation'],
        'class' => 'tl_info',
        'tl_class' => 'long clr',
    ],
];

$dca['fields']['readerConfig'] = [
    'exclude' => true,
    'inputType' => 'select',
    'foreignKey' => 'tl_reader_config.title',
    'eval' => [
        'tl_class' => 'w50',
        'mandatory' => true,
        'includeBlankOption' => true,
        'chosen' => true,
        'isAssociative' => true,
    ],
    'sql' => 'int(10) unsigned NOT NULL default 0',
];

$dca['fields']['relatedListModule'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_reader_config_element']['relatedListModule'],
    'exclude' => true,
    'inputType' => 'select',
    'eval' => [
        'tl_class' => 'w50',
        'mandatory' => true,
        'includeBlankOption' => true,
        'chosen' => true,
        'isAssociative' => true,
    ],
    'sql' => "varchar(64) NOT NULL default ''",
];

$dca['fields']['relatedCriteriaExplanation'] = [
    'inputType' => 'explanation',
    'eval' => [
        'text' => &$GLOBALS['TL_LANG']['tl_reader_config_element']['relatedCriteriaExplanation'],
        'class' => 'tl_info',
        'tl_class' => 'long clr',
    ],
];

$dca['fields']['relatedCriteria'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_reader_config_element']['relatedCriteria'],
    'exclude' => true,
    'filter' => true,
    'inputType' => 'checkbox',
    'reference' => &$GLOBALS['TL_LANG']['tl_reader_config_element']['reference'],
    'eval' => [
        'tl_class' => 'w50',
        'mandatory' => true,
        'includeBlankOption' => true,
        'multiple' => true,
        'submitOnChange' => true,
    ],
    'sql' => 'blob NULL',
];

$dca['fields']['tagsField'] = [
    'inputType' => 'select',
    'options_callback' => function (DataContainer $dc) {
        $element = \Contao\ContentModel::findByPk($dc->id);

        if (!$element || !($readerConfig = System::getContainer()->get('huh.utils.model')->findModelInstanceByPk('tl_reader_config', $dc->activeRecord->readerConfig)) || !$readerConfig->dataContainer) {
            return [];
        }

        return System::getContainer()->get('huh.utils.choice.field')->getCachedChoices([
            'dataContainer' => $readerConfig->dataContainer,
            'inputTypes' => ['cfgTags'],
        ]);
    },
    'exclude' => true,
    'eval' => ['includeBlankOption' => true, 'mandatory' => true, 'chosen' => true, 'tl_class' => 'w50 clr'],
    'sql' => "varchar(64) NOT NULL default ''",
];

$dca['fields']['categoriesField'] = [
    'inputType' => 'select',
    'options_callback' => function (DataContainer $dc) {
        if (!$dc->activeRecord->readerConfig) {
            return [];
        }

        if (null === ($readerConfig = System::getContainer()->get('huh.utils.model')->findModelInstanceByPk('tl_reader_config', $dc->activeRecord->readerConfig)) || !$readerConfig->dataContainer) {
            return [];
        }

        return System::getContainer()->get('huh.utils.choice.field')->getCachedChoices([
            'dataContainer' => $readerConfig->dataContainer,
            'inputTypes' => ['categoryTree'],
        ]);
    },
    'exclude' => true,
    'eval' => ['includeBlankOption' => true, 'mandatory' => true, 'chosen' => true, 'tl_class' => 'w50'],
    'sql' => "varchar(64) NOT NULL default ''",
];
