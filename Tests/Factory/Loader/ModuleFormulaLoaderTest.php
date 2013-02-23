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

namespace Hearsay\RequireJSBundle\Tests\Factory\Loader;

use Hearsay\RequireJSBundle\Factory\Loader\ModuleFormulaLoader;

/**
 * Unit tests for the module formula loader.
 * @author Kevin Montag <kevin@hearsay.it>
 */
class ModuleFormulaLoaderTest extends \PHPUnit_Framework_TestCase
{
    public function testFormulaeLoaded()
    {
        $file1 = __DIR__ . '/dir/file';
        $file2 = __DIR__ . '/dir/other_file';
                
        $factory = $this->getMockBuilder('Assetic\Factory\AssetFactory')
                ->disableOriginalConstructor()
                ->getMock();
        $factory->expects($this->at(0))
                ->method('generateAssetName')
                ->with($file1, array())
                ->will($this->returnValue('file'));
        $factory->expects($this->at(1))
                ->method('generateAssetName')
                ->with($file2, array())
                ->will($this->returnValue('other_file'));
        
        $mapping = $this->getMock('Hearsay\RequireJSBundle\Configuration\NamespaceMappingInterface');
        $mapping->expects($this->at(0))
                ->method('getModulePath')
                ->with($file1)
                ->will($this->returnValue('first/module'));
        $mapping->expects($this->at(1))
                ->method('getModulePath')
                ->with($file2)
                ->will($this->returnValue('second/module'));
        
        $resource = $this->getMock('Assetic\Factory\Resource\ResourceInterface');
        $resource->expects($this->any())
                ->method('getContent')
                ->will($this->returnValue($file1 . "\nsome other\ntext\n" . $file2));
        
        $loader = new ModuleFormulaLoader($factory, $mapping);
        $formulae = $loader->load($resource);
        
        $this->assertEquals(array(
            'file' => array($file1, array(), array('output' => 'first/module')),
            'other_file' => array($file2, array(), array('output' => 'second/module'))
        ), $formulae, 'Unexpected formulae loaded');
    }
}
