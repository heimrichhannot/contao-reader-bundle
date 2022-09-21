<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ReaderBundle\Manager;

use Contao\Config;
use Contao\Controller;
use Contao\CoreBundle\Exception\RedirectResponseException;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\CoreBundle\Routing\ResponseContext\HtmlHeadBag\HtmlHeadBag;
use Contao\CoreBundle\Routing\ResponseContext\ResponseContextAccessor;
use Contao\Database;
use Contao\DataContainer;
use Contao\Date;
use Contao\Input;
use Contao\Model;
use Contao\StringUtil;
use Contao\System;
use Doctrine\DBAL\Query\QueryBuilder;
use HeimrichHannot\EntityFilterBundle\Backend\EntityFilter;
use HeimrichHannot\FilterBundle\Config\FilterConfig;
use HeimrichHannot\FilterBundle\Manager\FilterManager;
use HeimrichHannot\ReaderBundle\Backend\ReaderConfig;
use HeimrichHannot\ReaderBundle\Event\ReaderModifyQueryBuilderEvent;
use HeimrichHannot\ReaderBundle\Event\ReaderModifyRetrievedItemEvent;
use HeimrichHannot\ReaderBundle\Exception\MissingItemClassException;
use HeimrichHannot\ReaderBundle\Item\ItemInterface;
use HeimrichHannot\ReaderBundle\Model\ReaderConfigModel;
use HeimrichHannot\ReaderBundle\QueryBuilder\ReaderQueryBuilder;
use HeimrichHannot\ReaderBundle\Registry\ReaderConfigElementRegistry;
use HeimrichHannot\ReaderBundle\Registry\ReaderConfigRegistry;
use HeimrichHannot\TwigSupportBundle\Filesystem\TwigTemplateLocator;
use HeimrichHannot\UtilsBundle\Driver\DC_Table_Utils;
use HeimrichHannot\UtilsBundle\Form\FormUtil;
use HeimrichHannot\UtilsBundle\Image\ImageUtil;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;
use HeimrichHannot\UtilsBundle\Url\UrlUtil;
use HeimrichHannot\UtilsBundle\Util\Utils;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Twig\Environment;

class ReaderManager implements ReaderManagerInterface, ServiceSubscriberInterface
{
    protected ContaoFramework $framework;

    /**
     * @var FilterManager
     */
    protected $filterManager;

    /**
     * @var FilterConfig
     */
    protected $filterConfig;

    /**
     * @var ReaderConfigModel
     */
    protected $readerConfig;

    /**
     * @var ReaderQueryBuilder
     */
    protected $readerQueryBuilder;

    /**
     * @var EntityFilter
     */
    protected $entityFilter;

    /**
     * @var ReaderConfigRegistry
     */
    protected $readerConfigRegistry;

    /**
     * @var ReaderConfigElementRegistry
     */
    protected $readerConfigElementRegistry;

    /**
     * @var ModelUtil
     */
    protected $modelUtil;

    /**
     * @var UrlUtil
     */
    protected $urlUtil;

    /**
     * @var FormUtil
     */
    protected $formUtil;

    /**
     * @var \Twig\Environment
     */
    protected $twig;

    /**
     * @var ItemInterface
     */
    protected $item;

    /**
     * @var DataContainer
     */
    protected $dc;

    /**
     * @var array
     */
    protected $moduleData;

    /**
     * @var Database
     */
    protected $database;
    /**
     * @var TwigTemplateLocator
     */
    protected $templateLocator;
    /**
     * @var ContainerInterface
     */
    private $container;
    /**
     * @var object|\Symfony\Component\EventDispatcher\ContainerAwareEventDispatcher|\Symfony\Component\HttpKernel\Debug\TraceableEventDispatcher|null
     */
    private $_dispatcher;
    private Utils $utils;

