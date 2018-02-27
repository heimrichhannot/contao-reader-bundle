<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ReaderBundle\Module;

use Contao\Controller;
use Contao\CoreBundle\Exception\PageNotFoundException;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\DataContainer;
use Contao\Environment;
use Contao\Model;
use Contao\ModuleModel;
use Contao\System;
use HeimrichHannot\ReaderBundle\Manager\ReaderManager;
use HeimrichHannot\ReaderBundle\Model\ReaderConfigModel;
use HeimrichHannot\ReaderBundle\Registry\ReaderConfigRegistry;
use HeimrichHannot\StatusMessages\StatusMessage;
use Patchwork\Utf8;
use Symfony\Component\Translation\Translator;

class ModuleReader extends \Contao\Module
{
    protected $strTemplate = 'mod_reader';

    /** @var ContaoFramework */
    protected $framework;

    /** @var ReaderManager */
    protected $manager;

    /** @var Translator */
    protected $translator;

    /** @var ReaderConfigModel */
    protected $readerConfig;

    /** @var ReaderConfigRegistry */
    protected $readerConfigRegistry;

    /** @var Model */
    protected $item;

    /** @var DataContainer */
    protected $dc;

    /**
     * ModuleReader constructor.
     *
     * @param ModuleModel $objModule
     * @param string      $strColumn
     */
    public function __construct(ModuleModel $objModule, $strColumn = 'main')
    {
        $this->framework = System::getContainer()->get('contao.framework');
        $this->manager = System::getContainer()->get('huh.reader.manager.reader');
        $this->translator = System::getContainer()->get('translator');
        $this->readerConfigRegistry = System::getContainer()->get('huh.reader.reader-config-registry');

        parent::__construct($objModule, $strColumn);
    }

    public function generate()
    {
        if (TL_MODE == 'BE') {
            $objTemplate = new \BackendTemplate('be_wildcard');
            $objTemplate->wildcard = '### '.Utf8::strtoupper($GLOBALS['TL_LANG']['FMD'][$this->type][0]).' ###';
            $objTemplate->title = $this->headline;
            $objTemplate->id = $this->id;
            $objTemplate->link = $this->name;
            $objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id='.$this->id;

            return $objTemplate->parse();
        }

        Controller::loadDataContainer('tl_reader_config');

        $this->manager->setModuleData($this->arrData);

        $this->readerConfig = $readerConfig = $this->readerConfigRegistry->computeReaderConfig(
            $this->manager->getReaderConfig()->id
        );

        $this->manager->setReaderConfig($readerConfig);

        $this->item = $this->manager->retrieveItem();

        if (null !== $this->item) {
            $this->manager->createDataContainerFromItem();
            $this->manager->triggerOnLoadCallbacks();
        }

        // throw a 404 if no item found
        if (null === $this->item) {
            throw new PageNotFoundException('Page not found: '.Environment::get('uri'));
        }

        return parent::generate();
    }

    protected function compile()
    {
        $readerConfig = $this->readerConfig;
        $item = $this->item;

        Controller::loadDataContainer($readerConfig->dataContainer);
        System::loadLanguageFile($readerConfig->dataContainer);

        // apply module fields to template
        $this->Template->headline = $this->headline;
        $this->Template->hl = $this->hl;

        // add class to every reader template
        $cssID = $this->cssID;
        $cssID[1] = $cssID[1].($cssID[1] ? ' ' : '').'huh-reader';

        $this->cssID = $cssID;

        if (!$this->manager->checkPermission()) {
            StatusMessage::addError($this->translator->trans('huh.reader.messages.permissionDenied'), $this->id);
            $this->Template->invalid = true;

            return;
        }

        $this->manager->doFieldDependentRedirect();

        $this->manager->setPageTitle();

        $preparedItem = $this->manager->prepareItem($item->row());
        $this->Template->item = $this->manager->parseItem($preparedItem);
    }
}
