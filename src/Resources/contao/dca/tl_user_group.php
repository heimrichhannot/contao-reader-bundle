<?php

$dca = &$GLOBALS['TL_DCA']['tl_user_group'];

/**
 * Palettes
 */
$dca['palettes']['default'] = str_replace('fop;', 'fop;{reader-bundle_legend},readerbundles,readerbundlep;', $dca['palettes']['default']);

/**
 * Fields
 */
$dca['fields']['readerbundles'] = [
    'label'      => &$GLOBALS['TL_LANG']['tl_user']['readerbundles'],
    'exclude'    => true,
    'inputType'  => 'checkbox',
    'foreignKey' => 'tl_reader_config.title',
    'eval'       => ['multiple' => true],
    'sql'        => "blob NULL"
];

$dca['fields']['readerbundlep'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_user']['readerbundlep'],
    'exclude'   => true,
    'inputType' => 'checkbox',
    'options'   => ['create', 'delete'],
    'reference' => &$GLOBALS['TL_LANG']['MSC'],
    'eval'      => ['multiple' => true],
    'sql'       => "blob NULL"
];