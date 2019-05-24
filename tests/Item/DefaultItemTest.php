<?php

/*
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ReaderBundle\Tests\Item;

use HeimrichHannot\ReaderBundle\Item\DefaultItem;
use HeimrichHannot\ReaderBundle\Tests\TestCaseEnvironmentReaderManager;

class DefaultItemTest extends TestCaseEnvironmentReaderManager
{
    public function setUp(): void
    {
        parent::setUp();
    }

    public function testSetRawGetRaw()
    {
        $this->prepareReaderConfig();

        $testData = ['id' => 1, 'test' => true, 'string' => 'string', 'timestamp' => 1557989217];

        $defaultItem = new DefaultItem($this->manager, $testData);

        $this->assertSame($testData['id'], $defaultItem->id);
        $this->assertTrue($defaultItem->getRawValue('test'));

        $defaultItem->setRawValue('test', false);
        $this->assertFalse($defaultItem->getRawValue('test'));
    }

    public function testParse()
    {
        $this->prepareReaderConfig(['itemTemplate' => 'my_item_template']);
        $testData = ['id' => 1, 'test' => true, 'string' => 'string', 'timestamp' => 1557989217, 'images' => null];

        $defaultItem = new DefaultItem($this->manager, $testData);
        $parseResult = $defaultItem->parse();
        $this->assertSame('twigResult', $parseResult);
    }

    public function testSetAndGetFormattedValue()
    {
        $this->prepareReaderConfig();

        $testData = ['id' => 1, 'test' => true, 'string' => 'string', 'timestamp' => 1557989217];
        $defaultItem = new DefaultItem($this->manager, $testData);
        $defaultItem->dc = null;
        $defaultItem->setFormattedValue('firstname', 'Lothar');
        $this->assertSame('Lothar', $defaultItem->getFormattedValue('firstname'));
    }
}
