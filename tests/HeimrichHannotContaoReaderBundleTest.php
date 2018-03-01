<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ReaderBundle\Tests;

use HeimrichHannot\ReaderBundle\DependencyInjection\ReaderExtension;
use HeimrichHannot\ReaderBundle\HeimrichHannotContaoReaderBundle;
use PHPUnit\Framework\TestCase;

class HeimrichHannotContaoReaderBundleTest extends TestCase
{
    /**
     * Tests the object instantiation.
     */
    public function testCanBeInstantiated()
    {
        $bundle = new HeimrichHannotContaoReaderBundle();
        $this->assertInstanceOf(HeimrichHannotContaoReaderBundle::class, $bundle);
    }

    /**
     * Tests the getContainerExtension() method.
     */
    public function testReturnsTheContainerExtension()
    {
        $bundle = new HeimrichHannotContaoReaderBundle();
        $this->assertInstanceOf(ReaderExtension::class, $bundle->getContainerExtension());
    }
}
