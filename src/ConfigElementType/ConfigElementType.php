<?php

/*
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ReaderBundle\ConfigElementType;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use HeimrichHannot\ReaderBundle\Item\ItemInterface;
use HeimrichHannot\ReaderBundle\Model\ReaderConfigElementModel;

/**
 * Interface ConfigElementType.
 *
 * @deprecated Use ReaderConfigElementTypeInterface instead
 */
interface ConfigElementType
{
    public function __construct(ContaoFrameworkInterface $framework);

    public function addToItemData(ItemInterface $item, ReaderConfigElementModel $readerConfigElement);
}