    public function __construct(
        ContainerInterface $container,
        ContaoFramework $framework,
        FilterManager $filterManager,
        ReaderQueryBuilder $readerQueryBuilder,
        ReaderConfigRegistry $readerConfigRegistry,
        ReaderConfigElementRegistry $readerConfigElementRegistry,
        ModelUtil $modelUtil,
        UrlUtil $urlUtil,
        ImageUtil $imageUtil,
        FormUtil $formUtil,
        Environment $twig,
        TwigTemplateLocator $templateLocator,
        Utils $utils
    ) {
        $this->framework = $framework;
        $this->filterManager = $filterManager;
        $this->readerQueryBuilder = $readerQueryBuilder;
        $this->entityFilter = $container->get('huh.entity_filter.backend.entity_filter');
        $this->readerConfigRegistry = $readerConfigRegistry;
        $this->readerConfigElementRegistry = $readerConfigElementRegistry;
        $this->modelUtil = $modelUtil;
        $this->urlUtil = $urlUtil;
        $this->formUtil = $formUtil;
        $this->imageUtil = $imageUtil;
        $this->twig = $twig;
        $this->database = $framework->createInstance(Database::class);
        $this->container = $container;
        $this->_dispatcher = System::getContainer()->get('event_dispatcher');
        $this->templateLocator = $templateLocator;
        $this->utils = $utils;
    }

    /**
     * {@inheritdoc}
     */
    public function retrieveItem(): ?ItemInterface
    {
        // necessary for getSearchablePages hook
        if (\PHP_SAPI === 'cli') {
            return null;
        }

        $readerConfig = $this->getReaderConfig();
        Controller::loadDataContainer($readerConfig->dataContainer);

        // reset since this method might be run more than once
        $this->getQueryBuilder()->resetQueryParts(['where', 'join', 'from']);

        $item = null;

        switch ($readerConfig->itemRetrievalMode) {
            case ReaderConfig::ITEM_RETRIEVAL_MODE_AUTO_ITEM:
                $item = $this->retrieveItemByAutoItem();

                break;

            case ReaderConfig::ITEM_RETRIEVAL_MODE_FIELD_CONDITIONS:
                $item = $this->retrieveItemByFieldConditions();

                break;
        }

        if (null === $item) {
            return null;
        }

        // add fields without sql key in DCA (could have a value by load_callback)
        $itemFields = array_keys($item);

        foreach (array_keys($GLOBALS['TL_DCA'][$readerConfig->dataContainer]['fields']) as $field) {
            if (!\in_array($field, $itemFields)) {
                $item[$field] = null;
            }
        }

        // hide unpublished items?
        if (null !== $item && $readerConfig->hideUnpublishedItems) {
            $isPublished = !$readerConfig->invertPublishedField && $item[$readerConfig->publishedField]
                || $readerConfig->invertPublishedField && !$item[$readerConfig->publishedField];

            if ($isPublished && $readerConfig->addStartAndStop) {
                $time = Date::floorToMinute();

                $isPublished = ('' === $item[$readerConfig->startField] || $item[$readerConfig->startField] <= $time) &&
                    ('' === $item[$readerConfig->stopField] || $item[$readerConfig->stopField] > ($time + 60));
            }

            if (version_compare(VERSION, '4.9', '<')) {
                $isPreview = \defined('BE_USER_LOGGED_IN') && BE_USER_LOGGED_IN === true && Input::cookie('FE_PREVIEW');
            } else {
                $isPreview = \defined('BE_USER_LOGGED_IN') && BE_USER_LOGGED_IN === true;
            }

            if ($isPreview) {
                $isPublished = true;
            }

            if (!$isPublished) {
                return null;
            }
        }

        $this->dc = DC_Table_Utils::createFromModelData($item, $this->readerConfig->dataContainer);

        $itemClass = $this->getItemClassByName($this->readerConfig->item ?: 'default');

        if (!$itemClass) {
            throw new MissingItemClassException('No reader item class found for '.$this->readerConfig->item.', please check your configuration!');
        }

        $reflection = new \ReflectionClass($itemClass);

        if (!$reflection->implementsInterface(ItemInterface::class)) {
            throw new \Exception(sprintf('Item class %s must implement %s', $itemClass, ItemInterface::class));
        }

        if (!$reflection->implementsInterface(\JsonSerializable::class)) {
            throw new \Exception(sprintf('Item class %s must implement %s', $itemClass, \JsonSerializable::class));
        }

        $this->item = new $itemClass($this, $item);

        return $this->item;
    }

