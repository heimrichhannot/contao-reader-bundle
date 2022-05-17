<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

$lang = &$GLOBALS['TL_LANG']['tl_reader_config'];

/*
 * Fields
 */
$lang['title'] = ['Title', 'Please enter a title.'];
$lang['tstamp'] = ['Revision date', ''];

// filter
$lang['filter'][0] = 'Filter';
$lang['filter'][1] = 'Choose a filter if needed.';
$lang['evaluateFilter'] = [
    'Evaluate filter for item retrival',
    'Choose this option to evaluation the filter for item retrival. This option is recommended to prevent accessing all items.',
];

/*
 * Legends
 */
$lang['general_legend'] = 'General settings';
$lang['filter_legend'] = 'Filter settings';
$lang['config_legend'] = 'Configuration';
$lang['fields_legend'] = 'Field settings';

/*
 * Buttons
 */
$lang['new'] = ['New Reader configuration', 'Create Reader configuration'];
$lang['edit'] = ['Edit Reader configuration', 'Edit Reader configuration ID %s'];
$lang['copy'] = ['Duplicate Reader configuration', 'Duplicate Reader configuration ID %s'];
$lang['delete'] = ['Delete Reader configuration', 'Delete Reader configuration ID %s'];
$lang['toggle'] = ['Publish/unpublish Reader configuration', 'Publish/unpublish Reader configuration ID %s'];
$lang['show'] = ['Reader configuration details', 'Show the details of Reader configuration ID %s'];
