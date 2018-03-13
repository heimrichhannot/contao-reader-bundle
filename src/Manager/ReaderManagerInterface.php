<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ReaderBundle\Manager;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\DataContainer;
use HeimrichHannot\ReaderBundle\Item\ItemInterface;
use HeimrichHannot\ReaderBundle\Model\ReaderConfigModel;
use HeimrichHannot\ReaderBundle\Registry\ReaderConfigElementRegistry;
use HeimrichHannot\UtilsBundle\Form\FormUtil;

interface ReaderManagerInterface
{
    /**
     * Set the current module data.
     *
     * @param array $moduleData
     */
    public function setModuleData(array $moduleData): void;

    /**
     * Get current module data.
     *
     * @return array
     */
    public function getModuleData(): array;

    /**
     * Get the reader config model.
     *
     * @return ReaderConfigModel
     */
    public function getReaderConfig(): ReaderConfigModel;

    /**
     * Set the current reader config model.
     *
     * @return mixed
     */
    public function setReaderConfig(ReaderConfigModel $readerConfig): void;

    /**
     * Retrieve the current item model from given conditions (auto_item…).
     *
     * @return ItemInterface|null
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
     * Set the page title based on current item.
     */
    public function setPageTitle(): void;

    /**
     * Check permission state.
     *
     * @return bool True if access granted, false to deny access
     */
    public function checkPermission(): bool;

    /**
     * Set current DataContainer instance.
     *
     * @param DataContainer $dc
     */
    public function setDataContainer(DataContainer $dc): void;

    /**
     * Get the current data container.
     *
     * @return null|DataContainer
     */
    public function getDataContainer(): ?DataContainer;

    /**
     * Set the current item.
     *
     * @param ItemInterface $item
     */
    public function setItem(ItemInterface $item): void;

    /**
     * Get the current item.
     *
     * @return ItemInterface
     */
    public function getItem(): ItemInterface;

    /**
     * Get current reader config element registry.
     *
     * @return ReaderConfigElementRegistry
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
     *
     * @return \Twig_Environment
     */
    public function getTwig(): \Twig_Environment;

    /**
     * Get the contao framework.
     *
     * @return ContaoFrameworkInterface
     */
    public function getFramework(): ContaoFrameworkInterface;

    /**
     * Get current form utils.
     *
     * @return FormUtil
     */
    public function getFormUtil(): FormUtil;
}
