<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ReaderBundle\ConfigElementType;

use Contao\Config;
use Contao\Controller;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\Database;
use Contao\FilesModel;
use Contao\Model;
use Contao\StringUtil;
use Contao\System;
use HeimrichHannot\ReaderBundle\Backend\ReaderConfigElement;
use HeimrichHannot\ReaderBundle\Item\ItemInterface;
use HeimrichHannot\ReaderBundle\Model\ReaderConfigElementModel;

class ImageConfigElementType implements ReaderConfigElementTypeInterface
{
    const TYPE = 'image';
    const RANDOM_IMAGE_PLACEHOLDERS_SESSION_KEY = 'huh.random-image-placeholders';

    /**
     * @var ContaoFrameworkInterface
     */
    protected $framework;

    public function __construct(ContaoFrameworkInterface $framework)
    {
        $this->framework = $framework;
    }

    public function addToItemData(ItemInterface $item, ReaderConfigElementModel $readerConfigElement)
    {
        $image = null;
        $validImageType = $this->isValidImageType($item, $readerConfigElement);

        if ($readerConfigElement->imageSelectorField && $item->getRawValue($readerConfigElement->imageSelectorField) && $validImageType
            && $item->getRawValue($readerConfigElement->imageField)) {
            $imageSelectorField = $readerConfigElement->imageSelectorField;
            $image = $item->getRawValue($readerConfigElement->imageField);
            $imageField = $readerConfigElement->imageField;
        } elseif (!$readerConfigElement->imageSelectorField && $readerConfigElement->imageField && $item->getRawValue($readerConfigElement->imageField) && $validImageType) {
            $imageSelectorField = '';
            $image = $item->getRawValue($readerConfigElement->imageField);
            $imageField = $readerConfigElement->imageField;
        } elseif ($readerConfigElement->placeholderImageMode) {
            $imageSelectorField = $readerConfigElement->imageSelectorField;
            $imageField = $readerConfigElement->imageField;

            switch ($readerConfigElement->placeholderImageMode) {
                case ReaderConfigElement::PLACEHOLDER_IMAGE_MODE_GENDERED:
                    $image = $this->getGenderedPlaceholderImage($item, $readerConfigElement);

                    break;

                case ReaderConfigElement::PLACEHOLDER_IMAGE_MODE_SIMPLE:
                    $image = $readerConfigElement->placeholderImage;

                    break;

                case ReaderConfigElement::PLACEHOLDER_IMAGE_MODE_RANDOM:
                    $images = StringUtil::deserialize($readerConfigElement->placeholderImages, true);

                    $session = System::getContainer()->get('session');

                    $randomImagePlaceholders = [];

                    if (!$session->has(static::RANDOM_IMAGE_PLACEHOLDERS_SESSION_KEY)) {
                        $session->set(static::RANDOM_IMAGE_PLACEHOLDERS_SESSION_KEY, $randomImagePlaceholders);
                    } else {
                        $randomImagePlaceholders = $session->get(static::RANDOM_IMAGE_PLACEHOLDERS_SESSION_KEY);
                    }

                    if (null !== ($readerConfig = System::getContainer()->get('huh.utils.model')->findModelInstanceByPk('tl_reader_config', $readerConfigElement->pid))) {
                        $key = $readerConfig->dataContainer.'_'.$item->getRawValue('id');

                        if (isset($randomImagePlaceholders[$key])) {
                            $image = $randomImagePlaceholders[$key];
                        } elseif (null !== ($randomKey = array_rand($images))) {
                            $image = $randomImagePlaceholders[$key] = $images[$randomKey];
                        }

                        $session->set(static::RANDOM_IMAGE_PLACEHOLDERS_SESSION_KEY, $randomImagePlaceholders);
                    }

                    break;

                case ReaderConfigElement::PLACEHOLDER_IMAGE_MODE_FIELD:
                    if (empty($placeholderConfig = StringUtil::deserialize($readerConfigElement->fieldDependentPlaceholderConfig,
                        true))) {
                        return;
                    }

                    foreach ($placeholderConfig as $config) {
                        if (!System::getContainer()->get('huh.utils.comparison')->compareValue($config['operator'],
                            $item->{$config['field']}, Controller::replaceInsertTags($config['value']))) {
                            continue;
                        }

                        $image = $config['placeholderImage'];
                    }
            }
        } else {
            return;
        }

        /** @var FilesModel $filesModel */
        $filesModel = $this->framework->getAdapter(FilesModel::class);

        // support for multifileupload
        $image = StringUtil::deserialize($image);

        if (\is_array($image)) {
            $image = array_values($image)[0];
        }

        if (null === ($imageFile = $filesModel->findByUuid($image))) {
            $uuid = StringUtil::deserialize($image, true)[0];
            $imageFile = $filesModel->findByUuid($uuid);
        }

        $projectDir = System::getContainer()->get('huh.utils.container')->getProjectDir();

        if (null !== $imageFile && file_exists($projectDir.'/'.$imageFile->path) && (getimagesize($projectDir.'/'.$imageFile->path) || 'svg' === strtolower($imageFile->extension))) {
            $imageArray = $item->getRaw();

            // Override the default image size
            if ('' != $readerConfigElement->imgSize) {
                $size = StringUtil::deserialize($readerConfigElement->imgSize);

                if ($size[0] > 0 || $size[1] > 0 || is_numeric($size[2])) {
                    $imageArray['size'] = $readerConfigElement->imgSize;
                }
            }

            $imageArray[$imageField] = $imageFile->path;

            $templateContainer = ($readerConfigElement->overrideTemplateContainerVariable
            && $readerConfigElement->templateContainerVariable
                ? $readerConfigElement->templateContainerVariable : 'images');

            if (\in_array($templateContainer, Database::getInstance()->getFieldNames($item->getDataContainer()))) {
                throw new \Exception('Contao Reader Bundle: You specified that images of a reader config element should be added to an array called "'.$templateContainer.'" in your reader config element ID '.$readerConfigElement->id.'. The associated DCA '.$item->getDataContainer().' contains a field of the same name which isn\'t supported. Please adjust the template container variable name in the reader config element to be different from "'.$templateContainer.'".');
            }

            $templateData = [];

            $templateData[$templateContainer] = $item->getFormattedValue($templateContainer) ?: [];
            $templateData[$templateContainer][$readerConfigElement->templateVariable ?: $imageField] = [];

            System::getContainer()->get('huh.utils.image')->addToTemplateData($imageField, $imageSelectorField,
                $templateData[$templateContainer][$readerConfigElement->templateVariable ?: $imageField], $imageArray, null, null, null, $imageFile);

            $item->setFormattedValue($templateContainer, $templateData[$templateContainer]);
        }
    }

