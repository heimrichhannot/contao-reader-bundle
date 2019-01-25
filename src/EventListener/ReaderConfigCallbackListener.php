<?php

/*
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ReaderBundle\EventListener;

use Contao\Controller;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\DC_Table;
use Contao\Input;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;
use Symfony\Component\Translation\TranslatorBagInterface;
use Symfony\Component\Translation\TranslatorInterface;

class ReaderConfigCallbackListener
{
    const SELECTOR_FIELD = 'typeSelectorField';
    const TYPE_FIELD = 'typeField';

    /**
     * @var ContaoFrameworkInterface
     */
    private $framework;
    /**
     * @var TranslatorInterface
     */
    private $translator;
    /**
     * @var ModelUtil
     */
    private $modelUtil;

    public function __construct(ContaoFrameworkInterface $framework, TranslatorInterface $translator, ModelUtil $modelUtil)
    {
        $this->framework = $framework;
        $this->translator = $translator;
        $this->modelUtil = $modelUtil;
    }

    /**
     * onload_callback.
     *
     * @param DC_Table $dc
     */
    public function updateLabel(DC_Table $dc)
    {
        /** @var Input $input */
        $input = $this->framework->getAdapter(Input::class);

        if (!$input->get('act') || 'edit' !== $input->get('act')) {
            return;
        }

        if (!$this->translator instanceof TranslatorBagInterface) {
            return;
        }
        $table = $dc->table;
        $configModel = $this->modelUtil->findModelInstanceByIdOrAlias($table, $dc->id);

        if (!$configModel) {
            return;
        }
        $type = $configModel->type;
        Controller::loadDataContainer($table);
        $dca = &$GLOBALS['TL_DCA'][$table];

        if (!strpos($dca['palettes'][$type], static::SELECTOR_FIELD)) {
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
