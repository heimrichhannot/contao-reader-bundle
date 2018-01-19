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
 * @property bool   $limitFields
 * @property string $fields
 * @property string $itemRetrievalMode
 * @property string $itemRetrievalAutoItemField
 * @property bool   $hideUnpublishedItems
 * @property string $publishedField
 * @property bool   $invertPublishedField
 * @property bool   $addShowConditions
 * @property string $showItemConditions
 * @property string $itemTemplate
 */
class ReaderConfigModel extends \Model
{
    protected static $strTable = 'tl_reader_config';
}
