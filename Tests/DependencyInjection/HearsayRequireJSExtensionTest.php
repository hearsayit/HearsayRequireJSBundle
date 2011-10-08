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

namespace Hearsay\RequireJSBundle\Tests\DependencyInjection;

use Hearsay\RequireJSBundle\DependencyInjection\HearsayRequireJSExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Unit tests for the bundle loader.
 * @author Kevin Montag <kevin@hearsay.it>
 */
class HearsayRequireJSExtensionTest extends \PHPUnit_Framework_TestCase
{

    public function testNamespacesMapped()
    {
        $config = array(
            'base_directory' => '/home/user/base',
            'paths' => array(
                'namespace' => '/home/user/path',
            ),
            'optimizer' => array(
                'path' => '/path/to/r.js',
            ),
        );
        $container = $this->getContainerBuilder();
        $loader = new HearsayRequireJSExtension();
        
        $loader->load(array($config), $container);
        
        // Check the namespace mapping
        $mapping = $container->getDefinition('hearsay_require_js.namespace_mapping');
        $methods = $mapping->getMethodCalls();
        $this->assertEquals(2, count($methods), 'Incorrect number of method calls on namespace mapping');
        $this->assertContains(array(
            'registerNamespace', array('/home/user/path', 'namespace'),
        ), $methods, 'Did not find expected method call');
        $this->assertContains(array(
            'registerNamespace', array('/home/user/base', ''),
        ), $methods, 'Did not find expected method call');
        
        // Check the optimization filter
        $filter = $container->getDefinition('hearsay_require_js.optimizer_filter');
        $methods = $filter->getMethodCalls();
        $this->assertEquals(1, count($methods), 'Incorrect number of method calls on optimizer filter');
        $this->assertContains(array(
            'setOption', array('paths.namespace', '/home/user/path'),
        ), $methods, 'Did not find expected method call');
        
        // And the Assetic resources
        foreach(array('/home/user/base', '/home/user/path') as $path) {
            /* @var $resource \Symfony\Component\DependencyInjection\DefinitionDecorator */
            $resource = $container->getDefinition('hearsay_require_js.directory_filename_resource.' . md5($path));
            $this->assertInstanceOf('Symfony\Component\DependencyInjection\DefinitionDecorator', $resource);
            $this->assertEquals(array($path), $resource->getArguments(), 'Incorrect constructor arguments for assetic resource');
            $this->assertEquals('hearsay_require_js.directory_filename_resource', $resource->getParent(), 'Incorrect parent for assetic resource');

            $tag = $resource->getTag('assetic.formula_resource');
            $this->assertEquals(array(array('loader' => 'require_js')), $tag, 'Unexpected formula resource tag');
        }
    }
    
    public function testOptimizerOmittedIfNotConfigured()
    {
        $config = array(
            'base_directory' => '/home/user/base',
        );
        $container = $this->getContainerBuilder();
        $loader = new HearsayRequireJSExtension();
        
        $loader->load(array($config), $container);
        
        $this->assertFalse($container->hasDefinition('hearsay_require_js.optimizer_filter'), 'Expected optimizer filter not to be defined');
    }
    
    public function testOptimizerOptionsSet()
    {
        $config = array(
            'base_directory' => '/home/user/base',
            'optimizer' => array(
                'path' => '/path/to/r.js',
                'options' => array(
                    'option' => 'value',
                ),
            ),
        );
        $container = $this->getContainerBuilder();
        $loader = new HearsayRequireJSExtension();
        
        $loader->load(array($config), $container);
        
        $optimizer = $container->getDefinition('hearsay_require_js.optimizer_filter');
        $methods = $optimizer->getMethodCalls();
        $this->assertEquals(1, count($methods), 'Incorrect number of method calls on optimizer');
        $this->assertContains(array(
            'setOption', array('option', 'value'),
        ), $methods, 'Did not find expected method call');
    }
    
    public function testExceptionOnUnrecognizedBundle()
    {
        $config = array(
            'base_directory' => '@UnknownBundle/Resources/scripts',
        );
        $container = $this->getContainerBuilder();
        $loader = new HearsayRequireJSExtension();
        
        $this->setExpectedException('InvalidArgumentException', 'Unrecognized bundle: "UnknownBundle"');
        $loader->load(array($config), $container);
    }
    
    /**
     * Helper to get a new container builder with necessary initialization
     * performed.
     * @return ContainerBuilder
     */
    protected function getContainerBuilder()
    {
        $container = new ContainerBuilder();
        $bundles = array(
            'HearsayRequireJSBundle' => 'Hearsay\RequireJSBundle\HearsayRequireJSBundle',
        );        
        $container->setParameter('kernel.bundles', $bundles);
        return $container;
    }
    
}
