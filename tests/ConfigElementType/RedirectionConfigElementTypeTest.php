<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ReaderBundle\Tests\ConfigElementType;

use Contao\System;
use HeimrichHannot\EntityFilterBundle\Backend\EntityFilter;
use HeimrichHannot\ReaderBundle\ConfigElementType\RedirectionConfigElementType;
use HeimrichHannot\ReaderBundle\Item\DefaultItem;
use HeimrichHannot\ReaderBundle\Model\ReaderConfigElementModel;
use HeimrichHannot\ReaderBundle\Model\ReaderConfigModel;
use HeimrichHannot\ReaderBundle\Registry\ReaderConfigRegistry;
use HeimrichHannot\ReaderBundle\Tests\TestCaseEnvironmentReaderManager;
use HeimrichHannot\RequestBundle\Component\HttpFoundation\Request;
use HeimrichHannot\UtilsBundle\Url\UrlUtil;
use Symfony\Component\Filesystem\Filesystem;

class RedirectionConfigElementTypeTest extends TestCaseEnvironmentReaderManager
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

        if (!\function_exists('ampersand')) {
            include_once __DIR__.'/../../vendor/contao/core-bundle/src/Resources/contao/helper/functions.php';
        }
    }

    public function testCheckPermission()
    {
        $redirectionConfig = new RedirectionConfigElementType($this->framework);

        $readerConfigElementModel = new ReaderConfigElementModel();
        $readerConfigElementModel->setRow([
            'addRedirectConditions' => false,
        ]);
        $defaultItem = new DefaultItem($this->manager, ['id' => 12]);
        $this->assertTrue($redirectionConfig->checkPermission($readerConfigElementModel, $defaultItem));

        $readerConfigElementModel = new ReaderConfigElementModel();
        $readerConfigElementModel->setRow(['addRedirectConditions' => true, 'redirectConditions' => serialize([])]);
        $this->assertFalse($redirectionConfig->checkPermission($readerConfigElementModel, $defaultItem));

        $readerConfigRegistry = $this->createMock(ReaderConfigRegistry::class);
        $readerConfigRegistry->method('findByPk')->willReturn(null);
        $container = System::getContainer();
        $container->set('huh.reader.reader-config-registry', $readerConfigRegistry);
        System::setContainer($container);
        $readerConfigElementModel = new ReaderConfigElementModel();
        $readerConfigElementModel->setRow(['addRedirectConditions' => true, 'redirectConditions' => 'a:1:{i:0;a:4:{s:13:"parameterType";s:11:"field_value";s:4:"name";s:2:"id";s:12:"defaultValue";s:0:"";s:5:"field";s:2:"id";}}', 'pid' => 12]);
        $this->assertFalse($redirectionConfig->checkPermission($readerConfigElementModel, $defaultItem));

        $readerConfig = $this->mockClassWithProperties(ReaderConfigModel::class, ['dataContainer' => 'tl_reader_config']);
        $readerConfigRegistry = $this->createMock(ReaderConfigRegistry::class);
        $readerConfigRegistry->method('findByPk')->willReturn($readerConfig);
        $backendEntityFilter = $this->createMock(EntityFilter::class);
        $backendEntityFilter->method('computeSqlCondition')->willReturn(['whereShit', [['id' => 1], ['id' => 2]]]);
        $container = System::getContainer();
        $container->set('huh.reader.reader-config-registry', $readerConfigRegistry);
        $container->set('huh.entity_filter.backend.entity_filter', $backendEntityFilter);
        System::setContainer($container);
        $readerConfigElementModel = new ReaderConfigElementModel();
        $readerConfigElementModel->setRow(['addRedirectConditions' => true, 'redirectConditions' => 'a:1:{i:0;a:4:{s:13:"parameterType";s:11:"field_value";s:4:"name";s:2:"id";s:12:"defaultValue";s:0:"";s:5:"field";s:2:"id";}}', 'pid' => 12]);
        $this->assertFalse($redirectionConfig->checkPermission($readerConfigElementModel, $defaultItem));
    }

    public function testAddToItemData()
    {
        $redirectionConfigElementType = new RedirectionConfigElementType($this->framework);
        $defaultItem = new DefaultItem($this->manager, ['id' => 1, 'test' => true, 'string' => 'string', 'timestamp' => 1557989217, 'list' => 'list']);
        $readerConfigElementModel = new ReaderConfigElementModel();
        $readerConfigElementModel->setRow(['name' => 'name', 'addRedirectConditions' => true, 'redirectConditions' => serialize([])]);
        $this->assertNull($redirectionConfigElementType->addToItemData($defaultItem, $readerConfigElementModel));

        $readerConfigElementModel = new ReaderConfigElementModel();
        $readerConfigElementModel->setRow(['name' => 'name', 'addRedirectConditions' => false, 'jumpTo' => 1]);
        $this->assertNull($redirectionConfigElementType->addToItemData($defaultItem, $readerConfigElementModel));

        $request = $this->createMock(Request::class);
        $request->method('hasGet')->willReturn(true);
        $request->method('getGet')->willReturn('foo');
        $container = System::getContainer();
        $container->set('huh.utils.url', new UrlUtil($this->framework));
        $container->set('huh.request', $request);
        System::setContainer($container);
        $readerConfigElementModel = new ReaderConfigElementModel();
        $readerConfigElementModel->setRow(['name' => 'name', 'addRedirectConditions' => false, 'jumpTo' => 2, 'addAutoItem' => false, 'addRedirectParam' => true, 'redirectParams' => 'a:1:{i:0;a:4:{s:13:"parameterType";s:11:"field_value";s:4:"name";s:2:"id";s:12:"defaultValue";s:0:"";s:5:"field";s:2:"id";}}']);
        $redirectionConfigElementType->addToItemData($defaultItem, $readerConfigElementModel);
        $this->assertSame('www.heimrich-hannot.de?id=1', $defaultItem->getFormattedValue('name'));

        $readerConfigElementModel = new ReaderConfigElementModel();
        $readerConfigElementModel->setRow([
            'name' => 'name',
            'addRedirectConditions' => false,
            'jumpTo' => 2,
            'addAutoItem' => true,
            'addRedirectParam' => true,
            'redirectParams' => 'a:2:{i:0;a:4:{s:13:"parameterType";s:13:"default_value";s:4:"name";s:6:"action";s:12:"defaultValue";s:6:"delete";s:5:"field";s:0:"";}i:1;a:4:{s:13:"parameterType";s:11:"field_value";s:4:"name";s:2:"id";s:12:"defaultValue";s:0:"";s:5:"field";s:2:"id";}}',
        ]);
        $redirectionConfigElementType->addToItemData($defaultItem, $readerConfigElementModel);
        $this->assertSame('www.heimrich-hannot.de?action=delete&id=1', $defaultItem->getFormattedValue('name'));
    }
}
