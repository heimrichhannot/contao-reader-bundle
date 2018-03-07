<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
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

class ImageConfigElementType implements ConfigElementType
{
    /**
     * @var ContaoFrameworkInterface
     */
    protected $framework;

    public function __construct(ContaoFrameworkInterface $framework)
    {
        $this->framework = $framework;
    }

    public function addToTemplateData(ItemInterface $item, array &$templateData, ReaderConfigElementModel $readerConfigElement)
    {
        $image = null;

        if (isset($item->getRaw()[$readerConfigElement->imageSelectorField]) && $item->getRaw()[$readerConfigElement->imageSelectorField]
            && isset($item->getRaw()[$readerConfigElement->imageField])
            && $item->getRaw()[$readerConfigElement->imageField]
        ) {
            $imageSelectorField = $readerConfigElement->imageSelectorField;
            $image = $item->getRaw()[$readerConfigElement->imageField];
            $imageField = $readerConfigElement->imageField;
        } elseif ($readerConfigElement->placeholderImageMode) {
            $imageSelectorField = $readerConfigElement->imageSelectorField;
            $imageField = $readerConfigElement->imageField;

            switch ($readerConfigElement->placeholderImageMode) {
                case ReaderConfigElement::PLACEHOLDER_IMAGE_MODE_GENDERED:
                    if (isset($item->getRaw()[$readerConfigElement->genderField])
                        && 'female' == $item->getRaw()[$readerConfigElement->genderField]
                    ) {
                        $image = $readerConfigElement->placeholderImageFemale;
                    } else {
                        $image = $readerConfigElement->placeholderImage;
                    }
                    break;
                case ReaderConfigElement::PLACEHOLDER_IMAGE_MODE_SIMPLE:
                    $image = $readerConfigElement->placeholderImage;
                    break;
            }
        } else {
            return;
        }

        /**
         * @var FilesModel
         */
        $imageFile = $this->framework->getAdapter(FilesModel::class)->findByUuid($image);

        if (null !== $imageFile
            && file_exists(System::getContainer()->get('huh.utils.container')->getProjectDir().'/'.$imageFile->path)
        ) {
            $imageArray = $item->getRaw();

            // Override the default image size
            if ('' != $readerConfigElement->imgSize) {
                $size = StringUtil::deserialize($readerConfigElement->imgSize);

                if ($size[0] > 0 || $size[1] > 0 || is_numeric($size[2])) {
                    $imageArray['size'] = $readerConfigElement->imgSize;
                }
            }

            $imageArray[$imageField] = $imageFile->path;
            $templateData['images'][$imageField] = [];

            System::getContainer()->get('huh.utils.image')->addToTemplateData(
                $imageField,
                $imageSelectorField,
                $templateData['images'][$imageField],
                $imageArray,
                null,
                null,
                null,
                $imageFile
            );
        }
    }
}
