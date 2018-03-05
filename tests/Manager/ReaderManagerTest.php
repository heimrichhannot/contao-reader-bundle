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
use Contao\DataContainer;
use Contao\FilesModel;
use Contao\Model;
use Contao\PageModel;
use Contao\System;
use HeimrichHannot\EntityFilterBundle\Backend\EntityFilter;
use HeimrichHannot\ReaderBundle\Backend\ReaderConfig;
use HeimrichHannot\ReaderBundle\Backend\ReaderConfigElement;
use HeimrichHannot\ReaderBundle\ConfigElementType\ImageConfigElementType;
use HeimrichHannot\ReaderBundle\Manager\ReaderManager;
use HeimrichHannot\ReaderBundle\Model\ReaderConfigElementModel;
use HeimrichHannot\ReaderBundle\Model\ReaderConfigModel;
use HeimrichHannot\ReaderBundle\Registry\ReaderConfigElementRegistry;
use HeimrichHannot\ReaderBundle\Registry\ReaderConfigRegistry;
use HeimrichHannot\ReaderBundle\Tests\TestCaseEnvironment;
use HeimrichHannot\Request\Request;
use HeimrichHannot\UtilsBundle\Container\ContainerUtil;
use HeimrichHannot\UtilsBundle\Form\FormUtil;
use HeimrichHannot\UtilsBundle\Image\ImageUtil;
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

        $this->manager = new ReaderManager(
            $this->framework,
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

    public function loadCallback($value, $dc)
    {
        if ('Doe' === $value) {
            return 'DoeModified';
        }

        return $value;
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
        $this->manager->setItem($this->johnDoeModel);
        $this->manager->setDataContainer(
            $this->mockClassWithProperties(
                DataContainer::class,
                [
                    'table' => 'tl_test',
                ]
            )
        );

        Config::set('dateFormat', 'd.m.Y');

        $this->assertSame(
            [
                'raw' => [
                    'firstname' => 'John',
                    'lastname' => 'DoeModified',
                    'someDate' => 1520004293,
                ],
                'formatted' => [
                    'firstname' => 'John',
                    'lastname' => 'DoeModified',
                    'someDate' => '02.03.2018',
                ],
            ],
            $this->manager->prepareItem(
                [
                    'firstname' => 'John',
                    'lastname' => 'Doe',
                    'someDate' => 1520004293,
                ]
            )
        );

        $this->prepareReaderConfig(
            [
                'limitFormattedFields' => true,
                'formattedFields' => serialize(
                    [
                        'firstname',
                        'lastname',
                    ]
                ),
            ]
        );

        $this->assertSame(
            [
                'raw' => [
                    'firstname' => 'John',
                    'lastname' => 'DoeModified',
                    'someDate' => 1520004293,
                ],
                'formatted' => [
                    'firstname' => 'John',
                    'lastname' => 'DoeModified',
                ],
            ],
            $this->manager->prepareItem(
                [
                    'firstname' => 'John',
                    'lastname' => 'Doe',
                    'someDate' => 1520004293,
                ]
            )
        );
    }

    public function testParseItem()
    {
        $this->prepareReaderConfig(
            [
                'itemTemplate' => 'my_item_template',
            ]
        );

        $this->assertSame('twigResult', $this->manager->parseItem(
            [
                'raw' => [
                    'firstname' => 'John',
                    'lastname' => 'DoeModified',
                    'someDate' => 1520004293,
                ],
                'formatted' => [
                    'firstname' => 'John',
                    'lastname' => 'DoeModified',
                ],
            ]
        ));
    }

    public function testGetConfigElementTypeClassByName()
    {
        $function = self::getMethod(ReaderManager::class, 'getConfigElementTypeClassByName');

        $this->assertSame('HeimrichHannot\ReaderBundle\ConfigElementType\ImageConfigElementType', $function->invokeArgs($this->manager, ['image']));

        $this->assertNull($function->invokeArgs($this->manager, ['notexisting']));
    }

    protected static function getMethod($class, $name)
    {
        $class = new \ReflectionClass($class);
        $method = $class->getMethod($name);
        $method->setAccessible(true);

        return $method;
    }
}
