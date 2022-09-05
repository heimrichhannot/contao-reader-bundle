<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ReaderBundle\Generator;

use Codefog\TagsBundle\CodefogTagsBundle;
use Contao\Controller;
use Contao\Database;
use HeimrichHannot\CategoriesBundle\CategoriesBundle;
use HeimrichHannot\CategoriesBundle\Manager\CategoryManager;
use HeimrichHannot\ReaderBundle\DataContainer\ReaderConfigElementContainer;
use HeimrichHannot\UtilsBundle\Util\Utils;
use Psr\Container\ContainerInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class RelatedListGenerator implements ServiceSubscriberInterface
{
    private Utils              $utils;
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container, Utils $utils)
    {
        $this->utils = $utils;
        $this->container = $container;
    }

    /**
     * Render a related list.
     *
     * Options:
     * - column: the layout column. Default 'main'
     */
    public function generate(RelatedListGeneratorConfig $config, array $options = []): string
    {
        $options = array_merge([
            'column' => 'main',
        ], $options);

        $GLOBALS['HUH_LIST_RELATED'] = [];

        if (class_exists(CodefogTagsBundle::class) && $config->getFilterCfTags()) {
            $this->applyTagsFilter($config->getDataContainer(), $config->getTagsField(), $config->getEntityId());
        }

        if (class_exists(CategoriesBundle::class) && $config->getFilterCategories()) {
            $this->applyCategoriesFilter($config->getDataContainer(), $config->getCategoriesField(), $config->getEntityId());
        }

        $result = Controller::getFrontendModule($config->getListConfigId(), $options['column']);
        unset($GLOBALS['HUH_LIST_RELATED']);

        return $result;
    }

    public static function getSubscribedServices()
    {
        return [
            '?'.'HeimrichHannot\CategoriesBundle\Manager\CategoryManager',
        ];
    }

    protected function applyTagsFilter(string $table, string $tagsField, int $entityId): void
    {
        $dca = $GLOBALS['TL_DCA'][$table]['fields'][$tagsField];

        $source = $dca['eval']['tagsManager'];

        $nonTlTable = $this->utils->string()->removeLeadingString('tl_', $table);

        $cfgTable = $dca['relation']['relationTable'] ?? 'tl_cfg_tag_'.$nonTlTable;

        $tagRecords = Database::getInstance()->prepare(
            "SELECT t.id FROM tl_cfg_tag t INNER JOIN $cfgTable t2 ON t.id = t2.cfg_tag_id "
            ."WHERE t2.{$nonTlTable}_id=? AND t.source=?")->execute(
            $entityId,
            $source
        );

        if ($tagRecords->numRows > 0) {
            $relatedIds = Database::getInstance()->prepare(
                "SELECT t.* FROM $cfgTable t WHERE t.cfg_tag_id IN (".implode(',', $tagRecords->fetchEach('id')).')'
            )->execute();

            if ($relatedIds->numRows > 0) {
                $itemIds = $relatedIds->fetchEach($nonTlTable.'_id');

                // exclude the item itself
                $itemIds = array_diff($itemIds, [$entityId]);

                $GLOBALS['HUH_LIST_RELATED'][ReaderConfigElementContainer::RELATED_CRITERIUM_TAGS] = [
                    'itemIds' => $itemIds,
                ];
            }
        }
    }

    protected function applyCategoriesFilter(string $table, string $categoriesField, int $entityId): void
    {
        if (!class_exists(CategoryManager::class) && !$this->container->has(CategoryManager::class)) {
            return;
        }

        $categories = $this->container->get(CategoryManager::class)->findByEntityAndCategoryFieldAndTable(
            $entityId, $categoriesField, $table
        );

        if (!$categories) {
            return;
        }

        $relatedIds = Database::getInstance()->prepare(
            'SELECT t.* FROM tl_category_association t WHERE t.category IN ('.implode(',', $categories->fetchEach('id')).')'
        )->execute();

        if ($relatedIds->numRows > 0) {
            $itemIds = $relatedIds->fetchEach('entity');

            // exclude the item itself
            $itemIds = array_diff($itemIds, [$entityId]);

            $GLOBALS['HUH_LIST_RELATED'][ReaderConfigElementContainer::RELATED_CRITERIUM_CATEGORIES] = [
                'itemIds' => $itemIds,
            ];
        }
    }
}
