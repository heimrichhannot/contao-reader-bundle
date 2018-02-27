<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ReaderBundle\Registry;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use HeimrichHannot\ReaderBundle\Model\ReaderConfigElementModel;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;

class ReaderConfigElementRegistry
{
    /** @var ContaoFrameworkInterface */
    protected $framework;

    /** @var ReaderConfigRegistry */
    protected $readerConfigRegistry;

    /** @var ModelUtil */
    protected $modelUtil;

    public function __construct(ContaoFrameworkInterface $framework, ReaderConfigRegistry $readerConfigRegistry, ModelUtil $modelUtil)
    {
        $this->framework = $framework;
        $this->readerConfigRegistry = $readerConfigRegistry;
        $this->modelUtil = $modelUtil;
    }

    /**
     * Adapter function for the model's findBy method.
     *
     * @param mixed $column
     * @param mixed $value
     * @param array $options
     *
     * @return \Contao\Model\Collection|ReaderConfigElementModel|null
     */
    public function findBy($column, $value, array $options = [])
    {
        return $this->modelUtil->findModelInstancesBy(
            'tl_reader_config_element', $column, $value, $options);
    }

    /**
     * Adapter function for the model's findOneBy method.
     *
     * @param mixed $column
     * @param mixed $value
     * @param array $options
     *
     * @return \Contao\Model\Collection|ReaderConfigElementModel|null
     */
    public function findOneBy($column, $value, array $options = [])
    {
        return $this->modelUtil->findModelInstancesBy(
            'tl_reader_config_element', $column, $value, $options);
    }

    /**
     * Adapter function for the model's findByPk method.
     *
     * @param mixed $column
     * @param mixed $value
     * @param array $options
     *
     * @return \Contao\Model\Collection|ReaderConfigElementModel|null
     */
    public function findByPk($pk, array $options = [])
    {
        return $this->modelUtil->findModelInstanceByPk(
            'tl_reader_config_element', $pk, $options);
    }

    /**
     * Returns the filter associated to a reader config element.
     *
     * @param int $readerConfigPk
     *
     * @return array|null
     */
    public function getFilterByPk(int $readerConfigElementPk)
    {
        if (null === ($readerConfigElement = $this->findByPk($readerConfigElementPk))) {
            return null;
        }

        return $this->readerConfigRegistry->getFilterByPk($readerConfigElement->pid);
    }
}
