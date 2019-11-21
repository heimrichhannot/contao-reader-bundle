<?php

/*
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ReaderBundle\ConfigElementType;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\FilesModel;
use Contao\Model;
use Contao\StringUtil;
use Contao\System;
use HeimrichHannot\ReaderBundle\Backend\ReaderConfigElement;
use HeimrichHannot\ReaderBundle\Item\ItemInterface;
use HeimrichHannot\ReaderBundle\Model\ReaderConfigElementModel;

class VideoConfigElementType implements ReaderConfigElementTypeInterface
{
    const TYPE = 'video';
    const RANDOM_VIDEO_PLACEHOLDERS_SESSION_KEY = 'huh.random-video-placeholders';

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
        // video preparation
        if ($readerConfigElement->videoSelectorField && $item->getRawValue($readerConfigElement->videoSelectorField)
            && $item->getRawValue($readerConfigElement->videoField)) {
            $video = $item->getRawValue($readerConfigElement->videoField);
        } elseif (!$readerConfigElement->videoSelectorField && $readerConfigElement->videoField && $item->getRawValue($readerConfigElement->videoField)) {
            $video = $item->getRawValue($readerConfigElement->videoField);
        } else {
            return;
        }

        // poster image
        $image = null;

        if ($readerConfigElement->posterImageSelectorField && $item->getRawValue($readerConfigElement->posterImageSelectorField)
            && $item->getRawValue($readerConfigElement->posterImageField)) {
            $image = $item->getRawValue($readerConfigElement->posterImageField);
        } elseif (!$readerConfigElement->posterImageSelectorField && $readerConfigElement->posterImageField && $item->getRawValue($readerConfigElement->posterImageField)) {
            $image = $item->getRawValue($readerConfigElement->posterImageField);
        } elseif ($readerConfigElement->placeholderImageMode) {
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

                    if (!$session->has(static::RANDOM_VIDEO_PLACEHOLDERS_SESSION_KEY)) {
                        $session->set(static::RANDOM_VIDEO_PLACEHOLDERS_SESSION_KEY, $randomImagePlaceholders);
                    } else {
                        $randomImagePlaceholders = $session->get(static::RANDOM_VIDEO_PLACEHOLDERS_SESSION_KEY);
                    }

                    if (null !== ($readerConfig = System::getContainer()->get('huh.utils.model')->findModelInstanceByPk('tl_reader_config', $readerConfigElement->pid))) {
                        $key = $readerConfig->dataContainer.'_'.$item->getRawValue('id');

                        if (isset($randomImagePlaceholders[$key])) {
                            $image = $randomImagePlaceholders[$key];
                        } elseif (null !== ($randomKey = array_rand($images))) {
                            $image = $randomImagePlaceholders[$key] = $images[$randomKey];
                        }

                        $session->set(static::RANDOM_VIDEO_PLACEHOLDERS_SESSION_KEY, $randomImagePlaceholders);
                    }
            }
        }

        /** @var FilesModel $filesModel */
        $filesModel = $this->framework->getAdapter(FilesModel::class);

        // support for multifileupload
        $image = StringUtil::deserialize($image);

        if (\is_array($image)) {
            $image = array_values($image)[0];
        }

        $projectDir = System::getContainer()->get('huh.utils.container')->getProjectDir();
        $poster = null;
        $posterImg = null;

        if (null !== ($imageFile = $filesModel->findByUuid($image)) && file_exists($projectDir.'/'.$imageFile->path) && getimagesize($projectDir.'/'.$imageFile->path)) {
            $poster = $imageFile->path;

            if ($readerConfigElement->renderPosterAsImg) {
                $imageArray = [
                    $readerConfigElement->posterImageField => $imageFile->uuid,
                ];

                // Override the default image size
                if ('' != $readerConfigElement->imgSize) {
                    $size = StringUtil::deserialize($readerConfigElement->imgSize);

                    if ($size[0] > 0 || $size[1] > 0 || is_numeric($size[2])) {
                        $imageArray['size'] = $readerConfigElement->imgSize;
                    }
                }

                $imageArray['posterImg'] = $imageFile->path;

                $templateData = [];

                System::getContainer()->get('huh.utils.image')->addToTemplateData($readerConfigElement->posterImageField, $readerConfigElement->posterImageSelectorField, $templateData, $imageArray, null, null, null, $imageFile);

                $posterImg = $templateData;
            }
        }

        // video
        // support for multifileupload
        $video = StringUtil::deserialize($video);

        if (\is_array($video)) {
            $video = array_values($video)[0];
        }

        $projectDir = System::getContainer()->get('huh.utils.container')->getProjectDir();

        if (null !== ($videoFile = $filesModel->findByUuid($video)) && file_exists($projectDir.'/'.$videoFile->path)) {
            $videoData = [
                'poster' => $poster, // poster path
                'posterImg' => $posterImg, // poster as picture for img element
                'files' => [$videoFile],
                'autoplay' => $readerConfigElement->videoAutoplay,
                'preload' => $readerConfigElement->videoPreload,
                'loop' => $readerConfigElement->videoLoop,
                'controls' => $readerConfigElement->videoControls,
                'playsinline' => $readerConfigElement->videoPlaysInline,
                'muted' => $readerConfigElement->videoMuted,
            ];

            $videosArray = [];
            $videosArray['videos'] = $item->getFormattedValue('videos') ?: [];
            $videosArray['videos'][$readerConfigElement->templateVariable ?: $readerConfigElement->videoField] = $videoData;

            $item->setFormattedValue('videos', $videosArray['videos']);
        }
    }

    /**
     * @param ItemInterface            $item
     * @param ReaderConfigElementModel $listConfigElement
     *
     * @return string
     */
    public function getGenderedPlaceholderImage(ItemInterface $item, Model $listConfigElement): string
    {
        if ($item->getRawValue($listConfigElement->genderField) && 'female' == $item->getRawValue($listConfigElement->genderField)) {
            $image = $listConfigElement->placeholderImageFemale;
        } else {
            $image = $listConfigElement->placeholderImage;
        }

        return $image;
    }

    /**
     * Return the reader config element type alias.
     *
     * @return string
     */
    public static function getType(): string
    {
        return 'video';
    }

    /**
     * Return the reader config element type palette.
     *
     * @return string
     */
    public function getPalette(): string
    {
        return '{config_legend},videoAutoplay,videoLoop,videoMuted,videoControls,videoPlaysInline,videoPreload,videoSelectorField,videoField,posterImageSelectorField,posterImageField,renderPosterAsImg,imgSize,placeholderImageMode;';
    }

    /**
     * Update the item data.
     *
     * @param ReaderConfigElementData $configElementData
     */
    public function addToReaderItemData(ReaderConfigElementData $configElementData): void
    {
        $this->addToItemData($configElementData->getItem(), $configElementData->getReaderConfigElement());
    }
}
