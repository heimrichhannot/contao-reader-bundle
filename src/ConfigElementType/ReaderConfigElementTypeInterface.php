<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ReaderBundle\ConfigElementType;

/**
 * Interface ReaderConfigElementTypeInterface.
 *
 * @deprecated Use ConfigElementTypeInterface instead
 */
interface ReaderConfigElementTypeInterface
{
    /**
     * Return the reader config element type alias.
     */
    public static function getType(): string;

    /**
     * Return the reader config element type palette.
     */
    public function getPalette(): string;

    /**
     * Update the item data.
     */
    public function addToReaderItemData(ReaderConfigElementData $configElementData): void;
}
