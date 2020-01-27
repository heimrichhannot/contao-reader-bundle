<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ReaderBundle\ConfigElementType\Delete;

use HeimrichHannot\ReaderBundle\Item\ItemInterface;
use HeimrichHannot\ReaderBundle\Model\ReaderConfigElementModel;

interface DeleteInterface
{
    /**
     * @return mixed
     */
    public function delete(ItemInterface $item, ReaderConfigElementModel $readerConfigElement);
}
