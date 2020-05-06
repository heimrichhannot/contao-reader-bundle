<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ReaderBundle\Manager;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\DataContainer;
use Doctrine\DBAL\Query\QueryBuilder;
use HeimrichHannot\FilterBundle\Config\FilterConfig;
use HeimrichHannot\ReaderBundle\Item\ItemInterface;
use HeimrichHannot\ReaderBundle\Model\ReaderConfigModel;
use HeimrichHannot\ReaderBundle\Registry\ReaderConfigElementRegistry;
use HeimrichHannot\UtilsBundle\Form\FormUtil;

interface ReaderManagerInterface
{
    const DC_MULTILINGUAL_SUFFIX = '_dcm';

    /**
     * Set the current module data.
     */
    public function setModuleData(array $moduleData): void;

    /**
     * Get current module data.
     */
    public function getModuleData(): array;

    /**
     * Get the reader config model.
     */
    public function getReaderConfig(): ReaderConfigModel;

    /**
     * Get the reader config.
     *
     * @return FilterConfig
     */
    public function getFilterConfig(): ?FilterConfig;

    /**
     * Get the query builder.
     */
    public function getQueryBuilder(): QueryBuilder;

    /**
     * Set the current reader config model.
     *
     * @return mixed
     */
    public function setReaderConfig(ReaderConfigModel $readerConfig): void;

    /**
     * Retrieve the current item model from given conditions (auto_item…).
     */
    public function retrieveItem(): ?ItemInterface;

    /**
     * Trigger the DataContainer onload_callback callbacks.
     */
    public function triggerOnLoadCallbacks(): void;

    /**
     * Do field dependent redirects, based on item conditions.
     */
    public function doFieldDependentRedirect(): void;

    /**
     * Set the head tags.
     */
    public function setHeadTags(): void;

    /**
     * Check permission state.
     *
     * @return bool True if access granted, false to deny access
     */
    public function checkPermission(): bool;

    /**
     * Set current DataContainer instance.
     */
    public function setDataContainer(DataContainer $dc): void;

    /**
     * Get the current data container.
     */
    public function getDataContainer(): ?DataContainer;

    /**
     * Set the current item.
     */
    public function setItem(ItemInterface $item): void;

    /**
     * Get the current item.
     */
    public function getItem(): ItemInterface;

    /**
     * Get current reader config element registry.
     */
    public function getReaderConfigElementRegistry(): ReaderConfigElementRegistry;

    /**
     * Get the current item template path.
     *
     * @param string $name Item template name
     *
     * @return string|null
     */
    public function getItemTemplateByName(string $name);

    /**
     * Get the current item class.
     *
     * @param string $name Item name
     *
     * @return string|null
     */
    public function getItemClassByName(string $name);

    /**
     * Get current twig environment.
     */
    public function getTwig(): \Twig_Environment;

    /**
     * Get the contao framework.
     */
    public function getFramework(): ContaoFrameworkInterface;

    /**
     * Get current form utils.
     */
    public function getFormUtil(): FormUtil;
}
