<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ReaderBundle\ConfigElementType;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use HeimrichHannot\ReaderBundle\Model\ReaderConfigElementModel;

interface ConfigElementType
{
    public function __construct(ContaoFrameworkInterface $framework);

    public function addToTemplateData(array $item, array &$templateData, ReaderConfigElementModel $readerConfigElement);
}
