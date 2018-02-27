<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ReaderBundle\Util;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\DataContainer;
use HeimrichHannot\ReaderBundle\Registry\ReaderConfigElementRegistry;
use HeimrichHannot\ReaderBundle\Registry\ReaderConfigRegistry;
use HeimrichHannot\UtilsBundle\Choice\FieldChoice;

class ReaderConfigElementUtil
{
    /** @var ContaoFrameworkInterface */
    protected $framework;

    /** @var ReaderConfigRegistry */
    protected $readerConfigRegistry;

    /** @var ReaderConfigElementRegistry */
    protected $readerConfigElementRegistry;

    /** @var FieldChoice */
    protected $fieldChoice;

    public function __construct(ContaoFrameworkInterface $framework, ReaderConfigRegistry $readerConfigRegistry,
        ReaderConfigElementRegistry $readerConfigElementRegistry, FieldChoice $fieldChoice)
    {
        $this->framework = $framework;
        $this->readerConfigRegistry = $readerConfigRegistry;
        $this->readerConfigElementRegistry = $readerConfigElementRegistry;
        $this->fieldChoice = $fieldChoice;
    }

    public function getFields(DataContainer $dc)
    {
        if (!$dc->id || null === ($readerConfigElement = $this->readerConfigElementRegistry->findByPk($dc->id))) {
            return [];
        }

        if (!$dc->id
            || null === ($readerConfig = $this->readerConfigRegistry->findByPk($readerConfigElement->pid))
        ) {
            return [];
        }

        return $this->fieldChoice->getCachedChoices(
            [
                'dataContainer' => $readerConfig->dataContainer,
            ]
        );
    }

    public function getCheckboxFields(DataContainer $dc)
    {
        if (!$dc->id || null === ($readerConfigElement = $this->readerConfigElementRegistry->findByPk($dc->id))) {
            return [];
        }

        if (!$dc->id
            || null === ($readerConfig = $this->readerConfigRegistry->findByPk($readerConfigElement->pid))
        ) {
            return [];
        }

        return $this->fieldChoice->getCachedChoices(
            [
                'dataContainer' => $readerConfig->dataContainer,
                'inputType' => ['checkbox'],
            ]
        );
    }
}
