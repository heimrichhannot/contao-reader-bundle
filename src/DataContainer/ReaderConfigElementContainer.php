<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ReaderBundle\DataContainer;

use Contao\Config;
use Contao\Controller;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\ServiceAnnotation\Callback;
use Contao\DataContainer;
use Contao\Date;
use Contao\Input;
use Contao\Message;
use HeimrichHannot\ConfigElementTypeBundle\ConfigElementType\ConfigElementTypeInterface;
use HeimrichHannot\ReaderBundle\ConfigElementType\SyndicationConfigElementType;
use HeimrichHannot\ReaderBundle\Model\ReaderConfigElementModel;
use HeimrichHannot\ReaderBundle\Registry\ReaderConfigElementRegistry;
use HeimrichHannot\UtilsBundle\Util\Utils;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Translation\TranslatorBagInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ReaderConfigElementContainer
{
    public const RELATED_CRITERIUM_TAGS = 'tags';
    public const RELATED_CRITERIUM_CATEGORIES = 'categories';

    const SELECTOR_FIELD = 'typeSelectorField';
    const TYPE_FIELD = 'typeField';

    protected const PREPEND_PALETTE = '{title_type_legend},title,type,templateVariable;';
    protected const APPEND_PALETTE = '';

    private ReaderConfigElementRegistry $configElementTypeRegistry;
    private ContainerInterface $container;
    private TranslatorInterface $translator;
    private ContaoFramework $framework;
    private Utils $utils;

    /**
     * ReaderConfigElementContainer constructor.
     */
    public function __construct(ReaderConfigElementRegistry $configElementTypeRegistry, ContainerInterface $container, TranslatorInterface $translator, ContaoFramework $framework, Utils $utils)
    {
        $this->configElementTypeRegistry = $configElementTypeRegistry;
        $this->container = $container;
        $this->translator = $translator;
        $this->framework = $framework;
        $this->utils = $utils;
    }

    public function getRelatedCriteriaAsOptions()
    {
        $options = [];

        if (class_exists('\Codefog\TagsBundle\CodefogTagsBundle')) {
            $options[] = static::RELATED_CRITERIUM_TAGS;
        }

        if (class_exists('\HeimrichHannot\CategoriesBundle\CategoriesBundle')) {
            $options[] = static::RELATED_CRITERIUM_CATEGORIES;
        }

        return $options;
    }

    /**
     * Update dca palettes with config element types palettes.
     *
     * @param $dc
     *
     * @Callback(table="tl_reader_config_element", target="config.onload")
     */
    public function onLoadCallback(DataContainer $dc = null): void
    {
        $this->updateLabel($dc);

        /** @var $readerConfigElementModel ReaderConfigElementModel */
        if (null === ($readerConfigElementModel = $this->container->get('huh.utils.model')->findModelInstanceByPk('tl_reader_config_element', $dc->id))) {
            return;
        }

        $configElementTypes = $this->configElementTypeRegistry->getReaderConfigElementTypes();

        if (empty($configElementTypes)) {
            return;
        }

        foreach ($configElementTypes as $readerConfigElementType) {
            if ($readerConfigElementType instanceof ConfigElementTypeInterface) {
                $palette = $readerConfigElementType->getPalette(static::PREPEND_PALETTE, static::APPEND_PALETTE);
            } else {
                $palette = static::PREPEND_PALETTE.$readerConfigElementType->getPalette().static::APPEND_PALETTE;
            }
            $GLOBALS['TL_DCA'][ReaderConfigElementModel::getTable()]['palettes'][$readerConfigElementType::getType()] = $palette;
        }

        $readConfigElement = $this->configElementTypeRegistry->getReaderConfigElementType($readerConfigElementModel->type);

        if ($readConfigElement && $readConfigElement instanceof ConfigElementTypeInterface) {
            $GLOBALS['TL_DCA'][ReaderConfigElementModel::getTable()]['fields']['templateVariable']['eval']['mandatory'] = true;
        }

        if (\in_array($readerConfigElementModel->type, [SyndicationConfigElementType::getType()])) {
            Message::addInfo($GLOBALS['TL_LANG']['ERR']['readerBundleConfigElementTypeDeprecated']);
        }
    }

    /**
     * Return a list of content element types for dca options callback.
     *
     * @return array
     */
    public function getConfigElementTypes()
    {
        $types = array_keys($this->configElementTypeRegistry->getReaderConfigElementTypes());

        /**
         * @todo remove in next major version
         */
        $readerConfig = $this->container->getParameter('huh.reader');
        $configElementTypes = $readerConfig['reader']['config_element_types'];

        if (empty($configElementTypes)) {
            return $types;
        }

        foreach ($configElementTypes as $configElementType) {
            if (\in_array($configElementType['name'], $types)) {
                continue;
            }
            $types[] = $configElementType['name'];
        }

        return $types;
    }

    /**
     * @param $rows
     *
     * @return string
     */
    public function listChildren($rows)
    {
        $reference = $GLOBALS['TL_DCA']['tl_reader_config_element']['fields']['type']['reference'];

        return '<div class="tl_content_left">'.($rows['title'] ?: $rows['id']).' <span style="color:#b3b3b3; padding-left:3px">['
            .$reference[$rows['type']].'] ('
            .Date::parse(Config::get('datimFormat'), trim($rows['dateAdded'])).')</span></div>';
    }

    /**
     * Return all template files of a particular group as array.
     *
     * @return array An array of template names
     */
    public function getCommentTemplates()
    {
        return Controller::getTemplateGroup('com_');
    }

    public function updateLabel(DataContainer $dc)
    {
        /** @var Input $input */
        $input = $this->framework->getAdapter(Input::class);

        if (!$input->get('act') || 'edit' !== $input->get('act')) {
            return;
        }

        if (!$this->translator instanceof TranslatorBagInterface) {
            return;
        }
        $table = ReaderConfigElementModel::getTable();
        $configModel = $this->utils->model()->findModelInstanceByIdOrAlias($table, $dc->id);

        if (!$configModel) {
            return;
        }
        $type = $configModel->type;
        Controller::loadDataContainer($table);
        $dca = &$GLOBALS['TL_DCA'][$table];

        if (empty($dca['palettes'][$type]) || !str_contains($dca['palettes'][$type], static::SELECTOR_FIELD)) {
            return;
        }

        if ($this->translator->getCatalogue()->has("huh.reader.tl_reader_config_element.field.typeSelectorField.$type.name") &&
            $this->translator->getCatalogue()->has("huh.reader.tl_reader_config_element.field.typeSelectorField.$type.desc")) {
            $dca['fields'][static::SELECTOR_FIELD]['label'] = [
                $this->translator->trans('huh.reader.tl_reader_config_element.field.typeSelectorField.'.$type.'.name'),
                $this->translator->trans('huh.reader.tl_reader_config_element.field.typeSelectorField.'.$type.'.desc'),
            ];
        }

        if (!strpos($dca['palettes'][$type], static::TYPE_FIELD)) {
            return;
        }

        if ($this->translator->getCatalogue()->has("huh.reader.tl_reader_config_element.field.typeField.$type.name") &&
            $this->translator->getCatalogue()->has("huh.reader.tl_reader_config_element.field.typeField.$type.desc")) {
            $dca['fields'][static::TYPE_FIELD]['label'] = [
                $this->translator->trans("huh.reader.tl_reader_config_element.field.typeField.$type.name"),
                $this->translator->trans("huh.reader.tl_reader_config_element.field.typeField.$type.desc"),
            ];
        }
    }
}
