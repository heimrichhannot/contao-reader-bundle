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

    public function addToTemplateData(array $item, array &$templateData, ReaderConfigElementModel $readerConfigElement)
    {
        $image = null;

        if (isset($item['raw'][$readerConfigElement->imageSelectorField]) && $item['raw'][$readerConfigElement->imageSelectorField]
            && isset($item['raw'][$readerConfigElement->imageField])
            && $item['raw'][$readerConfigElement->imageField]
        ) {
            $imageSelectorField = $readerConfigElement->imageSelectorField;
            $image = $item['raw'][$readerConfigElement->imageField];
            $imageField = $readerConfigElement->imageField;
        } elseif ($readerConfigElement->placeholderImageMode) {
            $imageSelectorField = $readerConfigElement->imageSelectorField;
            $imageField = $readerConfigElement->imageField;

            switch ($readerConfigElement->placeholderImageMode) {
                case ReaderConfigElement::PLACEHOLDER_IMAGE_MODE_GENDERED:
                    if (isset($item['raw'][$readerConfigElement->genderField])
                        && $item['raw'][$readerConfigElement->genderField] == 'female'
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
            $imageArray = $item['raw'];

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