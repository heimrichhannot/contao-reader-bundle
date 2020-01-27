<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ReaderBundle\Tests\Manager;

use Contao\Config;
use Contao\CoreBundle\Exception\RedirectResponseException;
use Contao\DataContainer;
use Doctrine\DBAL\Driver\ResultStatement;
use Doctrine\DBAL\Query\Expression\ExpressionBuilder;
use HeimrichHannot\FilterBundle\Config\FilterConfig;
use HeimrichHannot\FilterBundle\Manager\FilterManager;
use HeimrichHannot\ReaderBundle\Backend\ReaderConfig;
use HeimrichHannot\ReaderBundle\Item\DefaultItem;
use HeimrichHannot\ReaderBundle\Manager\ReaderManager;
use HeimrichHannot\ReaderBundle\Model\ReaderConfigModel;
use HeimrichHannot\ReaderBundle\QueryBuilder\ReaderQueryBuilder;
use HeimrichHannot\ReaderBundle\Tests\TestCaseEnvironmentReaderManager;

class ReaderManagerTest extends TestCaseEnvironmentReaderManager
{
    /**
     * @var array
     */
    protected static $testArray = [];

    public function setUp(): void
    {
        parent::setUp();
    }

    public function testRetrieveItem()
    {
        // auto_item
        Config::set('useAutoItem', true);
        $this->request->setGet('auto_item', 'john-doe');
        $this->container->set('huh.request', $this->request);

        $this->prepareReaderConfig([
            'itemRetrievalMode' => 'blahbluh',
            'itemRetrievalAutoItemField' => 'alias',
            'hideUnpublishedItems' => true,
            'publishedField' => 'published',
        ]);
        $this->assertNull($this->manager->retrieveItem());

        $this->prepareReaderConfig([
            'itemRetrievalMode' => ReaderConfig::ITEM_RETRIEVAL_MODE_AUTO_ITEM,
            'itemRetrievalAutoItemField' => 'alias',
            'hideUnpublishedItems' => true,
            'publishedField' => 'published',
            'invertPublishedField' => false,
        ]);
        $readerManager = $this->manager;
        // create reflection class for reader manager class and set protected function addDcMultilingualSupport accessible
        $reflectionClassReaderManager = new \ReflectionClass(ReaderManager::class);
        // overwrite property readerQueryBuilder
        $resultStatement = $this->createMock(ResultStatement::class);
        $resultStatement->method('fetch')->willReturn(['published' => false]);
        $expressionBuilder = $this->createMock(ExpressionBuilder::class);
        $expressionBuilder->method('eq');
        $readerQueryBuilderMock = $this->createMock(ReaderQueryBuilder::class);
        $readerQueryBuilderMock->method('select');
        $readerQueryBuilderMock->method('setMaxResults')->willReturnSelf();
        $readerQueryBuilderMock->method('where');
        $readerQueryBuilderMock->method('expr')->willReturn($expressionBuilder);
        $readerQueryBuilderMock->method('setParameter');
        $readerQueryBuilderMock->method('from')->willReturnSelf();
        $readerQueryBuilderMock->method('execute')->willReturn($resultStatement);
        $reflectionReaderQueryBuilder = $reflectionClassReaderManager->getProperty('readerQueryBuilder');
        $reflectionReaderQueryBuilder->setAccessible(true);
        $reflectionReaderQueryBuilder->setValue($readerManager, $readerQueryBuilderMock);
        $this->assertNull($readerManager->retrieveItem());

        // field conditions
        $this->prepareReaderConfig([
            'itemRetrievalMode' => ReaderConfig::ITEM_RETRIEVAL_MODE_AUTO_ITEM,
            'itemRetrievalAutoItemField' => 'alias',
            'hideUnpublishedItems' => false,
            'publishedField' => 'published',
            'invertPublishedField' => false,
            'item' => 'default',
            'itemRetrievalFieldConditions' => serialize([
                [
                    'bracketLeft' => true,
                    'field' => 'firstname',
                    'operator' => 'equal',
                    'value' => 'John',
                    'bracketRight' => true,
                ],
            ]),
        ]);
        $readerManager = $this->manager;
        // create reflection class for reader manager class and set protected function addDcMultilingualSupport accessible
        $reflectionClassReaderManager = new \ReflectionClass(ReaderManager::class);
        // overwrite property readerQueryBuilder
        $resultStatement = $this->createMock(ResultStatement::class);
        $resultStatement->method('fetch')->willReturn($this->johnDoeModel->row());
        $expressionBuilder = $this->createMock(ExpressionBuilder::class);
        $expressionBuilder->method('eq');
        $readerQueryBuilderMock = $this->createMock(ReaderQueryBuilder::class);
        $readerQueryBuilderMock->method('select');
        $readerQueryBuilderMock->method('setMaxResults')->willReturnSelf();
        $readerQueryBuilderMock->method('where');
        $readerQueryBuilderMock->method('expr')->willReturn($expressionBuilder);
        $readerQueryBuilderMock->method('setParameter');
        $readerQueryBuilderMock->method('from')->willReturnSelf();
        $readerQueryBuilderMock->method('execute')->willReturn($resultStatement);
        $reflectionReaderQueryBuilder = $reflectionClassReaderManager->getProperty('readerQueryBuilder');
        $reflectionReaderQueryBuilder->setAccessible(true);
        $reflectionReaderQueryBuilder->setValue($readerManager, $readerQueryBuilderMock);

        $item = new DefaultItem($readerManager, $this->johnDoeModel->row());
        $itemReturn = $readerManager->retrieveItem();

        $this->assertSame($item->getRawValue('lastname'), $itemReturn->getRawValue('lastname'));
        $this->assertSame($item->getRawValue('firstname'), $itemReturn->getRawValue('firstname'));
    }

