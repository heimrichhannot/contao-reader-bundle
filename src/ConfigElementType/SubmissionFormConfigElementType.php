<?php

/*
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ReaderBundle\ConfigElementType;

use Contao\Module;
use HeimrichHannot\ReaderBundle\Item\ItemInterface;
use HeimrichHannot\ReaderBundle\Model\ReaderConfigElementModel;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;
use Twig\Environment;

class SubmissionFormConfigElementType implements ReaderConfigElementTypeInterface
{
    const TYPE = 'submission_form';

    public static $recipientEmail;
    /**
     * @var Environment
     */
    private $twig;
    /**
     * @var ModelUtil
     */
    private $modelUtil;

    private static $count = 0;

    public function __construct(ModelUtil $modelUtil, Environment $twig)
    {
        $this->modelUtil = $modelUtil;
        $this->twig = $twig;
    }

    public function addToItemData(ItemInterface $item, ReaderConfigElementModel $configElement)
    {
        // add email value to notification center tokens
        static::$recipientEmail = $item->getRawValue($configElement->emailField);
        $GLOBALS['TL_HOOKS']['formhybridBeforeCreateNotifications']['contao-reader-bundle.addEmailToTokens'] = [static::class, 'addEmailToTokens'];

        // generate form
        $moduleId = $item->getModule()->id;

        $table = $item->getManager()->getDataContainer();

        $identifier = $table.'_'.$moduleId.$item->getRawValue('id');

        $item->setFormattedValue($configElement->templateVariable ?: 'submissionForm', $this->twig->render('@HeimrichHannotContaoReader/config_element/submission_form_modal_bootstrap4.html.twig', [
            'identifier' => $identifier,
            'item' => $this,
            'form' => $this->generateSubmissionReader((int) $configElement->submissionReader),
        ]));
    }

    public function addEmailToTokens(&$submissionData, $submission)
    {
        $submissionData['form_value_submission_form_email'] = static::$recipientEmail;

        return true;
    }

    public function generateSubmissionReader(int $submissionReader)
    {
        if (null === ($moduleModel = $this->modelUtil->findModelInstanceByPk('tl_module', $submissionReader))) {
            return '';
        }

        $class = Module::findClass($moduleModel->type);

        if (!class_exists($class)) {
            return '';
        }

        // the form might be more than one time on the page
        $moduleModel->formHybridUseCustomFormIdSuffix = true;
        $moduleModel->formHybridCustomFormIdSuffix = ++static::$count;

        /** @var Module $module */
        $module = new $class($moduleModel);

        return $module->generate();
    }

    /**
     * Return the reader config element type alias.
     *
     * @return string
     */
    public static function getType(): string
    {
        return static::TYPE;
    }

    /**
     * Return the reader config element type palette.
     *
     * @return string
     */
    public function getPalette(): string
    {
        return '{config_legend},submissionFormExplanation,submissionReader,emailField;';
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
