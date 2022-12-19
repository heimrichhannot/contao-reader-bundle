<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ReaderBundle\Model;

use Contao\Model;

/**
 * @property int         $id
 * @property int         $tstamp
 * @property int         $dateAdded
 * @property string      $title
 * @property string      $dataContainer
 * @property int         $filter
 * @property string      $evaluateFilter
 * @property string      $manager
 * @property string      $item
 * @property bool        $limitFormattedFields
 * @property string      $formattedFields
 * @property string      $itemRetrievalMode
 * @property string      $itemRetrievalAutoItemField
 * @property string      $itemRetrievalFieldConditions
 * @property bool        $hideUnpublishedItems
 * @property string      $publishedField
 * @property bool        $invertPublishedField
 * @property bool        $addShowConditions
 * @property string      $showFieldConditions
 * @property bool        $addFieldDependentRedirect
 * @property int         $fieldDependentJumpTo
 * @property string      $redirectFieldConditions
 * @property array       $headTags
 * @property string      $itemTemplate
 * @property string|bool $addOverview
 * @property string      $overviewMode
 * @property string      $jumpToOverview
 * @property string      $jumpToOverviewMultilingual
 * @property string|bool $customJumpToOverviewLabel
 * @property string      $jumpToOverviewLabel
 */
class ReaderConfigModel extends Model
{
    protected static $strTable = 'tl_reader_config';
}
