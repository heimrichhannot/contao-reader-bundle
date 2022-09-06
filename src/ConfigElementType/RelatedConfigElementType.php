<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ReaderBundle\ConfigElementType;

use Contao\StringUtil;
use HeimrichHannot\ConfigElementTypeBundle\ConfigElementType\ConfigElementData;
use HeimrichHannot\ConfigElementTypeBundle\ConfigElementType\ConfigElementResult;
use HeimrichHannot\ConfigElementTypeBundle\ConfigElementType\ConfigElementTypeInterface;
use HeimrichHannot\ReaderBundle\EventListener\RelatedListListener;
use HeimrichHannot\ReaderBundle\Generator\RelatedListGenerator;
use HeimrichHannot\ReaderBundle\Generator\RelatedListGeneratorConfig;
use HeimrichHannot\ReaderBundle\Model\ReaderConfigModel;

class RelatedConfigElementType implements ConfigElementTypeInterface
{
    private RelatedListGenerator $relatedListGenerator;

    public function __construct(RelatedListGenerator $relatedListGenerator)
    {
        $this->relatedListGenerator = $relatedListGenerator;
    }

    /**
     * Return the config element type alias.
     */
    public static function getType(): string
    {
        return 'related';
    }

    /**
     * Return the config element type palette.
     */
    public function getPalette(string $prependPalette, string $appendPalette): string
    {
        return $prependPalette.'{config_legend},relatedExplanation,relatedListModule,relatedCriteriaExplanation,relatedCriteria;'.$appendPalette;
    }

    public function applyConfiguration(ConfigElementData $configElementData): ConfigElementResult
    {
        $criteria = StringUtil::deserialize($configElementData->getConfiguration()->relatedCriteria, true);

        if (empty($criteria) || !($readerConfigModel = ReaderConfigModel::findByPk($configElementData->getConfiguration()->pid))) {
            return new ConfigElementResult(ConfigElementResult::TYPE_NONE, null);
        }

        $listGeneratorConfig = new RelatedListGeneratorConfig(
            $readerConfigModel->dataContainer,
            $configElementData->getItemData()['id'],
            $configElementData->getConfiguration()->relatedListModule
        );

        if (\in_array(RelatedListListener::CRITERIUM_TAGS, $criteria)) {
            $listGeneratorConfig->setTagsField($configElementData->getConfiguration()->tagsField);
        }

        if (\in_array(RelatedListListener::CRITERIUM_CATEGORIES, $criteria)) {
            $listGeneratorConfig->setTagsField($configElementData->getConfiguration()->categoriesField);
        }

        return new ConfigElementResult(
            ConfigElementResult::TYPE_FORMATTED_VALUE,
            $this->relatedListGenerator->generate($listGeneratorConfig)
        );
    }
}
