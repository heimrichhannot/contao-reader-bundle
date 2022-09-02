<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ReaderBundle\Generator;

use Contao\Controller;
use Contao\Database;
use HeimrichHannot\ReaderBundle\DataContainer\ReaderConfigElementContainer;
use HeimrichHannot\UtilsBundle\Util\Utils;

class RelatedListGenerator
{
    private Utils $utils;

    public function __construct(Utils $utils)
    {
        $this->utils = $utils;
    }

    public function generate(RelatedListGeneratorConfig $config): string
    {
        $GLOBALS['HUH_LIST_RELATED'] = [];

        if ($config->getFilterCfTags()) {
            $this->applyTagsFilter($config->getDataContainer(), $config->getTagsField(), $config->getEntityId());
        }

//        $this->applyCategoriesFilter($configElement, $item);

        $result = Controller::getFrontendModule($config->getListConfigId());

        unset($GLOBALS['HUH_LIST_RELATED']);

        return $result;
    }

    protected function applyTagsFilter(string $table, string $tagsField, int $entityId)
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
}
