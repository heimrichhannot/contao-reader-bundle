<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ReaderBundle\Command;

use Ausi\SlugGenerator\SlugGenerator;
use Contao\CoreBundle\Command\AbstractLockedCommand;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\Model;
use Contao\ModuleModel;
use HeimrichHannot\FilterBundle\Model\FilterConfigElementModel;
use HeimrichHannot\FilterBundle\Model\FilterConfigModel;
use HeimrichHannot\ReaderBundle\Controller\FrontendModule\ReaderFrontendModuleController;
use HeimrichHannot\ReaderBundle\Model\ReaderConfigModel;
use HeimrichHannot\UtilsBundle\Dca\DcaUtil;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class MakeCommand extends AbstractLockedCommand
{
    /**
     * @var SymfonyStyle
     */
    private $io;
    /**
     * @var ContaoFramework
     */
    private $framework;
    /**
     * @var DcaUtil
     */
    private $dcaUtil;
    /**
     * @var ModelUtil
     */
    private $modelUtil;
    /**
     * @var string
     */
    private $name;

    public function __construct(
        ContaoFramework $contaoFramework,
        DcaUtil $dcaUtil,
        ModelUtil $modelUtil,
        $name = null
    ) {
        $this->framework = $contaoFramework;
        $this->dcaUtil = $dcaUtil;
        $this->modelUtil = $modelUtil;
        $this->name = $name;

        parent::__construct($name);
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('huh-reader:make')->setDescription('Creates reader modules based on heimrichhannot/contao-reader-bundle.');
    }

    /**
     * {@inheritdoc}
     */
    protected function executeLocked(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);

        $this->framework->initialize();

        $themes = $this->modelUtil->findAllModelInstances('tl_theme', [
            'order' => 'tl_theme.name ASC',
        ]);

        if (null === $themes) {
            $this->io->error('You need at least 1 record in tl_theme before creating modules.');

            return 0;
        }

        $themeOptions = [];

        while ($themes->next()) {
            $themeOptions[$themes->id] = $themes->name;
        }

        $table = $this->io->ask('Which database entities would you like to display? Please type in the table name', 'tl_news');

        $this->dcaUtil->loadDc($table);

        if (!isset($GLOBALS['TL_DCA'][$table]) || !\is_array($GLOBALS['TL_DCA'][$table])) {
            $this->io->error('No DCA for "'.$table.'" could be found.');

            return 0;
        }

        $dca = $GLOBALS['TL_DCA'][$table];

        if ($this->io->confirm('Did you already create a filter so that we can skip the creation?', false)) {
            $filterConfigId = $this->io->ask('Please type in the filter ID', '0');

            if (null === ($filterConfig = $this->modelUtil->findModelInstanceByPk('tl_filter_config', $filterConfigId))) {
                $this->io->error('No filter found for ID '.$filterConfigId.'.');

                return 0;
            }

            $filterTitle = $filterConfig->title;
        } else {
            [$filterConfig, $filterTitle] = $this->createFilterConfig($table);

            $this->createPublishedFilterConfigElement($table, $filterConfig);
            $this->createArchiveFilterConfigElement($table, $filterConfig, $dca);
        }

        $readerConfig = $this->createReaderConfig($table, $filterTitle, $filterConfig);

        $this->createModule($filterTitle, $themeOptions, $readerConfig);

        return 0;
    }

    protected function createFilterConfig(string $table)
    {
        $slugGenerator = new SlugGenerator();

        $filterTitle = $this->io->ask('Please type in the title of the filter configuration');

        $filterConfig = new FilterConfigModel();

        $filterConfig = $this->dcaUtil->setDefaultsFromDca('tl_filter_config', $filterConfig);

        $filterConfig->dateAdded = $filterConfig->tstamp = time();

        $filterConfig->mergeRow([
            'title' => $filterTitle,
            'name' => $slugGenerator->generate($filterTitle),
            'dataContainer' => $table,
            'template' => 'bootstrap_4_layout',
            'published' => true,
            'type' => 'filter',
        ]);

        $filterConfig->save();

        $this->io->success('Filter config created with ID '.$filterConfig->id.'.');

        return [$filterConfig, $filterTitle];
    }

    protected function createPublishedFilterConfigElement(string $table, Model $filterConfig)
    {
        $dca = $GLOBALS['TL_DCA'][$table];

        $publishedField = $publishedStartField = $publishedStopField = null;
        $invertPublished = false;
        $addStartStop = false;

        if ($this->io->confirm('Would you like to hide unpublished entities?')) {
            if (isset($dca['fields']['published'])) {
                $publishedField = 'published';
            } elseif (isset($dca['fields']['disable'])) {
                $publishedField = 'disable';
                $invertPublished = true;
            } elseif (isset($dca['fields']['invisible'])) {
                $publishedField = 'invisible';
                $invertPublished = true;
            }

            if (isset($dca['fields']['start'])) {
                $publishedStartField = 'start';
                $addStartStop = true;
            }

            if (isset($dca['fields']['stop'])) {
                $publishedStopField = 'stop';
                $addStartStop = true;
            }

            $publishedField = $this->io->ask('Please type in the published field\'s name', $publishedField);
            $invertPublished = $this->io->confirm('Should the published filter be inverted, i.e. is the "published" field defined inversely like "disabled"?', $invertPublished);

            if ($this->io->confirm('Does the entity have a start and stop field for publishing (e.g. tl_news.start and tl_news.stop)?', $addStartStop)) {
                $publishedStartField = $this->io->ask('Please type in the publish start field\'s name', $publishedStartField);
                $publishedStopField = $this->io->ask('Please type in the publish stop field\'s name', $publishedStopField);
            }
        }

        if ($publishedField) {
            $filterConfigElement = new FilterConfigElementModel();

            $filterConfigElement = $this->dcaUtil->setDefaultsFromDca('tl_filter_config_element', $filterConfigElement);

            $filterConfigElement->dateAdded = $filterConfigElement->tstamp = time();

            $filterConfigElement->mergeRow([
                'title' => 'Veröffentlicht',
                'pid' => $filterConfig->id,
                'sorting' => 32,
                'type' => 'visible',
                'field' => $publishedField,
                'published' => true,
            ]);

            if ($invertPublished) {
                $filterConfigElement->invertField = true;
            }

            if ($publishedStartField) {
                $filterConfigElement->addStartAndStop = true;
                $filterConfigElement->startField = $publishedStartField;
                $filterConfigElement->stopField = $publishedStopField;
            }

            $filterConfigElement->save();

            $this->io->success('Filter config element of type "visible" created with ID '.$filterConfigElement->id.'.');
        }
    }

    protected function createArchiveFilterConfigElement(string $table, Model $filterConfig, array $dca)
    {
        $parentTable = $dca['config']['ptable'] ?? null;

        if (!$this->io->confirm('Does the entity have one or more parent entities?', $parentTable ? true : false)) {
            return;
        }

        if (!$this->io->confirm('Would you like to filter the entities based on their parents?', true)) {
            return;
        }

        $parentTable = $this->io->ask('Please specify the parent table you\'d like to filter', $parentTable);
        $pidField = $this->io->ask('Please specify the parent id field in '.$table, isset($dca['fields']['pid']) ? 'pid' : null);

        $archives = $this->modelUtil->findAllModelInstances($parentTable);
        $archiveOptions = [];

        while ($archives->next()) {
            $archiveOptions[$archives->id] = $archives->name ?: ($archives->title ?: $archives->id);
        }

        $pid = $this->io->choice('Please type in the ID of the archive the entities need to be in', $archiveOptions);

        if (!is_numeric($pid)) {
            $pid = array_flip($archiveOptions)[$pid];
        }

        $filterConfigElement = new FilterConfigElementModel();

        $filterConfigElement = $this->dcaUtil->setDefaultsFromDca('tl_filter_config_element', $filterConfigElement);

        $filterConfigElement->dateAdded = $filterConfigElement->tstamp = time();

        $filterConfigElement->mergeRow([
            'title' => 'Archiv',
            'pid' => $filterConfig->id,
            'sorting' => 64,
            'type' => 'parent',
            'field' => $pidField,
            'isInitial' => true,
            'initialValueType' => 'scalar',
            'initialValue' => $pid,
            'operator' => 'equal',
            'published' => true,
        ]);

        $filterConfigElement->save();

        $this->io->success('Filter config element of type "parent" created with ID '.$filterConfigElement->id.'.');
    }

    protected function createReaderConfig(string $table, string $filterTitle, Model $filterConfig)
    {
        $dca = $GLOBALS['TL_DCA'][$table];

        $readerTitle = $this->io->ask('Please type in the title of the reader configuration', $filterTitle);
        $parentReaderConfig = $this->io->ask('Do you want to create a child reader config inheriting from a parent? If so, please type in the parent ID here', 0);

        $useAlias = isset($dca['fields']['alias']);

        if ($useAlias = $this->io->confirm('Would you like to use the entity\'s alias field as auto_item?', $useAlias)) {
            $aliasField = $this->io->ask('Please type in the alias field\'s name', isset($dca['fields']['alias']) ? 'alias' : null);
        }

        $readerConfig = new ReaderConfigModel();

        $readerConfig = $this->dcaUtil->setDefaultsFromDca('tl_reader_config', $readerConfig);

        $readerConfig->dateAdded = $readerConfig->tstamp = time();

        $readerConfig->mergeRow([
            'title' => $readerTitle,
            'dataContainer' => $table,
            'filter' => $filterConfig->id,
            'manager' => 'default',
            'item' => 'default',
            'limitFormattedFields' => true,
            'sortingMode' => 'field',
            'itemRetrievalMode' => 'auto_item',
            'itemRetrievalAutoItemField' => $useAlias ? $aliasField : 'id',
            'readerTemplate' => 'default',
            'itemTemplate' => 'default',
        ]);

        if ($parentReaderConfig) {
            $readerConfig->pid = $parentReaderConfig;
        }

        $readerConfig->save();

        $this->io->success('Reader config created with ID '.$readerConfig->id.'.');

        return $readerConfig;
    }

    protected function createModule(string $filterTitle, array $themeOptions, Model $readerConfig)
    {
        $moduleName = $this->io->ask('Please type in the title of the reader module', $filterTitle);
        $modulePid = $this->io->choice('Please type in the ID of the theme which you\'d like to place the module under', $themeOptions);

        if (!is_numeric($modulePid)) {
            $modulePid = array_flip($themeOptions)[$modulePid];
        }

        $module = new ModuleModel();

        $module = $this->dcaUtil->setDefaultsFromDca('tl_module', $module);

        $module->tstamp = time();

        $module->mergeRow([
            'name' => $moduleName,
            'pid' => $modulePid,
            'type' => ReaderFrontendModuleController::TYPE,
            'readerConfig' => $readerConfig->id,
        ]);

        $module->save();

        $this->io->success('Module created with ID '.$module->id.'.');
    }
}
