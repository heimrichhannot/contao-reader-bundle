<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

use HeimrichHannot\ReaderBundle\Model\ReaderConfigElementModel;
use HeimrichHannot\ReaderBundle\Model\ReaderConfigModel;
use HeimrichHannot\ReaderBundle\Module\ModuleReader;

$GLOBALS['BE_MOD']['system']['reader_configs'] = [
    'tables' => ['tl_reader_config'],
];

/*
 * Frontend modules
 */
//array_insert(
//    $GLOBALS['FE_MOD']['reader'],
//    3,
//    [
//        ModuleReader::TYPE => ModuleReader::class,
//    ]
//);

/*
 * Permissions
 */
$GLOBALS['TL_PERMISSIONS'][] = 'readerbundles';
$GLOBALS['TL_PERMISSIONS'][] = 'readerbundlep';

/*
 * Models
 */
$GLOBALS['TL_MODELS']['tl_reader_config'] = ReaderConfigModel::class;
$GLOBALS['TL_MODELS']['tl_reader_config_element'] = ReaderConfigElementModel::class;
