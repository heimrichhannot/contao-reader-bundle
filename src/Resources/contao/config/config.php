<?php

/**
 * Backend modules
 */
$GLOBALS['BE_MOD']['system']['reader_configs'] = [
    'tables' => ['tl_reader_config']
];

/**
 * Frontend modules
 */
array_insert(
    $GLOBALS['FE_MOD']['reader'],
    3,
    [
        \HeimrichHannot\ReaderBundle\Module\ModuleReader::TYPE => \HeimrichHannot\ReaderBundle\Module\ModuleReader::class,
    ]
);

/**
 * Permissions
 */
$GLOBALS['TL_PERMISSIONS'][] = 'readerbundles';
$GLOBALS['TL_PERMISSIONS'][] = 'readerbundlep';

/**
 * Models
 */
$GLOBALS['TL_MODELS']['tl_reader_config']         = 'HeimrichHannot\ReaderBundle\Model\ReaderConfigModel';
$GLOBALS['TL_MODELS']['tl_reader_config_element'] = 'HeimrichHannot\ReaderBundle\Model\ReaderConfigElementModel';