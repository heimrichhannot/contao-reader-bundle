<?php

/*
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ReaderBundle\ConfigElementType\Syndication\Link;

use Contao\System;

class DefaultLink implements LinkInterface
{
    /**
     * @var string
     */
    protected $href = '';

    /**
     * @var string
     */
    protected $cssClass = '';

    /**
     * @var string
     */
    protected $title = '';

    /**
     * @var string
     */
    protected $content = '';

    /**
     * @var string
     */
    protected $target = '';

    /**
     * @var string
     */
    protected $name = '';

    /**
     * @var string
     */
    protected $rel = '';

    /**
     * @var string
     */
    protected $onclick = '';

    /**
     * @var array
     */
    protected $attributes = [];

    /**
     * {@inheritdoc}
     */
    public function getData(): array
    {
        return System::getContainer()->get('huh.utils.class')->jsonSerialize($this);
    }

    /**
     * {@inheritdoc}
     */
    public function getHref(): string
    {
        return $this->href;
    }

    /**
     * {@inheritdoc}
     */
    public function setHref(string $href): LinkInterface
    {
        $this->href = $href;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCssClass(): string
    {
        return $this->cssClass;
    }

    /**
     * {@inheritdoc}
     */
    public function setCssClass(string $cssClass): LinkInterface
    {
        $this->cssClass = $cssClass;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * {@inheritdoc}
     */
    public function setTitle(string $title): LinkInterface
    {
        $this->title = $title;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * {@inheritdoc}
     */
    public function setContent(string $content): LinkInterface
    {
        $this->content = $content;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getTarget(): string
    {
        return $this->target;
    }

    /**
     * {@inheritdoc}
     */
    public function setTarget(string $target): LinkInterface
    {
        $this->target = $target;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function setName(string $name): LinkInterface
    {
        $this->name = $name;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getRel(): string
    {
        return $this->rel;
    }

    /**
     * {@inheritdoc}
     */
    public function setRel(string $rel): LinkInterface
    {
        $this->rel = $rel;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getOnClick(): string
    {
        return $this->onclick;
    }

    /**
     * {@inheritdoc}
     */
    public function setOnClick(string $onclick): LinkInterface
    {
        $this->onclick = $onclick;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setAttributes(array $attributes): LinkInterface
    {
        $this->attributes = $attributes;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * {@inheritdoc}
     */
    public function addAttribute(string $key, string $value): LinkInterface
    {
        $this->attributes[$key] = $value;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removeAttribute($key): bool
    {
        if (isset($this->attributes[$key])) {
            unset($this->attributes[$key]);

            return true;
        }

        return false;
    }
}