    /**
     * @param ReaderConfigElementModel $readerConfigElement
     */
    public function getGenderedPlaceholderImage(ItemInterface $item, Model $readerConfigElement): string
    {
        if ($item->getRawValue($readerConfigElement->genderField) && 'female' == $item->getRawValue($readerConfigElement->genderField)) {
            $image = $readerConfigElement->placeholderImageFemale;
        } else {
            $image = $readerConfigElement->placeholderImage;
        }

        return $image;
    }

    /**
     * Return the reader config element type alias.
     */
    public static function getType(): string
    {
        return 'image';
    }

    /**
     * Return the reader config element type palette.
     */
    public function getPalette(): string
    {
        return '{config_legend},imageSelectorField,imageField,imgSize,placeholderImageMode;{advanced_config},overrideTemplateContainerVariable;';
    }

    /**
     * Update the item data.
     */
    public function addToReaderItemData(ReaderConfigElementData $configElementData): void
    {
        $this->addToItemData($configElementData->getItem(), $configElementData->getReaderConfigElement());
    }

    /**
     * @throws \Exception
     */
    protected function isValidImageType(ItemInterface $item, ReaderConfigElementModel $readerConfigElement): bool
    {
        if (!$readerConfigElement->imageField || !$item->getRawValue($readerConfigElement->imageField)) {
            return false;
        }

        $uuid = StringUtil::deserialize($item->getRawValue($readerConfigElement->imageField), true)[0];

        if (null === ($file = System::getContainer()->get('huh.utils.file')->getFileFromUuid($uuid))) {
            return false;
        }

        return \in_array($file->getModel()->extension, explode(',', Config::get('validImageTypes')), true);
    }
}
