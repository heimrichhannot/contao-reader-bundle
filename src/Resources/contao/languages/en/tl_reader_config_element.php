<?php

$lang = &$GLOBALS['TL_LANG']['tl_reader_config_element'];

/**
 * Fields
 */
$lang['tstamp'][0]            = 'Revision date';
$lang['tstamp'][1]            = 'Revision date';
$lang['title'][0]             = 'Title';
$lang['title'][1]             = 'Please enter a title.';
$lang['type'][0]              = 'Type';
$lang['type'][1]              = 'Choose the element type.';
$lang['typeSelectorField'][0]     = 'Selector-Field';
$lang['typeSelectorField'][1]     = 'Choose the field, which contains the boolean selector for the type.';
$lang['typeField'][0]             = 'Field';
$lang['typeField'][1]             = 'Choose the field containing the reference for the type.';


/**
 * Reference
 */
$lang['reference'] = [
    \HeimrichHannot\ReaderBundle\ConfigElementType\ImageConfigElementType::getType()       => 'Image',
    \HeimrichHannot\ReaderBundle\ConfigElementType\RedirectionConfigElementType::getType() => 'Redirect',
    \HeimrichHannot\ReaderBundle\ConfigElementType\NavigationConfigElementType::getType()  => 'Navigation',
    \HeimrichHannot\ReaderBundle\ConfigElementType\SyndicationConfigElementType::getType() => 'Syndication',
    \HeimrichHannot\ReaderBundle\ConfigElementType\ListConfigElementType::getType()        => 'List',
    \HeimrichHannot\ReaderBundle\ConfigElementType\DeleteConfigElementType::getType()      => 'Delete',
    \HeimrichHannot\ReaderBundle\ConfigElementType\CommentConfigElementType::getType()     => 'Comment',
];

$lang['reference'][\HeimrichHannot\ReaderBundle\Backend\ReaderConfigElement::PLACEHOLDER_IMAGE_MODE_SIMPLE] = 'simple';
$lang['reference'][\HeimrichHannot\ReaderBundle\Backend\ReaderConfigElement::PLACEHOLDER_IMAGE_MODE_GENDERED] = 'gender-specific';


/**
 * Legends
 */
$lang['general_legend'] = 'General settings';


/**
 * Buttons
 */
$lang['new']    = ['New reader configuration element', 'Create reader configuration element'];
$lang['edit']   = ['Edit reader configuration element', 'Edit reader configuration element ID %s'];
$lang['copy']   = ['Duplicate reader configuration element', 'Duplicate reader configuration element ID %s'];
$lang['delete'] = ['Delete reader configuration element', 'Delete reader configuration element ID %s'];
$lang['toggle'] = ['Publish/unpublish reader configuration element', 'Publish/unpublish reader configuration element ID %s'];
$lang['show']   = ['reader configuration element details', 'Show the details of reader configuration element ID %s'];


$lang['listModule']    = ['List Module', 'Select a list module.'];
$lang['listName']      = ['List Name', 'Set a unique name for your list.'];
$lang['initialFilter'] = ['Initial Filter', ''];
$lang['selector']      = ['Selector', 'Select the field of the value.'];
$lang['filterElement'] = ['Filter Element', 'Select the filtered field.'];