<?php

/*
 * This file is part of the HearsayRequireJSBundle package.
 *
 * (c) Hearsay News Products, Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hearsay\RequireJSBundle\Tests\Assetic\Factory\Loader;

use Hearsay\RequireJSBundle\Assetic\Factory\Loader\ModuleFormulaLoader;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for the module formula loader.
 * @author Kevin Montag <kevin@hearsay.it>
 */
class ModuleFormulaLoaderTest extends TestCase
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

        $mapping = $this->createMock('Hearsay\RequireJSBundle\Configuration\NamespaceMappingInterface');
        $mapping->expects($this->at(0))
                ->method('getModulePath')
                ->with($file1)
                ->will($this->returnValue('first/module'));
        $mapping->expects($this->at(1))
                ->method('getModulePath')
                ->with($file2)
                ->will($this->returnValue('second/module'));

        $resource = $this->createMock('Assetic\Factory\Resource\ResourceInterface');
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
