<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ReaderBundle\Generator;

class RelatedListGeneratorConfig
{
    private string $dataContainer;
    private int $entityId;

    private string $tagsField;
    private string $categoriesField;

    private bool $filterCfTags = false;
    private bool $filterCategories = false;
    private int  $listConfigId;

    public function __construct(string $dataContainer, int $entityId, int $listConfigId)
    {
        $this->dataContainer = $dataContainer;
        $this->entityId = $entityId;
        $this->listConfigId = $listConfigId;
    }

    public function getDataContainer(): string
    {
        return $this->dataContainer;
    }

    public function getEntityId(): int
    {
        return $this->entityId;
    }

    public function getFilterCfTags(): bool
    {
        return $this->filterCfTags;
    }

    public function getFilterCategories(): bool
    {
        return $this->filterCategories;
    }

    public function getTagsField(): string
    {
        return $this->tagsField;
    }

    public function setTagsField(string $tagsField): self
    {
        $this->filterCfTags = true;
        $this->tagsField = $tagsField;

        return $this;
    }

    public function getListConfigId(): int
    {
        return $this->listConfigId;
    }

    public function getCategoriesField(): string
    {
        return $this->categoriesField;
    }

    public function setCategoriesField(string $categoriesField): self
    {
        $this->filterCategories = true;
        $this->categoriesField = $categoriesField;

        return $this;
    }
}
