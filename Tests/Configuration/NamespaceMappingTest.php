<?php

/*
 * This file is part of the HearsayRequireJSBundle package.
 *
 * (c) Hearsay News Products, Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
     * @covers Hearsay\RequireJSBundle\Configuration\NamespaceMapping::registerNamespace
     * @covers Hearsay\RequireJSBundle\Configuration\NamespaceMapping::getModulePath
     */
    public function testFilesInDirectoryConvertedToModules()
    {
        $this->mapping->registerNamespace('modules', __DIR__ . '/dir');

        $this->assertEquals(
            'js/modules/file.js',
            $this->mapping->getModulePath(__DIR__ . '/dir/file.js'),
            'Incorrect file-to-module conversion'
        );
    }

    /**
     * @covers Hearsay\RequireJSBundle\Configuration\NamespaceMapping::registerNamespace
     * @covers Hearsay\RequireJSBundle\Configuration\NamespaceMapping::getModulePath
     */
    public function testFileConvertedToModule()
    {
        $this->mapping->registerNamespace('module', __DIR__ . '/dir/file.js');

        $this->assertEquals(
            'js/module/file.js',
            $this->mapping->getModulePath(__DIR__ . '/dir/file.js'),
            'Incorrect file-to-module conversion'
        );
    }

    /**
     * @covers Hearsay\RequireJSBundle\Configuration\NamespaceMapping::registerNamespace
     * @covers Hearsay\RequireJSBundle\Configuration\NamespaceMapping::getModulePath
     */
    public function testExtraSlashesIgnored()
    {
        $mapping = new NamespaceMapping('js//');
        $mapping->registerNamespace('/modules/', __DIR__ . '/dir');

        $this->assertEquals(
            'js/modules/file.js',
            $mapping->getModulePath(__DIR__ . '/dir/file.js'),
            'Incorrect file-to-module conversion'
        );
    }

    /**
     * @covers Hearsay\RequireJSBundle\Configuration\NamespaceMapping::registerNamespace
     * @covers Hearsay\RequireJSBundle\Configuration\NamespaceMapping::getModulePath
     */
    public function testRelativePathsReduced()
    {
        $this->mapping->registerNamespace('modules', __DIR__ . '/dir/../dir');

        $this->assertEquals(
            'js/modules/file.js',
            $this->mapping->getModulePath(__DIR__ . '/../Configuration/dir/file.js'),
            'Incorrect file-to-module conversion'
        );
    }

    /**
     * @covers Hearsay\RequireJSBundle\Configuration\NamespaceMapping::registerNamespace
     * @covers Hearsay\RequireJSBundle\Configuration\NamespaceMapping::getModulePath
     */
    public function testCoffeeFilesInDirectoryRenamedToJs()
    {
        $this->mapping->registerNamespace('modules', __DIR__ . '/dir');

        $this->assertEquals(
            'js/modules/file2.js',
            $this->mapping->getModulePath(__DIR__ . '/dir/file2.coffee'),
            'Incorrect file-to-module conversion'
        );
    }

    /**
     * @covers Hearsay\RequireJSBundle\Configuration\NamespaceMapping::registerNamespace
     * @covers Hearsay\RequireJSBundle\Configuration\NamespaceMapping::getModulePath
     */
    public function testCoffeeFileRenamedToJs()
    {
        $this->mapping->registerNamespace('modules', __DIR__ . '/dir/file2.coffee');

        $this->assertEquals(
            'js/modules/file2.js',
            $this->mapping->getModulePath(__DIR__ . '/dir/file2.coffee'),
            'Incorrect file-to-module conversion'
        );
    }
}
