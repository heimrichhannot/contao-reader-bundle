<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ReaderBundle\Manager;

use Contao\Config;
use Contao\Controller;
use Contao\CoreBundle\Exception\RedirectResponseException;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\Database;
use Contao\DataContainer;
use Contao\StringUtil;
use Contao\System;
use HeimrichHannot\EntityFilterBundle\Backend\EntityFilter;
use HeimrichHannot\ReaderBundle\Backend\ReaderConfig;
use HeimrichHannot\ReaderBundle\Item\ItemInterface;
use HeimrichHannot\ReaderBundle\Model\ReaderConfigModel;
use HeimrichHannot\ReaderBundle\Registry\ReaderConfigElementRegistry;
use HeimrichHannot\ReaderBundle\Registry\ReaderConfigRegistry;
use HeimrichHannot\Request\Request;
use HeimrichHannot\UtilsBundle\Container\ContainerUtil;
use HeimrichHannot\UtilsBundle\Driver\DC_Table_Utils;
use HeimrichHannot\UtilsBundle\Form\FormUtil;
use HeimrichHannot\UtilsBundle\Image\ImageUtil;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;
use HeimrichHannot\UtilsBundle\Url\UrlUtil;

class ReaderManager implements ReaderManagerInterface
{
    /**
     * @var ContaoFrameworkInterface
     */
    protected $framework;

    /**
     * @var ReaderConfigModel
     */
    protected $readerConfig;

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
     * @var ContainerUtil
     */
    protected $containerUtil;

