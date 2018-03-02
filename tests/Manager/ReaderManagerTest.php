<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ReaderBundle\Tests\Manager;

use Contao\Config;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\Database;
use Contao\Model;
use HeimrichHannot\EntityFilterBundle\Backend\EntityFilter;
use HeimrichHannot\ReaderBundle\Backend\ReaderConfig;
use HeimrichHannot\ReaderBundle\Manager\ReaderManager;
use HeimrichHannot\ReaderBundle\Model\ReaderConfigModel;
use HeimrichHannot\ReaderBundle\Registry\ReaderConfigElementRegistry;
use HeimrichHannot\ReaderBundle\Registry\ReaderConfigRegistry;
use HeimrichHannot\ReaderBundle\Tests\TestCaseEnvironment;
use HeimrichHannot\Request\Request;
use HeimrichHannot\UtilsBundle\Form\FormUtil;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;
use HeimrichHannot\UtilsBundle\Url\UrlUtil;

class ReaderManagerTest extends TestCaseEnvironment
{
    /**
     * @var ContaoFrameworkInterface
     */
    protected $framework;

    /**
     * @var ReaderManager
     */
    protected $manager;

    /**
     * @var Model
     */
    protected $johnDoeModel;

    /**
     * @var Model
     */
    protected $janeDoeModel;

    /**
     * @var array
     */
    protected static $testArray = [];

    public function setUp()
    {
        parent::setUp();

        $this->entityFilter = $this->createConfiguredMock(
            EntityFilter::class,
            ['computeSqlCondition' => ['firstname=?', ['John']]]
        );

        $this->readerConfigRegistry = $this->createConfiguredMock(
            ReaderConfigRegistry::class,
            []
        );

        $this->readerConfigElementRegistry = $this->createConfiguredMock(
            ReaderConfigElementRegistry::class,
            []
        );

        $johnDoeModel = $this->mockClassWithProperties(
            Model::class,
            [
                'id' => '1',
                'firstname' => 'John',
                'lastname' => 'Doe',
                'published' => '1',
            ]
        );

        $this->johnDoeModel = $johnDoeModel;

        $this->janeDoeModel = $janeDoeModel = $this->mockClassWithProperties(
            Model::class,
            [
                'id' => '2',
                'firstname' => 'Jane',
                'lastname' => 'Doe',
                'published' => '',
            ]
        );

        $modelUtil = $this->createMock(ModelUtil::class);
        $modelUtil->method('findOneModelInstanceBy')->willReturnCallback(
            function ($table, $columns, $values) use ($johnDoeModel) {
                if ('tl_test' === $table && $columns === ['tl_test.alias=?'] && $values === ['john-doe']) {
                    return $johnDoeModel;
                }
            }
        );

        $modelUtil->method('findModelInstanceByPk')->willReturnCallback(
            function ($table, $pk) use ($johnDoeModel, $janeDoeModel) {
                if ('tl_test' === $table && '1' === $pk) {
                    return $johnDoeModel;
                }

                if ('tl_test' === $table && '2' === $pk) {
                    return $janeDoeModel;
                }
            }
        );

        $this->modelUtil = $modelUtil;

        $this->urlUtil = $this->createConfiguredMock(
            UrlUtil::class,
            []
        );

        $this->formUtil = $this->createConfiguredMock(
            FormUtil::class,
            []
        );

        $this->twig = $this->createConfiguredMock(
            \Twig_Environment::class,
            []
        );

        // database
        $databaseAdapter = $this->mockAdapter(['execute', 'prepare', 'limit']);
        $databaseAdapter->method('execute')->willReturnCallback(
            function ($values, $id = null) {
                if (!isset($id)) {
                    return $this->mockClassWithProperties(Database\Result::class, ['total' => 1]);
                }

                if ('1' === $id) {
                    return $this->mockClassWithProperties(Database\Result::class, ['numRows' => 1, 'id' => '1']);
                }

                return $this->mockClassWithProperties(Database\Result::class, ['numRows' => 0]);
            }
        );
        $databaseAdapter->method('prepare')->willReturn($databaseAdapter);
        $limitAdapter = $this->mockAdapter(['execute']);
        $limitAdapter->method('execute')->willReturnCallback(
            function ($values, $id = null) {
                return $this->mockClassWithProperties(Database\Result::class, ['numRows' => 1, 'id' => '1']);
            }
        );
        $databaseAdapter->method('limit')->willReturn($limitAdapter);
        $this->framework = $this->mockContaoFramework();
        $this->framework->method('createInstance')->willReturn($databaseAdapter);

        $this->manager = new ReaderManager(
            $this->framework,
            $this->entityFilter,
            $this->readerConfigRegistry,
            $this->readerConfigElementRegistry,
            $this->modelUtil,
            $this->urlUtil,
            $this->formUtil,
            $this->twig
        );

        if (!\interface_exists('listable')) {
            include_once __DIR__.'/../../vendor/contao/core-bundle/src/Resources/contao/helper/interface.php';
        }
    }