    public function testRetrieveItemByAutoItem()
    {
        $this->prepareReaderConfig([
            'itemRetrievalMode' => ReaderConfig::ITEM_RETRIEVAL_MODE_AUTO_ITEM,
            'itemRetrievalAutoItemField' => 'alias',
            'hideUnpublishedItems' => true,
            'publishedField' => 'published',
        ]);
        // set manager to a variable for changing properties just for this test, so it wont affect other tests
        $readerManager = $this->manager;

        $readerConfigMock = $this->mockClassWithProperties(ReaderConfigModel::class, ['dataContainer' => 'tl_test', 'itemRetrievalAutoItemField' => 'alias']);
        // create reflection class for reader manager class and set protected function addDcMultilingualSupport accessible
        $reflectionClassReaderManager = new \ReflectionClass(ReaderManager::class);
        $testMethodRetrieveItemByAutoItem = $reflectionClassReaderManager->getMethod('retrieveItemByAutoItem');
        $testMethodRetrieveItemByAutoItem->setAccessible(true);

        $reflectionReaderConfig = $reflectionClassReaderManager->getProperty('readerConfig');
        $reflectionReaderConfig->setAccessible(true);
        $reflectionReaderConfig->setValue($readerManager, $readerConfigMock);

        // overwrite property readerQueryBuilder
        $resultStatement = $this->createMock(ResultStatement::class);
        $resultStatement->method('fetch')->willReturn(['works']);
        $expressionBuilder = $this->createMock(ExpressionBuilder::class);
        $expressionBuilder->method('eq');
        $readerQueryBuilderMock = $this->createMock(ReaderQueryBuilder::class);
        $readerQueryBuilderMock->method('select');
        $readerQueryBuilderMock->method('setMaxResults')->willReturnSelf();
        $readerQueryBuilderMock->method('where');
        $readerQueryBuilderMock->method('expr')->willReturn($expressionBuilder);
        $readerQueryBuilderMock->method('setParameter');
        $readerQueryBuilderMock->method('from')->willReturnSelf();
        $readerQueryBuilderMock->method('execute')->willReturn($resultStatement);
        $reflectionReaderQueryBuilder = $reflectionClassReaderManager->getProperty('readerQueryBuilder');
        $reflectionReaderQueryBuilder->setAccessible(true);
        $reflectionReaderQueryBuilder->setValue($readerManager, $readerQueryBuilderMock);

        $item = $testMethodRetrieveItemByAutoItem->invokeArgs($readerManager, []);
        $this->assertNull($item);

        // auto_item
        Config::set('useAutoItem', true);
        $this->request->setGet('auto_item', 'john-doe');
        $item = $testMethodRetrieveItemByAutoItem->invokeArgs($readerManager, []);
        $this->assertSame(['works'], $item);
    }

