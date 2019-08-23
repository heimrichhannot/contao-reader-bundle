<?php

/*
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ReaderBundle;

use HeimrichHannot\ReaderBundle\DependencyInjection\Compiler\ReaderCompilerPass;
use HeimrichHannot\ReaderBundle\DependencyInjection\ReaderExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
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

    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new ReaderCompilerPass());
        parent::build($container);
    }
}
