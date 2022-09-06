<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ReaderBundle\Controller\ContentElement;

use Contao\BackendTemplate;
use Contao\ContentModel;
use Contao\Controller;
use Contao\CoreBundle\Controller\ContentElement\AbstractContentElementController;
use Contao\CoreBundle\ServiceAnnotation\ContentElement;
use Contao\StringUtil;
use Contao\Template;
use HeimrichHannot\ReaderBundle\EventListener\RelatedListListener;
use HeimrichHannot\ReaderBundle\Generator\RelatedListGenerator;
use HeimrichHannot\ReaderBundle\Generator\RelatedListGeneratorConfig;
use HeimrichHannot\ReaderBundle\Manager\ReaderManager;
use HeimrichHannot\ReaderBundle\Model\ReaderConfigModel;
use HeimrichHannot\UtilsBundle\Util\Utils;
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
    private Utils                $utils;

    public function __construct(RelatedListGenerator $relatedListGenerator, ReaderManager $readerManager, Utils $utils)
    {
        $this->relatedListGenerator = $relatedListGenerator;
        $this->readerManager = $readerManager;
        $this->utils = $utils;
    }

    protected function getResponse(Template $template, ContentModel $model, Request $request): ?Response
    {
        $readerConfigModel = ReaderConfigModel::findByPk($model->readerConfig);

        if (!$readerConfigModel) {
            return $template->getResponse();
        }

        $criteria = StringUtil::deserialize($model->relatedCriteria, true);

        if ($this->utils->container()->isBackend()) {
            $backendTemplate = new BackendTemplate('be_wildcard');
            $backendTemplate->headline = $template->headline;

            Controller::loadLanguageFile('tl_reader_config_element');

            foreach ($criteria as $key => $value) {
                $criteria[$key] = ($GLOBALS['TL_LANG']['tl_reader_config_element']['reference'][$value] ?? $value);
            }
            $backendTemplate->wildcard = $readerConfigModel->title
                .'<br />'
                .($GLOBALS['TL_LANG']['tl_reader_config_element']['relatedCriteria'][0] ?? 'Criteria').': '
                .implode(', ', $criteria);

            return $backendTemplate->getResponse();
        }

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

        $template->relatedList = $this->relatedListGenerator->generate(
            $listGeneratorConfig,
            ['column' => $request->attributes->get('section', 'main')]
        );

        return $template->getResponse();
    }
}
