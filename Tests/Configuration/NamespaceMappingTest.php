<?php

/**
 * Copyright (c) 2011 Hearsay News Products, Inc.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy 
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights 
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell 
 * copies of the Software, and to permit persons to whom the Software is 
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in 
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR 
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, 
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE 
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER 
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, 
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE 
 * SOFTWARE.
 */

namespace Hearsay\RequireJSBundle\Tests\Configuration;

use Hearsay\RequireJSBundle\Configuration\NamespaceMapping;

/**
 * Unit tests for the namespace mapping container.
 * @author Kevin Montag <kevin@hearsay.it>
 */
class NamespaceMappingTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var NamespaceMapping
     */
    private $mapping;

    /**
     * {@inheritDoc}
     */
    public function setUp()
    {
        parent::setUp();

        $this->mapping = new NamespaceMapping('js');
    }

    /**
     * @covers Hearsay\RequireJSBundle\Configuration\NamespaceMapping::getModulePath
     * @covers Hearsay\RequireJSBundle\Configuration\NamespaceMapping::registerNamespace
     */
    public function testFilesConvertedToModules()
    {
        $this->mapping->registerNamespace(__DIR__ . '/dir', 'modules');

        $this->assertEquals(
            'js/modules/file',
            $this->mapping->getModulePath(__DIR__ . '/dir/file.js'),
            'Incorrect file-to-module conversion'
        );
    }

    /**
     * @covers Hearsay\RequireJSBundle\Configuration\NamespaceMapping::getModulePath
     * @covers Hearsay\RequireJSBundle\Configuration\NamespaceMapping::registerNamespace
     */
    public function testExtraSlashesIgnored()
    {
        $mapping = new NamespaceMapping('js//');
        $mapping->registerNamespace(__DIR__ . '/dir', '/modules/');

        $this->assertEquals(
            'js/modules/file',
            $mapping->getModulePath(__DIR__ . '/dir/file.js'),
            'Incorrect file-to-module conversion'
        );
    }

    /**
     * @covers Hearsay\RequireJSBundle\Configuration\NamespaceMapping::getModulePath
     * @covers Hearsay\RequireJSBundle\Configuration\NamespaceMapping::registerNamespace
     */
    public function testRelativePathsReduced()
    {
        $this->mapping->registerNamespace(__DIR__ . '/dir/../dir', 'modules');

        $this->assertEquals(
            'js/modules/file',
            $this->mapping->getModulePath(__DIR__ . '/../Configuration/dir/file.js'),
            'Incorrect file-to-module conversion'
        );
    }

    /**
     * @covers Hearsay\RequireJSBundle\Configuration\NamespaceMapping::getModulePath
     */
    public function testNonExistentNamespaceReturnsFalse()
    {
        $this->assertFalse(
            $this->mapping->getModulePath(__DIR__ . '/dir/file.js'),
            'Non-existent module expected not to be converted'
        );
    }

    /**
     * @covers Hearsay\RequireJSBundle\Configuration\NamespaceMapping::getModulePath
     * @covers Hearsay\RequireJSBundle\Configuration\NamespaceMapping::registerNamespace
     */
    public function testNonExistentModulePathThrowsException()
    {
        $this->setExpectedException(
            'Hearsay\RequireJSBundle\Exception\PathNotFoundException',
            ''
        );

        $this->mapping->registerNamespace('dir', 'root');
    }
}
