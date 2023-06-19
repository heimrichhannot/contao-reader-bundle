<?php

/*
 * Copyright (c) 2023 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

use HeimrichHannot\ReaderBundle\Controller\FrontendModule\ReaderFrontendModuleController;

$dca = &$GLOBALS['TL_DCA']['tl_module'];

/*
 * Palettes
 */
$dca['palettes'][ReaderFrontendModuleController::TYPE] =
    '{title_legend},name,headline,type;'.'{config_legend},readerConfig,readerNoItemBehavior;'.'{template_legend:hide},customTpl;'
    .'{protected_legend:hide},protected;{expert_legend:hide},guests,cssID';

$dca['palettes']['__selector__'][] = 'readerNoItemBehavior';
$dca['subpalettes']['readerNoItemBehavior_forward'] = 'jumpTo';

/**
 * Fields.
 */
$fields = [
    'readerConfig' => [
        'exclude' => true,
        'filter' => true,
        'inputType' => 'select',
        'foreignKey' => 'tl_reader_config.title',
        'eval' => ['tl_class' => 'wizard clr', 'mandatory' => true, 'includeBlankOption' => true, 'chosen' => true],
        'sql' => "int(10) unsigned NOT NULL default '0'",
    ],
    'readerNoItemBehavior' => [
        'inputType' => 'select',
        'exclude' => true,
        'options' => [
            'forward' => 'forward',
            '404' => '404',
            'empty' => 'empty',
        ],
        'reference' => &$GLOBALS['TL_LANG']['tl_module']['readerNoItemBehavior'],
        'eval' => [
            'tl_class' => 'w50',
            'includeBlankOption' => true,
            'submitOnChange' => true,
        ],
        'sql' => "varchar(8) NOT NULL default ''",
    ],
];

$dca['fields'] += $fields;
