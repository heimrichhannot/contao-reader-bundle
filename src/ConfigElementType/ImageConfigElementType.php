<?php

/*
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ReaderBundle\ConfigElementType;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\FilesModel;
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

        if ($item->getRawValue($readerConfigElement->imageSelectorField) && $item->getRawValue($readerConfigElement->imageField)) {
            $imageSelectorField = $readerConfigElement->imageSelectorField;
            $image = $item->getRawValue($readerConfigElement->imageField);
            $imageField = $readerConfigElement->imageField;
        } elseif (!$readerConfigElement->imageSelectorField && $item->getRawValue($readerConfigElement->imageField)) {
            $imageSelectorField = '';
            $image = $item->getRawValue($readerConfigElement->imageField);
            $imageField = $readerConfigElement->imageField;
        } elseif ($readerConfigElement->placeholderImageMode) {
            $imageSelectorField = $readerConfigElement->imageSelectorField;
            $imageField = $readerConfigElement->imageField;

            switch ($readerConfigElement->placeholderImageMode) {
                case ReaderConfigElement::PLACEHOLDER_IMAGE_MODE_GENDERED:
                    if ($item->getRawValue($readerConfigElement->genderField) && 'female' == $item->getRawValue($readerConfigElement->genderField)) {
                        $image = $readerConfigElement->placeholderImageFemale;
                    } else {
                        $image = $readerConfigElement->placeholderImage;
                    }

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
            }
        } else {
            return;
        }

        /**
         * @var FilesModel
         */
        $imageFile = $this->framework->getAdapter(FilesModel::class)->findByUuid($image);

        if (null !== $imageFile
            && file_exists(System::getContainer()->get('huh.utils.container')->getProjectDir().'/'.$imageFile->path)) {
            $imageArray = $item->getRaw();

            // Override the default image size
            if ('' != $readerConfigElement->imgSize) {
                $size = StringUtil::deserialize($readerConfigElement->imgSize);

                if ($size[0] > 0 || $size[1] > 0 || is_numeric($size[2])) {
                    $imageArray['size'] = $readerConfigElement->imgSize;
                }
            }

            $imageArray[$imageField] = $imageFile->path;

            $templateData['images'] = $item->images ?? [];
            $templateData['images'] = $item->getFormattedValue('images') ?? [];
            $templateData['images'][$imageField] = [];

            System::getContainer()->get('huh.utils.image')->addToTemplateData($imageField, $imageSelectorField, $templateData['images'][$imageField], $imageArray, null, null, null, $imageFile);

            $item->setFormattedValue('images', $templateData['images']);
        }
    }

    /**
     * Return the reader config element type alias.
     *
     * @return string
     */
    public static function getType(): string
    {
        return 'image';
    }

    /**
     * Return the reader config element type palette.
     *
     * @return string
     */
    public function getPalette(): string
    {
        return '{config_legend},imageSelectorField,imageField,imgSize,placeholderImageMode;';
    }

    /**
     * Update the item data.
     *
     * @param ReaderConfigElementData $configElementData
     */
    public function addToListItemData(ReaderConfigElementData $configElementData): void
    {
        $this->addToItemData($configElementData->getItem(), $configElementData->getReaderConfigElement());
    }
}
