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
use Contao\CoreBundle\Routing\ScopeMatcher;
use Contao\Database;
use Contao\DataContainer;
use Contao\FilesModel;
use Contao\Model;
use Contao\PageModel;
use Contao\System;
use Doctrine\DBAL\Driver\Connection;
use Doctrine\DBAL\Driver\Mysqli\Driver;
use HeimrichHannot\EntityFilterBundle\Backend\EntityFilter;
use HeimrichHannot\FilterBundle\Manager\FilterManager;
use HeimrichHannot\FilterBundle\Session\FilterSession;
use HeimrichHannot\ReaderBundle\Backend\ReaderConfig;
use HeimrichHannot\ReaderBundle\Backend\ReaderConfigElement;
use HeimrichHannot\ReaderBundle\ConfigElementType\ImageConfigElementType;
use HeimrichHannot\ReaderBundle\Item\DefaultItem;
use HeimrichHannot\ReaderBundle\Manager\ReaderManager;
use HeimrichHannot\ReaderBundle\Model\ReaderConfigElementModel;
use HeimrichHannot\ReaderBundle\Model\ReaderConfigModel;
use HeimrichHannot\ReaderBundle\QueryBuilder\ReaderQueryBuilder;
use HeimrichHannot\ReaderBundle\Registry\ReaderConfigElementRegistry;
use HeimrichHannot\ReaderBundle\Registry\ReaderConfigRegistry;
use HeimrichHannot\ReaderBundle\Tests\TestCaseEnvironment;
use HeimrichHannot\Request\Request;
use HeimrichHannot\UtilsBundle\Classes\ClassUtil;
use HeimrichHannot\UtilsBundle\Container\ContainerUtil;
use HeimrichHannot\UtilsBundle\Form\FormUtil;
use HeimrichHannot\UtilsBundle\Image\ImageUtil;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;
use HeimrichHannot\UtilsBundle\Template\TemplateUtil;
use HeimrichHannot\UtilsBundle\Url\UrlUtil;
use Symfony\Component\HttpFoundation\RequestMatcher;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

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
     * @var ReaderConfigRegistry
     */
    protected $readerConfigRegistry;

    /**
     * @var ReaderQueryBuilder
     */
    protected $readerQueryBuilder;

    /**
     * @var FilterManager
     */
    protected $filterManager;

    /**
     * @var array
     */
    protected static $testArray = [];

    public function setUp()
    {
        parent::setUp();

        if (!\defined('TL_ROOT')) {
            \define('TL_ROOT', $this->getFixturesDir());
        }

        $GLOBALS['TL_LANGUAGE'] = 'en';
        $GLOBALS['TL_LANG']['MSC'] = ['test' => 'bar'];

        $GLOBALS['TL_DCA']['tl_reader_config'] = [
            'config' => [
                'dataContainer' => 'Table',
                'sql' => [
                    'keys' => [
                    ],
                ],
            ],
            'fields' => [
            ],
        ];

        $this->entityFilter = $this->createConfiguredMock(
            EntityFilter::class,
            ['computeSqlCondition' => ['firstname=?', ['John']]]
        );

        $this->readerConfigRegistry = $this->createMock(ReaderConfigRegistry::class);

        $imageElement1 = $this->mockClassWithProperties(
            ReaderConfigElementModel::class,
            [
                'type' => ReaderConfigElement::TYPE_IMAGE,
                'imageSelectorField' => 'addImage1',
                'imageField' => 'singleSRC1',
                'placeholderImageMode' => ReaderConfigElement::PLACEHOLDER_IMAGE_MODE_GENDERED,
                'genderField' => 'gender',
                'placeholderImageFemale' => 'female',
                'placeholderImage' => 'male',
            ]
        );

        $imageElement2 = $this->mockClassWithProperties(
            ReaderConfigElementModel::class,
            [
                'type' => ReaderConfigElement::TYPE_IMAGE,
                'imageSelectorField' => 'addImage2',
                'imageField' => 'singleSRC2',
                'placeholderImageMode' => ReaderConfigElement::PLACEHOLDER_IMAGE_MODE_SIMPLE,
                'placeholderImage' => 'male',
                'imgSize' => serialize([1, 1, 1]),
            ]
        );

        $imageElement3 = $this->mockClassWithProperties(
            ReaderConfigElementModel::class,
            [
                'type' => ReaderConfigElement::TYPE_IMAGE,
                'imageSelectorField' => 'addImage3',
                'imageField' => 'singleSRC3',
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
            ['findBy' => [$imageElement1, $imageElement2, $imageElement3/*, $listElement*/]]
        );

        $johnDoeData = [
            'id' => '1',
            'firstname' => 'John',
            'lastname' => 'Doe',
            'someDate' => 1520004293,
            'published' => '1',
        ];

        $johnDoeModel = $this->mockClassWithProperties(
            Model::class,
            $johnDoeData
        );

        $johnDoeModel->method('row')->willReturn($johnDoeData);

        $this->johnDoeModel = $johnDoeModel;

        $janeDoeData = [
            'id' => '2',
            'firstname' => 'Jane',
            'lastname' => 'Doe',
            'someDate' => 1520004293,
            'published' => '',
        ];

        $this->janeDoeModel = $janeDoeModel = $this->mockClassWithProperties(
            Model::class,
            $janeDoeData
        );

        $this->janeDoeModel->method('row')->willReturn($janeDoeData);

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

        $this->imageUtil = $this->createMock(ImageUtil::class);
        $this->imageUtil->method('addToTemplateData')->willReturnCallback(
            function (
                string $imageField,
                string $imageSelectorField,
                array &$templateData,
                array $item,
                int $maxWidth = null,
                string $lightboxId = null,
                string $lightboxName = null,
                FilesModel $model = null
            ) {
                $templateData['picture'] = $item[$imageField];
            }
        );

        $this->formUtil = $this->createMock(FormUtil::class);

        $this->formUtil->method('prepareSpecialValueForOutput')->willReturnCallback(
            function ($field, $value, $dc) {
                switch ($field) {
                    case 'firstname':
                        return $value;
                        break;
                    case 'lastname':
                        return $value;
                        break;
                    case 'someDate':
                        return '02.03.2018';
                        break;
                }
            }
        );

        $this->formUtil->method('escapeAllHtmlEntities')->willReturnCallback(
            function ($table, $field, $value) {
                switch ($field) {
                    case 'firstname':
                        return $value;
                        break;
                    case 'lastname':
                        return $value;
                        break;
                    case 'someDate':
                        return '02.03.2018';
                        break;
                }
            }
        );

        $this->twig = $this->createConfiguredMock(
            \Twig_Environment::class,
            ['render' => 'twigResult']
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

        // container
        $container = $this->mockContainer();
        $container->setParameter(
            'huh.reader',
            [
                'reader' => [
                    'managers' => [
                        ['name' => 'default', 'id' => 'huh.reader.manager.reader'],
                    ],
                    'items' => [
                        ['name' => 'default', 'class' => 'HeimrichHannot\ReaderBundle\Item\DefaultItem'],
                    ],
                    'config_element_types' => [
                        ['name' => 'image', 'class' => 'HeimrichHannot\ReaderBundle\ConfigElementType\ImageConfigElementType'],
                        ['name' => 'list', 'class' => 'HeimrichHannot\ReaderBundle\ConfigElementType\ListConfigElementType'],
                    ],
                    'templates' => [
                        'item' => [
                            ['name' => 'my_item_template', 'template' => 'template.twig'],
                        ],
                    ],
                ],
            ]
        );

        $container->set('huh.utils.container', $this->containerUtil);
        $container->set('huh.utils.image', $this->imageUtil);
        $container->set('huh.utils.model', $this->modelUtil);
        $container->set('huh.utils.class', new ClassUtil());
        $container->set('database_connection', $this->createMock(Connection::class));
        $container->set('request_stack', $this->createRequestStackMock());
        $container->set('router', $this->createRouterMock());
        $container->set('session', new Session(new MockArraySessionStorage()));

        $requestStack = new RequestStack();
        $requestStack->push(new \Symfony\Component\HttpFoundation\Request());

        $container->set('huh.request', new \HeimrichHannot\RequestBundle\Component\HttpFoundation\Request($this->mockContaoFramework(), $requestStack, $this->mockScopeMatcher()));

        $container->set('contao.framework', $this->mockContaoFramework());

        $container->set('huh.utils.template', new TemplateUtil($this->mockContaoFramework()));

        System::setContainer($container);

        $filesAdapter = $this->mockAdapter(
            [
                'findByUuid',
            ]
        );

        $filesAdapter->method('findByUuid')->willReturnCallback(
            function ($uuid) {
                switch ($uuid) {
                    case 'default':
                        return $this->mockClassWithProperties(
                            FilesModel::class,
                            [
                                'path' => 'data/image.png',
                            ]
                        );
                        break;
                    case 'female':
                        return $this->mockClassWithProperties(
                            FilesModel::class,
                            [
                                'path' => 'data/female.png',
                            ]
                        );
                        break;
                    case 'male':
                        return $this->mockClassWithProperties(
                            FilesModel::class,
                            [
                                'path' => 'data/male.png',
                            ]
                        );
                        break;
                }
            }
        );

        $this->framework = $this->mockContaoFramework(
            [
                FilesModel::class => $filesAdapter,
            ]
        );

        $this->framework->method('createInstance')->willReturnCallback(
            function ($class) use ($databaseAdapter) {
                switch ($class) {
                    case Database::class:
                        return $databaseAdapter;
                        break;
                    case ImageConfigElementType::class:
                        return new ImageConfigElementType($this->framework);
                        break;
                    default:
                        return null;
                }
            }
        );

        $session = new Session(new MockArraySessionStorage());
        $filterSession = new FilterSession($this->framework, $session);

        $this->filterManager = new FilterManager($this->framework, $filterSession);

        $this->readerQueryBuilder = new ReaderQueryBuilder($this->framework, new \Doctrine\DBAL\Connection([], new Driver()));

        $this->manager = new ReaderManager(
            $this->framework,
            $this->filterManager,
            $this->readerQueryBuilder,
            $this->entityFilter,
            $this->readerConfigRegistry,
            $this->readerConfigElementRegistry,
            $this->modelUtil,
            $this->urlUtil,
            $this->containerUtil,
            $this->imageUtil,
            $this->formUtil,
            $this->twig
        );

        $this->manager->setModuleData(['id' => 1, 'readerConfig' => 1]);

        if (!\interface_exists('listable')) {
            include_once __DIR__.'/../../vendor/contao/core-bundle/src/Resources/contao/helper/interface.php';
        }

        $GLOBALS['TL_DCA']['tl_test']['fields'] = [
            'firstname' => [
                'inputType' => 'text',
                'eval' => ['maxlength' => 255, 'tl_class' => 'w50', 'mandatory' => true],
            ],
            'lastname' => [
                'inputType' => 'text',
                'load_callback' => [
                    ['HeimrichHannot\ReaderBundle\Tests\Manager\ReaderManagerTest', 'loadCallback'],
                ],
                'eval' => ['maxlength' => 255, 'tl_class' => 'w50', 'mandatory' => true],
            ],
            'someDate' => [
                'inputType' => 'text',
                'eval' => ['rgxp' => 'date', 'datepicker' => true, 'tl_class' => 'w50 wizard', 'mandatory' => true],
            ],
        ];
    }

    public function prepareReaderConfig(array $attributes = [])
    {
        $readerConfig = new ReaderConfigModel();
        $readerConfig->setRow(array_merge(
            [
                'dataContainer' => 'tl_test',
            ],
            $attributes
        ));

        $this->readerConfigRegistry = $this->createMock(ReaderConfigRegistry::class);

        $this->readerConfigRegistry->method('findByPk')->willReturn($readerConfig);
        $this->readerConfigRegistry->method('computeReaderConfig')->willReturn($readerConfig);

        $this->manager = new ReaderManager(
            $this->framework,
            $this->filterManager,
            $this->readerQueryBuilder,
            $this->entityFilter,
            $this->readerConfigRegistry,
            $this->readerConfigElementRegistry,
            $this->modelUtil,
            $this->urlUtil,
            $this->containerUtil,
            $this->imageUtil,
            $this->formUtil,
            $this->twig
        );

        $this->manager->setModuleData(['id' => 1, 'readerConfig' => 1]);
        $this->manager->setReaderConfig($readerConfig);
    }

    public function testRetrieveItem()
    {
        $this->markTestSkipped('FIXME: Test within Mock Querybuilder');

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

        $item = new DefaultItem($this->manager, $this->johnDoeModel->row());

        $data = json_decode(json_encode($item));
        $managerData = json_decode(json_encode($this->manager->retrieveItem()));

        $this->assertSame($data->raw, $managerData->raw);

        Request::setGet('auto_item', '1');

        $data = json_decode(json_encode($item));
        $managerData = json_decode(json_encode($this->manager->retrieveItem()));

        $this->assertSame($data->raw, $managerData->raw);

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

        $data = json_decode(json_encode($item));

        $managerData = json_decode(json_encode($this->manager->retrieveItem()));

        $this->assertSame($data->raw, $managerData->raw);
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
            $this->assertEquals('Could not find template "notexisting"', $e->getMessage());
        }
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

    public function testAddDataToTemplate()
    {
        $this->markTestSkipped('FIXME: Test within DefaultItemTest');

        $readerConfig = $this->mockClassWithProperties(
            ReaderConfigModel::class,
            [
                'dataContainer' => 'tl_test',
            ]
        );

        // positive
        $item = [
            'raw' => [
                'addImage1' => true,
                'singleSRC1' => 'default',
            ],
        ];

        $templateData = [];

        $function = self::getMethod(ReaderManager::class, 'applyReaderConfigElements');
        $function->invokeArgs($this->manager, [$item, &$templateData, $readerConfig]);

        $this->assertSame(
            [
                'images' => [
                    'singleSRC1' => [
                        'picture' => 'data/image.png',
                    ],
                    'singleSRC2' => [
                        'picture' => 'data/male.png',
                    ],
                ],
            ],
            $templateData
        );

        // selector not set
        // male
        $templateData = [];

        $item = [
            'raw' => [
                'addImage1' => false,
                'gender' => 'male',
            ],
        ];

        $function->invokeArgs($this->manager, [$item, &$templateData, $readerConfig]);

        $this->assertSame(
            [
                'images' => [
                    'singleSRC1' => [
                        'picture' => 'data/male.png',
                    ],
                    'singleSRC2' => [
                        'picture' => 'data/male.png',
                    ],
                ],
            ],
            $templateData
        );

        // female
        $templateData = [];

        $item = [
            'raw' => [
                'addImage1' => false,
                'gender' => 'female',
            ],
        ];

        $function->invokeArgs($this->manager, [$item, &$templateData, $readerConfig]);

        $this->assertSame(
            [
                'images' => [
                    'singleSRC1' => [
                        'picture' => 'data/female.png',
                    ],
                    'singleSRC2' => [
                        'picture' => 'data/male.png',
                    ],
                ],
            ],
            $templateData
        );
    }

    public function testPrepareItem()
    {
        $this->prepareReaderConfig();

        $this->manager->setDataContainer(
            $this->mockClassWithProperties(
                DataContainer::class,
                [
                    'table' => 'tl_test',
                ]
            )
        );

        $johnDoeItem = new DefaultItem($this->manager, $this->johnDoeModel->row());

        $this->manager->setItem($johnDoeItem);

        Config::set('dateFormat', 'd.m.Y');

        $data = json_decode(json_encode($johnDoeItem));

        $this->assertEquals(
            [
                'raw' => (object) [
                    'id' => '1',
                    'firstname' => 'John',
                    'lastname' => 'DoeModified',
                    'someDate' => 1520004293,
                    'published' => '1',
                ],
                'formatted' => (object) [
                    'id' => '1',
                    'firstname' => 'John',
                    'lastname' => 'DoeModified',
                    'someDate' => '02.03.2018',
                    'published' => '1',
                ],
            ],
            [
                'raw' => $data->raw,
                'formatted' => $data->formatted,
            ]
        );
    }

    /**
     * Mocks a request scope matcher.
     *
     * @return ScopeMatcher
     */
    protected function mockScopeMatcher(): ScopeMatcher
    {
        return new ScopeMatcher(
            new RequestMatcher(null, null, null, null, ['_scope' => 'backend']),
            new RequestMatcher(null, null, null, null, ['_scope' => 'frontend'])
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
