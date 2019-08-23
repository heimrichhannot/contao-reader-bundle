<?php

/*
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ReaderBundle\ConfigElementType;

interface ReaderConfigElementTypeInterface
{
    /**
     * Return the reader config element type alias.
     *
     * @return string
     */
    public static function getType(): string;

    /**
     * Return the reader config element type palette.
     *
     * @return string
     */
    public function getPalette(): string;

    /**
     * Update the item data.
     *
     * @param ReaderConfigElementData $configElementData
     */
    public function addToListItemData(ReaderConfigElementData $configElementData): void;
}
