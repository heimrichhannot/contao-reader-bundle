<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ReaderBundle\Tests\ConfigElementType;

use HeimrichHannot\ReaderBundle\ConfigElementType\ListConfigElementType;
use HeimrichHannot\ReaderBundle\Item\DefaultItem;
use HeimrichHannot\ReaderBundle\Model\ReaderConfigElementModel;
use HeimrichHannot\ReaderBundle\Tests\TestCaseEnvironmentReaderManager;
use Symfony\Component\Filesystem\Filesystem;

class ListConfigElementTypeTest extends TestCaseEnvironmentReaderManager
{
    /**
     * {@inheritdoc}
     */
    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();

        $fs = new Filesystem();

        if ($fs->exists(TL_ROOT.'/Fixtures')) {
            $fs->remove(TL_ROOT.'/Fixtures');
        }
    }

    /**
     * initial setup.
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->prepareReaderConfig();

        // create this directory to be able to create new ModuleList object
        if (!is_dir(TL_ROOT.'/Fixtures/languages/en')) {
            mkdir(TL_ROOT.'/Fixtures/languages/en', 0777, true);
        }
    }

    public function testAddToItemData()
    {
        $listConfig = new ListConfigElementType($this->framework);
        $defaultItem = new DefaultItem($this->manager, ['id' => 1, 'test' => true, 'string' => 'string', 'timestamp' => 1557989217, 'list' => 'list']);
        $readerConfigElementModel = new ReaderConfigElementModel();
        $readerConfigElementModel->setRow([
            'listModule' => 1,
            'initialFilter' => null,
        ]);
        $this->assertNull($listConfig->addToItemData($defaultItem, $readerConfigElementModel));

        $readerConfigElementModel->setRow([
            'listModule' => 2,
            'initialFilter' => null,
        ]);
        $this->assertNull($listConfig->addToItemData($defaultItem, $readerConfigElementModel));

        $readerConfigElementModel->setRow([
            'listModule' => 2,
            'initialFilter' => serialize([['filterElement' => true, 'selector' => true]]),
        ]);
        $listConfig->addToItemData($defaultItem, $readerConfigElementModel);
        $this->assertSame(['' => 'generate'], $defaultItem->getFormattedValue('list'));
    }
}