    /**
     * @var \Twig_Environment
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

    public function __construct(
        ContaoFrameworkInterface $framework,
        EntityFilter $entityFilter,
        ReaderConfigRegistry $readerConfigRegistry,
        ReaderConfigElementRegistry $readerConfigElementRegistry,
        ModelUtil $modelUtil,
        UrlUtil $urlUtil,
        ContainerUtil $containerUtil,
        ImageUtil $imageUtil,
        FormUtil $formUtil,
        \Twig_Environment $twig
    ) {
        $this->framework = $framework;
        $this->entityFilter = $entityFilter;
        $this->readerConfigRegistry = $readerConfigRegistry;
        $this->readerConfigElementRegistry = $readerConfigElementRegistry;
        $this->modelUtil = $modelUtil;
        $this->urlUtil = $urlUtil;
        $this->formUtil = $formUtil;
        $this->containerUtil = $containerUtil;
        $this->imageUtil = $imageUtil;
        $this->twig = $twig;
        $this->database = $framework->createInstance(Database::class);
    }

    /**
     * {@inheritdoc}
     */
    public function retrieveItem(): ?ItemInterface
    {
        $readerConfig = $this->getReaderConfig();
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

        // hide unpublished items?
        if (null !== $item && $readerConfig->hideUnpublishedItems) {
            if (!$readerConfig->invertPublishedField && !$item->{$readerConfig->publishedField}
                || $readerConfig->invertPublishedField && $item->{$readerConfig->publishedField}
            ) {
                return null;
            }
        }

        $data = $item->row();
        $this->dc = DC_Table_Utils::createFromModelData($data, $this->readerConfig->dataContainer);

        if (null !== ($itemClass = $this->getItemClassByName($this->readerConfig->item ?: 'default'))) {
            $reflection = new \ReflectionClass($itemClass);

            if (!$reflection->implementsInterface(ItemInterface::class)) {
                throw new \Exception(sprintf('Item class %s must implement %s', $itemClass, ItemInterface::class));
            }

            if (!$reflection->implementsInterface(\JsonSerializable::class)) {
                throw new \Exception(sprintf('Item class %s must implement %s', $itemClass, \JsonSerializable::class));
            }

            $this->item = new $itemClass($this, $data);
        }

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
                list($whereCondition, $values) = $this->entityFilter->computeSqlCondition(
                    $itemConditions,
                    $readerConfig->dataContainer
                );

                $statement = $this->database->prepare(
                    "SELECT * FROM $readerConfig->dataContainer WHERE ($whereCondition) AND $readerConfig->dataContainer.id=?"
                );

                $result = call_user_func_array([$statement, 'execute'], array_merge($values, [$this->item->id]));

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
            list($whereCondition, $values) = $this->entityFilter->computeSqlCondition(
                $itemConditions,
                $readerConfig->dataContainer
            );

            $statement = $this->database->prepare(
                "SELECT * FROM $readerConfig->dataContainer WHERE ($whereCondition) AND $readerConfig->dataContainer.id=?"
            );

            $result = call_user_func_array([$statement, 'execute'], array_merge($values, [$this->item->id]));

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
            $pageTitle = preg_replace_callback(
                '@%([^%]+)%@i',
                function (array $matches) use ($item) {
                    return $item->{$matches[1]};
                },
                $readerConfig->pageTitleFieldPattern
            );

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
            $description = preg_replace_callback(
                '@%([^%]+)%@i',
                function (array $matches) use ($item) {
                    return $item->{$matches[1]};
                },
                $readerConfig->metaDescriptionFieldPattern
            );

            $description = Controller::replaceInsertTags($description, false);
            $description = strip_tags($description);
            $description = str_replace("\n", ' ', $description);
            $description = \StringUtil::substr($description, 320);

            System::getContainer()->get('huh.head.tag.meta_description')->setContent(trim($description));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setHeadTags(): void
    {
        $item = $this->item;
        $tags = StringUtil::deserialize($this->readerConfig->headTags, true);

        foreach ($tags as $config) {
            if (!isset($config['service'])) {
                continue;
            }

            $service = $config['service'];
            $pattern = $config['pattern'] ?? '';

            if (!System::getContainer()->has($service)) {
                continue;
            }

            $value = preg_replace_callback(
                '@%([^%]+)%@i',
                function (array $matches) use ($item) {
                    return System::getContainer()->get('huh.utils.form')->prepareSpecialValueForOutput($matches[1], $item->{$matches[1]}, $this->getDataContainer());
                },
                $pattern
            );

            switch ($service) {
                case 'huh.head.tag.title':
                    global $objPage;
                    $objPage->pageTitle = $value;
                    break;
                default:
                    System::getContainer()->get($service)->setContent($value);
            }
        }
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
        $readerConfig = $this->readerConfigRegistry->computeReaderConfig(
            $readerConfigId
        );

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
        $config = System::getContainer()->getParameter('huh.reader');

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
        $config = System::getContainer()->getParameter('huh.reader');

        if (!isset($config['reader']['templates']['item'])) {
            return null;
        }

        $templates = $config['reader']['templates']['item'];

        foreach ($templates as $template) {
            if ($template['name'] == $name) {
                return $template['template'];
            }
        }

        return null;
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
    public function getTwig(): \Twig_Environment
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

    /**
     * Modify current page title.
     *
     * @param string $pageTitle
     */
    protected function modifyPageTitle(string $pageTitle)
    {
        global $objPage;

        $objPage->pageTitle = strip_tags(\StringUtil::stripInsertTags($pageTitle));
    }

    /**
     * Retrieve current item by auto_item request parameter.
     *
     * @return mixed|null
     */
    protected function retrieveItemByAutoItem()
    {
        $readerConfig = $this->readerConfig;
        $item = null;

        if (Config::get('useAutoItem') && ($autoItem = Request::getGet('auto_item'))) {
            $field = $readerConfig->itemRetrievalAutoItemField;

            // try to find by a certain field (likely alias)
            $item = $this->modelUtil->findOneModelInstanceBy(
                $readerConfig->dataContainer,
                [
                    $readerConfig->dataContainer.'.'.$field.'=?',
                ],
                [
                    $autoItem,
                ]
            );

            // fallback: ID
            if (null === $item) {
                $item = $this->modelUtil->findModelInstanceByPk($readerConfig->dataContainer, $autoItem);
            }
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
        $item = null;

        $itemConditions = StringUtil::deserialize($readerConfig->itemRetrievalFieldConditions, true);

        if (!empty($itemConditions)) {
            list($whereCondition, $values) = $this->entityFilter->computeSqlCondition(
                $itemConditions,
                $readerConfig->dataContainer
            );

            $statement = $this->database->prepare(
                "SELECT * FROM $readerConfig->dataContainer WHERE ($whereCondition)"
            )->limit(1);

            $result = call_user_func_array([$statement, 'execute'], $values);

            if ($result->numRows > 0) {
                $item = $this->modelUtil->findModelInstanceByPk($readerConfig->dataContainer, $result->id);
            }
        }

        return $item;
    }
}
