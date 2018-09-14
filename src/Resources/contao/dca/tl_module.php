<?php

$dca = &$GLOBALS['TL_DCA']['tl_module'];

/**
 * Palettes
 */
$dca['palettes'][\HeimrichHannot\ReaderBundle\Module\ModuleReader::TYPE] =
    '{title_legend},name,headline,type;' . '{config_legend},readerConfig;' . '{template_legend:hide},customTpl;'
    . '{protected_legend:hide},protected;{expert_legend:hide},guests,cssID';

/**
 * Fields
 */
$fields = [
    'readerConfig' => [
        'label'      => &$GLOBALS['TL_LANG']['tl_module']['readerConfig'],
        'exclude'    => true,
        'filter'     => true,
        'inputType'  => 'select',
        'foreignKey' => 'tl_reader_config.title',
        'eval'       => ['tl_class' => 'long clr', 'mandatory' => true, 'includeBlankOption' => true, 'chosen' => true],
        'sql'        => "int(10) unsigned NOT NULL default '0'"
    ],
];

$dca['fields'] += $fields;