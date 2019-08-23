<?php

/*
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ReaderBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class ReaderExtension extends Extension
{
    public function getAlias()
    {
        return 'huh_reader';
    }

    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration(true);
        $processedConfig = $this->processConfiguration($configuration, $configs);

        $container->setParameter('huh.reader', $processedConfig);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

        $loader->load('services.yml');
        $loader->load('datacontainers.yml');
        $loader->load('listener.yml');
    }
}
