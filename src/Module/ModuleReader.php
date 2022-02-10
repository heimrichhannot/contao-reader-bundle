<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ReaderBundle\Module;

use Contao\BackendTemplate;
use Contao\Controller;
use Contao\CoreBundle\Exception\PageNotFoundException;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\Environment;
use Contao\Model;
use Contao\Module;
use Contao\ModuleModel;
use Contao\System;
use HeimrichHannot\ReaderBundle\Manager\ReaderManagerInterface;
use HeimrichHannot\ReaderBundle\Model\ReaderConfigModel;
use HeimrichHannot\ReaderBundle\Registry\ReaderConfigRegistry;
use HeimrichHannot\StatusMessages\StatusMessage;
use Symfony\Contracts\Translation\TranslatorInterface;

class ModuleReader extends Module
{
    const TYPE = 'huhreader';

    protected $strTemplate = 'mod_reader';

    /**
     * @var ContaoFramework
     */
    protected $framework;

    /**
     * @var ReaderManagerInterface
     */
    protected $manager;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var ReaderConfigModel
     */
    protected $readerConfig;

    /**
     * @var ReaderConfigRegistry
     */
    protected $readerConfigRegistry;

    /**
     * @var Model
     */
    protected $item;

    /**
     * ModuleReader constructor.
     *
     * @param string $strColumn
     */
    public function __construct(ModuleModel $objModule, $strColumn = 'main')
    {
        $this->framework = System::getContainer()->get('contao.framework');
        $this->translator = System::getContainer()->get('translator');
        $this->readerConfigRegistry = System::getContainer()->get('huh.reader.reader-config-registry');
        $this->readerConfig = $this->readerConfigRegistry->findByPk($objModule->readerConfig);

        $this->manager = $this->readerConfigRegistry->getReaderManagerByName($this->readerConfig->manager ?: 'default');

        parent::__construct($objModule, $strColumn);
    }

    public function generate()
    {
        if (TL_MODE == 'BE') {
            $objTemplate = new BackendTemplate('be_wildcard');
            $objTemplate->wildcard = '### '.$GLOBALS['TL_LANG']['FMD'][$this->type][0].' ###';
            $objTemplate->title = $this->headline;
            $objTemplate->id = $this->id;
            $objTemplate->link = $this->name;
            $objTemplate->href = 'contao?do=themes&amp;table=tl_module&amp;act=edit&amp;id='.$this->id;

            return $objTemplate->parse();
        }

        if (null === $this->manager) {
            return parent::generate();
        }

        $this->framework->getAdapter(Controller::class)->loadDataContainer('tl_reader_config');

        $this->manager->setModuleData($this->arrData);

        $this->item = $this->manager->retrieveItem();

        if (null !== $this->item) {
            $this->manager->triggerOnLoadCallbacks();
        }

        if (null === $this->item) {
            if ($this->readerConfig->disable404) {
                return '';
            }

            throw new PageNotFoundException('Page not found: '.Environment::get('uri'));
        }

        return parent::generate();
    }

    protected function compile()
    {
        $readerConfig = $this->manager->getReaderConfig();

        Controller::loadDataContainer($readerConfig->dataContainer);
        System::loadLanguageFile($readerConfig->dataContainer);

        // apply module fields to template
        $this->Template->headline = $this->headline;
        $this->Template->hl = $this->hl;

        // add class to every reader template
        $cssID = $this->cssID;
        $cssID[0] = $cssID[0] ?: 'huh-reader-'.$this->id;
        $cssID[1] = $cssID[1].($cssID[1] ? ' ' : '').'huh-reader '.$readerConfig->dataContainer;

        $this->cssID = $cssID;

        if (null === $this->item) {
            return;
        }

        if (!$this->manager->checkPermission()) {
            StatusMessage::addError($this->translator->trans('huh.reader.messages.permissionDenied'), $this->id);
            $this->Template->invalid = true;

            return;
        }

        $this->manager->doFieldDependentRedirect();
        $this->manager->setHeadTags();
        $this->manager->setCanonicalLink();

        $this->Template->item = $this->manager->getItem()->parse();
    }
}
