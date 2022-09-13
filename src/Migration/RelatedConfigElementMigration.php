<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ReaderBundle\Migration;

use Contao\CoreBundle\Migration\MigrationInterface;
use Contao\CoreBundle\Migration\MigrationResult;
use HeimrichHannot\ReaderBundle\ConfigElementType\RelatedConfigElementType;
use HeimrichHannot\ReaderBundle\Model\ReaderConfigElementModel;

class RelatedConfigElementMigration implements MigrationInterface
{
    public function getName(): string
    {
        return 'Related config element type migration';
    }

    public function shouldRun(): bool
    {
        $elements = ReaderConfigElementModel::findBy(
            [ReaderConfigElementModel::getTable().'.type=?'],
            [RelatedConfigElementType::getType()]
        );

        if ($elements) {
            while ($elements->next()) {
                if (!$elements->templateVariable) {
                    return true;
                }
            }
        }

        return false;
    }

    public function run(): MigrationResult
    {
        $elements = ReaderConfigElementModel::findBy(
            [ReaderConfigElementModel::getTable().'.type=?'],
            [RelatedConfigElementType::getType()]
        );

        if ($elements) {
            foreach ($elements as $element) {
                if (!$element->templateVariable) {
                    $element->templateVariable = 'relatedItems';
                    $element->save();
                }
            }
        }

        return new MigrationResult(true, 'Migrated related config element types');
    }
}