    public function testTriggerOnLoadCallbacks()
    {
        $this->prepareReaderConfig();

        $GLOBALS['TL_DCA']['tl_test']['config']['onload_callback'] = [
            ['HeimrichHannot\ReaderBundle\Tests\Manager\ReaderManagerTest', 'onloadCallback1'],
            ['HeimrichHannot\ReaderBundle\Tests\Manager\ReaderManagerTest', 'onloadCallback2', true],
        ];

        $this->manager->triggerOnLoadCallbacks();

        $this->assertSame(['b'], static::$testArray);
    }

    public function onloadCallback1()
    {
        static::$testArray[] = 'a';
    }

    public function onloadCallback2()
    {
        static::$testArray[] = 'b';
    }

    public function loadCallback($value, $dc)
    {
        if ('Doe' === $value) {
            return 'DoeModified';
        }

        return $value;
    }

    public function testCheckPermission()
    {
        $this->prepareReaderConfig();

        $johnDoeItem = new DefaultItem($this->manager, $this->johnDoeModel->row());
        $this->manager->setItem($johnDoeItem);

        // no conditions -> always allowed
        $this->assertTrue($this->manager->checkPermission());

        // conditions
        $this->prepareReaderConfig([
            'addShowConditions' => true,
            'showFieldConditions' => serialize([
                [
                    'bracketLeft' => true,
                    'field' => 'firstname',
                    'operator' => 'equal',
                    'value' => 'John',
                    'bracketRight' => true,
                ],
            ]),
        ]);

        $this->manager->setItem($johnDoeItem);

        $this->assertTrue($this->manager->checkPermission());

        $janeDoeItem = new DefaultItem($this->manager, $this->janeDoeModel->row());
        $this->manager->setItem($janeDoeItem);
        $this->assertFalse($this->manager->checkPermission());
    }

    public function testSetModuleData()
    {
        $this->manager->setModuleData(['id' => 1]);

        $this->assertSame(['id' => 1], $this->manager->getModuleData());
    }

    public function testGetReaderConfig()
    {
        $this->manager->setModuleData([
            'readerConfig' => 1,
        ]);

        $readerConfigMock = $this->createMock(ReaderConfigModel::class);

        $this->readerConfigRegistry->method('findByPk')->willReturnCallback(function ($id) use ($readerConfigMock) {
            switch ($id) {
                case 1:
                    return $readerConfigMock;

                    break;

                default:
                    return null;
            }
        });

        $this->manager->setModuleData([
            'id' => 3,
            'readerConfig' => 2,
        ]);

        $this->expectExceptionMessage('The module 3 has no valid reader config. Please set one.');
        $this->manager->getReaderConfig();
    }

    public function testGetItemTemplateByName()
    {
        if (!\defined('TL_MODE')) {
            \define('TL_MODE', 'FE');
        }

        global $objPage;

        $objPage = new \stdClass();

        $objPage->templateGroup = '';

        $function = self::getMethod(ReaderManager::class, 'getItemTemplateByName');

        $this->assertSame('template.twig', $function->invokeArgs($this->manager, ['my_item_template']));

        try {
            $function->invokeArgs($this->manager, ['notexisting']); //if this method not throw exception it must be fail too.
            $this->fail("Expected exception 'Could not find template \"notexisting\"' not thrown");
        } catch (\Exception $e) { //Not catching a generic Exception or the fail function is also catched
            $this->assertEquals('Unable to find template "notexisting".', $e->getMessage());
        }
    }

