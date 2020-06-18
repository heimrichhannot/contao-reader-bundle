<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
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
     */
    public function getRaw(): array;

    /**
     * Set entire raw item data.
     */
    public function setRaw(array $data = []): void;

    /**
     * Get raw value for a given property.
     *
     * @param string $name The property name
     *
     * @return mixed
     */
    public function getRawValue(string $name);

    /**
     * Set a raw value for a given property.
     *
     * @param string $name  The property name
     * @param mixed  $value The property value
     */
    public function setRawValue(string $name, $value): void;

    /**
     * Get the entire formatted data.
     */
    public function getFormatted(): array;

    /**
     * Set entire formatted item data.
     */
    public function setFormatted(array $data = []): void;

    /**
     * Get formatted value for a given property.
     *
     * @param string $name The property name
     *
     * @return mixed
     */
    public function getFormattedValue(string $name);

    /**
     * Set a formatted value for a given property.
     *
     * @param string $name  The property name
     * @param mixed  $value The property value
     */
    public function setFormattedValue(string $name, $value): void;

    /**
     * Get the reader manager.
     */
    public function getManager(): ReaderManagerInterface;

    /**
     * Get the reader config dataContainer name.
     */
    public function getDataContainer(): string;

    /**
     * Get the current module data.
     */
    public function getModule(): array;

    /**
     * Get the details url if available.
     *
     * @param bool $external Determine if external urls should be returned as well (required by search index)
     */
    public function getDetailsUrl(bool $external = true, bool $isCanonical = false): ?string;

    /**
     * @return mixed
     */
    public function jsonSerialize();
}
