<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ReaderBundle\EventListener;

use Contao\ContentModel;
use Contao\CoreBundle\DataContainer\PaletteManipulator;
use Contao\CoreBundle\ServiceAnnotation\Callback;
use Contao\DataContainer;
use Contao\ModuleModel;
use Contao\StringUtil;
use HeimrichHannot\ListBundle\DataContainer\ListConfigElementContainer;
use HeimrichHannot\ReaderBundle\ConfigElementType\RelatedConfigElementType;
use HeimrichHannot\ReaderBundle\Controller\ContentElement\RelatedListContentElementController;
use HeimrichHannot\ReaderBundle\Model\ReaderConfigElementModel;
use HeimrichHannot\ReaderBundle\Model\ReaderConfigModel;
use HeimrichHannot\UtilsBundle\Dca\DcaUtil;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class RelatedListListener implements ServiceSubscriberInterface
{
    public const CRITERIUM_TAGS = 'tags';
    public const CRITERIUM_CATEGORIES = 'categories';

    private ContainerInterface $container;
    private RequestStack       $requestStack;
    private DcaUtil            $dcaUtil;

    public function __construct(ContainerInterface $container, RequestStack $requestStack, DcaUtil $dcaUtil)
    {
        $this->container = $container;
        $this->requestStack = $requestStack;
        $this->dcaUtil = $dcaUtil;
    }

    /**
     * @Callback(table="tl_content", target="config.onload", priority=-10)
     * @Callback(table="tl_reader_config_element", target="config.onload", priority=-10)
     */
    public function onLoadCallback(DataContainer $dc = null): void
    {
        if (null === $dc || !$dc->id || 'edit' !== $this->requestStack->getCurrentRequest()->query->get('act')) {
            return;
        }

        if (ReaderConfigElementModel::getTable() === $dc->table) {
            /** @var $readerConfigElementModel ReaderConfigElementModel */
            if (!($readerConfigElementModel = ReaderConfigElementModel::findByPk($dc->id))
                || $readerConfigElementModel->type !== RelatedConfigElementType::getType()) {
                return;
            }
            $criteria = StringUtil::deserialize($readerConfigElementModel->relatedCriteria, true);
            $palette = RelatedConfigElementType::getType();
        } elseif (ContentModel::getTable() === $dc->table) {
            if (!($element = ContentModel::findByPk($dc->id)) || RelatedListContentElementController::TYPE !== $element->type) {
                return;
            }
            $criteria = StringUtil::deserialize($element->relatedCriteria, true);
            $palette = RelatedListContentElementController::TYPE;
        } else {
            return;
        }

        $paletteManipulator = PaletteManipulator::create();
        $isFirstField = false;

        if (\in_array(static::CRITERIUM_TAGS, $criteria)) {
            $paletteManipulator->addField('tagsField', 'relatedCriteria');
        } else {
            $isFirstField = true;
        }

        if (\in_array(static::CRITERIUM_CATEGORIES, $criteria)) {
            $paletteManipulator->addField('categoriesField', $isFirstField ? 'relatedCriteria' : 'tagsField');

            if ($isFirstField) {
                $GLOBALS['TL_DCA'][$dc->table]['fields']['categoriesField']['eval']['tl_class'] .= ' clr';
            }
        }

        $paletteManipulator->applyToPalette($palette, $dc->table);
    }

    /**
     * @Callback(table="tl_content", target="fields.relatedCriteria.options")
     */
    public function onRelatedCriteriaOptionsCallback(): array
    {
        if (class_exists(ListConfigElementContainer::class) && $this->container->has(ListConfigElementContainer::class)) {
            return $this->container->get(ListConfigElementContainer::class)->getRelatedCriteriaAsOptions();
        }

        return [];
    }

    /**
     * @Callback(table="tl_content", target="fields.relatedListModule.options")
     */
    public function onRelatedListModuleOptionsCallback(): array
    {
        $options = [];

        $modules = ModuleModel::findBy(['tl_module.type=?'], ['huhlist']);

        if (!$modules) {
            return $options;
        }

        foreach ($modules as $module) {
            $options[$module->id] = sprintf('%s (ID %d)', $module->name, $module->id);
        }

        return $options;
    }

    /**
     * @Callback(table="tl_content", target="fields.tagsField.options")
     */
    public function onTagsOptionsCallback(DataContainer $dc = null): array
    {
        $element = ContentModel::findByPk($dc->id);

        if (!$element || !($readerConfig = ReaderConfigModel::findByPk($element->readerConfig))) {
            return [];
        }

        return $this->dcaUtil->getFields($readerConfig->dataContainer, ['inputTypes' => ['cfgTags']]);
    }

    /**
     * @Callback(table="tl_content", target="fields.categoriesField.options")
     */
    public function onCategoriesOptionsCallback(DataContainer $dc = null): array
    {
        $element = ContentModel::findByPk($dc->id);

        if (!$element || !($readerConfig = ReaderConfigModel::findByPk($element->readerConfig))) {
            return [];
        }

        return $this->dcaUtil->getFields($readerConfig->dataContainer, ['inputTypes' => ['categoryTree']]);
    }

    public static function getSubscribedServices()
    {
        $services = [];

        if (class_exists(ListConfigElementContainer::class)) {
            $services[] = '?'.ListConfigElementContainer::class;
        }

        return $services;
    }
}
