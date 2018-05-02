<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
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
     *
     * @return string
     */
    public function getHref(): string;

    /**
     * Set the href attribute.
     *
     * @param string $href
     *
     * @return LinkInterface
     */
    public function setHref(string $href): self;

    /**
     * Get the css class attribute.
     *
     * @return string
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
     *
     * @return string
     */
    public function getTitle(): string;

    /**
     * Set the title attribute.
     *
     * @param string $title
     *
     * @return LinkInterface
     */
    public function setTitle(string $title): self;

    /**
     * Get the link content.
     *
     * @return string
     */
    public function getContent(): string;

    /**
     * Set the link content.
     *
     * @param string $content
     *
     * @return LinkInterface
     */
    public function setContent(string $content): self;

    /**
     * Get the target attribute.
     *
     * @return string
     */
    public function getTarget(): string;

    /**
     * Set the link target.
     *
     * @param string $target
     *
     * @return LinkInterface
     */
    public function setTarget(string $target): self;

    /**
     * Get the name attribute.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Set the name attribute.
     *
     * @param string $name
     *
     * @return LinkInterface
     */
    public function setName(string $name): self;

    /**
     * Get the rel attribute.
     *
     * @return string
     */
    public function getRel(): string;

    /**
     * Set the rel attribute.
     *
     * @param string $rel
     *
     * @return LinkInterface
     */
    public function setRel(string $rel): self;

    /**
     * Get the onclick attribute.
     *
     * @return string
     */
    public function getOnClick(): string;

    /**
     * Set the onclick attribute.
     *
     * @param string $onclick
     *
     * @return LinkInterface
     */
    public function setOnClick(string $onclick): self;

    /**
     * Set the additional attribute.
     *
     * @param array $attributes
     *
     * @return LinkInterface
     */
    public function setAttributes(array $attributes): self;

    /**
     * Get the additional attributes.
     *
     * @return array
     */
    public function getAttributes(): array;

    /**
     * Add an additional attribute.
     *
     * @param string $key
     * @param string $value
     *
     * @return LinkInterface
     */
    public function addAttribute(string $key, string $value): self;

    /**
     * Remove an additional attribute.
     *
     * @param $key
     *
     * @return bool
     */
    public function removeAttribute($key): bool;
}
