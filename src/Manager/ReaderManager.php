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
use Contao\ModuleModel;
use Contao\StringUtil;
use Contao\System;
use HeimrichHannot\EntityFilterBundle\Backend\EntityFilter;
use HeimrichHannot\FileCredit\FilesModel;
use HeimrichHannot\ListBundle\Module\ModuleList;
use HeimrichHannot\ReaderBundle\Backend\ReaderConfig;
use HeimrichHannot\ReaderBundle\Backend\ReaderConfigElement;
use HeimrichHannot\ReaderBundle\Model\ReaderConfigElementModel;
use HeimrichHannot\ReaderBundle\Model\ReaderConfigModel;
use HeimrichHannot\ReaderBundle\Registry\ReaderConfigElementRegistry;
use HeimrichHannot\ReaderBundle\Registry\ReaderConfigRegistry;
use HeimrichHannot\Request\Request;
use HeimrichHannot\UtilsBundle\Driver\DC_Table_Utils;
use HeimrichHannot\UtilsBundle\Form\FormUtil;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;
use HeimrichHannot\UtilsBundle\Url\UrlUtil;

class ReaderManager
{
    /** @var ContaoFrameworkInterface */
    protected $framework;

    /** @var ReaderConfigModel */
    protected $readerConfig;

    /** @var EntityFilter */
    protected $entityFilter;

    /** @var ReaderConfigRegistry */
    protected $readerConfigRegistry;

    /** @var ReaderConfigElementRegistry */
    protected $readerConfigElementRegistry;

    /** @var ModelUtil */
    protected $modelUtil;

    /** @var UrlUtil */
    protected $urlUtil;

    /** @var FormUtil */
    protected $formUtil;

    /** @var \Twig_Environment */
    protected $twig;

    /** @var Model */
    protected $item;

    /** @var DataContainer */
    protected $dc;

    /** @var array */
    protected $moduleData;

