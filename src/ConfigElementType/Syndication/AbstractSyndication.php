<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ReaderBundle\ConfigElementType\Syndication;

use Contao\Controller;
use Contao\StringUtil;
use Contao\System;
use HeimrichHannot\ReaderBundle\ConfigElementType\Syndication\Link\LinkInterface;
use HeimrichHannot\ReaderBundle\Item\ItemInterface;
use HeimrichHannot\ReaderBundle\Model\ReaderConfigElementModel;

abstract class AbstractSyndication
{
    /**
     * @var ItemInterface
     */
    protected $item;

    /**
     * @var ReaderConfigElementModel
     */
    protected $readerConfigElement;

    /**
     * Current item title.
     *
     * @var string
     */
    protected $title;

    /**
     * Current item url.
     *
     * @var string
     */
    protected $url;

    /**
     * Current item description.
     *
     * @var
     */
    protected $description;

    /**
     * AbstractSyndication constructor.
     */
    public function __construct(ItemInterface $item, ReaderConfigElementModel $readerConfigElement)
    {
        $this->item = $item;
        $this->readerConfigElement = $readerConfigElement;
        $this->url = System::getContainer()->get('request_stack')->getMasterRequest()->getSchemeAndHttpHost().System::getContainer()->get('request_stack')->getMasterRequest()->getPathInfo();

        /*
         * @var PageModel $objPage
         */
        global $objPage;

        $this->title = $objPage->pageTitle;

        $description = StringUtil::decodeEntities(System::getContainer()->get('huh.head.tag.meta_description')->getContent());
        $description = Controller::replaceInsertTags($description, false);
        $description = strip_tags($description);
        $description = str_replace("\n", ' ', $description);
        $description = \StringUtil::substr($description, 320);

        $this->description = $description;
    }

    /**
     * Generate the syndication link.
     */
    abstract public function generate(): LinkInterface;

    /**
     * Determine if syndication is enabled, check against $readerConfigElement property for example.
     */
    abstract public function isEnabled(): bool;

    /**
     * Get current item title.
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * Set current item title.
     *
     * @return AbstractSyndication
     */
    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get current item url.
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * Get current item url.
     *
     * @return AbstractSyndication
     */
    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get current item description.
     *
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set the current item description.
     *
     * @param mixed $description
     *
     * @return AbstractSyndication
     */
    public function setDescription($description): self
    {
        $this->description = $description;

        return $this;
    }
}
