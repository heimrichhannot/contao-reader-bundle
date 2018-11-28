<?php

\Contao\Controller::loadDataContainer('tl_module');
\Contao\Controller::loadLanguageFile('tl_list_config');
\Contao\Controller::loadLanguageFile('tl_news_archive');

$GLOBALS['TL_DCA']['tl_reader_config_element'] = [
    'config'      => [
        'dataContainer'     => 'Table',
        'ptable'            => 'tl_reader_config',
        'enableVersioning'  => true,
        'onload_callback'   => [
            ['huh.reader.backend.reader-config-element', 'checkPermission'],
            ['huh.reader.backend.reader-config-element', 'modifyPalette'],
            ['huh.reader.listener.callback.readerconfigelement', 'updateLabel']
        ],
        'onsubmit_callback' => [
            ['huh.utils.dca', 'setDateAdded'],
        ],
        'oncopy_callback'   => [
            ['huh.utils.dca', 'setDateAddedOnCopy'],
        ],
        'sql'               => [
            'keys' => [
                'id' => 'primary',
            ],
        ],
    ],
    'list'        => [
        'label'             => [
            'fields' => ['title'],
            'format' => '%s',
        ],
        'sorting'           => [
            'mode'                  => 4,
            'fields'                => ['title'],
            'headerFields'          => ['title'],
            'panelLayout'           => 'filter;sort,search,limit',
            'child_record_callback' => ['huh.reader.backend.reader-config-element', 'listChildren'],
        ],
        'global_operations' => [
            'all' => [
                'label'      => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href'       => 'act=select',
                'class'      => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset();"',
            ],
        ],
        'operations'        => [
            'edit'   => [
                'label' => &$GLOBALS['TL_LANG']['tl_reader_config_element']['edit'],
                'href'  => 'act=edit',
                'icon'  => 'edit.gif',
            ],
            'copy'   => [
                'label' => &$GLOBALS['TL_LANG']['tl_reader_config_element']['copy'],
                'href'  => 'act=copy',
                'icon'  => 'copy.gif',
            ],
            'delete' => [
                'label'      => &$GLOBALS['TL_LANG']['tl_reader_config_element']['delete'],
                'href'       => 'act=delete',
                'icon'       => 'delete.gif',
                'attributes' => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm']
                    . '\'))return false;Backend.getScrollOffset()"',
            ],
            'show'   => [
                'label' => &$GLOBALS['TL_LANG']['tl_reader_config_element']['show'],
                'href'  => 'act=show',
                'icon'  => 'show.gif',
            ],
        ],
    ],
    'palettes'    => [
        '__selector__'                                                             => [
            'type',
            'placeholderImageMode',
            'addRedirectConditions',
            'addRedirectParam',
            'addMemberGroups',
            'syndicationMail',
            'syndicationPinterest',
            'syndicationPrint',
            'syndicationPdf',
            'syndicationIcs',
            'syndicationIcsAddTime',
            'commentOverridePalette',
            'commentHideFields'
        ],
        'default'                                                                  => '{type_legend},title,type;',
        \HeimrichHannot\ReaderBundle\Backend\ReaderConfigElement::TYPE_IMAGE       => '{title_type_legend},title,type;{config_legend},imageSelectorField,imageField,imgSize,placeholderImageMode;',
        \HeimrichHannot\ReaderBundle\Backend\ReaderConfigElement::TYPE_REDIRECTION => '{title_type_legend},title,type;{config_legend},name,jumpTo,addRedirectConditions,addRedirectParam,addAutoItem;',
        \HeimrichHannot\ReaderBundle\Backend\ReaderConfigElement::TYPE_NAVIGATION  => '{title_type_legend},title,type;{config_legend},name,navigationTemplate,previousLabel,nextLabel,previousTitle,nextTitle,sortingField,sortingDirection,listConfig,infiniteNavigation;',
        \HeimrichHannot\ReaderBundle\Backend\ReaderConfigElement::TYPE_SYNDICATION => '{title_type_legend},title,type;{config_legend},name,syndicationTemplate,syndicationFacebook,syndicationTwitter,syndicationGooglePlus,syndicationLinkedIn,syndicationXing,syndicationMail,syndicationPdf,syndicationPrint,syndicationIcs,syndicationTumblr,syndicationPinterest,syndicationReddit,syndicationWhatsApp;',
        \HeimrichHannot\ReaderBundle\Backend\ReaderConfigElement::TYPE_DELETE      => '{title_type_legend},title,type;{config_legend},name,jumpTo,addRedirectConditions,addRedirectParam,addAutoItem,addMemberGroups,deleteClass,deleteJumpTo;',
        \HeimrichHannot\ReaderBundle\Backend\ReaderConfigElement::TYPE_COMMENT     => '{title_type_legend},title,type;{config_legend},commentTemplate,commentCustomTemplate,commentNotify,commentSortOrder,commentPerPage,commentModerate,commentBbcode,commentRequireLogin,commentDisableCaptcha,commentOverridePalette,commentHideFields;',
    ],
    'subpalettes' => [
        'placeholderImageMode_' . \HeimrichHannot\ReaderBundle\Backend\ReaderConfigElement::PLACEHOLDER_IMAGE_MODE_SIMPLE => 'placeholderImage',
        'placeholderImageMode_'
        . \HeimrichHannot\ReaderBundle\Backend\ReaderConfigElement::PLACEHOLDER_IMAGE_MODE_GENDERED                       => 'genderField,placeholderImage,placeholderImageFemale',
        'addRedirectConditions'                                                                                           => 'redirectConditions',
        'addRedirectParam'                                                                                                => 'redirectParams',
        'syndicationMail'                                                                                                 => 'mailSubjectLabel,mailBodyLabel',
        'syndicationPinterest'                                                                                            => 'imageSelectorField,imageField,imgSize',
        'syndicationPrint'                                                                                                => 'syndicationPrintTemplate',
        'syndicationPdf'                                                                                                  => 'syndicationPdfReader,syndicationPdfTemplate,syndicationPdfFontDirectories,syndicationPdfMasterTemplate,syndicationPdfPageMargin',
        'syndicationIcs'                                                                                                  => 'syndicationIcsTitleField,syndicationIcsDescriptionField,syndicationIcsStartDateField,syndicationIcsEndDateField,syndicationIcsAddTime',
        'syndicationIcsAddTime'                                                                                           => 'syndicationIcsAddTimeField,syndicationIcsStartTimeField,syndicationIcsEndTimeField',
        'addMemberGroups'                                                                                                 => 'memberGroups',
        'commentOverridePalette'                                                                                          => 'commentPalette',
        'commentHideFields'                                                                                               => 'commentHideFieldsPalette'
    ],
    'fields'      => [
        'id'                             => [
            'sql' => "int(10) unsigned NOT NULL auto_increment",
        ],
        'pid'                            => [
            'foreignKey' => 'tl_reader_config.title',
            'sql'        => "int(10) unsigned NOT NULL default '0'",
            'relation'   => ['type' => 'belongsTo', 'load' => 'eager'],
        ],
        'tstamp'                         => [
            'label' => &$GLOBALS['TL_LANG']['tl_reader_config_element']['tstamp'],
            'sql'   => "int(10) unsigned NOT NULL default '0'",
        ],
        'dateAdded'                      => [
            'label'   => &$GLOBALS['TL_LANG']['MSC']['dateAdded'],
            'sorting' => true,
            'flag'    => 6,
            'eval'    => ['rgxp' => 'datim', 'doNotCopy' => true],
            'sql'     => "int(10) unsigned NOT NULL default '0'",
        ],
        'title'                          => [
            'label'     => &$GLOBALS['TL_LANG']['tl_reader_config_element']['title'],
            'exclude'   => true,
            'search'    => true,
            'inputType' => 'text',
            'eval'      => ['maxlength' => 255, 'tl_class' => 'w50', 'mandatory' => true],
            'sql'       => "varchar(255) NOT NULL default ''",
        ],
        'type'                           => [
            'label'     => &$GLOBALS['TL_LANG']['tl_reader_config_element']['type'],
            'exclude'   => true,
            'filter'    => true,
            'inputType' => 'select',
            'options'   => System::getContainer()->get('huh.reader.util.reader-config-element-util')->getConfigElementTypes(),
            'reference' => &$GLOBALS['TL_LANG']['tl_reader_config_element']['reference'],
            'eval'      => ['tl_class' => 'w50', 'mandatory' => true, 'includeBlankOption' => true, 'submitOnChange' => true],
            'sql'       => "varchar(64) NOT NULL default ''",
        ],
        'typeSelectorField'              => [
            'label'            => &$GLOBALS['TL_LANG']['tl_reader_config_element']['typeSelectorField'],
            'inputType'        => 'select',
            'options_callback' => function (DataContainer $dc) {
                return System::getContainer()->get('huh.reader.util.reader-config-element-util')->getCheckboxFields($dc);
            },
            'exclude'          => true,
            'eval'             => ['includeBlankOption' => true, 'tl_class' => 'w50 autoheight'],
            'sql'              => "varchar(64) NOT NULL default ''",
        ],
        'typeField'                      => [
            'label'            => &$GLOBALS['TL_LANG']['tl_reader_config_element']['typeField'],
            'inputType'        => 'select',
            'options_callback' => function (DataContainer $dc) {
                return $dc->activeRecord->pid > 0 ? System::getContainer()
                    ->get('huh.reader.util.reader-config-util')
                    ->getFields($dc->activeRecord->pid) : [];
            },
            'exclude'          => true,
            'eval'             => ['includeBlankOption' => true, 'mandatory' => true, 'chosen' => true, 'tl_class' => 'w50 autoheight'],
            'sql'              => "varchar(64) NOT NULL default ''",
        ],
        'imageSelectorField'             => [
            'label'            => &$GLOBALS['TL_LANG']['tl_reader_config_element']['imageSelectorField'],
            'inputType'        => 'select',
            'options_callback' => function (DataContainer $dc) {
                return System::getContainer()->get('huh.reader.util.reader-config-element-util')->getCheckboxFields($dc);
            },
            'exclude'          => true,
            'eval'             => ['includeBlankOption' => true, 'chosen' => true, 'tl_class' => 'w50 autoheight'],
            'sql'              => "varchar(64) NOT NULL default ''",
        ],
        'imageField'                     => [
            'label'            => &$GLOBALS['TL_LANG']['tl_reader_config_element']['imageField'],
            'inputType'        => 'select',
            'options_callback' => function (DataContainer $dc) {
                return $dc->activeRecord->pid > 0 ? System::getContainer()
                    ->get('huh.reader.util.reader-config-util')
                    ->getFields($dc->activeRecord->pid) : [];
            },
            'exclude'          => true,
            'eval'             => ['includeBlankOption' => true, 'mandatory' => true, 'chosen' => true, 'tl_class' => 'w50'],
            'sql'              => "varchar(64) NOT NULL default ''",
        ],
        'syndicationIcsTitleField'       => [
            'label'            => &$GLOBALS['TL_LANG']['tl_reader_config_element']['syndicationIcsTitleField'],
            'inputType'        => 'select',
            'options_callback' => function (DataContainer $dc) {
                return $dc->activeRecord->pid > 0 ? System::getContainer()
                    ->get('huh.reader.util.reader-config-util')
                    ->getFields($dc->activeRecord->pid) : [];
            },
            'exclude'          => true,
            'eval'             => ['includeBlankOption' => true, 'mandatory' => true, 'chosen' => true, 'tl_class' => 'w50'],
            'sql'              => "varchar(64) NOT NULL default ''",
        ],
        'syndicationIcsDescriptionField' => [
            'label'            => &$GLOBALS['TL_LANG']['tl_reader_config_element']['syndicationIcsDescriptionField'],
            'inputType'        => 'select',
            'options_callback' => function (DataContainer $dc) {
                return $dc->activeRecord->pid > 0 ? System::getContainer()
                    ->get('huh.reader.util.reader-config-util')
                    ->getFields($dc->activeRecord->pid) : [];
            },
            'exclude'          => true,
            'eval'             => ['includeBlankOption' => true, 'chosen' => true, 'tl_class' => 'w50'],
            'sql'              => "varchar(64) NOT NULL default ''",
        ],
        'syndicationIcsStartDateField'   => [
            'label'            => &$GLOBALS['TL_LANG']['tl_reader_config_element']['syndicationIcsStartDateField'],
            'inputType'        => 'select',
            'options_callback' => function (DataContainer $dc) {
                return $dc->activeRecord->pid > 0 ? System::getContainer()
                    ->get('huh.reader.util.reader-config-util')
                    ->getFields($dc->activeRecord->pid) : [];
            },
            'exclude'          => true,
            'eval'             => ['includeBlankOption' => true, 'mandatory' => true, 'chosen' => true, 'tl_class' => 'w50'],
            'sql'              => "varchar(64) NOT NULL default ''",
        ],
        'syndicationIcsEndDateField'     => [
            'label'            => &$GLOBALS['TL_LANG']['tl_reader_config_element']['syndicationIcsEndDateField'],
            'inputType'        => 'select',
            'options_callback' => function (DataContainer $dc) {
                return $dc->activeRecord->pid > 0 ? System::getContainer()
                    ->get('huh.reader.util.reader-config-util')
                    ->getFields($dc->activeRecord->pid) : [];
            },
            'exclude'          => true,
            'eval'             => ['includeBlankOption' => true, 'chosen' => true, 'tl_class' => 'w50'],
            'sql'              => "varchar(64) NOT NULL default ''",
        ],
        'syndicationIcsAddTime'          => [
            'label'     => &$GLOBALS['TL_LANG']['tl_reader_config_element']['syndicationIcsAddTime'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => ['tl_class' => 'w50', 'submitOnChange' => true],
            'sql'       => "char(1) NOT NULL default ''"
        ],
        'syndicationIcsAddTimeField'     => [
            'label'            => &$GLOBALS['TL_LANG']['tl_reader_config_element']['syndicationIcsAddTimeField'],
            'inputType'        => 'select',
            'options_callback' => function (DataContainer $dc) {
                return $dc->activeRecord->pid > 0 ? System::getContainer()
                    ->get('huh.reader.util.reader-config-util')
                    ->getFields($dc->activeRecord->pid) : [];
            },
            'exclude'          => true,
            'eval'             => ['includeBlankOption' => true, 'mandatory' => true, 'chosen' => true, 'tl_class' => 'w50'],
            'sql'              => "varchar(64) NOT NULL default ''",
        ],
        'syndicationIcsStartTimeField'   => [
            'label'            => &$GLOBALS['TL_LANG']['tl_reader_config_element']['syndicationIcsStartTimeField'],
            'inputType'        => 'select',
            'options_callback' => function (DataContainer $dc) {
                return $dc->activeRecord->pid > 0 ? System::getContainer()
                    ->get('huh.reader.util.reader-config-util')
                    ->getFields($dc->activeRecord->pid) : [];
            },
            'exclude'          => true,
            'eval'             => ['includeBlankOption' => true, 'chosen' => true, 'tl_class' => 'w50'],
            'sql'              => "varchar(64) NOT NULL default ''",
        ],
        'syndicationIcsEndTimeField'     => [
            'label'            => &$GLOBALS['TL_LANG']['tl_reader_config_element']['syndicationIcsEndTimeField'],
            'inputType'        => 'select',
            'options_callback' => function (DataContainer $dc) {
                return $dc->activeRecord->pid > 0 ? System::getContainer()
                    ->get('huh.reader.util.reader-config-util')
                    ->getFields($dc->activeRecord->pid) : [];
            },
            'exclude'          => true,
            'eval'             => ['includeBlankOption' => true, 'chosen' => true, 'tl_class' => 'w50'],
            'sql'              => "varchar(64) NOT NULL default ''",
        ],
        'orderField'                     => [
            'label'            => &$GLOBALS['TL_LANG']['tl_reader_config_element']['orderField'],
            'inputType'        => 'select',
            'options_callback' => function (DataContainer $dc) {
                return $dc->activeRecord->pid > 0 ? System::getContainer()
                    ->get('huh.reader.util.reader-config-util')
                    ->getFields($dc->activeRecord->pid) : [];
            },
            'exclude'          => true,
            'eval'             => ['includeBlankOption' => true, 'chosen' => true, 'tl_class' => 'w50 autoheight'],
            'sql'              => "varchar(64) NOT NULL default ''",
        ],
        'imgSize'                        => $GLOBALS['TL_DCA']['tl_module']['fields']['imgSize'],
        'placeholderImageMode'           => [
            'label'     => &$GLOBALS['TL_LANG']['tl_reader_config_element']['placeholderImageMode'],
            'exclude'   => true,
            'filter'    => true,
            'inputType' => 'select',
            'options'   => \HeimrichHannot\ReaderBundle\Backend\ReaderConfigElement::PLACEHOLDER_IMAGE_MODES,
            'reference' => &$GLOBALS['TL_LANG']['tl_reader_config_element']['reference'],
            'eval'      => ['tl_class' => 'w50', 'includeBlankOption' => true, 'submitOnChange' => true],
            'sql'       => "varchar(64) NOT NULL default ''",
        ],
        'placeholderImage'               => [
            'label'     => &$GLOBALS['TL_LANG']['tl_reader_config_element']['placeholderImage'],
            'exclude'   => true,
            'inputType' => 'fileTree',
            'eval'      => [
                'tl_class'   => 'w50 autoheight',
                'fieldType'  => 'radio',
                'filesOnly'  => true,
                'extensions' => Config::get('validImageTypes'),
                'mandatory'  => true
            ],
            'sql'       => "binary(16) NULL",
        ],
        'placeholderImageFemale'         => [
            'label'     => &$GLOBALS['TL_LANG']['tl_reader_config_element']['placeholderImageFemale'],
            'exclude'   => true,
            'inputType' => 'fileTree',
            'eval'      => [
                'tl_class'   => 'w50 autoheight',
                'fieldType'  => 'radio',
                'filesOnly'  => true,
                'extensions' => Config::get('validImageTypes'),
                'mandatory'  => true
            ],
            'sql'       => "binary(16) NULL",
        ],
        'genderField'                    => [
            'label'            => &$GLOBALS['TL_LANG']['tl_reader_config_element']['genderField'],
            'inputType'        => 'select',
            'options_callback' => function (DataContainer $dc) {
                return $dc->activeRecord->id > 0 ? System::getContainer()
                    ->get('huh.reader.util.reader-config-util')
                    ->getFields($dc->activeRecord->id) : [];
            },
            'exclude'          => true,
            'eval'             => ['includeBlankOption' => true, 'mandatory' => true, 'chosen' => true, 'tl_class' => 'w50 autoheight'],
            'sql'              => "varchar(64) NOT NULL default ''",
        ],
        // security
        'addRedirectConditions'          => [
            'label'     => &$GLOBALS['TL_LANG']['tl_reader_config_element']['addRedirectConditions'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => ['tl_class' => 'w50', 'submitOnChange' => true],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'name'                           => [
            'label'     => &$GLOBALS['TL_LANG']['tl_reader_config_element']['name'],
            'exclude'   => true,
            'search'    => true,
            'sorting'   => true,
            'flag'      => 1,
            'inputType' => 'text',
            'eval'      => ['mandatory' => true, 'tl_class' => 'w50', 'notOverridable' => true, 'maxlength' => 128],
            'sql'       => "varchar(128) NOT NULL default ''",
        ],
        'jumpTo'                         => [
            'label'      => &$GLOBALS['TL_LANG']['tl_reader_config_element']['jumpTo'],
            'exclude'    => true,
            'inputType'  => 'pageTree',
            'foreignKey' => 'tl_page.title',
            'eval'       => ['fieldType' => 'radio', 'mandatory' => true, 'tl_class' => 'w50'],
            'sql'        => "int(10) unsigned NOT NULL default '0'",
            'relation'   => ['type' => 'hasOne', 'load' => 'eager'],
        ],
        'addRedirectParam'               => [
            'label'     => &$GLOBALS['TL_LANG']['tl_reader_config_element']['addRedirectParam'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => ['tl_class' => 'w50', 'submitOnChange' => true],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'redirectParams'                 => [
            'label'     => &$GLOBALS['TL_LANG']['tl_reader_config_element']['redirectParams'],
            'inputType' => 'multiColumnEditor',
            'eval'      => [
                'tl_class'          => 'clr',
                'multiColumnEditor' => [
                    'sortable'            => true,
                    'minRowCount'         => 1,
                    'maxRowCount'         => 5,
                    'skipCopyValuesOnAdd' => false,
                    'fields'              => [
                        'parameterType' => [
                            'label'     => &$GLOBALS['TL_LANG']['tl_reader_config_element']['parameterType'],
                            'filter'    => true,
                            'inputType' => 'select',
                            'options'   => \HeimrichHannot\ReaderBundle\Backend\ReaderConfigElement::REDIRECTION_PARAM_TYPES,
                            'eval'      => ['tl_class' => 'w50', 'chosen' => true, 'includeBlankOption' => true, 'groupStyle' => 'width: 250px'],
                        ],
                        'name'          => [
                            'label'     => &$GLOBALS['TL_LANG']['tl_reader_config_element']['name'],
                            'inputType' => 'text',
                            'eval'      => ['tl_class' => 'w50', 'chosen' => true, 'includeBlankOption' => true, 'groupStyle' => 'width: 250px'],
                        ],
                        'defaultValue'  => [
                            'label'     => &$GLOBALS['TL_LANG']['tl_reader_config_element']['defaultValue'],
                            'inputType' => 'text',
                            'eval'      => ['tl_class' => 'w50', 'chosen' => true, 'includeBlankOption' => true, 'groupStyle' => 'width: 250px'],
                        ],
                        'field'         => [
                            'label'            => &$GLOBALS['TL_LANG']['tl_reader_config_element']['field'],
                            'inputType'        => 'select',
                            'options_callback' => ['huh.reader.backend.reader-config-element', 'getFieldsAsOptions'],
                            'eval'             => [
                                'tl_class'           => 'w50',
                                'chosen'             => true,
                                'includeBlankOption' => true,
                                'groupStyle'         => 'width: 250px'
                            ],
                        ],
                    ],
                ],
            ],
            'sql'       => 'blob NULL',
        ],
        'navigationTemplate'             => [
            'label'            => &$GLOBALS['TL_LANG']['tl_reader_config_element']['navigationTemplate'],
            'exclude'          => true,
            'inputType'        => 'select',
            'default'          => 'readernavigation_default',
            'options_callback' => function (\Contao\DataContainer $dc) {
                return \Contao\System::getContainer()->get('huh.utils.choice.twig_template')->getCachedChoices(['readernavigation_']);
            },
            'eval'             => ['tl_class' => 'w50', 'includeBlankOption' => true, 'mandatory' => true],
            'sql'              => "varchar(64) NOT NULL default ''",
        ],
        'listConfig'                     => [
            'label'      => &$GLOBALS['TL_LANG']['tl_reader_config_element']['listConfig'],
            'exclude'    => true,
            'filter'     => true,
            'inputType'  => 'select',
            'foreignKey' => 'tl_list_config.title',
            'relation'   => ['type' => 'belongsTo', 'load' => 'lazy'],
            'eval'       => ['tl_class' => 'long clr', 'includeBlankOption' => true, 'chosen' => true],
            'sql'        => "int(10) unsigned NOT NULL default '0'",
        ],
        'previousLabel'                  => [
            'label'            => &$GLOBALS['TL_LANG']['tl_reader_config_element']['previousLabel'],
            'exclude'          => true,
            'inputType'        => 'select',
            'default'          => 'huh.reader.element.label.previous.default',
            'options_callback' => function (\DataContainer $dc) {
                return \Contao\System::getContainer()->get('huh.utils.choice.message')->getCachedChoices('huh.reader.element.label.previous');
            },
            'eval'             => ['chosen' => true, 'mandatory' => true, 'maxlength' => 128, 'includeBlankOption' => true, 'tl_class' => 'w50'],
            'sql'              => "varchar(64) NOT NULL default ''",
        ],
        'nextLabel'                      => [
            'label'            => &$GLOBALS['TL_LANG']['tl_reader_config_element']['nextLabel'],
            'exclude'          => true,
            'inputType'        => 'select',
            'default'          => 'huh.reader.element.label.next.default',
            'options_callback' => function (\DataContainer $dc) {
                return \Contao\System::getContainer()->get('huh.utils.choice.message')->getCachedChoices('huh.reader.element.label.next');
            },
            'eval'             => ['chosen' => true, 'mandatory' => true, 'maxlength' => 128, 'includeBlankOption' => true, 'tl_class' => 'w50'],
            'sql'              => "varchar(64) NOT NULL default ''",
        ],
        'previousTitle'                  => [
            'label'            => &$GLOBALS['TL_LANG']['tl_reader_config_element']['previousTitle'],
            'exclude'          => true,
            'inputType'        => 'select',
            'default'          => 'huh.reader.element.title.previous.default',
            'options_callback' => function (\DataContainer $dc) {
                return \Contao\System::getContainer()->get('huh.utils.choice.message')->getCachedChoices('huh.reader.element.title.previous');
            },
            'eval'             => ['chosen' => true, 'maxlength' => 128, 'includeBlankOption' => true, 'tl_class' => 'w50'],
            'sql'              => "varchar(64) NOT NULL default ''",
        ],
        'nextTitle'                      => [
            'label'            => &$GLOBALS['TL_LANG']['tl_reader_config_element']['nextTitle'],
            'exclude'          => true,
            'inputType'        => 'select',
            'default'          => 'huh.reader.element.title.next.default',
            'options_callback' => function (\DataContainer $dc) {
                return \Contao\System::getContainer()->get('huh.utils.choice.message')->getCachedChoices('huh.reader.element.title.next');
            },
            'eval'             => ['chosen' => true, 'maxlength' => 128, 'includeBlankOption' => true, 'tl_class' => 'w50'],
            'sql'              => "varchar(64) NOT NULL default ''",
        ],
        'sortingField'                   => [
            'label'            => &$GLOBALS['TL_LANG']['tl_reader_config_element']['sortingField'],
            'exclude'          => true,
            'filter'           => true,
            'inputType'        => 'select',
            'options_callback' => function (DataContainer $dc) {
                return $dc->activeRecord->pid > 0 ? System::getContainer()
                    ->get('huh.reader.util.reader-config-util')
                    ->getFields($dc->activeRecord->pid) : [];
            },
            'eval'             => ['tl_class' => 'w50', 'mandatory' => true, 'includeBlankOption' => true, 'chosen' => true],
            'sql'              => "varchar(64) NOT NULL default ''",
        ],
        // security
        'infiniteNavigation'             => [
            'label'     => &$GLOBALS['TL_LANG']['tl_reader_config_element']['infiniteNavigation'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => ['tl_class' => 'w50'],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'syndicationTemplate'            => [
            'label'            => &$GLOBALS['TL_LANG']['tl_reader_config_element']['syndicationTemplate'],
            'exclude'          => true,
            'inputType'        => 'select',
            'default'          => 'readersyndication_default',
            'options_callback' => function (\Contao\DataContainer $dc) {
                return \Contao\System::getContainer()->get('huh.utils.choice.twig_template')->getCachedChoices(['readersyndication_']);
            },
            'eval'             => ['tl_class' => 'w50', 'includeBlankOption' => true, 'mandatory' => true],
            'sql'              => "varchar(64) NOT NULL default ''",
        ],
        'syndicationFacebook'            => [
            'label'     => &$GLOBALS['TL_LANG']['tl_reader_config_element']['syndicationFacebook'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => ['tl_class' => 'w50 clr'],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'syndicationTwitter'             => [
            'label'     => &$GLOBALS['TL_LANG']['tl_reader_config_element']['syndicationTwitter'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => ['tl_class' => 'w50'],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'syndicationGooglePlus'          => [
            'label'     => &$GLOBALS['TL_LANG']['tl_reader_config_element']['syndicationGooglePlus'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => ['tl_class' => 'w50'],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'syndicationLinkedIn'            => [
            'label'     => &$GLOBALS['TL_LANG']['tl_reader_config_element']['syndicationLinkedIn'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => ['tl_class' => 'w50'],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'syndicationXing'                => [
            'label'     => &$GLOBALS['TL_LANG']['tl_reader_config_element']['syndicationXing'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => ['tl_class' => 'w50', 'submitOnChange' => true],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'syndicationMail'                => [
            'label'     => &$GLOBALS['TL_LANG']['tl_reader_config_element']['syndicationMail'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => ['tl_class' => 'w50', 'submitOnChange' => true],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'mailSubjectLabel'               => [
            'label'            => &$GLOBALS['TL_LANG']['tl_reader_config_element']['mailSubjectLabel'],
            'exclude'          => true,
            'inputType'        => 'select',
            'default'          => 'huh.reader.element.mail.subject.syndication.default',
            'options_callback' => function (\DataContainer $dc) {
                return \Contao\System::getContainer()->get('huh.utils.choice.message')->getCachedChoices('huh.reader.element.mail.subject');
            },
            'eval'             => ['chosen' => true, 'mandatory' => true, 'maxlength' => 128, 'includeBlankOption' => true, 'tl_class' => 'w50'],
            'sql'              => "varchar(64) NOT NULL default ''",
        ],
        'mailBodyLabel'                  => [
            'label'            => &$GLOBALS['TL_LANG']['tl_reader_config_element']['mailBodyLabel'],
            'exclude'          => true,
            'inputType'        => 'select',
            'default'          => 'huh.reader.element.mail.body.syndication.default',
            'options_callback' => function (\DataContainer $dc) {
                return \Contao\System::getContainer()->get('huh.utils.choice.message')->getCachedChoices('huh.reader.element.mail.body');
            },
            'eval'             => ['chosen' => true, 'mandatory' => true, 'maxlength' => 128, 'includeBlankOption' => true, 'tl_class' => 'w50'],
            'sql'              => "varchar(64) NOT NULL default ''",
        ],
        'syndicationPdf'                 => [
            'label'     => &$GLOBALS['TL_LANG']['tl_reader_config_element']['syndicationPdf'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => ['tl_class' => 'w50', 'submitOnChange' => true],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'syndicationPdfReader'           => [
            'label'            => &$GLOBALS['TL_LANG']['tl_reader_config_element']['syndicationPdfReader'],
            'exclude'          => true,
            'inputType'        => 'select',
            'default'          => 'default',
            'options_callback' => function (\Contao\DataContainer $dc) {
                return \Contao\System::getContainer()->get('huh.reader.choice.syndication-pdf-reader')->getCachedChoices();
            },
            'eval'             => ['tl_class' => 'w50', 'includeBlankOption' => true, 'mandatory' => true],
            'sql'              => "varchar(64) NOT NULL default ''",
        ],
        'syndicationPdfFontDirectories'  => [
            'label'     => &$GLOBALS['TL_LANG']['tl_reader_config_element']['syndicationPdfFontDirectories'],
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => ['includeBlankOption' => true, 'tl_class' => 'clr'],
            'sql'       => "varchar(255) NOT NULL default ''",
        ],
        'syndicationPdfTemplate'         => [
            'label'            => &$GLOBALS['TL_LANG']['tl_reader_config_element']['syndicationPdfTemplate'],
            'exclude'          => true,
            'inputType'        => 'select',
            'default'          => 'readerpdf_default',
            'options_callback' => function (\Contao\DataContainer $dc) {
                return \Contao\System::getContainer()->get('huh.utils.choice.twig_template')->getCachedChoices(['readerpdf_']);
            },
            'eval'             => ['tl_class' => 'w50', 'includeBlankOption' => true, 'mandatory' => true],
            'sql'              => "varchar(64) NOT NULL default ''",
        ],
        'syndicationPdfMasterTemplate'   => [
            'label'     => &$GLOBALS['TL_LANG']['tl_reader_config_element']['syndicationPdfMasterTemplate'],
            'inputType' => 'fileTree',
            'exclude'   => true,
            'eval'      => [
                'filesOnly'  => true,
                'extensions' => 'pdf',
                'fieldType'  => 'radio',
                'tl_class'   => 'w50 clr',
            ],
            'sql'       => "binary(16) NULL",
        ],
        'syndicationPdfPageMargin'       => [
            'label'     => &$GLOBALS['TL_LANG']['tl_reader_config_element']['syndicationPdfPageMargin'],
            'exclude'   => true,
            'inputType' => 'trbl',
            'default'   => [
                'bottom' => '15',
                'left'   => '15',
                'right'  => '15',
                'top'    => '15',
                'unit'   => 'mm',
            ],
            'options'   => [
                'mm',
            ],
            'eval'      => ['includeBlankOption' => true, 'tl_class' => 'w50'],
            'sql'       => "varchar(128) NOT NULL default ''",
        ],
        'syndicationPrint'               => [
            'label'     => &$GLOBALS['TL_LANG']['tl_reader_config_element']['syndicationPrint'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => ['tl_class' => 'w50', 'submitOnChange' => true],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'syndicationPrintTemplate'       => [
            'label'            => &$GLOBALS['TL_LANG']['tl_reader_config_element']['syndicationPrintTemplate'],
            'exclude'          => true,
            'inputType'        => 'select',
            'default'          => 'readerprint_default',
            'options_callback' => function (\Contao\DataContainer $dc) {
                return \Contao\System::getContainer()->get('huh.utils.choice.twig_template')->getCachedChoices(['readerprint_']);
            },
            'eval'             => ['tl_class' => 'w50', 'includeBlankOption' => true, 'mandatory' => true],
            'sql'              => "varchar(64) NOT NULL default ''",
        ],
        'syndicationIcs'                 => [
            'label'     => &$GLOBALS['TL_LANG']['tl_reader_config_element']['syndicationIcs'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => ['tl_class' => 'w50'],
            'sql'       => "char(1) NOT NULL default ''"
        ],
        'syndicationTumblr'              => [
            'label'     => &$GLOBALS['TL_LANG']['tl_reader_config_element']['syndicationTumblr'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => ['tl_class' => 'w50'],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'syndicationPinterest'           => [
            'label'     => &$GLOBALS['TL_LANG']['tl_reader_config_element']['syndicationPinterest'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => ['tl_class' => 'w50', 'submitOnChange' => true],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'syndicationReddit'              => [
            'label'     => &$GLOBALS['TL_LANG']['tl_reader_config_element']['syndicationReddit'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => ['tl_class' => 'w50'],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'syndicationWhatsApp'            => [
            'label'     => &$GLOBALS['TL_LANG']['tl_reader_config_element']['syndicationWhatsApp'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => ['tl_class' => 'w50'],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'addMemberGroups'                => [
            'label'     => &$GLOBALS['TL_LANG']['tl_reader_config_element']['addMemberGroups'],
            'exclude'   => true,
            'eval'      => ['tl_class' => 'w50 clr', 'submitOnChange' => true],
            'inputType' => 'checkbox',
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'memberGroups'                   => [
            'label'      => &$GLOBALS['TL_LANG']['tl_reader_config_element']['memberGroups'],
            'exclude'    => true,
            'inputType'  => 'checkbox',
            'foreignKey' => 'tl_member_group.name',
            'eval'       => ['mandatory' => true, 'multiple' => true],
            'sql'        => "blob NULL",
            'relation'   => ['type' => 'hasMany', 'load' => 'lazy'],
        ],
        'deleteClass'                    => [
            'label'     => &$GLOBALS['TL_LANG']['tl_reader_config_element']['deleteClass'],
            'exclude'   => true,
            'inputType' => 'select',
            'options'   => System::getContainer()->get('huh.reader.util.reader-config-element-util')->getDeleteClasses(),
            'eval'      => ['tl_class' => 'w50', 'mandatory' => true, 'includeBlankOption' => true],
            'sql'       => "varchar(64) NOT NULL default ''",
        ],
        'addAutoItem'                    => [
            'label'     => &$GLOBALS['TL_LANG']['tl_reader_config_element']['addAutoItem'],
            'exclude'   => true,
            'eval'      => ['tl_class' => 'w50'],
            'inputType' => 'checkbox',
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'deleteJumpTo'                   => [
            'label'      => &$GLOBALS['TL_LANG']['tl_reader_config_element']['deleteJumpTo'],
            'exclude'    => true,
            'inputType'  => 'pageTree',
            'foreignKey' => 'tl_page.title',
            'eval'       => ['fieldType' => 'radio', 'mandatory' => true, 'tl_class' => 'w50'],
            'sql'        => "int(10) unsigned NOT NULL default '0'",
            'relation'   => ['type' => 'hasOne', 'load' => 'eager'],
        ],
        'commentCustomTemplate'          => [
            'label'            => &$GLOBALS['TL_LANG']['tl_reader_config_element']['commentCustomTemplate'],
            'exclude'          => true,
            'inputType'        => 'select',
            'options_callback' => ['huh.reader.backend.reader-config-element', 'getCustomCommentTemplate'],
            'eval'             => ['tl_class' => 'w50', 'includeBlankOption' => true],
            'sql'              => "varchar(64) NOT NULL default ''",
        ],
        'commentTemplate'                => [
            'label'            => &$GLOBALS['TL_LANG']['tl_reader_config_element']['commentTemplate'],
            'exclude'          => true,
            'inputType'        => 'select',
            'options_callback' => ['tl_module_comments', 'getCommentTemplates'],
            'eval'             => ['tl_class' => 'w50', 'includeBlankOption' => true],
            'sql'              => "varchar(64) NOT NULL default ''",
        ],
        'commentNotify'                  => [
            'label'     => &$GLOBALS['TL_LANG']['tl_news_archive']['notify'],
            'default'   => 'notify_admin',
            'exclude'   => true,
            'inputType' => 'select',
            'options'   => ['notify_admin', 'notify_author', 'notify_both'],
            'eval'      => ['tl_class' => 'w50'],
            'reference' => &$GLOBALS['TL_LANG']['tl_news_archive'],
            'sql'       => "varchar(16) NOT NULL default ''"
        ],
        'commentSortOrder'               => [
            'label'     => &$GLOBALS['TL_LANG']['tl_news_archive']['sortOrder'],
            'default'   => 'ascending',
            'exclude'   => true,
            'inputType' => 'select',
            'options'   => ['ascending', 'descending'],
            'reference' => &$GLOBALS['TL_LANG']['MSC'],
            'eval'      => ['tl_class' => 'w50 clr'],
            'sql'       => "varchar(32) NOT NULL default ''"
        ],
        'commentPerPage'                 => [
            'label'     => &$GLOBALS['TL_LANG']['tl_news_archive']['perPage'],
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => ['rgxp' => 'natural', 'tl_class' => 'w50'],
            'sql'       => "smallint(5) unsigned NOT NULL default '0'"
        ],
        'commentModerate'                => [
            'label'     => &$GLOBALS['TL_LANG']['tl_news_archive']['moderate'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => ['tl_class' => 'w50'],
            'sql'       => "char(1) NOT NULL default ''"
        ],
        'commentBbcode'                  => [
            'label'     => &$GLOBALS['TL_LANG']['tl_news_archive']['bbcode'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => ['tl_class' => 'w50'],
            'sql'       => "char(1) NOT NULL default ''"
        ],
        'commentRequireLogin'            => [
            'label'     => &$GLOBALS['TL_LANG']['tl_news_archive']['requireLogin'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => ['tl_class' => 'w50'],
            'sql'       => "char(1) NOT NULL default ''"
        ],
        'commentDisableCaptcha'          => [
            'label'     => &$GLOBALS['TL_LANG']['tl_news_archive']['disableCaptcha'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => ['tl_class' => 'w50'],
            'sql'       => "char(1) NOT NULL default ''"
        ],
        'commentOverridePalette'         => [
            'label'     => &$GLOBALS['TL_LANG']['tl_reader_config_element']['commentOverridePalette'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => ['tl_class' => 'w50', 'submitOnChange' => true],
            'sql'       => "char(1) NOT NULL default ''"
        ],
        'commentPalette'                 => [
            'inputType' => 'checkboxWizard',
            'label'     => &$GLOBALS['TL_LANG']['tl_module']['formHybridEditable'],
            'options'   => ['name', 'email', 'website', 'comment', 'notify'],
            'exclude'   => true,
            'eval'      => ['multiple' => true, 'includeBlankOption' => true, 'tl_class' => 'w50 autoheight clr'],
            'sql'       => "blob NULL",
        ],
        'commentHideFields'              => [
            'label'     => &$GLOBALS['TL_LANG']['tl_reader_config_element']['commentHideFields'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => ['tl_class' => 'w50', 'submitOnChange' => true],
            'sql'       => "char(1) NOT NULL default ''"
        ],
        'commentHideFieldsPalette'       => [
            'label'     => &$GLOBALS['TL_LANG']['tl_module']['formHybridEditable'],
            'inputType' => 'checkboxWizard',
            'options'   => ['name', 'email', 'website'],
            'exclude'   => true,
            'eval'      => ['multiple' => true, 'includeBlankOption' => true, 'tl_class' => 'w50 autoheight clr'],
            'sql'       => "blob NULL",
        ]
    ],
];

\Contao\System::getContainer()->get('huh.entity_filter.manager')->addFilterToDca('redirectConditions', 'tl_reader_config_element', '');

$dca = &$GLOBALS['TL_DCA']['tl_reader_config_element'];

// list type
if (\Contao\System::getContainer()->get('huh.utils.container')->isBundleActive('HeimrichHannot\ListBundle\HeimrichHannotContaoListBundle')) {
    $dca['palettes'][\HeimrichHannot\ReaderBundle\Backend\ReaderConfigElement::TYPE_LIST] =
        '{title_type_legend},title,type;{config_legend},listName,listModule,initialFilter;';

    $dca['fields'] = array_merge($dca['fields'], [
        'sortingDirection' => [
            'label'     => &$GLOBALS['TL_LANG']['tl_reader_config_element']['sortingDirection'],
            'exclude'   => true,
            'filter'    => true,
            'default'   => \HeimrichHannot\ListBundle\Backend\ListConfig::SORTING_DIRECTION_DESC,
            'inputType' => 'select',
            'options'   => \HeimrichHannot\ListBundle\Backend\ListConfig::SORTING_DIRECTIONS,
            'reference' => &$GLOBALS['TL_LANG']['tl_list_config']['reference'],
            'eval'      => ['tl_class' => 'w50', 'mandatory' => true, 'includeBlankOption' => true],
            'sql'       => "varchar(16) NOT NULL default ''",
        ],
        'listModule'       => [
            'label'            => &$GLOBALS['TL_LANG']['tl_reader_config_element']['listModule'],
            'inputType'        => 'select',
            'exclude'          => true,
            'options_callback' => function () {
                return \Contao\System::getContainer()->get('huh.list.datacontainer.module')->getAllListModules();
            },
            'eval'             => [
                'includeBlankOption' => true,
                'mandatory'          => true,
                'chosen'             => true,
                'tl_class'           => 'w50 autoheight',
                'submitOnChange'     => true
            ],
            'sql'              => "int(10) unsigned NOT NULL default '0'",
        ],
        'listName'         => [
            'label'     => &$GLOBALS['TL_LANG']['tl_reader_config_element']['listName'],
            'exclude'   => true,
            'search'    => true,
            'inputType' => 'text',
            'eval'      => ['maxlength' => 128, 'tl_class' => 'w50', 'mandatory' => true],
            'sql'       => "varchar(128) NOT NULL default ''",
        ],
        'initialFilter'    => [
            'label'     => &$GLOBALS['TL_LANG']['tl_reader_config_element']['initialFilter'],
            'inputType' => 'multiColumnEditor',
            'eval'      => [
                'tl_class'          => 'clr',
                'multiColumnEditor' => [
                    'sortable'            => true,
                    'minRowCount'         => 1,
                    'maxRowCount'         => 5,
                    'skipCopyValuesOnAdd' => false,
                    'fields'              => [
                        'selector'      => [
                            'label'            => &$GLOBALS['TL_LANG']['tl_reader_config_element']['selector'],
                            'inputType'        => 'select',
                            'filter'           => true,
                            'options_callback' => function (DataContainer $dc) {
                                return System::getContainer()->get('huh.reader.util.reader-config-element-util')->getFields($dc);
                            },
                            'eval'             => [
                                'includeBlankOption' => true,
                                'chosen'             => true,
                                'mandatory'          => true,
                                'groupStyle'         => 'width:250px'
                            ],
                        ],
                        'filterElement' => [
                            'label'            => &$GLOBALS['TL_LANG']['tl_reader_config_element']['filterElement'],
                            'inputType'        => 'select',
                            'options_callback' => function (DataContainer $dc) {
                                return \Contao\System::getContainer()->get('huh.reader.backend.module')->getFieldsByListModule($dc);
                            },
                            'eval'             => [
                                'includeBlankOption' => true,
                                'chosen'             => true,
                                'mandatory'          => true,
                                'groupStyle'         => 'width:250px'
                            ],
                        ],
                    ],
                ],
            ],
            'sql'       => "blob NULL",
        ],
    ]);
} else {
    $dca['fields']['type']['options'] =
        array_diff($dca['fields']['type']['options'], [\HeimrichHannot\ReaderBundle\Backend\ReaderConfigElement::TYPE_LIST]);
}

