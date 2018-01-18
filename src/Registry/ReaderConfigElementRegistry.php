<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0+
 */

namespace HeimrichHannot\ReaderBundle\Registry;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\System;
use HeimrichHannot\ReaderBundle\Model\ReaderConfigElementModel;

class ReaderConfigElementRegistry
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
     * @return \Contao\Model\Collection|ReaderConfigElementModel|null
     */
    public function findBy($column, $value, array $options = [])
    {
        return System::getContainer()->get('huh.utils.model')->findModelInstancesBy(
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
        return System::getContainer()->get('huh.utils.model')->findModelInstancesBy(
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
        return System::getContainer()->get('huh.utils.model')->findModelInstanceByPk(
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

        return System::getContainer()->get('huh.reader.reader-config-registry')->getFilterByPk($readerConfigElement->pid);
    }
}
