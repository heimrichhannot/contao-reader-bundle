<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

use HeimrichHannot\ReaderBundle\Controller\ContentElement\RelatedListContentElementController;

$lang = &$GLOBALS['TL_LANG'];

$lang['CTE'][RelatedListContentElementController::TYPE][0] = 'Liste ähnlicher Instanzen';
$lang['CTE'][RelatedListContentElementController::TYPE][1] = 'Gibt eine Liste ähnlicher Instanzen für die aktuell Reader-Instanz aus.';

$lang['MSC']['readerBundle'] = [
    'parentConfig' => 'Elterkonfiguration',
];

$lang['ERR']['readerBundleConfigElementTypeDeprecated'] = 'Dieses Leserkonfigurationselement ist veraltet und wird in der nächsten Major-Version entfernt. Bitte lesen sie die <a href="https://github.com/heimrichhannot/contao-reader-bundle" target="_blank">Dokumentation</a> für mehr Informationen.';
