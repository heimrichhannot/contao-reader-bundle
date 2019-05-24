<?php

/*
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ReaderBundle\Tests;

use Contao\CoreBundle\Config\ResourceFinder;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\CoreBundle\Routing\ScopeMatcher;
use Contao\Database;
use Contao\FilesModel;
use Contao\Model;
use Contao\PageModel;
use Contao\System;
use Doctrine\DBAL\Driver\Connection;
use Doctrine\DBAL\Driver\Mysqli\Driver;
use Doctrine\DBAL\Driver\ResultStatement;
use HeimrichHannot\EntityFilterBundle\Backend\EntityFilter;
use HeimrichHannot\FilterBundle\Manager\FilterManager;
use HeimrichHannot\FilterBundle\Session\FilterSession;
use HeimrichHannot\ReaderBundle\Backend\ReaderConfigElement;
use HeimrichHannot\ReaderBundle\ConfigElementType\ImageConfigElementType;
use HeimrichHannot\ReaderBundle\Manager\ReaderManager;
use HeimrichHannot\ReaderBundle\Model\ReaderConfigElementModel;
use HeimrichHannot\ReaderBundle\Model\ReaderConfigModel;
use HeimrichHannot\ReaderBundle\QueryBuilder\ReaderQueryBuilder;
use HeimrichHannot\ReaderBundle\Registry\ReaderConfigElementRegistry;
use HeimrichHannot\ReaderBundle\Registry\ReaderConfigRegistry;
use HeimrichHannot\RequestBundle\Component\HttpFoundation\Request;
use HeimrichHannot\UtilsBundle\Classes\ClassUtil;
use HeimrichHannot\UtilsBundle\Container\ContainerUtil;
use HeimrichHannot\UtilsBundle\Form\FormUtil;
use HeimrichHannot\UtilsBundle\Image\ImageUtil;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;
use HeimrichHannot\UtilsBundle\Template\TemplateUtil;
use HeimrichHannot\UtilsBundle\Url\UrlUtil;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpFoundation\RequestMatcher;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\HttpKernel\KernelInterface;
use Twig\Environment;

/**
 * Copyright (c) 2019 Heimrich & Hannot GmbH.
 *
 * @license LGPL-3.0-or-later
 */
abstract class TestCaseEnvironmentReaderManager extends TestCaseEnvironment
{
    use FixturesTrait;
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
     * @var \HeimrichHannot\RequestBundle\Component\HttpFoundation\Request
     */
    protected $request;

    /**
     * @var array
     */
    protected static $testArray = [];

