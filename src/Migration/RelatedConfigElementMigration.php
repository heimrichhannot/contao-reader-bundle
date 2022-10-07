<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ReaderBundle\Migration;

use Contao\CoreBundle\Migration\MigrationInterface;
use Contao\CoreBundle\Migration\MigrationResult;
use Doctrine\DBAL\Connection;
use HeimrichHannot\ReaderBundle\ConfigElementType\RelatedConfigElementType;
use HeimrichHannot\ReaderBundle\Model\ReaderConfigElementModel;

class RelatedConfigElementMigration implements MigrationInterface
{
    /**
     * @var Connection
     */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function getName(): string
    {
        return 'Related config element type migration';
    }

    public function shouldRun(): bool
    {
        if (!\in_array(ReaderConfigElementModel::getTable(), $this->connection->getSchemaManager()->listTableNames())) {
            return false;
        }

        $result = $this->connection->executeQuery(
            'SELECT COUNT(id) AS count FROM '.ReaderConfigElementModel::getTable().' '
            ."WHERE type=? AND templateVariable=''",
            [RelatedConfigElementType::getType()]
        );

        if ($result->rowCount() > 0 && ($result->fetchAssociative()['count'] ?? 0) > 0) {
            return true;
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
