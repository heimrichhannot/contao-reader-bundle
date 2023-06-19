<?php

$lang = &$GLOBALS['TL_LANG']['tl_module'];

/**
 * Fields
 */
$lang['readerConfig'][0] = 'Reader configuration';
$lang['readerConfig'][1] = 'Please select the reader configuration to use here.';
$lang['readerNoItemBehavior'][0] = 'Behavior when called without element';
$lang['readerNoItemBehavior'][1] = 'Please select here how the module should behave when called without an element (usually auto-item parameter).';
$lang['readerNoItemBehavior']['forward'] = 'Forward';
$lang['readerNoItemBehavior']['404'] = 'Output error message (404 - Not found)';
$lang['readerNoItemBehavior']['empty'] = 'Output empty reader (template without item content)';