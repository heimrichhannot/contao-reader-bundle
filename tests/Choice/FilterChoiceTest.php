<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ReaderBundle\Tests\Choice;

use Contao\Model\Collection;
use Contao\System;
use HeimrichHannot\FilterBundle\Model\FilterConfigModel;
use HeimrichHannot\ReaderBundle\Choice\FilterChoice;
use HeimrichHannot\ReaderBundle\Tests\TestCaseEnvironment;
use Symfony\Component\HttpKernel\Kernel;

class FilterChoiceTest extends TestCaseEnvironment
{
    public function setUp(): void
    {
        parent::setUp();

        $kernel = $this->createMock(Kernel::class);
        $kernel->method('getCacheDir')->willReturn(TL_ROOT.'/tmp');

        $container = System::getContainer();
        $container->set('kernel', $kernel);
        System::setContainer($container);
    }

    public function testCollect()
    {
        $filters = $this->createMock(Collection::class);
        $filters->method('fetchEach')->willReturn(['title1', 'title2', 'title3']);

        $filterConfigModel = $this->mockAdapter(['findByDataContainers', 'findAll']);
        $filterConfigModel->method('findByDataContainers')->willReturn(null);
        $filterConfigModel->method('findAll')->willReturn($filters);

        $reflectionClass = new \ReflectionClass(FilterChoice::class);
        $testMethod = $reflectionClass->getMethod('collect');
        $testMethod->setAccessible(true);
        $itemChoice = new FilterChoice($this->mockContaoFramework([FilterConfigModel::class => $filterConfigModel]));
        $result = $testMethod->invokeArgs($itemChoice, []);
        $this->assertSame([], $result);

        $filterConfigModel = $this->mockAdapter(['findByDataContainers', 'findAll']);
        $filterConfigModel->method('findByDataContainers')->willReturn($filters);
        $filterConfigModel->method('findAll')->willReturn($filters);

        $itemChoice = new FilterChoice($this->mockContaoFramework([FilterConfigModel::class => $filterConfigModel]));
        $result = $testMethod->invokeArgs($itemChoice, []);
        $this->assertSame(['title1', 'title2', 'title3'], $result);
    }
}
