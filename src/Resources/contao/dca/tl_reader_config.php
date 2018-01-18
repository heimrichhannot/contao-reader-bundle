<?php

\Contao\Controller::loadDataContainer('tl_module');
\Contao\System::loadLanguageFile('tl_module');

$GLOBALS['TL_DCA']['tl_reader_config'] = [
    'config'      => [
        'dataContainer'     => 'Table',
        'ctable'            => 'tl_reader_config_element',
        'enableVersioning'  => true,
        'onload_callback' => [
            ['HeimrichHannot\ReaderBundle\Backend\ReaderConfig', 'modifyPalette'],
        ],
        'onsubmit_callback' => [
            ['huh.utils.dca', 'setDateAdded'],
        ],
        'oncopy_callback'   => [
            ['huh.utils.dca', 'setDateAddedOnCopy'],
        ],
        'sql'               => [
            'keys' => [
                'id' => 'primary'
            ]
        ]
    ],
    'list'        => [
        'label'             => [
            'fields' => ['title'],
            'format' => '%s'
        ],
        'sorting'           => [
            'mode'         => 1,
            'fields'       => ['title'],
            'headerFields' => ['title'],
            'panelLayout'  => 'filter;sort,search,limit'
        ],
        'global_operations' => [
            'all' => [
                'label'      => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href'       => 'act=select',
                'class'      => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset();"'
            ],
        ],
        'operations'        => [
            'edit'       => [
                'label' => &$GLOBALS['TL_LANG']['tl_reader_config']['edit'],
                'href'  => 'table=tl_reader_config_element',
                'icon'  => 'edit.svg'
            ],
            'editheader' => [
                'label'           => &$GLOBALS['TL_LANG']['tl_reader_config']['editheader'],
                'href'            => 'act=edit',
                'icon'            => 'header.svg',
                'button_callback' => ['HeimrichHannot\ReaderBundle\Backend\ReaderConfig', 'editHeader']
            ],
            'copy'       => [
                'label' => &$GLOBALS['TL_LANG']['tl_reader_config']['copy'],
                'href'  => 'act=copy',
                'icon'  => 'copy.gif'
            ],
            'delete'     => [
                'label'      => &$GLOBALS['TL_LANG']['tl_reader_config']['delete'],
                'href'       => 'act=delete',
                'icon'       => 'delete.gif',
                'attributes' => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm']
                                . '\'))return false;Backend.getScrollOffset()"'
            ],
            'show'       => [
                'label' => &$GLOBALS['TL_LANG']['tl_reader_config']['show'],
                'href'  => 'act=show',
                'icon'  => 'show.gif'
            ],
        ]
    ],
    'palettes'    => [
        '__selector__' => [
            'addShowConditions'
        ],
        'default'      => '{general_legend},title;' . '{entity_legend},dataContainer,entityRetrievalMode;' . '{security_legend},addShowConditions;'
                          . '{jumpto_legend},addFieldDependendRedirect;' . '{misc_legend},setPageTitleByField;' . '{template_legend},itemTemplate;'
    ],
    'subpalettes' => [
        'addShowConditions' => 'showConditions'
    ],
    'fields'      => [
        'id'        => [
            'sql' => "int(10) unsigned NOT NULL auto_increment"
        ],
        'tstamp'    => [
            'label' => &$GLOBALS['TL_LANG']['tl_reader_config']['tstamp'],
            'sql'   => "int(10) unsigned NOT NULL default '0'"
        ],
        'dateAdded' => [
            'label'   => &$GLOBALS['TL_LANG']['MSC']['dateAdded'],
            'sorting' => true,
            'flag'    => 6,
            'eval'    => ['rgxp' => 'datim', 'doNotCopy' => true],
            'sql'     => "int(10) unsigned NOT NULL default '0'"
        ],
        // general
        'title'     => [
            'label'     => &$GLOBALS['TL_LANG']['tl_reader_config']['title'],
            'exclude'   => true,
            'search'    => true,
            'sorting'   => true,
            'flag'      => 1,
            'inputType' => 'text',
            'eval'      => ['mandatory' => true, 'tl_class' => 'w50'],
            'sql'       => "varchar(255) NOT NULL default ''"
        ],
        // entity
        'dataContainer' => [
            'inputType'        => 'select',
            'label'            => &$GLOBALS['TL_LANG']['tl_reader_config']['dataContainer'],
            'options_callback' => ['huh.utils.choice.data_container', 'getChoices'],
            'eval'             => [
                'chosen'             => true,
                'submitOnChange'     => true,
                'includeBlankOption' => true,
                'tl_class'           => 'w50',
                'mandatory'          => true,
            ],
            'exclude'          => true,
            'sql'              => "varchar(128) NOT NULL default ''",
        ],
        // security
        'addShowConditions' => [
            'label'                   => &$GLOBALS['TL_LANG']['tl_reader_config']['addShowConditions'],
            'exclude'                 => true,
            'inputType'               => 'checkbox',
            'eval'                    => ['tl_class' => 'w50', 'submitOnChange' => true],
            'sql'                     => "char(1) NOT NULL default ''"
        ],
    ]
];

//\Contao\System::getContainer()->get('huh.entity_filter.manager')->addFilterToDca(
//    'showConditions',
//    'tl_reader_config',
//    '' // is set in modifyPalette
//);