    /**
     * {@inheritdoc}
     */
    public function triggerOnLoadCallbacks(): void
    {
        if (null === $this->dc) {
            $this->retrieveItem();
        }

        $table = $this->readerConfig->dataContainer;

        // Only call onload_callbacks with *true* as third argument
        if (\is_array($GLOBALS['TL_DCA'][$table]['config']['onload_callback'])) {
            foreach ($GLOBALS['TL_DCA'][$table]['config']['onload_callback'] as $callback) {
                if (\is_array($callback) && isset($callback[2]) && $callback[2]) {
                    $instance = System::importStatic($callback[0]);
                    $instance->{$callback[1]}($this->dc);
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setDataContainer(DataContainer $dc): void
    {
        $this->dc = $dc;
    }

    /**
     * {@inheritdoc}
     */
    public function getDataContainer(): ?DataContainer
    {
        return $this->dc;
    }

    /**
     * {@inheritdoc}
     */
    public function checkPermission(): bool
    {
        $readerConfig = $this->readerConfig;
        $allowed = true;

        if ($readerConfig->addShowConditions) {
            $itemConditions = StringUtil::deserialize($readerConfig->showFieldConditions, true);

            if (!empty($itemConditions)) {
                [$whereCondition, $values] = $this->entityFilter->computeSqlCondition($itemConditions, $readerConfig->dataContainer);

                $statement = $this->database->prepare("SELECT * FROM $readerConfig->dataContainer WHERE ($whereCondition) AND $readerConfig->dataContainer.id=?");

                $result = \call_user_func_array([$statement, 'execute'], array_merge($values, [$this->item->id]));

                if ($result->numRows < 1) {
                    $allowed = false;
                }
            }
        }

        return $allowed;
    }

    /**
     * {@inheritdoc}
     */
    public function doFieldDependentRedirect(): void
    {
        $readerConfig = $this->readerConfig;
        $redirect = false;

        if (!$readerConfig->addFieldDependentRedirect || !$readerConfig->fieldDependentJumpTo) {
            return;
        }

        $itemConditions = StringUtil::deserialize($readerConfig->redirectFieldConditions, true);

        if (!empty($itemConditions)) {
            [$whereCondition, $values] = $this->entityFilter->computeSqlCondition($itemConditions, $readerConfig->dataContainer);

            $statement = $this->database->prepare("SELECT * FROM $readerConfig->dataContainer WHERE ($whereCondition) AND $readerConfig->dataContainer.id=?");

            $result = \call_user_func_array([$statement, 'execute'], array_merge($values, [$this->item->id]));

            $redirect = $result->numRows > 0;
        }

        if ($redirect) {
            $jumpTo = $this->urlUtil->getJumpToPageObject($readerConfig->fieldDependentJumpTo);

            if (null !== $jumpTo) {
                throw new RedirectResponseException('/'.$jumpTo->getFrontendUrl());
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setPageTitle(): void
    {
        $readerConfig = $this->readerConfig;
        $item = $this->item;

        if ($readerConfig->setPageTitleByField && $readerConfig->pageTitleFieldPattern) {
            $pageTitle = preg_replace_callback('@%([^%]+)%@i', function (array $matches) use ($item) {
                return $item->{$matches[1]};
            }, $readerConfig->pageTitleFieldPattern);

            $this->modifyPageTitle($pageTitle);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setMetaDescription(): void
    {
        $readerConfig = $this->readerConfig;
        $item = $this->item;

        if ($readerConfig->setMetaDescriptionByField && $readerConfig->metaDescriptionFieldPattern) {
            $description = preg_replace_callback('@%([^%]+)%@i', function (array $matches) use ($item) {
                return $item->{$matches[1]};
            }, $readerConfig->metaDescriptionFieldPattern);

            $description = Controller::replaceInsertTags($description, false);
            $description = strip_tags($description);
            $description = str_replace("\n", ' ', $description);
            $description = \StringUtil::substr($description, 320);

            $this->container->get('huh.head.tag.meta_description')->setContent(trim($description));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setHeadTags(): void
    {
        $pageModel = $this->utils->request()->getCurrentPageModel();

        if (!$pageModel) {
            return;
        }

        $item = $this->item;
        $tags = StringUtil::deserialize($this->readerConfig->headTags, true);

        foreach ($tags as $config) {
            if (!isset($config['service'])) {
                continue;
            }

            $service = $config['service'];
            $pattern = $config['pattern'] ?? '';

            $value = preg_replace_callback('@%([^%]+)%@i', function (array $matches) use ($item) {
                return $this->formUtil->prepareSpecialValueForOutput($matches[1], $item->{$matches[1]}, $this->getDataContainer());
            }, $pattern);

            switch ($service) {
                case 'title':
                case 'huh.head.tag.title':
                    $pageModel->pageTitle = $value;

                    break;

                case 'meta_description':
                case 'huh.head.tag.meta_description':
                    $pageModel->description = $value;

                    break;
            }
        }
    }

    public function setCanonicalLink()
    {
        $detailsUrl = $this->getItem()->getDetailsUrl(true, true);

        if (!$detailsUrl) {
            return;
        }

        $pageModel = $this->utils->request()->getCurrentPageModel();

        if (!$pageModel) {
            return;
        }

        if (!class_exists(HtmlHeadBag::class) || !$this->container->has(ResponseContextAccessor::class)) {
            return;
        }
        $contextAccessor = $this->container->get(ResponseContextAccessor::class);

        if (!$contextAccessor->getResponseContext()->has(HtmlHeadBag::class)) {
            return;
        }

        /* @var HtmlHeadBag $htmlHeadBag */
        $htmlHeadBag = $contextAccessor->getResponseContext()->get(HtmlHeadBag::class);

        if ($this->container->has('request_stack') && ($request = $this->container->get('request_stack')->getCurrentRequest())) {
            $pageModel->enableCanonical = true;
            $htmlHeadBag->setCanonicalUri($request->getSchemeAndHttpHost().'/'.ltrim($detailsUrl, '/'));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getFilterConfig(): ?FilterConfig
    {
        // Caching
        if (null !== $this->filterConfig && $this->filterConfig->getFilter()['id'] === $this->readerConfig->filter) {
            return $this->filterConfig;
        }

        if ($this->readerConfig->filter > 0) {
            $this->filterConfig = $this->filterManager->findById($this->readerConfig->filter);
        }

        return $this->filterConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function getQueryBuilder(): QueryBuilder
    {
        $filterConfig = $this->getFilterConfig();

        return null !== $filterConfig ? $filterConfig->getQueryBuilder() : $this->readerQueryBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function getReaderConfig(): ReaderConfigModel
    {
        // Caching
        if (null !== $this->readerConfig && $this->readerConfig->id === $this->moduleData['readerConfig']) {
            return $this->readerConfig;
        }

        $readerConfigId = $this->moduleData['readerConfig'];

        if (!$readerConfigId || null === ($readerConfig = $this->readerConfigRegistry->findByPk($readerConfigId))) {
            throw new \Exception(sprintf('The module %s has no valid reader config. Please set one.', $this->moduleData['id']));
        }

        // compute reader config respecting the inheritance hierarchy
        $readerConfig = $this->readerConfigRegistry->computeReaderConfig($readerConfigId);

        $this->readerConfig = $readerConfig;

        return $readerConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function setReaderConfig(ReaderConfigModel $readerConfig): void
    {
        $this->readerConfig = $readerConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function setModuleData(array $moduleData): void
    {
        $this->moduleData = $moduleData;
    }

    /**
     * {@inheritdoc}
     */
    public function getModuleData(): array
    {
        return $this->moduleData;
    }

    /**
     * {@inheritdoc}
     */
    public function setItem(ItemInterface $item): void
    {
        $this->item = $item;
    }

    /**
     * {@inheritdoc}
     */
    public function getItem(): ItemInterface
    {
        return $this->item;
    }

    /**
     * {@inheritdoc}
     */
    public function getItemClassByName(string $name)
    {
        $config = $this->container->getParameter('huh.reader');

        if (!isset($config['reader']['items'])) {
            return null;
        }

        $items = $config['reader']['items'];

        foreach ($items as $item) {
            if ($item['name'] == $name) {
                return class_exists($item['class']) ? $item['class'] : null;
            }
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getItemTemplateByName(string $name)
    {
        $config = $this->container->getParameter('huh.reader');

        if (!isset($config['reader']['templates']['item'])) {
            return $this->templateLocator->getTemplatePath($name);
        }

        $templates = $config['reader']['templates']['item'];

        foreach ($templates as $template) {
            if ($template['name'] == $name) {
                return $template['template'];
            }
        }

        return $this->templateLocator->getTemplatePath($name);
    }

    /**
     * {@inheritdoc}
     */
    public function getReaderConfigElementRegistry(): ReaderConfigElementRegistry
    {
        return $this->readerConfigElementRegistry;
    }

    /**
     * {@inheritdoc}
     */
    public function getTwig(): Environment
    {
        return $this->twig;
    }

    /**
     * {@inheritdoc}
     */
    public function getFramework(): ContaoFrameworkInterface
    {
        return $this->framework;
    }

    /**
     * {@inheritdoc}
     */
    public function getFormUtil(): FormUtil
    {
        return $this->formUtil;
    }

    public function isDcMultilingualActive(ReaderConfigModel $readerConfig, array $dca, string $table)
    {
        return $readerConfig->addDcMultilingualSupport
               && System::getContainer()->get('huh.utils.dca')->isDcMultilingual($table);
    }

    public function isDcMultilingualUtilsActive(ReaderConfigModel $readerConfig, array $dca, string $table)
    {
        return $GLOBALS['TL_LANGUAGE'] !== $dca['config']['fallbackLang']
            && $readerConfig->addDcMultilingualSupport
            && $this->utils->container()->isBundleActive('HeimrichHannot\DcMultilingualUtilsBundle\ContaoDcMultilingualUtilsBundle')
            && System::getContainer()->get('huh.utils.dca')->isDcMultilingual($table);
    }

    public function isMultilingualFieldsActive(ReaderConfigModel $readerConfig, string $table)
    {
        if (!$readerConfig->addMultilingualFieldsSupport) {
            return false;
        }

        $config = System::getContainer()->getParameter('huh_multilingual_fields');

        return isset($config['data_containers'][$table]);
    }

    public function isNonFallbackLanguage(array $dca)
    {
        return $GLOBALS['TL_LANGUAGE'] !== $dca['config']['fallbackLang'];
    }

    public function addMultilingualFieldsSupport(ReaderConfigModel $readerConfig, QueryBuilder $queryBuilder)
    {
        $dca = &$GLOBALS['TL_DCA'][$readerConfig->dataContainer];
        $dbFields = $this->database->getFieldNames($readerConfig->dataContainer);

        $fallbackLanguage = System::getContainer()->getParameter('huh_multilingual_fields')['fallback_language'];

        if ($GLOBALS['TL_LANGUAGE'] !== $fallbackLanguage) {
            // compute fields
            $fieldNames = [];

            foreach ($dca['fields'] as $field => $data) {
                if (!isset($data['sql']) || isset($data['eval']['translatedField'])) {
                    continue;
                }

                if (isset($data['eval']['isTranslatedField'])) {
                    $selectorField = $data['eval']['translationConfig'][$GLOBALS['TL_LANGUAGE']]['selector'];
                    $translationField = $data['eval']['translationConfig'][$GLOBALS['TL_LANGUAGE']]['field'];

                    $fieldNames[] = "IF($readerConfig->dataContainer.$selectorField=1, $readerConfig->dataContainer.$translationField, $readerConfig->dataContainer.$field) AS '$field'";
                } else {
                    $fieldNames[] = $readerConfig->dataContainer.'.'.$field;
                }
            }

            $fields = implode(', ', $fieldNames);
        } else {
            $fields = implode(', ', array_map(function ($field) use ($readerConfig) {
                return $readerConfig->dataContainer.'.'.$field;
            }, $dbFields));
        }

        return $fields;
    }

    public static function getSubscribedServices()
    {
        $services = [
            'request_stack' => RequestStack::class,
        ];

        if (class_exists(ResponseContextAccessor::class)) {
            $services[] = '?'.ResponseContextAccessor::class;
        }

        return $services;
    }

    /**
     * Modify current page title.
     */
    protected function modifyPageTitle(string $pageTitle)
    {
        global $objPage;

        $objPage->pageTitle = strip_tags(StringUtil::stripInsertTags($pageTitle));
    }

    /**
     * Retrieve current item by auto_item request parameter.
     *
     * @return mixed|null
     */
    protected function retrieveItemByAutoItem()
    {
        $readerConfig = $this->readerConfig;

        if ($readerConfig->evaluateFilter && $this->getFilterConfig() && $this->getFilterConfig()->getFilter()['dataContainer'] === $readerConfig->dataContainer) {
            $queryBuilder = $this->getFilterConfig()
                ->initQueryBuilder([], FilterConfig::QUERY_BUILDER_MODE_INITIAL_ONLY, true)
                ->setMaxResults(1);
        } else {
            $queryBuilder = $this->getQueryBuilder()
                ->from($readerConfig->dataContainer)
                ->setMaxResults(1);
        }

        $item = null;

        $dca = &$GLOBALS['TL_DCA'][$readerConfig->dataContainer];

        if (Config::get('useAutoItem') && ($autoItem = Input::get('auto_item'))) {
            // tell contao that the auto_item parameter has been used -> else a UnusedArgumentsException is thrown resulting in a 404 response
            Input::get('auto_item');

            $field = $readerConfig->itemRetrievalAutoItemField;

            /* @var Model $adapter */
            $adapter = $this->framework->getAdapter(Model::class);

            if (!($modelClass = $adapter->getClassFromTable($readerConfig->dataContainer))) {
                return $item;
            }

            /* @var Model $model */
            if (null === ($model = $this->framework->getAdapter($modelClass))) {
                return $item;
            }

            if (is_numeric($autoItem) && !$this->utils->string()->startsWith($autoItem, '-')) {
                $queryBuilder->andWhere($queryBuilder->expr()->eq($readerConfig->dataContainer.'.'.$model->getPk(), ':autoItem'));
            } else {
                $queryBuilder->andWhere($queryBuilder->expr()->eq($readerConfig->dataContainer.'.'.$field, ':autoItem'));
            }

            // get the parent record for dc_multilingual-based entities
            if ($this->isDcMultilingualActive($readerConfig, $dca, $readerConfig->dataContainer)) {
                if ($this->isNonFallbackLanguage($dca)) {
                    $instance = $this->database->prepare('SELECT * FROM '.$readerConfig->dataContainer.' WHERE '.$readerConfig->dataContainer.'.'.$field.'=?')->limit(1)->execute($autoItem);

                    if ($instance->numRows > 0) {
                        $langPidField = $dca['config']['langPid'];

                        if ($instance->{$langPidField}) {
                            $instance = $this->database->prepare('SELECT * FROM '.$readerConfig->dataContainer.' WHERE '.$readerConfig->dataContainer.'.'.$model->getPk().'=?')->limit(1)->execute($instance->{$langPidField});

                            if ($instance->numRows > 0) {
                                $autoItem = $instance->{$field};
                            }
                        }
                    }
                } else {
                    $langPidField = $dca['config']['langPid'];

                    $queryBuilder->andWhere("$readerConfig->dataContainer.$langPidField=0");
                }
            }

            $dca = &$GLOBALS['TL_DCA'][$readerConfig->dataContainer];
            $dbFields = $this->database->getFieldNames($readerConfig->dataContainer);

            if ($this->isDcMultilingualActive($readerConfig, $dca, $readerConfig->dataContainer) && $this->isNonFallbackLanguage($dca)) {
                $fields = $this->addDcMultilingualSupport($readerConfig, $queryBuilder);
            } elseif ($this->isMultilingualFieldsActive($readerConfig, $readerConfig->dataContainer)) {
                $fields = $this->addMultilingualFieldsSupport($readerConfig, $queryBuilder);

                $mfConfig = System::getContainer()->getParameter('huh_multilingual_fields');

                // multilingual alias?
                if ($GLOBALS['TL_LANGUAGE'] !== $mfConfig['fallback_language']) {
                    $translatableFields = System::getContainer()->get('HeimrichHannot\MultilingualFieldsBundle\Util\MultilingualFieldsUtil')->getTranslatableFields(
                        $readerConfig->dataContainer
                    );

                    if (\in_array($field, $translatableFields)) {
                        $instance = $this->database->prepare('SELECT * FROM '.$readerConfig->dataContainer.' WHERE '.
                            $readerConfig->dataContainer.'.'.$GLOBALS['TL_LANGUAGE'].'_'.$field.'=? AND '.
                            $readerConfig->dataContainer.'.'.$GLOBALS['TL_LANGUAGE'].'_translate_'.$field.'=1')->limit(1)->execute($autoItem);

                        if ($instance->numRows > 0) {
                            $autoItem = $instance->{$field};

                            // set normal alias
                            Input::setGet('auto_item', $instance->{$field});
                        }
                    }
                }
            } else {
                $fields = implode(', ', array_map(function ($field) use ($readerConfig) {
                    return $readerConfig->dataContainer.'.'.$field;
                }, $dbFields));
            }

            $queryBuilder->setParameter('autoItem', $autoItem);

            $event = $this->_dispatcher->dispatch(new ReaderModifyQueryBuilderEvent($queryBuilder, $this, $readerConfig, $fields), ReaderModifyQueryBuilderEvent::NAME);

            $queryBuilder->select($event->getFields());

            $item = $queryBuilder->execute()->fetch() ?: null;

            $event = $this->_dispatcher->dispatch(new ReaderModifyRetrievedItemEvent($item, $queryBuilder, $this, $readerConfig, $fields), ReaderModifyRetrievedItemEvent::NAME);

            $item = $event->getItem();
        }

        return $item;
    }

    /**
     * Retrieve current item by field conditions.
     *
     * @return mixed|null
     */
    protected function retrieveItemByFieldConditions()
    {
        $readerConfig = $this->readerConfig;
        $queryBuilder = $this->getQueryBuilder()->from($readerConfig->dataContainer)->setMaxResults(1);
        $item = null;

        $itemConditions = StringUtil::deserialize($readerConfig->itemRetrievalFieldConditions, true);

        if (!empty($itemConditions)) {
            $queryBuilder = $this->entityFilter->computeQueryBuilderCondition($queryBuilder, $itemConditions, $readerConfig->dataContainer);

            $dca = &$GLOBALS['TL_DCA'][$readerConfig->dataContainer];
            $dbFields = $this->database->getFieldNames($readerConfig->dataContainer);

            if ($this->isDcMultilingualActive($readerConfig, $dca, $readerConfig->dataContainer) && $this->isNonFallbackLanguage($dca)) {
                $fields = $this->addDcMultilingualSupport($readerConfig, $queryBuilder);
            } elseif ($this->isMultilingualFieldsActive($readerConfig, $readerConfig->dataContainer)) {
                $fields = $this->addMultilingualFieldsSupport($readerConfig, $queryBuilder);
            } else {
                $fields = implode(', ', array_map(function ($field) use ($readerConfig) {
                    return $readerConfig->dataContainer.'.'.$field;
                }, $dbFields));
            }

            $queryBuilder->select($fields);

            $item = $queryBuilder->execute()->fetch() ?: null;
        }

        return $item;
    }

    protected function addDcMultilingualSupport(ReaderConfigModel $readerConfig, QueryBuilder $queryBuilder)
    {
        $dca = &$GLOBALS['TL_DCA'][$readerConfig->dataContainer];

        $suffixedTable = $readerConfig->dataContainer.ReaderManagerInterface::DC_MULTILINGUAL_SUFFIX;

        $queryBuilder->innerJoin($readerConfig->dataContainer, $readerConfig->dataContainer, $suffixedTable, $readerConfig->dataContainer.'.id = '.$suffixedTable.'.'.$dca['config']['langPid'].' AND '.$suffixedTable.'.language = "'.$GLOBALS['TL_LANGUAGE'].'"');

        // compute fields
        $fieldNames = [];

        foreach ($dca['fields'] as $field => $data) {
            if (!isset($data['sql'])) {
                continue;
            }

            if ('*' === $data['eval']['translatableFor'] || $data['eval']['translatableFor'] === $GLOBALS['TL_LANGUAGE']) {
                $fieldNames[] = $suffixedTable.'.'.$field;
            } else {
                $fieldNames[] = $readerConfig->dataContainer.'.'.$field;
            }
        }

        $fields = implode(', ', $fieldNames);

        // add support for dc multilingual utils
        if ($this->isDcMultilingualUtilsActive($readerConfig, $dca, $readerConfig->dataContainer)) {
            if (!$this->utils->container()->isPreviewMode() &&
                isset($dca['config']['langPublished']) && isset($dca['fields'][$dca['config']['langPublished']]) && \is_array($dca['fields'][$dca['config']['langPublished']])) {
                $and = $queryBuilder->expr()->andX();

                if (isset($dca['config']['langStart']) && isset($dca['fields'][$dca['config']['langStart']]) && \is_array($dca['fields'][$dca['config']['langStart']])
                    && isset($dca['config']['langStop'])
                    && isset($dca['fields'][$dca['config']['langStop']])
                    && \is_array($dca['fields'][$dca['config']['langStop']])) {
                    $time = Date::floorToMinute();

                    $orStart = $queryBuilder->expr()->orX($queryBuilder->expr()->eq($suffixedTable.'.'.$dca['config']['langStart'], '""'), $queryBuilder->expr()->lte($suffixedTable.'.'.$dca['config']['langStart'], ':'.$dca['config']['langStart'].'_time'));

                    $and->add($orStart);
                    $queryBuilder->setParameter($dca['config']['langStart'].'_time', $time);

                    $orStop = $queryBuilder->expr()->orX($queryBuilder->expr()->eq($suffixedTable.'.'.$dca['config']['langStop'], '""'), $queryBuilder->expr()->gt($suffixedTable.'.'.$dca['config']['langStop'], ':'.$dca['config']['langStop'].'_time'));

                    $and->add($orStop);
                    $queryBuilder->setParameter($dca['config']['langStop'].'_time', $time + 60);
                }

                $and->add($queryBuilder->expr()->eq($suffixedTable.'.'.$dca['config']['langPublished'], 1));

                $queryBuilder->andWhere($and);
            }
        }

        return $fields;
    }
}
