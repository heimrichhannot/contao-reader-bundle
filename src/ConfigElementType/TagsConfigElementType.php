<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ReaderBundle\ConfigElementType;

use Contao\Controller;
use Contao\CoreBundle\Exception\RedirectResponseException;
use Contao\Database;
use Contao\System;
use HeimrichHannot\TwigSupportBundle\Filesystem\TwigTemplateLocator;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;
use HeimrichHannot\UtilsBundle\String\StringUtil;
use HeimrichHannot\UtilsBundle\Url\UrlUtil;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Environment;

class TagsConfigElementType implements ReaderConfigElementTypeInterface
{
    /**
     * @var TwigTemplateLocator
     */
    protected $templateLocator;
    /**
     * @var Environment
     */
    private $twig;
    /**
     * @var StringUtil
     */
    private $stringUtil;
    /**
     * @var UrlUtil
     */
    private $urlUtil;
    /**
     * @var ModelUtil
     */
    private $modelUtil;
    private RequestStack $requestStack;

    public function __construct(Environment $twig, StringUtil $stringUtil, UrlUtil $urlUtil, ModelUtil $modelUtil, TwigTemplateLocator $templateLocator, RequestStack $requestStack)
    {
        $this->twig = $twig;
        $this->stringUtil = $stringUtil;
        $this->urlUtil = $urlUtil;
        $this->modelUtil = $modelUtil;
        $this->templateLocator = $templateLocator;
        $this->requestStack = $requestStack;
    }

    public function renderTags($configElement, $item): ?string
    {
        $table = $item->getDataContainer();

        if (!$table || !isset($GLOBALS['TL_DCA'][$table]['fields'][$configElement->tagsField]['eval']['tagsManager']) || !$configElement->tagsField) {
            return '';
        }

        Controller::loadDataContainer($table);

        $source = $GLOBALS['TL_DCA'][$table]['fields'][$configElement->tagsField]['eval']['tagsManager'];

        $nonTlTable = $this->stringUtil->removeLeadingString('tl_', $item->getDataContainer());
        $cfgTable = 'tl_cfg_tag_'.$nonTlTable;

        $tags = [];

        $tagRecords = Database::getInstance()->prepare("SELECT t.* FROM tl_cfg_tag t INNER JOIN $cfgTable t2 ON t.id = t2.cfg_tag_id".
            " WHERE t2.{$nonTlTable}_id=? AND t.source=? ORDER BY t.name")->execute(
            $item->getRawValue('id'),
            $source
        );

        if ($tagRecords->numRows > 0) {
            $tags = $tagRecords->fetchAllAssoc();
        }

        if ($configElement->tagsAddLink) {
            $jumpTo = $this->urlUtil->getJumpToPageUrl($configElement->tagsJumpTo, false);
            $tagId = $tagAlias = null;

            if ($this->requestStack->getCurrentRequest()) {
                $tagId = $this->requestStack->getCurrentRequest()->get('huh_cfg_tag', null);
                $tagAlias = $this->requestStack->getCurrentRequest()->get('huh_cfg_tag_alias', null);
            }

            if ($tagAlias && $jumpTo) {
                $tag = System::getContainer()->get(ModelUtil::class)->findOneModelInstanceBy('tl_cfg_tag', ['tl_cfg_tag.alias=?'], [$tagAlias]);

                if (null !== $tag) {
                    $tagId = $tag->id;
                }
            }

            if ($tagId && $jumpTo) {
                if (null !== ($filterConfigElement = $this->modelUtil->findModelInstanceByPk('tl_filter_config_element', $configElement->tagsFilterConfigElement))) {
                    $sessionKey = System::getContainer()->get('huh.filter.manager')->findById($configElement->tagsFilter)->getSessionKey();

                    $sessionData = System::getContainer()->get('huh.filter.session')->getData($sessionKey);

                    $sessionData = \is_array($sessionData) ? $sessionData : [];

                    $sessionData[$filterConfigElement->field] = urldecode($tagId);

                    System::getContainer()->get('huh.filter.session')->setData($sessionKey, $sessionData);

                    throw new RedirectResponseException('/'.ltrim($jumpTo, '/'), 302);
                }
            }

            foreach ($tags as &$tag) {
                $tag['url'] = $this->urlUtil->addQueryString($configElement->tagsUseAlias ? 'huh_cfg_tag_alias='.urlencode($tag['alias']) : 'huh_cfg_tag='.$tag['id']);
            }
        }

        $data = [
            'configElement' => $configElement,
            'item' => $item,
        ];

        $data['tags'] = $tags;

        return $this->twig->render($this->templateLocator->getTemplatePath($configElement->tagsTemplate), $data);
    }

    /**
     * Return the config element type alias.
     */
    public static function getType(): string
    {
        return 'tags';
    }

    /**
     * Return the config element type palette.
     */
    public function getPalette(): string
    {
        return '{config_legend},tagsField,tagsAddLink,tagsTemplate;';
    }

    /**
     * Update the item data.
     */
    public function addToReaderItemData(ReaderConfigElementData $configElementData): void
    {
        $readerConfigElement = $configElementData->getReaderConfigElement();
        $item = $configElementData->getItem();

        $item->setFormattedValue(
            $readerConfigElement->templateVariable ?: 'tags',
            $this->renderTags($readerConfigElement, $item)
        );

        $configElementData->setItem($item);
    }
}
