<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ReaderBundle\Module;

use Contao\Config;
use Contao\Controller;
use Contao\CoreBundle\Exception\PageNotFoundException;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\Database;
use Contao\DataContainer;
use Contao\Environment;
use Contao\FilesModel;
use Contao\Model;
use Contao\ModuleModel;
use Contao\StringUtil;
use Contao\System;
use HeimrichHannot\ReaderBundle\Backend\ReaderConfig;
use HeimrichHannot\ReaderBundle\Backend\ReaderConfigElement;
use HeimrichHannot\ReaderBundle\Model\ReaderConfigModel;
use HeimrichHannot\Request\Request;
use HeimrichHannot\StatusMessages\StatusMessage;
use HeimrichHannot\UtilsBundle\Driver\DC_Table_Utils;
use Patchwork\Utf8;
use Symfony\Component\Translation\Translator;

class ModuleReader extends \Contao\Module
{
    protected $strTemplate = 'mod_reader';

    /** @var ContaoFramework */
    protected $framework;

    /** @var Translator */
    protected $translator;

    /** @var ReaderConfigModel */
    protected $readerConfig;

    /** @var Model */
    protected $item;

    /** @var DataContainer */
    protected $dc;

    /**
     * ModuleReader constructor.
     *
     * @param ModuleModel $objModule
     * @param string      $strColumn
     */
    public function __construct(ModuleModel $objModule, $strColumn = 'main')
    {
        $this->framework = System::getContainer()->get('contao.framework');
        $this->translator = System::getContainer()->get('translator');

        parent::__construct($objModule, $strColumn);

        // add class to every reader template
        $cssID = $this->cssID;
        $cssID[1] = $cssID[1].($cssID[1] ? ' ' : '').'huh-reader';

        $this->cssID = $cssID;
    }

    public function generate()
    {
        if (TL_MODE == 'BE') {
            $objTemplate = new \BackendTemplate('be_wildcard');
            $objTemplate->wildcard = '### '.Utf8::strtoupper($GLOBALS['TL_LANG']['FMD'][$this->type][0]).' ###';
            $objTemplate->title = $this->headline;
            $objTemplate->id = $this->id;
            $objTemplate->link = $this->name;
            $objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id='.$this->id;

            return $objTemplate->parse();
        }

        Controller::loadDataContainer('tl_reader_config');

        $this->readerConfig = $readerConfig = $this->getReaderConfig();

        $this->item = $this->retrieveItem();

        if (null !== $this->item) {
            $this->dc = DC_Table_Utils::createFromModel($this->item);

            $this->triggerOnLoadCallbacks();
        }

        // throw a 404 if no item found
        if (null === $this->item) {
            throw new PageNotFoundException('Page not found: '.Environment::get('uri'));
        }

        return parent::generate();
    }

    public function checkPermission()
    {
        $readerConfig = $this->readerConfig;
        $allowed = true;

        if ($readerConfig->addShowConditions) {
            $itemConditions = StringUtil::deserialize($readerConfig->showItemConditions, true);

            if (!empty($itemConditions)) {
                list($whereCondition, $values) = System::getContainer()->get('huh.entity_filter.backend.entity_filter')->computeSqlCondition(
                    $itemConditions,
                    $readerConfig->dataContainer
                );

                $result = Database::getInstance()->prepare(
                    "SELECT * FROM $readerConfig->dataContainer WHERE ($whereCondition) AND id=".$this->item->id
                )->execute($values);

                if ($result->numRows < 1) {
                    $allowed = false;
                }
            }
        }

        return $allowed;
    }

    protected function compile()
    {
        $readerConfig = $this->readerConfig;
        $item = $this->item;

        Controller::loadDataContainer($readerConfig->dataContainer);
        System::loadLanguageFile($readerConfig->dataContainer);

        if (!$this->checkPermission()) {
            StatusMessage::addError($this->translator->trans('huh.reader.messages.permissionDenied'), $this->id);
            $this->Template->invalid = true;

            return;
        }

        $this->setPageTitle();

        $preparedItem = $this->prepareItem($item->row());
        $this->Template->item = $this->parseItem($preparedItem);
    }

