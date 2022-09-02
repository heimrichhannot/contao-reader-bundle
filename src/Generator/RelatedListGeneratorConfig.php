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

    public function setDataContainer(string $dataContainer): void
    {
        $this->dataContainer = $dataContainer;
    }

    public function getEntityId(): int
    {
        return $this->entityId;
    }

    public function setEntityId(int $entityId): void
    {
        $this->entityId = $entityId;
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

    public function setTagsField(string $tagsField): void
    {
        $this->filterCfTags = true;
        $this->tagsField = $tagsField;
    }

    public function getListConfigId(): int
    {
        return $this->listConfigId;
    }
}
