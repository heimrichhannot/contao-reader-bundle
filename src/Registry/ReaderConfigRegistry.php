<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ReaderBundle\Registry;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\System;
use HeimrichHannot\ReaderBundle\Model\ReaderConfigModel;

class ReaderConfigRegistry
{
    /**
     * @var ContaoFrameworkInterface
     */
    protected $framework;

    /**
     * Constructor.
     *
     * @param ContaoFrameworkInterface $framework
     */
    public function __construct(ContaoFrameworkInterface $framework)
    {
        $this->framework = $framework;
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
        return System::getContainer()->get('huh.utils.model')->findModelInstancesBy(
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
        return System::getContainer()->get('huh.utils.model')->findModelInstancesBy(
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
        return System::getContainer()->get('huh.utils.model')->findModelInstanceByPk(
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

        if (!$readerConfig->filter || null === ($filterConfig = System::getContainer()->get('huh.filter.registry')->findById($readerConfig->filter))) {
            return null;
        }

        return $filterConfig->getFilter();
    }

    public function getOverridableProperty($property, int $readerConfigPk)
    {
        if (null === ($readerConfig = $this->findByPk($readerConfigPk))) {
            return null;
        }

        $parentReaderConfigs = System::getContainer()->get('huh.utils.model')->findParentsRecursively(
            'parentReaderConfig',
            'tl_reader_config',
            $readerConfig
        );

        if (empty($parentReaderConfigs)) {
            return null;
        }

        return System::getContainer()->get('huh.utils.dca')->getOverridableProperty(
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

        if (!$readerConfig->parentReaderConfig) {
            return $readerConfig;
        }

        $computedReaderConfig = new ReaderConfigModel();

        $parentReaderConfigs = System::getContainer()->get('huh.utils.model')->findParentsRecursively(
            'parentReaderConfig', 'tl_reader_config', $readerConfig
        );

        $rootReaderConfig = System::getContainer()->get('huh.utils.model')->findRootParentRecursively(
            'parentReaderConfig', 'tl_reader_config', $readerConfig
        );

        foreach ($GLOBALS['TL_DCA']['tl_reader_config']['fields'] as $field => $data) {
            if ($data['eval']['notOverridable']) {
                $computedReaderConfig->{$field} = $rootReaderConfig->{$field};
            } else {
                $computedReaderConfig->{$field} = System::getContainer()->get('huh.utils.dca')->getOverridableProperty(
                    $field,
                    array_merge($parentReaderConfigs, [$readerConfig])
                );
            }
        }

        return $computedReaderConfig;
    }
}
