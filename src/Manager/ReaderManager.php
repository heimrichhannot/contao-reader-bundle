<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ReaderBundle\Manager;

use Contao\Config;
use Contao\CoreBundle\Exception\RedirectResponseException;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\Database;
use Contao\DataContainer;
use Contao\Model;
use Contao\StringUtil;
use Contao\System;
use HeimrichHannot\EntityFilterBundle\Backend\EntityFilter;
use HeimrichHannot\ReaderBundle\Backend\ReaderConfig;
use HeimrichHannot\ReaderBundle\ConfigElementType\ConfigElementType;
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

class ReaderManager
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
     * @var Model
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
     * @return Model|null
     */
    public function retrieveItem()
    {
        $readerConfig = $this->readerConfig;
        $item = null;

        switch ($readerConfig->itemRetrievalMode) {
            case ReaderConfig::ITEM_RETRIEVAL_MODE_AUTO_ITEM:
                $item = $this->retrieveItemByAutoItem();
                break;
            case ReaderConfig::ITEM_RETRIEVAL_MODE_FIELD_CONDITIONS:
                $item = $this->retrieveItemByFieldConditions();
                break;
        }

        // hide unpublished items?
        if (null !== $item && $readerConfig->hideUnpublishedItems) {
            if (!$readerConfig->invertPublishedField && !$item->{$readerConfig->publishedField}
                || $readerConfig->invertPublishedField && $item->{$readerConfig->publishedField}
            ) {
                return null;
            }
        }

        $this->item = $item;

        return $item;
    }

    public function triggerOnLoadCallbacks()
    {
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
     * @codeCoverageIgnore
     *
     * @return DataContainer
     */
    public function createDataContainerFromItem()
    {
        $this->dc = DC_Table_Utils::createFromModel($this->item);

        return $this->dc;
    }

    public function setDataContainer(DataContainer $dc)
    {
        $this->dc = $dc;
    }

    public function checkPermission()
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

                $result = $this->database->prepare(
                    "SELECT * FROM $readerConfig->dataContainer WHERE ($whereCondition) AND $readerConfig->dataContainer.id=?"
                )->execute($values, $this->item->id);

                if ($result->numRows < 1) {
                    $allowed = false;
                }
            }
        }

        return $allowed;
    }

    public function doFieldDependentRedirect()
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

            $result = $this->database->prepare(
                "SELECT * FROM $readerConfig->dataContainer WHERE ($whereCondition) AND $readerConfig->dataContainer.id=?"
            )->execute($values, $this->item->id);

            $redirect = $result->numRows > 0;
        }

        if ($redirect) {
            $jumpTo = $this->urlUtil->getJumpToPageObject($readerConfig->fieldDependentJumpTo);

            if (null !== $jumpTo) {
                throw new RedirectResponseException('/'.$jumpTo->getFrontendUrl());
            }
        }
    }

    public function setPageTitle()
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

    public function prepareItem(array $item): array
    {
        $readerConfig = $this->readerConfig;

        $result = [];

        $dca = &$GLOBALS['TL_DCA'][$readerConfig->dataContainer];
        $dc = $this->dc;

        // add raw values
        $result['raw'] = [];

        foreach (array_keys($dca['fields']) as $field) {
            $value = $item[$field];

            $dc->field = $field;

            if (isset($dca['fields'][$field]['load_callback']) && is_array($dca['fields'][$field]['load_callback'])) {
                foreach ($dca['fields'][$field]['load_callback'] as $callback) {
                    $instance = System::importStatic($callback[0]);
                    $value = $instance->{$callback[1]}($value, $dc);
                }
            }

            // add raw value
            $result['raw'][$field] = $value;
        }

        $fields = $readerConfig->limitFormattedFields ? StringUtil::deserialize(
            $readerConfig->formattedFields,
            true
        ) : array_keys($dca['fields']);

        foreach ($fields as $field) {
            $dc->field = $field;
            $value = $result['raw'][$field];

            $result['formatted'][$field] = $this->formUtil->prepareSpecialValueForOutput(
                $field,
                $value,
                $dc
            );

            // anti-xss: escape everything besides some tags
            $result['formatted'][$field] = $this->formUtil->escapeAllHtmlEntities(
                $readerConfig->dataContainer,
                $field,
                $result['formatted'][$field]
            );
        }

        return $result;
    }

    public function parseItem(array $item): string
    {
        $readerConfig = $this->readerConfig;

        $templateData = $item['formatted'];

        foreach ($item as $field => $value) {
            $templateData[$field] = $value;
        }

        $templateData['dataContainer'] = $readerConfig->dataContainer;

        $this->applyReaderConfigElements($item, $templateData, $readerConfig);

        $templateData['module'] = $this->moduleData;

        $this->modifyItemTemplateData($templateData, $item);

        $twig = $this->twig;

        $twig->hasExtension('\Twig_Extensions_Extension_Text') ?: $twig->addExtension(new \Twig_Extensions_Extension_Text());
        $twig->hasExtension('\Twig_Extensions_Extension_Intl') ?: $twig->addExtension(new \Twig_Extensions_Extension_Intl());
        $twig->hasExtension('\Twig_Extensions_Extension_Array') ?: $twig->addExtension(new \Twig_Extensions_Extension_Array());
        $twig->hasExtension('\Twig_Extensions_Extension_Date') ?: $twig->addExtension(new \Twig_Extensions_Extension_Date());

        return $twig->render($this->getItemTemplateByName($readerConfig->itemTemplate ?: 'default'), $templateData);
    }

    public function getReaderConfig()
    {
        $readerConfigId = $this->moduleData['readerConfig'];

        if (!$readerConfigId
            || null === ($readerConfig = $this->readerConfigRegistry->findByPk($readerConfigId))
        ) {
            throw new \Exception(sprintf('The module %s has no valid reader config. Please set one.', $this->moduleData['id']));
        }

        return $readerConfig;
    }

    public function setReaderConfig(ReaderConfigModel $readerConfig)
    {
        $this->readerConfig = $readerConfig;
    }

    public function setModuleData(array $moduleData)
    {
        $this->moduleData = $moduleData;
    }

    public function getModuleData()
    {
        return $this->moduleData;
    }

    public function setItem(Model $item)
    {
        $this->item = $item;
    }

    protected function modifyPageTitle(string $pageTitle)
    {
        global $objPage;

        $objPage->pageTitle = strip_tags(\StringUtil::stripInsertTags($pageTitle));
    }

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

            $result = $this->database->prepare(
                "SELECT * FROM $readerConfig->dataContainer WHERE ($whereCondition)"
            )->limit(1)->execute($values);

            if ($result->numRows > 0) {
                $item = $this->modelUtil->findModelInstanceByPk($readerConfig->dataContainer, $result->id);
            }
        }

        return $item;
    }

    protected function modifyItemTemplateData(array &$templateData, array $item): void
    {
    }

    protected function getItemTemplateByName($name)
    {
        $config = System::getContainer()->getParameter('huh.reader');
        $templates = $config['reader']['templates']['item'];

        foreach ($templates as $template) {
            if ($template['name'] == $name) {
                return $template['template'];
            }
        }

        return null;
    }

    protected function applyReaderConfigElements(array $item, array &$templateData, ReaderConfigModel $readerConfig)
    {
        $readerConfigElements = $this->readerConfigElementRegistry->findBy(['pid'], [$readerConfig->id]);

        if (null !== $readerConfigElements) {
            foreach ($readerConfigElements as $readerConfigElement) {
                $class = $this->getConfigElementTypeClassByName($readerConfigElement->type);

                if ($class && class_exists($class)) {
                    /**
                     * @var ConfigElementType
                     */
                    $type = $this->framework->createInstance($class, [$this->framework]);

                    $type->addToTemplateData($item, $templateData, $readerConfigElement);
                }
            }
        }
    }

    protected function getConfigElementTypeClassByName($name)
    {
        $config = System::getContainer()->getParameter('huh.reader');
        $templates = $config['reader']['config_element_types'];

        foreach ($templates as $template) {
            if ($template['name'] == $name) {
                return $template['class'];
            }
        }
    }
}