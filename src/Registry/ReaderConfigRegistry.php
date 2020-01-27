<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ReaderBundle\Registry;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\System;
use HeimrichHannot\FilterBundle\Manager\FilterManager;
use HeimrichHannot\ReaderBundle\Manager\ReaderManagerInterface;
use HeimrichHannot\ReaderBundle\Model\ReaderConfigModel;
use HeimrichHannot\UtilsBundle\Dca\DcaUtil;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;

class ReaderConfigRegistry
{
    /** @var ContaoFrameworkInterface */
    protected $framework;

    /** @var FilterManager */
    protected $filterManager;

    /** @var ModelUtil */
    protected $modelUtil;

    /** @var DcaUtil */
    protected $dcaUtil;

    public function __construct(ContaoFrameworkInterface $framework, FilterManager $filterManager, ModelUtil $modelUtil, DcaUtil $dcaUtil)
    {
        $this->framework = $framework;
        $this->filterManager = $filterManager;
        $this->modelUtil = $modelUtil;
        $this->dcaUtil = $dcaUtil;
    }

    /**
     * Adapter function for the model's findBy method.
     *
     * @param mixed $column
     * @param mixed $value
     * @param array $options
     *
     * @return \Contao\Model\Collection|ReaderConfigModel|null
     */
    public function findBy($column, $value, array $options = [])
    {
        return $this->modelUtil->findModelInstancesBy(
            'tl_reader_config',
            $column,
            $value,
            $options
        );
    }

    /**
     * Adapter function for the model's findOneBy method.
     *
     * @param mixed $column
     * @param mixed $value
     * @param array $options
     *
     * @return \Contao\Model\Collection|ReaderConfigModel|null
     */
    public function findOneBy($column, $value, array $options = [])
    {
        return $this->modelUtil->findModelInstancesBy(
            'tl_reader_config',
            $column,
            $value,
            $options
        );
    }

    /**
     * Adapter function for the model's findByPk method.
     *
     * @param mixed $column
     * @param mixed $value
     * @param array $options
     *
     * @return \Contao\Model\Collection|ReaderConfigModel|null
     */
    public function findByPk($pk, array $options = [])
    {
        return $this->modelUtil->findModelInstanceByPk(
            'tl_reader_config',
            $pk,
            $options
        );
    }

    /**
     * Returns the filter associated to a reader config.
     *
     * @param int $readerConfigPk
     *
     * @return array|null
     */
    public function getFilterByPk(int $readerConfigPk)
    {
        if (null === ($readerConfig = $this->findByPk($readerConfigPk))) {
            return null;
        }

        if (!$readerConfig->filter || null === ($filterConfig = $this->filterManager->findById($readerConfig->filter))) {
            return null;
        }

        return $filterConfig->getFilter();
    }

    public function getOverridableProperty($property, int $readerConfigPk)
    {
        if (null === ($readerConfig = $this->findByPk($readerConfigPk))) {
            return null;
        }

        $parentReaderConfigs = $this->modelUtil->findParentsRecursively(
            'parentReaderConfig',
            'tl_reader_config',
            $readerConfig
        );

        if (empty($parentReaderConfigs)) {
            return null;
        }

        return $this->dcaUtil->getOverridableProperty(
            $property,
            $parentReaderConfigs
        );
    }

    /**
     * Computes the reader config respecting the reader config hierarchy (sub reader configs can override parts of their ancestors).
     *
     * @param int $readerConfigPk
     *
     * @return ReaderConfigModel|null
     */
    public function computeReaderConfig(int $readerConfigPk)
    {
        if (null === ($readerConfig = $this->findByPk($readerConfigPk))) {
            return null;
        }

        $readerConfig->rootId = $readerConfig->id;

        if (!$readerConfig->parentReaderConfig) {
            return $readerConfig;
        }

        $computedReaderConfig = new ReaderConfigModel();

        $parentReaderConfigs = $this->modelUtil->findParentsRecursively(
            'parentReaderConfig', 'tl_reader_config', $readerConfig
        );

        $rootReaderConfig = $this->modelUtil->findRootParentRecursively(
            'parentReaderConfig', 'tl_reader_config', $readerConfig
        );

        foreach ($GLOBALS['TL_DCA']['tl_reader_config']['fields'] as $field => $data) {
            if ($data['eval']['notOverridable']) {
                $computedReaderConfig->{$field} = $rootReaderConfig->{$field};
            } else {
                $computedReaderConfig->{$field} = $this->dcaUtil->getOverridableProperty(
                    $field,
                    array_merge($parentReaderConfigs, [$readerConfig])
                );
            }
        }

        $computedReaderConfig->id = $readerConfigPk;
        $computedReaderConfig->rootId = $rootReaderConfig->id;

        return $computedReaderConfig;
    }

    /**
     * Get the reader manager.
     *
     * @param string $name
     *
     * @throws \Exception
     *
     * @return ReaderManagerInterface|null
     */
    public function getReaderManagerByName(string $name): ?ReaderManagerInterface
    {
        $config = System::getContainer()->getParameter('huh.reader');

        if (!isset($config['reader']['managers'])) {
            return null;
        }

        $managers = $config['reader']['managers'];

        foreach ($managers as $manager) {
            if ($manager['name'] == $name) {
                if (!System::getContainer()->has($manager['id'])) {
                    return null;
                }

                /** @var ReaderManagerInterface $manager */
                $manager = System::getContainer()->get($manager['id']);
                $interfaces = class_implements($manager);

                if (!\is_array($interfaces) || !\in_array(ReaderManagerInterface::class, $interfaces)) {
                    throw new \Exception(sprintf('Reader manager service %s must implement %s', $manager['id'], ReaderManagerInterface::class));
                }

                return $manager;
            }
        }

        return null;
    }
}
