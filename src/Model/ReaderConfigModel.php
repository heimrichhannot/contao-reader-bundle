<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ReaderBundle\Model;

/**
 * @property int    $id
 * @property int    $tstamp
 * @property int    $dateAdded
 * @property string $title
 * @property string $dataContainer
 * @property string $manager
 * @property string $item
 * @property bool   $limitFormattedFields
 * @property string $formattedFields
 * @property string $itemRetrievalMode
 * @property string $itemRetrievalAutoItemField
 * @property string $itemRetrievalFieldConditions
 * @property bool   $hideUnpublishedItems
 * @property string $publishedField
 * @property bool   $invertPublishedField
 * @property bool   $addShowConditions
 * @property string $showFieldConditions
 * @property bool   $addFieldDependentRedirect
 * @property int    $fieldDependentJumpTo
 * @property string $redirectFieldConditions
 * @property bool   $setPageTitleByField
 * @property string $pageTitleFieldPattern
 * @property string $itemTemplate
 */
class ReaderConfigModel extends \Model
{
    protected static $strTable = 'tl_reader_config';
}
