<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ReaderBundle\Tests\ConfigElementType;

use Contao\System;
use HeimrichHannot\ReaderBundle\ConfigElementType\DeleteConfigElementType;
use HeimrichHannot\ReaderBundle\Item\DefaultItem;
use HeimrichHannot\ReaderBundle\Model\ReaderConfigElementModel;
use HeimrichHannot\ReaderBundle\Tests\TestCaseEnvironmentReaderManager;
use HeimrichHannot\ReaderBundle\Tests\TestClass;
use Symfony\Component\Filesystem\Filesystem;

class DeleteConfigElementTypeTest extends TestCaseEnvironmentReaderManager
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

        $container = System::getContainer();
        $container->setParameter('huh.reader', ['reader' => ['delete_classes' => [['name' => 'name1', 'class' => DeleteConfigElementType::class], ['name' => 'name2']]]]);
        System::setContainer($container);
    }

    public function testGetDeleteClassByName()
    {
        $reflectionClass = new \ReflectionClass(DeleteConfigElementType::class);
        $testMethod = $reflectionClass->getMethod('getDeleteClassByName');
        $testMethod->setAccessible(true);

        $deleteConfig = new DeleteConfigElementType($this->framework);
        $result = $testMethod->invokeArgs($deleteConfig, ['name1']);
        $this->assertSame(DeleteConfigElementType::class, $result);

        $result = $testMethod->invokeArgs($deleteConfig, ['name3']);
        $this->assertNull($result);

        $container = System::getContainer();
        $container->setParameter('huh.reader', ['reader' => ['delete_classe' => [['name' => 'name1', 'class' => DeleteConfigElementType::class], ['name' => 'name2']]]]);
        System::setContainer($container);
        $result = $testMethod->invokeArgs($deleteConfig, ['name3']);
        $this->assertNull($result);
    }

    public function testAddToItemData()
    {
        $deleteConfigElementType = new DeleteConfigElementType($this->framework);
        $defaultItem = new DefaultItem($this->manager, ['id' => 1, 'test' => true, 'string' => 'string', 'timestamp' => 1557989217, 'list' => 'list']);
        $readerConfigElementModel = new ReaderConfigElementModel();
        $readerConfigElementModel->setRow([
            'addRedirectParam' => false,
            'name' => 'name',
        ]);
        $this->assertNull($deleteConfigElementType->addToItemData($defaultItem, $readerConfigElementModel));

        $readerConfigElementModel = new ReaderConfigElementModel();
        $readerConfigElementModel->setRow([
            'addRedirectParam' => true,
            'name' => 'name',
            'redirectParams' => 'a:2:{i:0;a:4:{s:13:"parameterType";s:13:"default_value";s:4:"name";s:6:"action";s:12:"defaultValue";s:6:"delete";s:5:"field";s:0:"";}i:1;a:4:{s:13:"parameterType";s:11:"field_value";s:4:"name";s:2:"id";s:12:"defaultValue";s:0:"";s:5:"field";s:2:"id";}}',
            'isDeleted' => false,
        ]);
        $this->assertNull($deleteConfigElementType->addToItemData($defaultItem, $readerConfigElementModel));

        $readerConfigElementModel = new ReaderConfigElementModel();
        $readerConfigElementModel->setRow([
            'addRedirectParam' => true,
            'name' => 'name',
            'redirectParams' => 'a:1:{i:0;a:4:{s:13:"parameterType";s:11:"field_value";s:4:"name";s:2:"id";s:12:"defaultValue";s:0:"";s:5:"field";s:2:"id";}}',
            'isDeleted' => false,
        ]);
        $this->assertNull($deleteConfigElementType->addToItemData($defaultItem, $readerConfigElementModel));

        $readerConfigElementModel = new ReaderConfigElementModel();
        $readerConfigElementModel->setRow([
            'addRedirectParam' => true,
            'name' => 'name',
            'deleteClass' => TestClass::class,
            'redirectParams' => 'a:1:{i:0;a:4:{s:13:"parameterType";s:10:"field_valu";s:4:"name";s:2:"id";s:12:"defaultValue";s:0:"";s:5:"field";s:2:"id";}}',
            'isDeleted' => false,
        ]);
        $this->assertNull($deleteConfigElementType->addToItemData($defaultItem, $readerConfigElementModel));

        $container = System::getContainer();
        $container->setParameter('huh.reader', ['reader' => ['delete_classes' => [['name' => 'testClass', 'class' => TestClass::class], ['name' => 'name2']]]]);
        System::setContainer($container);
        $readerConfigElementModel = new ReaderConfigElementModel();
        $readerConfigElementModel->setRow([
            'addRedirectParam' => true,
            'name' => 'name',
            'deleteClass' => 'testClass',
            'redirectParams' => 'a:1:{i:0;a:4:{s:13:"parameterType";s:10:"field_valu";s:4:"name";s:2:"id";s:12:"defaultValue";s:0:"";s:5:"field";s:2:"id";}}',
            'isDeleted' => false,
        ]);
        $this->assertFalse($readerConfigElementModel->isDeleted);
        $this->assertNull($deleteConfigElementType->addToItemData($defaultItem, $readerConfigElementModel));
        $this->assertTrue($readerConfigElementModel->isDeleted);
    }
}
