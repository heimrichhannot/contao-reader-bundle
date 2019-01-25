<?php

/*
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ReaderBundle\Choice;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use HeimrichHannot\ReaderBundle\Registry\ReaderConfigRegistry;
use HeimrichHannot\UtilsBundle\Choice\AbstractChoice;

class ParentReaderConfigChoice extends AbstractChoice
{
    /** @var ReaderConfigRegistry */
    protected $readerConfigRegistry;

    public function __construct(ContaoFrameworkInterface $framework, ReaderConfigRegistry $readerConfigRegistry)
    {
        $this->framework = $framework;
        $this->readerConfigRegistry = $readerConfigRegistry;

        parent::__construct($framework);
    }

    /**
     * @return array
     */
    protected function collect()
    {
        $id = $this->getContext()['id'];

        if (!$id
            || null === ($readerConfigs = $this->readerConfigRegistry->findBy(
                [
                    'tl_reader_config.id != ?',
                ],
                [
                    $id,
                ]
            ))
        ) {
            return [];
        }

        $choices = array_combine(
            $readerConfigs->fetchEach('id'),
            $readerConfigs->fetchEach('title')
        );

        asort($choices);

        return $choices;
    }
}
