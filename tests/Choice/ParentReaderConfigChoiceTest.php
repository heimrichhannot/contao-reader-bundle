<?php

/*
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ReaderBundle\Tests\Choice;

use Contao\Model\Collection;
use Contao\System;
use HeimrichHannot\ReaderBundle\Choice\ParentReaderConfigChoice;
use HeimrichHannot\ReaderBundle\Registry\ReaderConfigRegistry;
use HeimrichHannot\ReaderBundle\Tests\TestCaseEnvironment;
use Symfony\Component\HttpKernel\Kernel;

class ParentReaderConfigChoiceTest extends TestCaseEnvironment
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
        $reflectionClass = new \ReflectionClass(ParentReaderConfigChoice::class);
        $testMethod = $reflectionClass->getMethod('collect');
        $testMethod->setAccessible(true);

        $collection = $this->createMock(Collection::class);
        $collection->method('fetchEach')->willReturnCallback(function ($input) {
            switch ($input) {
                case 'id':
                    return [1, 2, 3];

                    break;

                case 'title':
                    return ['title1', 'title2', 'title3'];

                    break;

                default:
                    return [];

                    break;
            }
        });
        $readerConfigRegistry = $this->createMock(ReaderConfigRegistry::class);
        $readerConfigRegistry->method('findBy')->willReturn(null);

        $parentChoice = new ParentReaderConfigChoice($this->mockContaoFramework(), $readerConfigRegistry);
        $result = $testMethod->invokeArgs($parentChoice, []);
        $this->assertSame([], $result);

        $parentChoice->setContext(['id' => 12]);
        $result = $testMethod->invokeArgs($parentChoice, []);
        $this->assertSame([], $result);

        // check return array
        $readerConfigRegistry = $this->createMock(ReaderConfigRegistry::class);
        $readerConfigRegistry->method('findBy')->willReturn($collection);

        $parentChoice = new ParentReaderConfigChoice($this->mockContaoFramework(), $readerConfigRegistry);
        $parentChoice->setContext(['id' => 12]);
        $result = $testMethod->invokeArgs($parentChoice, []);
        $this->assertSame(['1' => 'title1', '2' => 'title2', '3' => 'title3'], $result);
    }
}
