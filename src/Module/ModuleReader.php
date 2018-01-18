<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0+
 */

namespace HeimrichHannot\ReaderBundle\Module;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\ModuleModel;
use Contao\System;
use HeimrichHannot\ReaderBundle\Model\ReaderConfigModel;
use Patchwork\Utf8;

class ModuleReader extends \Contao\Module
{
    protected $strTemplate = 'mod_reader';

    /** @var ContaoFramework */
    protected $framework;

    /** @var ReaderConfigModel */
    protected $readerConfig;

    /**
     * ModuleReader constructor.
     *
     * @param ModuleModel $objModule
     * @param string      $strColumn
     */
    public function __construct(ModuleModel $objModule, $strColumn = 'main')
    {
        $this->framework = System::getContainer()->get('contao.framework');

        parent::__construct($objModule, $strColumn);

        // add class to every reader template
        $cssID = $this->cssID;
        $cssID[1] = $cssID[1].($cssID[1] ? ' ' : '').'huh-reader';

        $this->cssID = $cssID;
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

        return parent::generate();
    }

    protected function compile()
    {
    }
}
