<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ReaderBundle;

use HeimrichHannot\ReaderBundle\DependencyInjection\ReaderExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class HeimrichHannotContaoReaderBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function getContainerExtension()
    {
        return new ReaderExtension();
    }
}