    public function testDoFieldDependentRedirect()
    {
        // needed properties not set
        $this->prepareReaderConfig();
        $this->assertNull($this->manager->doFieldDependentRedirect());

        $this->prepareReaderConfig([
            'addFieldDependentRedirect' => true,
            'fieldDependentJumpTo' => 1,
            'redirectFieldConditions' => serialize([
                [
                    'bracketLeft' => true,
                    'field' => 'firstname',
                    'operator' => 'equal',
                    'value' => 'John',
                    'bracketRight' => true,
                ],
            ]),
        ]);

        $janeDoeItem = new DefaultItem($this->manager, $this->janeDoeModel->row());

        // no redirect since entity didn't fulfill the conditions
        $this->manager->setItem($janeDoeItem);
        $this->assertNull($this->manager->doFieldDependentRedirect());

        $johnDoeItem = new DefaultItem($this->manager, $this->johnDoeModel->row());

        // regular redirect
        $this->manager->setItem($johnDoeItem);

        $this->expectException(RedirectResponseException::class);
        $this->manager->doFieldDependentRedirect();
    }

    public function testPrepareItem()
    {
        $this->prepareReaderConfig();

        $this->manager->setDataContainer($this->mockClassWithProperties(DataContainer::class, [
            'table' => 'tl_test',
        ]));

        $johnDoeItem = new DefaultItem($this->manager, $this->johnDoeModel->row());

        $this->manager->setItem($johnDoeItem);

        Config::set('dateFormat', 'd.m.Y');

        $data = json_decode(json_encode($johnDoeItem));

        $stdClass = new \stdClass();
        $stdClass->field = 'someDate';

        $this->assertEquals([
            'raw' => (object) [
                'id' => '1',
                'firstname' => 'John',
                'lastname' => 'DoeModified',
                'someDate' => 1520004293,
                'published' => '1',
                'dc' => $stdClass,
            ],
            'formatted' => (object) [
                'id' => '1',
                'firstname' => 'John',
                'lastname' => 'DoeModified',
                'someDate' => '02.03.2018',
                'published' => '1',
                'dc' => $stdClass,
            ],
        ], [
            'raw' => $data->raw,
            'formatted' => $data->formatted,
        ]);
    }

    public function testAddDcMultilingualSupport()
    {
        // create reflection class for reader manager class and set protected function addDcMultilingualSupport accessible
        $reflectionClassReaderManager = new \ReflectionClass(ReaderManager::class);
        $testMethodAddDcMultilingualSupport = $reflectionClassReaderManager->getMethod('addDcMultilingualSupport');
        $testMethodAddDcMultilingualSupport->setAccessible(true);

        $readerConfig = new ReaderConfigModel();
        $readerConfig->setRow(array_merge([
            'dataContainer' => 'tl_test',
        ], [
            'itemRetrievalMode' => ReaderConfig::ITEM_RETRIEVAL_MODE_AUTO_ITEM,
            'itemRetrievalAutoItemField' => 'alias',
            'hideUnpublishedItems' => true,
            'publishedField' => 'published',
        ]));

        $fields = $testMethodAddDcMultilingualSupport->invokeArgs($this->manager, [$readerConfig, $this->readerQueryBuilder]);
        $this->assertSame('tl_test.firstname, tl_test.lastname, tl_test.someDate', $fields);

        $readerConfig = new ReaderConfigModel();
        $readerConfig->setRow(array_merge([
            'dataContainer' => 'tl_test',
        ], [
            'itemRetrievalMode' => ReaderConfig::ITEM_RETRIEVAL_MODE_AUTO_ITEM,
            'itemRetrievalAutoItemField' => 'alias',
            'hideUnpublishedItems' => true,
            'publishedField' => 'published',
            'addDcMultilingualSupport' => true,
        ]));

        $fields = $testMethodAddDcMultilingualSupport->invokeArgs($this->manager, [$readerConfig, $this->readerQueryBuilder]);
        $this->assertSame('tl_test_dcm.lastname, tl_test.someDate', $fields);
    }

