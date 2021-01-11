<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ReaderBundle\Util;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\DataContainer;
use HeimrichHannot\ReaderBundle\Registry\ReaderConfigRegistry;
use HeimrichHannot\UtilsBundle\Choice\FieldChoice;
use HeimrichHannot\UtilsBundle\Dca\DcaUtil;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;

class ReaderConfigUtil
{
    /** @var ContaoFrameworkInterface */
    protected $framework;

    /** @var ReaderConfigRegistry */
    protected $readerConfigRegistry;

    /** @var FieldChoice */
    protected $fieldChoice;

    /** @var ModelUtil */
    protected $modelUtil;

    /** @var DcaUtil */
    protected $dcaUtil;

    public function __construct(
        ContaoFrameworkInterface $framework,
        ReaderConfigRegistry $readerConfigRegistry,
        FieldChoice $fieldChoice,
        ModelUtil $modelUtil,
        DcaUtil $dcaUtil
    ) {
        $this->framework = $framework;
        $this->readerConfigRegistry = $readerConfigRegistry;
        $this->fieldChoice = $fieldChoice;
        $this->modelUtil = $modelUtil;
        $this->dcaUtil = $dcaUtil;
    }

    /**
     * @param int $id Reader config id
     *
     * @return array
     */
    public function getFields(int $id = 0)
    {
        if (0 === $id || null === ($readerConfig = $this->readerConfigRegistry->findByPk($id))) {
            return [];
        }

        $readerConfig = $this->modelUtil->findRootParentRecursively(
            'parentReaderConfig', 'tl_reader_config', $readerConfig
        );

        return $this->fieldChoice->getCachedChoices(
            [
                'dataContainer' => $this->dcaUtil->getOverridableProperty('dataContainer', [
                    $readerConfig,
                ]),
            ]
        );
    }

    public function getTextFields(DataContainer $dc)
    {
        if (!$dc->id || null === ($readerConfig = $this->readerConfigRegistry->findByPk($dc->id))) {
            return [];
        }

        $readerConfig = $this->modelUtil->findRootParentRecursively(
            'parentReaderConfig', 'tl_reader_config', $readerConfig
        );

        return $this->fieldChoice->getCachedChoices(
            [
                'dataContainer' => $readerConfig->dataContainer,
                'inputTypes' => ['text'],
            ]
        );
    }

    public function getCheckboxFields(DataContainer $dc)
    {
        if (!$dc->id || null === ($readerConfig = $this->readerConfigRegistry->findByPk($dc->id))) {
            return [];
        }

        $readerConfig = $this->modelUtil->findRootParentRecursively(
            'parentReaderConfig', 'tl_reader_config', $readerConfig
        );

        return $this->fieldChoice->getCachedChoices(
            [
                'dataContainer' => $readerConfig->dataContainer,
                'inputTypes' => ['checkbox'],
            ]
        );
    }
}
