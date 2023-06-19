<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

use HeimrichHannot\ReaderBundle\Model\ReaderConfigElementModel;
use HeimrichHannot\ReaderBundle\Model\ReaderConfigModel;

$GLOBALS['BE_MOD']['system']['reader_configs'] = [
    'tables' => ['tl_reader_config'],
];

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
