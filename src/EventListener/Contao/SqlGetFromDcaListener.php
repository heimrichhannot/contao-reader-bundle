<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ReaderBundle\EventListener\Contao;

use Contao\Database;

class SqlGetFromDcaListener
{
    public function __invoke($sqlDcaData)
    {
        $this->migrateParentReaderConfigToPid($sqlDcaData);

        return $sqlDcaData;
    }

    protected function migrateParentReaderConfigToPid(array &$sqlDcaData)
    {
        $db = Database::getInstance();

        // migration already took place
        if (!$db->tableExists('tl_reader_config', null, true)) {
            return;
        }

        if ($db->fieldExists('pid', 'tl_reader_config', true)) {
            return;
        }

        $db->execute('ALTER TABLE tl_reader_config ADD pid INT UNSIGNED DEFAULT 0 NOT NULL');
        $db->execute('ALTER TABLE tl_reader_config ADD sorting INT UNSIGNED DEFAULT 0 NOT NULL');

        // remove fields from sql data in order to avoid duplicate column errors
        unset($sqlDcaData['tl_reader_config']['TABLE_FIELDS']['sorting'], $sqlDcaData['tl_reader_config']['TABLE_FIELDS']['pid']);

        // migrate parentReaderConfig to pid
        $db->execute('UPDATE tl_reader_config SET pid=parentReaderConfig');

        // create sorting based on alphabetical order
        $readerConfigs = $db->execute('SELECT * FROM tl_reader_config ORDER BY title ASC');

        $sorting = 128;

        if ($readerConfigs->numRows > 0) {
            while ($readerConfigs->next()) {
                $db->prepare('UPDATE tl_reader_config SET sorting=? WHERE id=?')->execute(
                    $sorting,
                    $readerConfigs->id
                );

                $sorting += 64;
            }
        }
    }
}
