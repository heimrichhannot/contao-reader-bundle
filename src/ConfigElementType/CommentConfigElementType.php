<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ReaderBundle\ConfigElementType;

use Contao\Comments;
use Contao\Controller;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\FrontendTemplate;
use Contao\FrontendUser;
use Contao\StringUtil;
use Contao\System;
use HeimrichHannot\ReaderBundle\Backend\ReaderConfigElement;
use HeimrichHannot\ReaderBundle\Item\ItemInterface;
use HeimrichHannot\ReaderBundle\Model\ReaderConfigElementModel;

class CommentConfigElementType implements ReaderConfigElementTypeInterface
{
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
        $template = $this->getCommentTemplate($readerConfigElement);

        $comments = new Comments();
        $comments->addCommentsToTemplate(
            $template,
            $this->getCommentConfig($readerConfigElement),
            $item->getDataContainer(),
            $item->getRawValue('id'),
            $this->getCommentNotifies($readerConfigElement)
        );

        if ($readerConfigElement->commentOverridePalette) {
            $template->fields = $this->modifyFields($template->fields, $readerConfigElement);
        }

        if ($readerConfigElement->commentHideFields) {
            $template->hiddenFields = $this->getHiddenFields($readerConfigElement);
        }

        // replace insert tags because of request token esi issue in prod mode
        $item->setFormattedValue('comments', Controller::replaceInsertTags($template->parse()));
    }

    /**
     * Return the reader config element type alias.
     */
    public static function getType(): string
    {
        return 'comment';
    }

    /**
     * Return the reader config element type palette.
     */
    public function getPalette(): string
    {
        return '{config_legend},commentTemplate,commentCustomTemplate,commentNotify,commentSortOrder,commentPerPage,commentModerate,commentBbcode,commentRequireLogin,commentDisableCaptcha,commentOverridePalette,commentHideFields;';
    }

    /**
     * Update the item data.
     */
    public function addToReaderItemData(ReaderConfigElementData $configElementData): void
    {
        $this->addToItemData($configElementData->getItem(), $configElementData->getReaderConfigElement());
    }

    /**
     * @return FrontendTemplate
     */
    protected function getCommentTemplate(ReaderConfigElementModel $config)
    {
        return new FrontendTemplate($config->commentCustomTemplate ?: 'mod_comments_reader');
    }

    /**
     * @return \stdClass
     */
    protected function getCommentConfig(ReaderConfigElementModel $config)
    {
        $commentConfig = new \stdClass();
        $commentConfig->perPage = $config->perPage;
        $commentConfig->order = $config->sortOrder;
        $commentConfig->template = $config->commentTemplate;
        $commentConfig->requireLogin = $config->requireLogin;
        $commentConfig->disableCaptcha = $config->disableCaptcha;
        $commentConfig->bbcode = $config->bbcode;
        $commentConfig->moderate = $config->moderate;

        return $commentConfig;
    }

    /**
     * @return array
     */
    protected function getCommentNotifies(ReaderConfigElementModel $config)
    {
        $notifies = [];

        // Notify the system administrator
        if ('notify_author' != $config->notify) {
            $notifies[] = $GLOBALS['TL_ADMIN_EMAIL'];
        }

        // Notify the author
        if ('notify_admin' != $config->notify && null !== ($user = FrontendUser::getInstance()) && $user->email) {
            $notifies[] = $user->email;
        }

        return $notifies;
    }

    /**
     * remove not used fields from palette.
     *
     * @return array
     */
    protected function modifyFields(array $standardFields, ReaderConfigElementModel $config)
    {
        if (empty($overridePalette = $this->getOverridePaletteFields($config))) {
            return $standardFields;
        }

        $fields = [];

        foreach ($standardFields as $key => $field) {
            if (!\in_array($key, $overridePalette)) {
                continue;
            }

            $fields[$key] = $field;
        }

        return $fields;
    }

    /**
     * @return array
     */
    protected function getOverridePaletteFields(ReaderConfigElementModel $config)
    {
        if (!$config->commentPalette) {
            return [];
        }

        $overridePalette = StringUtil::deserialize($config->commentPalette, true);

        if (!$config->disableCaptcha) {
            $overridePalette[] = 'captcha';
        }

        return $overridePalette;
    }

    /**
     * @param ReaderConfigElement $config
     *
     * @return array
     */
    protected function getHiddenFields(ReaderConfigElementModel $config)
    {
        if (!$config->commentHideFieldsPalette) {
            return [];
        }

        return StringUtil::deserialize($config->commentHideFieldsPalette);
    }
}
