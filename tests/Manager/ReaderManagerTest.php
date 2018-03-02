<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ReaderBundle\Tests\Manager;

use Contao\Config;
use Contao\CoreBundle\Exception\RedirectResponseException;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\Database;
use Contao\FilesModel;
use Contao\Model;
use Contao\PageModel;
use Contao\System;
use HeimrichHannot\EntityFilterBundle\Backend\EntityFilter;
use HeimrichHannot\ReaderBundle\Backend\ReaderConfig;
use HeimrichHannot\ReaderBundle\Backend\ReaderConfigElement;
use HeimrichHannot\ReaderBundle\Manager\ReaderManager;
use HeimrichHannot\ReaderBundle\Model\ReaderConfigElementModel;
use HeimrichHannot\ReaderBundle\Model\ReaderConfigModel;
use HeimrichHannot\ReaderBundle\Registry\ReaderConfigElementRegistry;
use HeimrichHannot\ReaderBundle\Registry\ReaderConfigRegistry;
use HeimrichHannot\ReaderBundle\Tests\TestCaseEnvironment;
use HeimrichHannot\Request\Request;
use HeimrichHannot\UtilsBundle\Container\ContainerUtil;
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

        $this->readerConfigRegistry = $this->createMock(ReaderConfigRegistry::class);

        $imageElement = $this->mockClassWithProperties(
            ReaderConfigElementModel::class,
            [
                'type' => ReaderConfigElement::TYPE_IMAGE,
                'imageSelectorField' => 'addImage',
                'imageField' => 'singleSRC',
                'placeholderImageMode' => ReaderConfigElement::PLACEHOLDER_IMAGE_MODE_GENDERED,
                'genderField' => 'gender',
                'placeholderImageFemale' => 'female.png',
                'placeholderImageMale' => 'male.png',
            ]
        );

//        $listElement = $this->mockClassWithProperties(
//            ReaderConfigElementModel::class,
//            [
//                'type' => ReaderConfigElement::TYPE_LIST,
//            ]
//        );

        $this->readerConfigElementRegistry = $this->createConfiguredMock(
            ReaderConfigElementRegistry::class,
            ['findBy' => [$imageElement/*, $listElement*/]]
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
                if ('tl_files' === $table) {
                    return $this->mockClassWithProperties(FilesModel::class, [
                        'path' => 'data/image.png',
                    ]);
                } elseif ('tl_test' === $table && $columns === ['tl_test.alias=?'] && $values === ['john-doe']) {
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
            [
                'getJumpToPageObject' => $this->createConfiguredMock(
                    PageModel::class,
                    [
                        'getFrontendUrl' => 'https://www.google.de',
                    ]
                ),
            ]
        );

        $this->containerUtil = $this->createConfiguredMock(
            ContainerUtil::class,
            ['getProjectDir' => __DIR__.'/..']
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

        $container = $this->mockContainer();
        $container->setParameter(
            'huh.reader',
            [
                'reader' => [
                    'templates' => [
                        'item' => [
                            ['name' => 'my_item_template', 'template' => 'template.twig'],
                        ],
                    ],
                ],
            ]
        );
        System::setContainer($container);

        $this->framework = $this->mockContaoFramework();
        $this->framework->method('createInstance')->willReturn($databaseAdapter);

        $this->manager = new ReaderManager(
            $this->framework,
            $this->entityFilter,
            $this->readerConfigRegistry,
            $this->readerConfigElementRegistry,
            $this->modelUtil,
            $this->urlUtil,
            $this->containerUtil,
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

    public function testGetReaderConfig()
    {
        $this->manager->setModuleData(
            [
                'readerConfig' => 1,
            ]
        );

        $readerConfigMock = $this->createMock(ReaderConfigModel::class);

        $this->readerConfigRegistry->method('findByPk')->willReturnCallback(
            function ($id) use ($readerConfigMock) {
                switch ($id) {
                    case 1:
                        return $readerConfigMock;
                        break;
                    default:
                        return null;
                }
            }
        );

        $this->assertSame($readerConfigMock, $this->manager->getReaderConfig());

        $this->manager->setModuleData(
            [
                'id' => 3,
                'readerConfig' => 2,
            ]
        );

        $this->expectExceptionMessage('The module 3 has no valid reader config. Please set one.');
        $this->manager->getReaderConfig();
    }

    public function testGetItemTemplateByName()
    {
        $function = self::getMethod(ReaderManager::class, 'getItemTemplateByName');

        $this->assertSame('template.twig', $function->invokeArgs($this->manager, ['my_item_template']));

        $this->assertNull($function->invokeArgs($this->manager, ['notexisting']));
    }

    public function testDoFieldDependentRedirect()
    {
        // needed properties not set
        $this->prepareReaderConfig();
        $this->assertNull($this->manager->doFieldDependentRedirect());

        $this->prepareReaderConfig(
            [
                'addFieldDependentRedirect' => true,
                'fieldDependentJumpTo' => 1,
                'redirectFieldConditions' => serialize(
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

        // no redirect since entity didn't fulfill the conditions
        $this->manager->setItem($this->janeDoeModel);
        $this->assertNull($this->manager->doFieldDependentRedirect());

        // regular redirect
        $this->manager->setItem($this->johnDoeModel);

        $this->expectException(RedirectResponseException::class);
        $this->manager->doFieldDependentRedirect();
    }

    public function testAddDataToTemplate()
    {
        $readerConfig = $this->mockClassWithProperties(
            ReaderConfigModel::class,
            [
                'dataContainer' => 'tl_test',
            ]
        );

        $item = [
            'raw' => [
                'addImage' => true,
                'singleSRC' => 'test',
            ],
        ];

        $templateData = [];

        $function = self::getMethod(ReaderManager::class, 'addDataToTemplate');
        $function->invokeArgs($this->manager, [$item, &$templateData, $readerConfig]);

        $this->assertSame(
            [
                'images' => [
                    'singleSRC' => 'test.png',
                ],
            ],
            $templateData
        );
    }

    protected static function getMethod($class, $name)
    {
        $class = new \ReflectionClass($class);
        $method = $class->getMethod($name);
        $method->setAccessible(true);

        return $method;
    }
}