    public function testRetrieveItemByFieldConditions()
    {
        $readerConfigMock = $this->mockClassWithProperties(ReaderConfigModel::class, ['dataContainer' => 'tl_test', 'itemRetrievalFieldConditions' => 'a:1:{i:0;a:6:{s:10:"connective";s:0:"";s:11:"bracketLeft";s:0:"";s:5:"field";s:15:"tl_ticket.alias";s:8:"operator";s:5:"equal";s:5:"value";s:13:"bayern-ticket";s:12:"bracketRight";s:0:"";}}']);
        // create reflection class for reader manager class and set protected function addDcMultilingualSupport accessible
        $reflectionClassReaderManager = new \ReflectionClass(ReaderManager::class);
        $testMethodRetrieveItemByFieldConditions = $reflectionClassReaderManager->getMethod('retrieveItemByFieldConditions');
        $testMethodRetrieveItemByFieldConditions->setAccessible(true);

        $reflectionReaderConfig = $reflectionClassReaderManager->getProperty('readerConfig');
        $reflectionReaderConfig->setAccessible(true);
        $reflectionReaderConfig->setValue($this->manager, $readerConfigMock);

        $item = $testMethodRetrieveItemByFieldConditions->invokeArgs($this->manager, []);
        $this->assertNull($item);
    }

    public function testGetQueryBuilder()
    {
        $readerQueryBuilder = $this->getMockBuilder(ReaderQueryBuilder::class)->disableOriginalConstructor()->getMock();

        $readerManager = $this->getMockBuilder(ReaderManager::class)->disableOriginalConstructor()->getMock();

        $reflectionClassReaderManager = new \ReflectionClass(ReaderManager::class);

        $reflectionFilterConfig = $reflectionClassReaderManager->getProperty('readerQueryBuilder');
        $reflectionFilterConfig->setAccessible(true);
        $reflectionFilterConfig->setValue($readerManager, $readerQueryBuilder);

        $testGetFilterConfig = $reflectionClassReaderManager->getMethod('getQueryBuilder');
        $testGetFilterConfig->setAccessible(true);
        $queryBuilderResult = $testGetFilterConfig->invokeArgs($readerManager, []);

        $this->assertSame($readerQueryBuilder, $queryBuilderResult);
    }

    public function testGetFilterConfig()
    {
        $filterConfigMock = $this->getMockBuilder(FilterConfig::class)->disableOriginalConstructor()->getMock();
        $filterConfigMock->method('getFilter')->willReturn(['id' => 1]);
        $readerConfigMock = $this->mockClassWithProperties(ReaderConfigModel::class, ['filter' => 1]);

        $readerManager = $this->getMockBuilder(ReaderManager::class)->disableOriginalConstructor()->getMock();

        $reflectionClassReaderManager = new \ReflectionClass(ReaderManager::class);
        $reflectionFilterConfig = $reflectionClassReaderManager->getProperty('filterConfig');
        $reflectionFilterConfig->setAccessible(true);
        $reflectionFilterConfig->setValue($readerManager, $filterConfigMock);

        $reflectionReaderConfig = $reflectionClassReaderManager->getProperty('readerConfig');
        $reflectionReaderConfig->setAccessible(true);
        $reflectionReaderConfig->setValue($readerManager, $readerConfigMock);

        $testGetFilterConfig = $reflectionClassReaderManager->getMethod('getFilterConfig');
        $testGetFilterConfig->setAccessible(true);

        $filterConfig = $testGetFilterConfig->invokeArgs($readerManager, []);

        $this->assertSame($filterConfigMock, $filterConfig);

        $readerConfigMock = $this->mockClassWithProperties(ReaderConfigModel::class, ['filter' => 2]);
        $filterManagerMock = $this->getMockBuilder(FilterManager::class)->disableOriginalConstructor()->getMock();
        $filterManagerMock->method('findById')->willReturn($filterConfigMock);

        $reflectionReaderConfig->setValue($readerManager, $readerConfigMock);

        $reflectionFilterManager = $reflectionClassReaderManager->getProperty('filterManager');
        $reflectionFilterManager->setAccessible(true);
        $reflectionFilterManager->setValue($readerManager, $filterManagerMock);

        $filterConfig = $testGetFilterConfig->invokeArgs($readerManager, []);
        $this->assertSame($filterConfigMock, $filterConfig);
    }
}
