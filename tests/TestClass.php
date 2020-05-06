<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ReaderBundle\Tests;

use HeimrichHannot\ReaderBundle\Item\ItemInterface;
use HeimrichHannot\ReaderBundle\Model\ReaderConfigElementModel;

class TestClass
{
    public function delete(ItemInterface $item, ReaderConfigElementModel &$readerConfigElement)
    {
        $readerConfigElement->isDeleted = true;
    }
}
