<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

use HeimrichHannot\ReaderBundle\Controller\ContentElement\RelatedListContentElementController;

$lang = &$GLOBALS['TL_LANG'];

$lang['CTE'][RelatedListContentElementController::TYPE][0] = 'Related instances list';
$lang['CTE'][RelatedListContentElementController::TYPE][1] = 'Outputs a list of related instances for current reader.';

$lang['MSC']['readerBundle'] = [
    'parentConfig' => 'Parent configuration',
];

$lang['ERR']['readerBundleConfigElementTypeDeprecated'] = 'This reader configuration element is deprecated and will be removed in the next major version. Please consider the <a href="https://github.com/heimrichhannot/contao-reader-bundle" target="_blank">documentation</a> for more information.';
