<?php

$dca = &$GLOBALS['TL_DCA']['tl_filter_config_element'];

/**
 * Subpalettes
 */
$dca['subpalettes']['alternativeValueSource_' . \HeimrichHannot\ReaderBundle\Backend\FilterConfigElement::ALTERNATIVE_SOURCE_READER_BUNDLE_ENTITY] = 'readerConfig,readerField';

/**
 * Fields
 */
$fields = [
    'readerConfig' => [
        'label'            => &$GLOBALS['TL_LANG']['tl_filter_config_element']['readerConfig'],
        'exclude'          => true,
        'filter'           => true,
        'inputType'        => 'select',
        'options_callback' => function (\Contao\DataContainer $dc) {
            return System::getContainer()->get('huh.utils.choice.model_instance')->getCachedChoices([
                'dataContainer' => 'tl_reader_config'
            ]);
        },
        'eval'             => ['tl_class' => 'w50', 'mandatory' => true, 'includeBlankOption' => true, 'submitOnChange' => true, 'chosen' => true],
        'sql'              => "varchar(64) NOT NULL default ''"
    ],
    'readerField'  => [
        'label'            => &$GLOBALS['TL_LANG']['tl_filter_config_element']['readerField'],
        'inputType'        => 'select',
        'options_callback' => function (DataContainer $dc) {
            return $dc->activeRecord->readerConfig > 0 ? System::getContainer()->get('huh.reader.util.reader-config-util')->getFields($dc->activeRecord->readerConfig) : [];
        },
        'exclude'          => true,
        'eval'             => ['includeBlankOption' => true, 'tl_class' => 'w50', 'chosen' => true],
        'sql'              => "varchar(64) NOT NULL default ''",
    ],
];

$dca['fields'] = array_merge($fields, is_array($dca['fields']) ? $dca['fields'] : []);

$dca['fields']['alternativeValueSource']['options'] = array_merge($dca['fields']['alternativeValueSource']['options'], [\HeimrichHannot\ReaderBundle\Backend\FilterConfigElement::ALTERNATIVE_SOURCE_READER_BUNDLE_ENTITY]);