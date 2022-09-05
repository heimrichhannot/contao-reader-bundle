<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ReaderBundle\Controller\ContentElement;

use Contao\ContentModel;
use Contao\CoreBundle\Controller\ContentElement\AbstractContentElementController;
use Contao\CoreBundle\ServiceAnnotation\ContentElement;
use Contao\StringUtil;
use Contao\Template;
use HeimrichHannot\ReaderBundle\EventListener\RelatedListListener;
use HeimrichHannot\ReaderBundle\Generator\RelatedListGenerator;
use HeimrichHannot\ReaderBundle\Generator\RelatedListGeneratorConfig;
use HeimrichHannot\ReaderBundle\Manager\ReaderManager;
use HeimrichHannot\ReaderBundle\Model\ReaderConfigModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @ContentElement(RelatedListContentElementController::TYPE, category="includes")
 */
class RelatedListContentElementController extends AbstractContentElementController
{
    const TYPE = 'related_list_content_element';

    private RelatedListGenerator $relatedListGenerator;
    private ReaderManager        $readerManager;

    public function __construct(RelatedListGenerator $relatedListGenerator, ReaderManager $readerManager)
    {
        $this->relatedListGenerator = $relatedListGenerator;
        $this->readerManager = $readerManager;
    }

    protected function getResponse(Template $template, ContentModel $model, Request $request): ?Response
    {
        $readerConfigModel = ReaderConfigModel::findByPk($model->readerConfig);

        if (!$readerConfigModel) {
            return $template->getResponse();
        }

        $criteria = StringUtil::deserialize($model->relatedCriteria, true);

        $this->readerManager->setModuleData(['readerConfig' => $readerConfigModel->id]);
        $this->readerManager->setReaderConfig($readerConfigModel);

        $item = $this->readerManager->retrieveItem();

        if (!$item || empty($criteria)) {
            return $template->getResponse();
        }

        $listGeneratorConfig = new RelatedListGeneratorConfig(
            $readerConfigModel->dataContainer,
            $item->getRawValue('id'),
            $model->relatedListModule
        );

        if (\in_array(RelatedListListener::CRITERIUM_TAGS, $criteria)) {
            $listGeneratorConfig->setTagsField($model->tagsField);
        }

        if (\in_array(RelatedListListener::CRITERIUM_CATEGORIES, $criteria)) {
            $listGeneratorConfig->setCategoriesField($model->categoriesField);
        }

        $template->relatedList = $this->relatedListGenerator->generate($listGeneratorConfig);

//        $this->relatedListGenerator->generate();

        return $template->getResponse();
    }
}