    protected function prepareItem(array $item): array
    {
        $readerConfig = $this->readerConfig;
        $formUtil = System::getContainer()->get('huh.utils.form');

        $result = [];

        $dca = &$GLOBALS['TL_DCA'][$readerConfig->dataContainer];
        $dc = $this->dc;

        $fields = $readerConfig->limitFields ? StringUtil::deserialize($readerConfig->fields, true) : array_keys($dca['fields']);

        foreach ($fields as $field) {
            $dc->field = $field;
            $value = $item[$field];

            if (is_array($dca['fields'][$field]['load_callback'])) {
                foreach ($dca['fields'][$field]['load_callback'] as $callback) {
                    $instance = System::importStatic($callback[0]);
                    $value = $instance->{$callback[1]}($value, $dc);
                }
            }

            // add raw value
            $result['raw'][$field] = $value;

            $result['formatted'][$field] = $formUtil->prepareSpecialValueForOutput(
                $field,
                $value,
                $dc
            );

            // anti-xss: escape everything besides some tags
            $result['formatted'][$field] = $formUtil->escapeAllHtmlEntities(
                $readerConfig->dataContainer,
                $field,
                $result['formatted'][$field]
            );
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

    protected function parseItem(array $item): string
    {
        $readerConfig = $this->readerConfig;

        $templateData = $item['formatted'];

        foreach ($item as $field => $value) {
            $templateData[$field] = $value;
        }

        $templateData['dataContainer'] = $readerConfig->dataContainer;

        $this->addImagesToTemplate($item, $templateData, $readerConfig);

        $templateData['module'] = $this->arrData;

        $this->modifyItemTemplateData($templateData, $item);

        return System::getContainer()->get('twig')->render($this->getItemTemplateByName($readerConfig->itemTemplate ?: 'default'), $templateData);
    }

    protected function modifyItemTemplateData(array &$templateData, array $item): void
    {
    }

    protected function triggerOnLoadCallbacks()
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
     * @return Model|null
     */
    protected function retrieveItem()
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

        return $item;
    }

    protected function retrieveItemByAutoItem()
    {
        $readerConfig = $this->readerConfig;
        $item = null;

        if (Config::get('useAutoItem') && ($autoItem = Request::getGet('auto_item'))) {
            $field = $readerConfig->itemRetrievalAutoItemField;

            // try to find by a certain field (likely alias)
            $item = System::getContainer()->get('huh.utils.model')->findOneModelInstanceBy(
                $readerConfig->dataContainer,
                [
                    $field.'=?',
                ],
                [
                    $autoItem,
                ]
            );

            // fallback: ID
            if (null === $item) {
                $item = System::getContainer()->get('huh.utils.model')->findModelInstanceByPk($readerConfig->dataContainer, $autoItem);
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
            list($whereCondition, $values) = System::getContainer()->get('huh.entity_filter.backend.entity_filter')->computeSqlCondition(
                $itemConditions,
                $readerConfig->dataContainer
            );

            $result = Database::getInstance()->prepare(
                "SELECT * FROM $readerConfig->dataContainer WHERE ($whereCondition)"
            )->limit(1)->execute($values);

            if ($result->numRows > 0) {
                $item = System::getContainer()->get('huh.utils.model')->findModelInstanceByPk($readerConfig->dataContainer, $result->id);
            }
        }

        return $item;
    }

    protected function getReaderConfig()
    {
        $readerConfigId = $this->arrData['readerConfig'];

        if (!$readerConfigId
            || null === ($readerConfig = System::getContainer()->get('huh.reader.reader-config-registry')->findByPk($readerConfigId))
        ) {
            throw new \Exception(sprintf('The module %s has no valid reader config. Please set one.', $this->id));
        }

        return $readerConfig;
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

    protected function addImagesToTemplate(array $item, array &$templateData, ReaderConfigModel $readerConfig)
    {
        $imageReaderConfigElements = System::getContainer()->get('huh.reader.reader-config-element-registry')->findBy(
            ['type=?', 'pid=?'],
            [ReaderConfigElement::TYPE_IMAGE, $readerConfig->id]
        );

        if (null !== $imageReaderConfigElements) {
            while ($imageReaderConfigElements->next()) {
                $image = null;

                if ($item['raw'][$imageReaderConfigElements->imageSelectorField] && $item['raw'][$imageReaderConfigElements->imageField]) {
                    $imageSelectorField = $imageReaderConfigElements->imageSelectorField;
                    $image = $item['raw'][$imageReaderConfigElements->imageField];
                    $imageField = $imageReaderConfigElements->imageField;
                } elseif ($imageReaderConfigElements->placeholderImageMode) {
                    $imageSelectorField = $imageReaderConfigElements->imageSelectorField;
                    $imageField = $imageReaderConfigElements->imageField;

                    switch ($imageReaderConfigElements->placeholderImageMode) {
                        case ReaderConfigElement::PLACEHOLDER_IMAGE_MODE_GENDERED:
                            if ($item['raw'][$imageReaderConfigElements->genderField] == 'female') {
                                $image = $imageReaderConfigElements->placeholderImageFemale;
                            } else {
                                $image = $imageReaderConfigElements->placeholderImage;
                            }
                            break;
                        case ReaderConfigElement::PLACEHOLDER_IMAGE_MODE_SIMPLE:
                            $image = $imageReaderConfigElements->placeholderImage;
                            break;
                    }
                } else {
                    continue;
                }

                $imageModel = FilesModel::findByUuid($image);

                if (null !== $imageModel
                    && is_file(System::getContainer()->get('huh.utils.container')->getProjectDir().'/'.$imageModel->path)
                ) {
                    $imageArray = $item['raw'];

                    // Override the default image size
                    if ('' != $imageReaderConfigElements->imgSize) {
                        $size = StringUtil::deserialize($imageReaderConfigElements->imgSize);

                        if ($size[0] > 0 || $size[1] > 0 || is_numeric($size[2])) {
                            $imageArray['size'] = $imageReaderConfigElements->imgSize;
                        }
                    }

                    $imageArray[$imageField] = $imageModel->path;
                    $templateData['images'][$imageField] = [];

                    System::getContainer()->get('huh.utils.image')->addToTemplateData(
                        $imageField,
                        $imageSelectorField,
                        $templateData['images'][$imageField],
                        $imageArray,
                        null,
                        null,
                        null,
                        $imageModel
                    );
                }
            }
        }
    }

    protected function setPageTitle()
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

            System::getContainer()->get('huh.head.tag.title')->setContent(
                $this->modifyPageTitle($pageTitle)
            );
        }
    }

    protected function modifyPageTitle(string $pageTitle): string
    {
        return $pageTitle;
    }
}
