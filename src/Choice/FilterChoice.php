<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ReaderBundle\Choice;

use HeimrichHannot\FilterBundle\Model\FilterConfigModel;
use HeimrichHannot\UtilsBundle\Choice\AbstractChoice;

class FilterChoice extends AbstractChoice
{
    /**
     * @return array
     */
    protected function collect()
    {
        $choices = [];

        $dataContainers = $this->getContext();

        if (!is_array($dataContainers)) {
            $dataContainers = [$dataContainers];
        }

        /**
         * @var FilterConfigModel
         */
        $adapter = $this->framework->getAdapter(FilterConfigModel::class);

        if (!empty($dataContainers)) {
            $filters = $adapter->findByDataContainers($dataContainers);
        } else {
            $filters = $adapter->findAll();
        }

        if (null !== $filters) {
            $choices = $filters->fetchEach('title');
        }

        return $choices;
    }
}
