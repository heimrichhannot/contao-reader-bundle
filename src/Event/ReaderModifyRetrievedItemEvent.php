<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ReaderBundle\Event;

use Doctrine\DBAL\Query\QueryBuilder;
use HeimrichHannot\ReaderBundle\Manager\ReaderManagerInterface;
use HeimrichHannot\ReaderBundle\Model\ReaderConfigModel;
use Symfony\Component\EventDispatcher\Event;

class ReaderModifyRetrievedItemEvent extends Event
{
    const NAME = 'huh.reader.event.reader_modify_retrieved_item';

    /**
     * @var QueryBuilder
     */
    protected $queryBuilder;

    /**
     * @var ReaderManagerInterface
     */
    protected $readerManager;

    /**
     * @var ReaderConfigModel
     */
    protected $readerConfig;
    /**
     * @var string
     */
    private $fields;
    /**
     * @var array
     */
    private $item;

    /**
     * @param QueryBuilder           $queryBuilder
     * @param ReaderManagerInterface $readerManager
     * @param ReaderConfigModel      $readerConfig
     */
    public function __construct(?array $item, QueryBuilder $queryBuilder, ReaderManagerInterface $readerManager, ReaderConfigModel $readerConfig, string $fields)
    {
        $this->queryBuilder = $queryBuilder;
        $this->readerManager = $readerManager;
        $this->readerConfig = $readerConfig;
        $this->fields = $fields;
        $this->item = $item;
    }

    /**
     * @return QueryBuilder
     */
    public function getQueryBuilder(): QueryBuilder
    {
        return $this->queryBuilder;
    }

    /**
     * @param QueryBuilder $queryBuilder
     */
    public function setQueryBuilder(QueryBuilder $queryBuilder): void
    {
        $this->queryBuilder = $queryBuilder;
    }

    /**
     * @return ReaderManagerInterface
     */
    public function getReaderManager(): ReaderManagerInterface
    {
        return $this->readerManager;
    }

    /**
     * @param ReaderManagerInterface $readerManager
     */
    public function setReaderManager(ReaderManagerInterface $readerManager): void
    {
        $this->readerManager = $readerManager;
    }

    /**
     * @return ReaderConfigModel
     */
    public function getReaderConfig(): ReaderConfigModel
    {
        return $this->readerConfig;
    }

    /**
     * @param ReaderConfigModel $readerConfig
     */
    public function setReaderConfig(ReaderConfigModel $readerConfig): void
    {
        $this->readerConfig = $readerConfig;
    }

    /**
     * @return string
     */
    public function getFields(): string
    {
        return $this->fields;
    }

    /**
     * @param string $fields
     */
    public function setFields(string $fields): void
    {
        $this->fields = $fields;
    }

    /**
     * @return array
     */
    public function getItem(): ?array
    {
        return $this->item;
    }

    /**
     * @param array $item
     */
    public function setItem(?array $item): void
    {
        $this->item = $item;
    }
}
