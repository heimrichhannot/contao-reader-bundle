<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ReaderBundle\Item;

use Contao\DataContainer;
use HeimrichHannot\ReaderBundle\Manager\ReaderManagerInterface;

interface ItemInterface
{
    /**
     * Parse the current item and return the parsed string.
     *
     * @return string The parsed item
     */
    public function parse(): string;

    /**
     * Get entire raw item data.
     *
     * @return array
     */
    public function getRaw(): array;

    /**
     * Set entire raw item data.
     *
     * @param array $data
     */
    public function setRaw(array $data = []): void;

    /**
     * Get formatted value for a given property.
     *
     * @param string $name The property name
     *
     * @return mixed
     */
    public function getFormattedValue(string $name): mixed;

    /**
     * Set a formatted value for a given property.
     *
     * @param string $name  The property name
     * @param mixed  $value The property value
     */
    public function setFormattedValue(string $name, $value): void;

    /**
     * Set entire formatted item data.
     *
     * @param array $data
     */
    public function setFormatted(array $data = []): void;

    /**
     * Get the entire formatted data.
     *
     * @return array
     */
    public function getFormatted(): array;

    /**
     * Get the reader manager.
     *
     * @return ReaderManagerInterface
     */
    public function getManager(): ReaderManagerInterface;

    /**
     * Get the reader config dataContainer name.
     *
     * @return string
     */
    public function getDataContainer(): string;

    /**
     * Get the current module data.
     *
     * @return array
     */
    public function getModule(): array;
}
