<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ReaderBundle\ConfigElementType\Syndication\Link;

interface LinkInterface
{
    /**
     * Get the link data as array.
     */
    public function getData(): array;

    /**
     * Get the href attribute.
     */
    public function getHref(): string;

    /**
     * Set the href attribute.
     *
     * @return LinkInterface
     */
    public function setHref(string $href): self;

    /**
     * Get the css class attribute.
     */
    public function getCssClass(): string;

    /**
     * Set the css class attribute.
     *
     * @param string $href
     *
     * @return LinkInterface
     */
    public function setCssClass(string $cssClass): self;

    /**
     * Get the title attribute.
     */
    public function getTitle(): string;

    /**
     * Set the title attribute.
     *
     * @return LinkInterface
     */
    public function setTitle(string $title): self;

    /**
     * Get the link content.
     */
    public function getContent(): string;

    /**
     * Set the link content.
     *
     * @return LinkInterface
     */
    public function setContent(string $content): self;

    /**
     * Get the target attribute.
     */
    public function getTarget(): string;

    /**
     * Set the link target.
     *
     * @return LinkInterface
     */
    public function setTarget(string $target): self;

    /**
     * Get the name attribute.
     */
    public function getName(): string;

    /**
     * Set the name attribute.
     *
     * @return LinkInterface
     */
    public function setName(string $name): self;

    /**
     * Get the rel attribute.
     */
    public function getRel(): string;

    /**
     * Set the rel attribute.
     *
     * @return LinkInterface
     */
    public function setRel(string $rel): self;

    /**
     * Get the onclick attribute.
     */
    public function getOnClick(): string;

    /**
     * Set the onclick attribute.
     *
     * @return LinkInterface
     */
    public function setOnClick(string $onclick): self;

    /**
     * Set the additional attribute.
     *
     * @return LinkInterface
     */
    public function setAttributes(array $attributes): self;

    /**
     * Get the additional attributes.
     */
    public function getAttributes(): array;

    /**
     * Add an additional attribute.
     *
     * @return LinkInterface
     */
    public function addAttribute(string $key, string $value): self;

    /**
     * Remove an additional attribute.
     *
     * @param $key
     */
    public function removeAttribute($key): bool;
}
