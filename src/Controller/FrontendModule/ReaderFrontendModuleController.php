<?php

namespace HeimrichHannot\ReaderBundle\Controller\FrontendModule;

use Contao\Controller;
use Contao\CoreBundle\Controller\FrontendModule\AbstractFrontendModuleController;
use Contao\CoreBundle\Exception\PageNotFoundException;
use Contao\CoreBundle\Exception\RedirectResponseException;
use Contao\CoreBundle\ServiceAnnotation\FrontendModule;
use Contao\Environment;
use Contao\Input;
use Contao\ModuleModel;
use Contao\PageModel;
use Contao\StringUtil;
use Contao\Template;
use HeimrichHannot\ReaderBundle\Backend\ReaderConfig;
use HeimrichHannot\ReaderBundle\Registry\ReaderConfigRegistry;
use HeimrichHannot\StatusMessages\StatusMessage;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @FrontendModule(ReaderFrontendModuleController::TYPE, category="reader", template="mod_reader")
 */
class ReaderFrontendModuleController extends AbstractFrontendModuleController
{
    public const TYPE = 'huhreader';

    private ReaderConfigRegistry $readerConfigRegistry;
    private TranslatorInterface  $translator;

    public function __construct(
        ReaderConfigRegistry $readerConfigRegistry,
        TranslatorInterface $translator
    )
    {
        $this->readerConfigRegistry = $readerConfigRegistry;
        $this->translator = $translator;
    }

    protected function getResponse(Template $template, ModuleModel $model, Request $request): ?Response
    {
        $readerConfigModel = $this->readerConfigRegistry->findByPk($model->readerConfig);
        $readerManager = $this->readerConfigRegistry->getReaderManagerByName($readerConfigModel->manager ?: 'default');

        if (null === $readerManager) {
            return $template->getResponse();
        }

        Controller::loadDataContainer('tl_reader_config');

        $readerManager->setModuleData($model->row());

        $item = $readerManager->retrieveItem();

        if (null !== $item) {
            $readerManager->triggerOnLoadCallbacks();
        }

        if (null === $item) {
            $pageModel = $this->getPageModel();

            // behavior when reader module is rendered without item
            if (
                ((ReaderConfig::ITEM_RETRIEVAL_MODE_AUTO_ITEM === $readerConfigModel->itemRetrievalMode && !Input::get('auto_item')) || (ReaderConfig::ITEM_RETRIEVAL_MODE_AUTO_ITEM !== $readerConfigModel->itemRetrievalMode))
                && $model->readerNoItemBehavior
            ) {
                switch ($model->readerNoItemBehavior) {
                    case '404':
                        throw new PageNotFoundException('Page not found: ' . Environment::get('uri'));
                    case 'forward':
                        $jumpToPageModel = PageModel::findByPk($model->jumpTo);
                        if ($jumpToPageModel && (!$pageModel || ($pageModel->id !== $jumpToPageModel->id))) {
                            throw new RedirectResponseException($jumpToPageModel->getAbsoluteUrl());
                        }
                        break;
                    case 'empty':
                        return $template->getResponse();
                }
            }

            if ($readerConfigModel->disable404) {
                return $template->getResponse();
            }

            throw new PageNotFoundException('Page not found: ' . Environment::get('uri'));
        }

        Controller::loadDataContainer($readerConfigModel->dataContainer);
        Controller::loadLanguageFile($readerConfigModel->dataContainer);

        // add default css id and class to templates
        $cssID = StringUtil::deserialize($model->cssID);
        if (!$template->cssID) {
            $cssID[0] = 'huh-reader-'.$model->id;
        }
        $cssID[1] = $template->class;
        $this->addCssAttributesToTemplate($template, '', $cssID, ['huh-reader', $readerConfigModel->dataContainer]);


        if (!$readerManager->checkPermission()) {
            StatusMessage::addError($this->translator->trans('huh.reader.messages.permissionDenied'), $model->id);
            $template->invalid = true;

            return $template->getResponse();
        }

        $readerManager->doFieldDependentRedirect();
        $readerManager->setHeadTags();
        $readerManager->setCanonicalLink();

        $template->item = $readerManager->getItem()->parse();

        return $template->getResponse();
    }
}