<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ReaderBundle\Model;

use Contao\Model;

/**
 * @property int    $id
 * @property int    $pid
 * @property int    $tstamp
 * @property int    $dateAdded
 * @property string $title
 * @property string $type
 * @property string $templateVariable
 * @property string $imageSelectorField
 * @property string $imageField
 * @property string $imgSize
 * @property string $placeholderImageMode
 * @property string $placeholderImage
 * @property string $placeholderImageFemale
 * @property string $genderField
 * @property int    $listModule
 * @property string $listName
 * @property array  $initialFilter
 * @property array  $showRedirectConditions
 * @property int    $redirection
 * @property string $name
 * @property bool   $addRedirectConditions
 * @property array  $redirectParams
 * @property bool   $addRedirectParam
 * @property string $navigationTemplate
 * @property string $nextLabel
 * @property string $previousLabel
 * @property string $previousTitle
 * @property string $nextTitle
 * @property int    $listConfig
 * @property string $sortingDirection
 * @property string $sortingField
 * @property string $infiniteNavigation
 * @property string $syndicationTemplate
 * @property bool   $syndicationFacebook
 * @property bool   $syndicationTwitter
 * @property bool   $syndicationGooglePlus
 * @property bool   $syndicationLinkedIn
 * @property bool   $syndicationXing
 * @property bool   $syndicationMail
 * @property string $mailSubjectLabel
 * @property string $mailBodyLabel
 * @property bool   $syndicationPdf
 * @property string $syndicationPdfTemplate
 * @property string $syndicationPdfReader
 * @property string $syndicationPdfFontDirectories
 * @property string $syndicationPdfMasterTemplate
 * @property string $syndicationPdfPageMargin
 * @property bool   $syndicationPrint
 * @property string $syndicationPrintTemplate
 * @property bool   $syndicationTumblr
 * @property bool   $syndicationPinterest
 * @property bool   $syndicationReddit
 * @property bool   $syndicationWhatsApp
 * @property bool   $syndicationIcs
 */
class ReaderConfigElementModel extends Model
{
    protected static $strTable = 'tl_reader_config_element';
}
