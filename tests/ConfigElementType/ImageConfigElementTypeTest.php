<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ReaderBundle\Tests\ConfigElementType;

use HeimrichHannot\ReaderBundle\Backend\ReaderConfigElement;
use HeimrichHannot\ReaderBundle\ConfigElementType\ImageConfigElementType;
use HeimrichHannot\ReaderBundle\Item\DefaultItem;
use HeimrichHannot\ReaderBundle\Model\ReaderConfigElementModel;
use HeimrichHannot\ReaderBundle\Tests\TestCaseEnvironmentReaderManager;

class ImageConfigElementTypeTest extends TestCaseEnvironmentReaderManager
{
    public function setUp(): void
    {
        parent::setUp();
        $this->prepareReaderConfig();
    }

    public function testAddToItemData()
    {
        // default return
        $imageConfigElementType = new ImageConfigElementType($this->framework);
        $defaultItem = new DefaultItem($this->manager, ['id' => 1, 'test' => true, 'string' => 'string', 'timestamp' => 1557989217, 'images' => null]);
        $readerConfigElementModel = new ReaderConfigElementModel();
        $readerConfigElementModel->setRow([
            'type' => 'blah',
            'imageSelectorField' => 'addImage1',
            'imageField' => false,
            'genderField' => 'gender',
            'placeholderImageFemale' => 'female',
            'placeholderImage' => 'male',
        ]);
        $this->assertNull($imageConfigElementType->addToItemData($defaultItem, $readerConfigElementModel));

        // image selector field and image field
        $readerConfigElementModel = new ReaderConfigElementModel();
        $readerConfigElementModel->setRow([
            'type' => ImageConfigElementType::getType(),
            'imageSelectorField' => 'addImage1',
            'imageField' => 'singleSRC1',
            'placeholderImageMode' => ReaderConfigElement::PLACEHOLDER_IMAGE_MODE_GENDERED,
            'genderField' => 'gender',
            'placeholderImageFemale' => 'female',
            'placeholderImage' => 'male',
            'imgSize' => 'a:3:{i:0;s:0:"";i:1;s:0:"";i:2;s:2:"19";}',
        ]);
        $defaultItem = new DefaultItem($this->manager, ['id' => 1, 'test' => true, 'string' => 'string', 'timestamp' => 1557989217, 'images' => null, 'addImage1' => 'female', 'singleSRC1' => 'female']);
        $imageConfigElementType->addToItemData($defaultItem, $readerConfigElementModel);
        $this->assertArrayHasKey('singleSRC1', $defaultItem->getFormattedValue('images'));
        $this->assertSame(['singleSRC1' => ['picture' => 'data/female.png']], $defaultItem->getFormattedValue('images'));

        // no image selector field
        $defaultItem = new DefaultItem($this->manager, ['id' => 1, 'test' => true, 'string' => 'string', 'timestamp' => 1557989217, 'images' => null, 'singleSRC1' => 'female']);
        $readerConfigElementModel->setRow([
            'type' => ImageConfigElementType::getType(),
            'imageField' => 'singleSRC1',
            'imageSelectorField' => false,
            'placeholderImageMode' => ReaderConfigElement::PLACEHOLDER_IMAGE_MODE_GENDERED,
            'genderField' => 'gender',
            'placeholderImageFemale' => 'female',
            'placeholderImage' => 'male',
            'imgSize' => 'a:3:{i:0;s:0:"";i:1;s:0:"";i:2;s:2:"19";}',
        ]);
        $imageConfigElementType->addToItemData($defaultItem, $readerConfigElementModel);
        $this->assertArrayHasKey('singleSRC1', $defaultItem->getFormattedValue('images'));
        $this->assertSame(['singleSRC1' => ['picture' => 'data/female.png']], $defaultItem->getFormattedValue('images'));

        // case PLACEHOLDER_IMAGE_MODE_GENDERED male
        $defaultItem = new DefaultItem($this->manager, ['id' => 1, 'test' => true, 'string' => 'string', 'timestamp' => 1557989217, 'images' => null, 'singleSRC1' => 'female']);
        $readerConfigElementModel->setRow([
            'type' => ImageConfigElementType::getType(),
            'imageField' => 'singleSRC1',
            'imageSelectorField' => true,
            'placeholderImageMode' => ReaderConfigElement::PLACEHOLDER_IMAGE_MODE_GENDERED,
            'genderField' => 'gender',
            'placeholderImageFemale' => 'female',
            'placeholderImage' => 'male',
            'imgSize' => 'a:3:{i:0;s:0:"";i:1;s:0:"";i:2;s:2:"19";}',
        ]);
        $imageConfigElementType->addToItemData($defaultItem, $readerConfigElementModel);
        $this->assertArrayHasKey('singleSRC1', $defaultItem->getFormattedValue('images'));
        $this->assertSame(['singleSRC1' => ['picture' => 'data/male.png']], $defaultItem->getFormattedValue('images'));

        // case PLACEHOLDER_IMAGE_MODE_GENDERED female
        $defaultItem = new DefaultItem($this->manager, ['id' => 1, 'test' => true, 'string' => 'string', 'timestamp' => 1557989217, 'images' => null, 'singleSRC1' => 'female', 'gender' => 'female']);
        $readerConfigElementModel->setRow([
            'type' => ImageConfigElementType::getType(),
            'imageField' => 'singleSRC1',
            'imageSelectorField' => true,
            'placeholderImageMode' => ReaderConfigElement::PLACEHOLDER_IMAGE_MODE_GENDERED,
            'genderField' => 'gender',
            'placeholderImageFemale' => 'female',
            'placeholderImage' => 'male',
            'imgSize' => 'a:3:{i:0;s:0:"";i:1;s:0:"";i:2;s:2:"19";}',
        ]);
        $imageConfigElementType->addToItemData($defaultItem, $readerConfigElementModel);
        $this->assertArrayHasKey('singleSRC1', $defaultItem->getFormattedValue('images'));
        $this->assertSame(['singleSRC1' => ['picture' => 'data/female.png']], $defaultItem->getFormattedValue('images'));

        // case PLACEHOLDER_IMAGE_MODE_SIMPLE
        $defaultItem = new DefaultItem($this->manager, ['id' => 1, 'test' => true, 'string' => 'string', 'timestamp' => 1557989217, 'images' => null, 'singleSRC1' => 'female', 'gender' => 'female']);
        $readerConfigElementModel->setRow([
            'type' => ImageConfigElementType::getType(),
            'imageField' => 'singleSRC1',
            'imageSelectorField' => true,
            'placeholderImageMode' => ReaderConfigElement::PLACEHOLDER_IMAGE_MODE_SIMPLE,
            'genderField' => 'gender',
            'placeholderImageFemale' => 'female',
            'placeholderImage' => 'male',
            'imgSize' => 'a:3:{i:0;s:0:"";i:1;s:0:"";i:2;s:2:"19";}',
        ]);
        $imageConfigElementType->addToItemData($defaultItem, $readerConfigElementModel);
        $this->assertArrayHasKey('singleSRC1', $defaultItem->getFormattedValue('images'));
        $this->assertSame(['singleSRC1' => ['picture' => 'data/male.png']], $defaultItem->getFormattedValue('images'));
    }
}
