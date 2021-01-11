<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ReaderBundle\Tests\Choice;

use Contao\System;
use HeimrichHannot\ReaderBundle\Choice\ReaderItemTemplateChoice;
use HeimrichHannot\ReaderBundle\Tests\TestCaseEnvironment;
use HeimrichHannot\UtilsBundle\Choice\TwigTemplateChoice;
use Symfony\Component\HttpKernel\Kernel;

class ReaderItemTemplateChoiceTest extends TestCaseEnvironment
{
    public function setUp(): void
    {
        parent::setUp();

        $kernel = $this->createMock(Kernel::class);
        $kernel->method('getCacheDir')->willReturn(TL_ROOT.'/tmp');

        $utilsChoiceTwig = $this->createMock(TwigTemplateChoice::class);
        $utilsChoiceTwig->method('setContext')->willReturnSelf();
        $utilsChoiceTwig->method('getCachedChoices')->willReturn(['template1']);

        $container = System::getContainer();
        $container->setParameter('huh.reader', ['reader' => ['templates' => ['item' => [['template' => 'template1', 'name' => 'name1'], ['templat' => 'template1', 'name' => 'name1']], 'item_prefixes' => true]]]);
        $container->set('huh.utils.choice.twig_template', $utilsChoiceTwig);
        $container->set('kernel', $kernel);
        System::setContainer($container);
    }

    public function testCollect()
    {
        $reflectionClass = new \ReflectionClass(ReaderItemTemplateChoice::class);
        $testMethod = $reflectionClass->getMethod('collect');
        $testMethod->setAccessible(true);
        $readerItemTemplateChoice = new ReaderItemTemplateChoice($this->mockContaoFramework());
        $result = $testMethod->invokeArgs($readerItemTemplateChoice, []);
        $this->assertSame(['name1' => 'template1 (Yaml)'], $result);

        $container = System::getContainer();
        $container->setParameter('huh.reader', []);
        System::setContainer($container);
        $result = $testMethod->invokeArgs($readerItemTemplateChoice, []);
        $this->assertSame([], $result);
    }
}
