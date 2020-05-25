<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ReaderBundle\DataContainer;

use Contao\StringUtil;
use HeimrichHannot\ReaderBundle\ConfigElementType\RelatedConfigElementType;
use HeimrichHannot\ReaderBundle\Model\ReaderConfigElementModel;
use HeimrichHannot\ReaderBundle\Registry\ReaderConfigElementRegistry;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ReaderConfigElementContainer
{
    const RELATED_CRITERIUM_TAGS = 'tags';
    const RELATED_CRITERIUM_CATEGORIES = 'categories';

    /**
     * @var ReaderConfigElementRegistry
     */
    private $configElementTypeRegistry;
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * ReaderConfigElementContainer constructor.
     */
    public function __construct(ReaderConfigElementRegistry $configElementTypeRegistry, ContainerInterface $container)
    {
        $this->configElementTypeRegistry = $configElementTypeRegistry;
        $this->container = $container;
    }

    public function getRelatedCriteriaAsOptions()
    {
        $options = [];

        if (class_exists('\Codefog\TagsBundle\CodefogTagsBundle')) {
            $options[] = static::RELATED_CRITERIUM_TAGS;
        }

        if (class_exists('\HeimrichHannot\CategoriesBundle\CategoriesBundle')) {
            $options[] = static::RELATED_CRITERIUM_CATEGORIES;
        }

        return $options;
    }

    /**
     * Update dca palettes with config element types palettes.
     *
     * @param $dc
     */
    public function onLoadCallback($dc)
    {
        if (null === ($readerConfigElement = $this->container->get('huh.utils.model')->findModelInstanceByPk('tl_reader_config_element', $dc->id))) {
            return;
        }

        $configElementTypes = $this->configElementTypeRegistry->getReaderConfigElementTypes();

        if (empty($configElementTypes)) {
            return;
        }

        foreach ($configElementTypes as $listConfigElementType) {
            $palette = '{title_type_legend},title,type,templateVariable;'.$listConfigElementType->getPalette();
            $GLOBALS['TL_DCA'][ReaderConfigElementModel::getTable()]['palettes'][$listConfigElementType::getType()] = $palette;
        }

        // related
        if ($readerConfigElement->type === RelatedConfigElementType::getType()) {
            $criteria = StringUtil::deserialize($readerConfigElement->relatedCriteria, true);

            $fields = [];

            if (\in_array(static::RELATED_CRITERIUM_TAGS, $criteria)) {
                $fields[] = 'tagsField';
            }

            if (\in_array(static::RELATED_CRITERIUM_CATEGORIES, $criteria)) {
                $fields[] = 'categoriesField';
            }

            $GLOBALS['TL_DCA']['tl_reader_config_element']['palettes'][RelatedConfigElementType::getType()] = str_replace(
                'relatedCriteria;', 'relatedCriteria,'.implode(',', $fields).';',
                $GLOBALS['TL_DCA']['tl_reader_config_element']['palettes'][RelatedConfigElementType::getType()]
            );
        }
    }

    /**
     * Return a list of content element types for dca options callback.
     *
     * @return array
     */
    public function getConfigElementTypes()
    {
        $types = array_keys($this->configElementTypeRegistry->getReaderConfigElementTypes());

        /**
         * @todo remove in next major version
         */
        $readerConfig = $this->container->getParameter('huh.reader');
        $configElementTypes = $readerConfig['reader']['config_element_types'];

        if (empty($configElementTypes)) {
            return $types;
        }

        foreach ($configElementTypes as $configElementType) {
            if (\in_array($configElementType['name'], $types)) {
                continue;
            }
            $types[] = $configElementType['name'];
        }

        return $types;
    }

    /**
     * @param $rows
     *
     * @return string
     */
    public function listChildren($rows)
    {
        $reference = $GLOBALS['TL_DCA']['tl_reader_config_element']['fields']['type']['reference'];

        return '<div class="tl_content_left">'.($rows['title'] ?: $rows['id']).' <span style="color:#b3b3b3; padding-left:3px">['
            .$reference[$rows['type']].'] ('
            .\Date::parse(\Contao\Config::get('datimFormat'), trim($rows['dateAdded'])).')</span></div>';
    }
}
