<?php

/*
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ReaderBundle\Tests\Choice;

use Contao\System;
use HeimrichHannot\ReaderBundle\Choice\ManagerChoice;
use HeimrichHannot\ReaderBundle\Tests\TestCaseEnvironment;
use Symfony\Component\HttpKernel\Kernel;

class ManagerChoiceTest extends TestCaseEnvironment
{
    public function setUp(): void
    {
        parent::setUp();

        $kernel = $this->createMock(Kernel::class);
        $kernel->method('getCacheDir')->willReturn(TL_ROOT.'/tmp');

        $container = System::getContainer();
        $container->setParameter('huh.reader', ['reader' => ['managers' => [['name' => 'name1', 'id' => 'class1'], ['name' => 'name3', 'id' => 'class3'], ['nam' => 'name3', 'id' => 'class3'], ['name' => 'name2', 'id' => 'class2']]]]);
        $container->set('kernel', $kernel);
        System::setContainer($container);
    }

    public function testCollect()
    {
        $reflectionClass = new \ReflectionClass(ManagerChoice::class);
        $testMethod = $reflectionClass->getMethod('collect');
        $testMethod->setAccessible(true);
        $itemChoice = new ManagerChoice($this->mockContaoFramework());
        $result = $testMethod->invokeArgs($itemChoice, []);
        $this->assertSame(['name1' => 'class1', 'name2' => 'class2', 'name3' => 'class3'], $result);

        $container = System::getContainer();
        $container->setParameter('huh.reader', []);
        System::setContainer($container);

        $result = $testMethod->invokeArgs($itemChoice, []);
        $this->assertSame([], $result);
    }
}
