<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ReaderBundle\Tests\Choice;

use Contao\System;
use HeimrichHannot\ReaderBundle\Choice\SyndicationPdfReaderChoice;
use HeimrichHannot\ReaderBundle\Tests\TestCaseEnvironment;
use Symfony\Component\HttpKernel\Kernel;

class SyndicationPdfReaderChoiceTest extends TestCaseEnvironment
{
    public function setUp(): void
    {
        parent::setUp();

        $kernel = $this->createMock(Kernel::class);
        $kernel->method('getCacheDir')->willReturn(TL_ROOT.'/tmp');

        $container = System::getContainer();
        $container->setParameter('huh.reader', ['reader' => ['syndication_pdf_readers' => [['class' => 'class1', 'name' => 'name1'], ['nam' => 'template1', 'name' => 'name1']]]]);
        $container->set('kernel', $kernel);
        System::setContainer($container);
    }

    public function testCollect()
    {
        $reflectionClass = new \ReflectionClass(SyndicationPdfReaderChoice::class);
        $testMethod = $reflectionClass->getMethod('collect');
        $testMethod->setAccessible(true);
        $readerItemTemplateChoice = new SyndicationPdfReaderChoice($this->mockContaoFramework());
        $result = $testMethod->invokeArgs($readerItemTemplateChoice, []);
        $this->assertSame(['name1' => 'class1'], $result);

        $container = System::getContainer();
        $container->setParameter('huh.reader', []);
        System::setContainer($container);
        $result = $testMethod->invokeArgs($readerItemTemplateChoice, []);
        $this->assertSame([], $result);
    }
}