    public function __construct(
        ContaoFrameworkInterface $framework,
        EntityFilter $entityFilter,
        ReaderConfigRegistry $readerConfigRegistry,
        ReaderConfigElementRegistry $readerConfigElementRegistry,
        ModelUtil $modelUtil,
        UrlUtil $urlUtil,
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
        $this->twig = $twig;
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
                || $readerConfig->invertPublishedField && $item->{$readerConfig->publishedField}) {
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

    public function createDataContainerFromItem()
    {
        $this->dc = DC_Table_Utils::createFromModel($this->item);

        return $this->dc;
    }

    public function checkPermission()
    {
        $readerConfig = $this->readerConfig;
        $allowed = true;

        if ($readerConfig->addShowConditions) {
            $itemConditions = StringUtil::deserialize($readerConfig->showFieldConditions, true);

            if (!empty($itemConditions)) {
                list($whereCondition, $values) = $this->entityFilter->computeSqlCondition($itemConditions, $readerConfig->dataContainer);

                $result = Database::getInstance()->prepare("SELECT * FROM $readerConfig->dataContainer WHERE ($whereCondition) AND id=".$this->item->id)->execute($values);

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
            list($whereCondition, $values) = $this->entityFilter->computeSqlCondition($itemConditions, $readerConfig->dataContainer);

            $result = Database::getInstance()->prepare("SELECT * FROM $readerConfig->dataContainer WHERE ($whereCondition) AND id=".$this->item->id)->execute($values);

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
            $pageTitle = preg_replace_callback('@%([^%]+)%@i', function (array $matches) use ($item) {
                return $item->{$matches[1]};
            }, $readerConfig->pageTitleFieldPattern);

            $this->modifyPageTitle($pageTitle);
        }
    }

    public function prepareItem(array $item): array
    {
        $readerConfig = $this->readerConfig;

        $result = [];

        $dca = &$GLOBALS['TL_DCA'][$readerConfig->dataContainer];
        $dc = $this->dc;

        $fields = $readerConfig->limitFormattedFields ? StringUtil::deserialize($readerConfig->formattedFields, true) : array_keys($dca['fields']);

        $result['raw'] = $item;

        foreach ($fields as $field) {
            $dc->field = $field;
            $value = $item[$field];

            if (is_array($dca['fields'][$field]['load_callback'])) {
                foreach ($dca['fields'][$field]['load_callback'] as $callback) {
                    $instance = System::importStatic($callback[0]);
                    $value = $instance->{$callback[1]}($value, $dc);
                }
            }

            $result['formatted'][$field] = $this->formUtil->prepareSpecialValueForOutput($field, $value, $dc);

            // anti-xss: escape everything besides some tags
            $result['formatted'][$field] = $this->formUtil->escapeAllHtmlEntities($readerConfig->dataContainer, $field, $result['formatted'][$field]);
        }

        // add the missing field's raw values (these should always be inserted completely)
        foreach (array_keys($dca['fields']) as $field) {
            if (isset($result['raw'][$field])) {
                continue;
            }

            $value = $item[$field];

            if (is_array($dca['fields'][$field]['load_callback'])) {
                foreach ($dca['fields'][$field]['load_callback'] as $callback) {
                    $instance = System::importStatic($callback[0]);
                    $value = $instance->{$callback[1]}($value, $dc);
                }
            }

            // add raw value
            $result['raw'][$field] = $value;
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

        $this->addDataToTemplate($item, $templateData, $readerConfig);

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
            || null === ($readerConfig = $this->readerConfigRegistry->findByPk($readerConfigId))) {
            throw new \Exception(sprintf('The module %s has no valid reader config. Please set one.', $this->id));
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

    public function setItem(Model $item)
    {
        $this->item = $item;
    }

    protected function retrieveItemByAutoItem()
    {
        $readerConfig = $this->readerConfig;
        $item = null;

        if (Config::get('useAutoItem') && ($autoItem = Request::getGet('auto_item'))) {
            $field = $readerConfig->itemRetrievalAutoItemField;

            // try to find by a certain field (likely alias)
            $item = $this->modelUtil->findOneModelInstanceBy($readerConfig->dataContainer, [
                $field.'=?',
            ], [
                $autoItem,
            ]);

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
            list($whereCondition, $values) = $this->entityFilter->computeSqlCondition($itemConditions, $readerConfig->dataContainer);

            $database = $this->framework->createInstance(Database::class);

            $result = $database->prepare("SELECT * FROM $readerConfig->dataContainer WHERE ($whereCondition)")->limit(1)->execute($values);

            if ($result->numRows > 0) {
                $item = $this->modelUtil->findModelInstanceByPk($readerConfig->dataContainer, $result->id);
            }
        }

        return $item;
    }

    protected function modifyPageTitle(string $pageTitle)
    {
        global $objPage;

        $objPage->pageTitle = strip_tags(\StringUtil::stripInsertTags($pageTitle));
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

    protected function addDataToTemplate(array $item, array &$templateData, ReaderConfigModel $readerConfig)
    {
        $readerConfigElements = $this->readerConfigElementRegistry->findBy(['pid'], [$readerConfig->id]);

        if (null !== $readerConfigElements) {
            foreach ($readerConfigElements as $readerConfigElement) {
                switch ($readerConfigElement->type) {
                    case ReaderConfigElement::TYPE_IMAGE:
                        $this->addImagesToTemplate($item, $templateData, $readerConfigElement);
                        break;
                    case ReaderConfigElement::TYPE_LIST:
                        $this->addListToTemplate($item, $templateData, $readerConfigElement);
                        break;
                    default:
                        break;
                }
            }
        }
    }

    protected function addListToTemplate(array $item, array &$templateData, ReaderConfigElementModel $listReaderConfigElement)
    {
        $module = ModuleModel::findById($listReaderConfigElement->listModule);

        if (null === $module) {
            return;
        }

        $listModule = new ModuleList($module);
        $filterConfig = $listModule->getFilterConfig();
        $filter = \Contao\StringUtil::deserialize($listReaderConfigElement->initialFilter, true);

        if (!isset($filter[0]['filterElement']) || !isset($filter[0]['selector'])) {
            return;
        }

        $filterConfig->addContextualValue($filter[0]['filterElement'], $item['raw'][$filter[0]['selector']]);
        $filterConfig->initQueryBuilder();
        $templateData['list'][$listReaderConfigElement->listName] = $listModule->generate();
    }

    protected function addImagesToTemplate(array $item, array &$templateData, ReaderConfigElementModel $imageReaderConfigElement)
    {
        $image = null;

        if ($item['raw'][$imageReaderConfigElement->imageSelectorField] && $item['raw'][$imageReaderConfigElement->imageField]) {
            $imageSelectorField = $imageReaderConfigElement->imageSelectorField;
            $image = $item['raw'][$imageReaderConfigElement->imageField];
            $imageField = $imageReaderConfigElement->imageField;
        } elseif ($imageReaderConfigElement->placeholderImageMode) {
            $imageSelectorField = $imageReaderConfigElement->imageSelectorField;
            $imageField = $imageReaderConfigElement->imageField;

            switch ($imageReaderConfigElement->placeholderImageMode) {
                case ReaderConfigElement::PLACEHOLDER_IMAGE_MODE_GENDERED:
                    if ($item['raw'][$imageReaderConfigElement->genderField] == 'female') {
                        $image = $imageReaderConfigElement->placeholderImageFemale;
                    } else {
                        $image = $imageReaderConfigElement->placeholderImage;
                    }
                    break;
                case ReaderConfigElement::PLACEHOLDER_IMAGE_MODE_SIMPLE:
                    $image = $imageReaderConfigElement->placeholderImage;
                    break;
            }
        } else {
            return;
        }

        $imageModel = FilesModel::findByUuid($image);

        if (null !== $imageModel
            && is_file(System::getContainer()->get('huh.utils.container')->getProjectDir().'/'.$imageModel->path)) {
            $imageArray = $item['raw'];

            // Override the default image size
            if ('' != $imageReaderConfigElement->imgSize) {
                $size = StringUtil::deserialize($imageReaderConfigElement->imgSize);

                if ($size[0] > 0 || $size[1] > 0 || is_numeric($size[2])) {
                    $imageArray['size'] = $imageReaderConfigElement->imgSize;
                }
            }

            $imageArray[$imageField] = $imageModel->path;
            $templateData['images'][$imageField] = [];

            System::getContainer()->get('huh.utils.image')->addToTemplateData($imageField, $imageSelectorField, $templateData['images'][$imageField], $imageArray, null, null, null, $imageModel);
        }
    }
}