    public function prepareReaderConfig(array $attributes = [])
    {
        $readerConfig = $this->mockClassWithProperties(
            ReaderConfigModel::class,
            array_merge(
                [
                    'dataContainer' => 'tl_test',
                ],
                $attributes
            )
        );

        $this->manager->setReaderConfig($readerConfig);
    }

    public function testRetrieveItem()
    {
        // auto_item
        Config::set('useAutoItem', true);
        Request::setGet('auto_item', 'john-doe');

        $this->prepareReaderConfig(
            [
                'itemRetrievalMode' => ReaderConfig::ITEM_RETRIEVAL_MODE_AUTO_ITEM,
                'itemRetrievalAutoItemField' => 'alias',
                'hideUnpublishedItems' => true,
                'publishedField' => 'published',
            ]
        );

        $this->assertSame($this->johnDoeModel, $this->manager->retrieveItem());

        Request::setGet('auto_item', '1');

        $this->assertSame($this->johnDoeModel, $this->manager->retrieveItem());

        // unpublished
        Request::setGet('auto_item', '2');

        $this->assertNull($this->manager->retrieveItem());

        // field conditions
        $this->prepareReaderConfig(
            [
                'itemRetrievalMode' => ReaderConfig::ITEM_RETRIEVAL_MODE_FIELD_CONDITIONS,
                'itemRetrievalFieldConditions' => serialize(
                    [
                        [
                            'bracketLeft' => true,
                            'field' => 'firstname',
                            'operator' => 'equal',
                            'value' => 'John',
                            'bracketRight' => true,
                        ],
                    ]
                ),
            ]
        );

        $this->assertSame($this->johnDoeModel, $this->manager->retrieveItem());
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

    public function testSetPageTitle()
    {
        $this->prepareReaderConfig(
            [
                'setPageTitleByField' => true,
                'pageTitleFieldPattern' => '%firstname% %lastname%',
            ]
        );

        $this->manager->setItem($this->johnDoeModel);

        global $objPage;

        $objPage = $this->createMock(\stdClass::class);

        $this->manager->setPageTitle();

        $this->assertSame('John Doe', $objPage->pageTitle);
    }

    public function testCheckPermission()
    {
        $this->prepareReaderConfig();
        $this->manager->setItem($this->johnDoeModel);

        // no conditions -> always allowed
        $this->assertTrue($this->manager->checkPermission());

        // conditions
        $this->prepareReaderConfig(
            [
                'addShowConditions' => true,
                'showFieldConditions' => serialize(
                    [
                        [
                            'bracketLeft' => true,
                            'field' => 'firstname',
                            'operator' => 'equal',
                            'value' => 'John',
                            'bracketRight' => true,
                        ],
                    ]
                ),
            ]
        );

        $this->assertTrue($this->manager->checkPermission());

        $this->manager->setItem($this->janeDoeModel);
        $this->assertFalse($this->manager->checkPermission());
    }

    public function testSetModuleData()
    {
        $this->manager->setModuleData(['id' => 1]);

        $this->assertSame(['id' => 1], $this->manager->getModuleData());
    }
}