    public function setUp(): void
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
                    'keys' => [],
                ],
            ],
            'fields' => [],
        ];

        $this->readerConfigRegistry = $this->createMock(ReaderConfigRegistry::class);

        $this->createReaderConfigElementRegistry();
        $this->createJohnAndJaneDoeData();
        $this->createModelUtil();

        $this->urlUtil = $this->createConfiguredMock(UrlUtil::class, [
            'getJumpToPageObject' => $this->createConfiguredMock(PageModel::class, [
                'getFrontendUrl' => 'https://www.google.de',
            ]),
        ]);

        $this->containerUtil = $this->createConfiguredMock(ContainerUtil::class, ['getProjectDir' => __DIR__]);
        $this->containerUtil->method('isBundleActive')->willReturn(true);

        $this->imageUtil = $this->createMock(ImageUtil::class);
        $this->imageUtil->method('addToTemplateData')->willReturnCallback(function (
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
        });

        $this->createFormUtil();

        $this->twig = $this->createConfiguredMock(Environment::class, ['render' => 'twigResult']);

        $this->createRequest();

        // container
        System::setContainer($this->getContainerMock());

        $this->createAdapters();

        $session = new Session(new MockArraySessionStorage());
        $filterSession = new FilterSession($this->framework, $session);

        $this->filterManager = new FilterManager($this->framework, $filterSession);

        $this->readerQueryBuilder = new ReaderQueryBuilder($this->framework, new \Doctrine\DBAL\Connection([], new Driver()));

        $resultStatement = $this->createMock(ResultStatement::class);
        $resultStatement->method('fetch')->willReturn(null);
        $readerQueryBuilderMock = $this->createMock(ReaderQueryBuilder::class);
        $readerQueryBuilderMock->method('select');
        $readerQueryBuilderMock->method('execute')->willReturn($resultStatement);
        $this->entityFilter = $this->createConfiguredMock(EntityFilter::class, ['computeSqlCondition' => ['firstname=?', ['John']]]);
        $this->entityFilter->method('computeQueryBuilderCondition')->willReturn($readerQueryBuilderMock);

        $this->manager = new ReaderManager($this->framework, $this->filterManager, $this->readerQueryBuilder, $this->entityFilter, $this->readerConfigRegistry, $this->readerConfigElementRegistry, $this->modelUtil, $this->urlUtil, $this->containerUtil, $this->imageUtil, $this->formUtil, $this->twig);

        $this->manager->setModuleData(['id' => 1, 'readerConfig' => 1]);

        if (!interface_exists('listable')) {
            include_once __DIR__.'/../vendor/contao/core-bundle/src/Resources/contao/helper/interface.php';
        }

        // TODO: config['lang...'] research to find correct values
        $GLOBALS['TL_DCA']['tl_test'] = [
            'fields' => [
                'firstname' => [
                    'inputType' => 'text',
                    'eval' => ['maxlength' => 255, 'tl_class' => 'w50', 'mandatory' => true],
                ],
                'lastname' => [
                    'inputType' => 'text',
                    'sql' => 'someStuff',
                    'load_callback' => [
                        ['HeimrichHannot\ReaderBundle\Tests\Manager\ReaderManagerTest', 'loadCallback'],
                    ],
                    'eval' => ['maxlength' => 255, 'tl_class' => 'w50', 'mandatory' => true, 'translatableFor' => '*'],
                ],
                'someDate' => [
                    'inputType' => 'text',
                    'sql' => 'someStuff',
                    'eval' => ['rgxp' => 'date', 'datepicker' => true, 'tl_class' => 'w50 wizard', 'mandatory' => true, 'translatableFor' => 'pl'],
                ],
            ],
            'config' => [
                'fallbackLang' => 'de',
                'langPid' => '2',
                'langPublished' => 'firstname',
                'langStart' => 'firstname',
                'langStop' => 'firstname',
            ],
        ];
    }

    public function prepareReaderConfig(array $attributes = [])
    {
        $readerConfig = new ReaderConfigModel();
        $readerConfig->setRow(array_merge([
            'dataContainer' => 'tl_test',
        ], $attributes));

        $this->readerConfigRegistry = $this->createMock(ReaderConfigRegistry::class);

        $this->readerConfigRegistry->method('findByPk')->willReturn($readerConfig);
        $this->readerConfigRegistry->method('computeReaderConfig')->willReturn($readerConfig);

        $this->manager = new ReaderManager($this->framework, $this->filterManager, $this->readerQueryBuilder, $this->entityFilter, $this->readerConfigRegistry, $this->readerConfigElementRegistry, $this->modelUtil, $this->urlUtil, $this->containerUtil, $this->imageUtil, $this->formUtil, $this->twig);

        $this->manager->setModuleData(['id' => 1, 'readerConfig' => 1]);
        $this->manager->setReaderConfig($readerConfig);
    }

    /**
     * Mocks a request scope matcher.
     *
     * @return ScopeMatcher
     */
    protected function mockScopeMatcher(): ScopeMatcher
    {
        return new ScopeMatcher(new RequestMatcher(null, null, null, null, ['_scope' => 'backend']), new RequestMatcher(null, null, null, null, ['_scope' => 'frontend']));
    }

    protected static function getMethod($class, $name)
    {
        $class = new \ReflectionClass($class);
        $method = $class->getMethod($name);
        $method->setAccessible(true);

        return $method;
    }

    protected function getContainerMock(ContainerBuilder $container = null)
    {
        if (!$container) {
            $container = $this->mockContainer();
        }

        if (!$container->has('kernel')) {
            $kernel = $this->createMock(KernelInterface::class);
            $kernel->method('getCacheDir')->willReturn($this->getTempDir());
            $kernel->method('isDebug')->willReturn(false);
            $container->setParameter('kernel.debug', true);
            $container->set('kernel', $kernel);
        }

        $container->setParameter('kernel.project_dir', $this->getFixturesDir());
        $container->setParameter('huh.reader', [
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
        ]);
        $container->set('contao.resource_finder', new ResourceFinder([$this->getFixturesDir()]));
        $container->set('huh.utils.container', $this->containerUtil);
        $container->set('huh.utils.image', $this->imageUtil);
        $container->set('huh.utils.model', $this->modelUtil);
        $container->set('huh.utils.class', new ClassUtil($container));
        $container->set('database_connection', $this->createMock(Connection::class));
        $container->set('request_stack', $this->createRequestStackMock());
        $container->set('router', $this->createRouterMock());
        $container->set('session', new Session(new MockArraySessionStorage()));
        $container->set('huh.request', $this->request);
        $container->set('contao.framework', $this->mockContaoFramework());
        $container->set('huh.utils.template', new TemplateUtil($container));

        return $container;
    }

    protected function createJohnAndJaneDoeData()
    {
        $johnDoeData = [
            'id' => '1',
            'firstname' => 'John',
            'lastname' => 'Doe',
            'someDate' => 1520004293,
            'published' => '1',
        ];

        $johnDoeModel = $this->mockClassWithProperties(Model::class, $johnDoeData);

        $johnDoeModel->method('row')->willReturn($johnDoeData);

        $this->johnDoeModel = $johnDoeModel;

        $janeDoeData = [
            'id' => '2',
            'firstname' => 'Jane',
            'lastname' => 'Doe',
            'someDate' => 1520004293,
            'published' => '',
        ];

        $this->janeDoeModel = $janeDoeModel = $this->mockClassWithProperties(Model::class, $janeDoeData);

        $this->janeDoeModel->method('row')->willReturn($janeDoeData);
    }

    protected function createModelUtil()
    {
        $johnDoeModel = $this->johnDoeModel;
        $janeDoeModel = $this->janeDoeModel;
        $modelUtil = $this->createMock(ModelUtil::class);
        $modelUtil->method('findOneModelInstanceBy')->willReturnCallback(function ($table, $columns, $values) use ($johnDoeModel) {
            if ('tl_test' === $table && $columns === ['tl_test.alias=?'] && $values === ['john-doe']) {
                return $johnDoeModel;
            }
        });

        $modelUtil->method('findModelInstanceByPk')->willReturnCallback(function ($table, $pk) use ($johnDoeModel, $janeDoeModel) {
            if ('tl_test' === $table && '1' === $pk) {
                return $johnDoeModel;
            }

            if ('tl_test' === $table && '2' === $pk) {
                return $janeDoeModel;
            }
        });

        $this->modelUtil = $modelUtil;
    }

    protected function createReaderConfigElementRegistry()
    {
        $imageElement1 = $this->mockClassWithProperties(ReaderConfigElementModel::class, [
            'type' => ReaderConfigElement::TYPE_IMAGE,
            'imageSelectorField' => 'addImage1',
            'imageField' => 'singleSRC1',
            'placeholderImageMode' => ReaderConfigElement::PLACEHOLDER_IMAGE_MODE_GENDERED,
            'genderField' => 'gender',
            'placeholderImageFemale' => 'female',
            'placeholderImage' => 'male',
        ]);

        $imageElement2 = $this->mockClassWithProperties(ReaderConfigElementModel::class, [
            'type' => ReaderConfigElement::TYPE_IMAGE,
            'imageSelectorField' => 'addImage2',
            'imageField' => 'singleSRC2',
            'placeholderImageMode' => ReaderConfigElement::PLACEHOLDER_IMAGE_MODE_SIMPLE,
            'placeholderImage' => 'male',
            'imgSize' => serialize([1, 1, 1]),
        ]);

        $imageElement3 = $this->mockClassWithProperties(ReaderConfigElementModel::class, [
            'type' => ReaderConfigElement::TYPE_IMAGE,
            'imageSelectorField' => 'addImage3',
            'imageField' => 'singleSRC3',
        ]);

        $this->readerConfigElementRegistry = $this->createConfiguredMock(ReaderConfigElementRegistry::class, ['findBy' => [$imageElement1, $imageElement2, $imageElement3]]);
        $this->readerConfigElementRegistry->method('getElementClassByName')->willReturn(ImageConfigElementType::class);
    }

    protected function createFormUtil()
    {
        $this->formUtil = $this->createMock(FormUtil::class);

        $this->formUtil->method('prepareSpecialValueForOutput')->willReturnCallback(function ($field, $value, $dc) {
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
        });

        $this->formUtil->method('escapeAllHtmlEntities')->willReturnCallback(function ($table, $field, $value) {
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
        });
    }

    protected function createRequest()
    {
        $requestStack = new RequestStack();
        $requestStack->push(new \Symfony\Component\HttpFoundation\Request());

        $backendMatcher = new RequestMatcher('/contao', 'test.com', null, ['192.168.1.0']);
        $frontendMatcher = new RequestMatcher('/index', 'test.com', null, ['192.168.1.0']);

        $scopeMatcher = new ScopeMatcher($backendMatcher, $frontendMatcher);

        $this->request = new Request($this->mockContaoFramework(), $requestStack, $scopeMatcher);
    }

    protected function createAdapters()
    {
        // database
        $databaseAdapter = $this->mockAdapter(['execute', 'prepare', 'limit', 'getFieldNames']);
        $databaseAdapter->method('execute')->willReturnCallback(function ($values, $id = null) {
            if (!isset($id)) {
                return $this->mockClassWithProperties(Database\Result::class, ['total' => 1]);
            }

            if ('1' === $id) {
                return $this->mockClassWithProperties(Database\Result::class, ['numRows' => 1, 'id' => '1']);
            }

            return $this->mockClassWithProperties(Database\Result::class, ['numRows' => 0]);
        });
        $databaseAdapter->method('prepare')->willReturn($databaseAdapter);
        $limitAdapter = $this->mockAdapter(['execute']);
        $limitAdapter->method('execute')->willReturnCallback(function ($values, $id = null) {
            return $this->mockClassWithProperties(Database\Result::class, ['numRows' => 1, 'id' => '1']);
        });
        $databaseAdapter->method('limit')->willReturn($limitAdapter);
        $databaseAdapter->method('getFieldNames')->willReturnCallback(function ($strTable) {
            $arrNames = [];
            $arrFields = $GLOBALS['TL_DCA'][$strTable]['fields'];

            foreach ($arrFields as $arrField => $arrValue) {
                $arrNames[] = $arrField;
            }

            return $arrNames;
        });

        // model
        $modelAdapter = $this->mockAdapter(['getClassFromTable']);
        $modelAdapter->method('getClassFromTable')->willReturn(Model::class);

        // files
        $filesAdapter = $this->mockAdapter([
            'findByUuid',
        ]);
        $filesAdapter->method('findByUuid')->willReturnCallback(function ($uuid) {
            switch ($uuid) {
                case 'default':
                    return $this->mockClassWithProperties(FilesModel::class, [
                        'path' => 'data/image.png',
                    ]);

                    break;

                case 'female':
                    return $this->mockClassWithProperties(FilesModel::class, [
                        'path' => 'data/female.png',
                    ]);

                    break;

                case 'male':
                    return $this->mockClassWithProperties(FilesModel::class, [
                        'path' => 'data/male.png',
                    ]);

                    break;
            }
        });

        $this->framework = $this->mockContaoFramework([
            FilesModel::class => $filesAdapter,
            Model::class => $modelAdapter,
        ]);

        $this->framework->method('createInstance')->willReturnCallback(function ($class) use ($databaseAdapter) {
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
        });
    }
}
