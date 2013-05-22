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

    public function testFilesConvertedToModules()
    {
        $mapping = new NamespaceMapping('js');
        $mapping->registerNamespace(__DIR__ . '/dir', 'modules');

        $this->assertEquals('js/modules/file.js', $mapping->getModulePath(__DIR__ . '/dir/file.js'), 'Incorrect file-to-module conversion');
    }

    public function testExtraSlashesIgnored()
    {
        $mapping = new NamespaceMapping('js//');
        $mapping->registerNamespace(__DIR__ . '/dir', '/modules/');

        $this->assertEquals('js/modules/file.js', $mapping->getModulePath(__DIR__ . '/dir/file.js'), 'Incorrect file-to-module conversion');
    }
    
    public function testRelativePathsReduced()
    {
        $mapping = new NamespaceMapping('js');
        $mapping->registerNamespace(__DIR__ . '/dir/../dir', 'modules');

        $this->assertEquals('js/modules/file.js', $mapping->getModulePath(__DIR__ . '/../Configuration/dir/file.js'), 'Incorrect file-to-module conversion');
    }
    
    public function testNonexistentNamespaceReturnsFalse()
    {
        $mapping = new NamespaceMapping('js');

        $this->assertFalse($mapping->getModulePath(__DIR__ . '/dir/file.js'), 'Non-existent module expected not to be converted');
    }

    public function testFilesRenamedToJs()
    {
        $mapping = new NamespaceMapping('js');
        $mapping->registerNamespace(__DIR__ . '/dir', 'modules');

        $this->assertEquals('js/modules/file2.js', $mapping->getModulePath(__DIR__ . '/dir/file2.coffee'), 'Incorrect file-to-module conversion');
    }
}
