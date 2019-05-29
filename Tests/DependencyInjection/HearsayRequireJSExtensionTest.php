<?php

/*
 * This file is part of the HearsayRequireJSBundle package.
 *
 * (c) Hearsay News Products, Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hearsay\RequireJSBundle\Tests\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use Hearsay\RequireJSBundle\DependencyInjection\HearsayRequireJSExtension;

/**
 * Unit tests for the bundle loader.
 * @author Kevin Montag <kevin@hearsay.it>
 */
class HearsayRequireJSExtensionTest extends TestCase
{
    /**
     * @var HearsayRequireJSExtension
     */
    private $extension;

    /**
     * {@inheritDoc}
     */
    public function setUp()
    {
        parent::setUp();

        $this->extension = new HearsayRequireJSExtension();
    }

    public function testPathHasInvalidType()
    {
        $config = array('base_dir' => 'base/directory');

        $paths = array(
            'boolean'  => array('location' => false),
            'integer'  => array('location' => 0),
            'float'    => array('location' => 0.0),
            'object'   => array('location' => new \stdClass()),
            'resource' => array('location' => fopen(__FILE__, 'r')),
            'null'     => array('location' => null)
        );

        $container = $this->getContainerBuilder();

        $exceptionsCount = 0;

        foreach ($paths as $path) {
            $config['paths'][0] = $path;

            $this->expectException(
                'Symfony\Component\Config\Definition\Exception\InvalidConfigurationException'
            );

            $this->extension->load(array($config), $container);

            $exceptionsCount++;
        }

        $this->assertCount($exceptionsCount, $paths);
    }

    public function testPathContainsJsExtension()
    {
        $config = array(
            'base_dir' => 'base/directory',
            'paths'          => array(
                'a' => array(
                    'location' => array(
                        'a1.js',
                        'a2.js',
                    ),
                ),
            )
        );

        $container = $this->getContainerBuilder();

        $this->expectException(
            'Symfony\Component\Config\Definition\Exception\InvalidConfigurationException'
        );

        $this->extension->load(array($config), $container);
    }

    public function testNamespacesMapped()
    {
        // Set up directory structure
        $base_dir = sys_get_temp_dir() . '/' . uniqid('hearsay_requirejs_base', true);

        $this->assertTrue(
            mkdir($base_dir),
            'There was a problem creating the temporary directory'
        );

        $namespace_dir = sys_get_temp_dir() . '/' . uniqid('hearsay_requirejs_namespace_dir', true);

        $this->assertTrue(
            mkdir($namespace_dir),
            'There was a problem creating the temporary directory'
        );

        $namespace_file = sys_get_temp_dir() . '/' . uniqid('hearsay_requirejs_namespace_file', true);

        $handle = fopen($namespace_file, 'w');

        if (!$handle) {
            throw new \RuntimeException('Cannot create the temporary file');
        }

        fclose($handle);

        $config = array(
            'base_dir' => $base_dir,
            'paths'          => array(
                'namespace'      => $namespace_dir,
                'namespace_file' => $namespace_file,
            ),
            'optimizer'      => array(
                'path' => '/path/to/r.js',
            ),
        );

        $container = $this->getContainerBuilder();

        $this->extension->load(array($config), $container);

        // Check the namespace mapping
        $mapping = $container->getDefinition('hearsay_require_js.namespace_mapping');
        $methods = $mapping->getMethodCalls();

        $this->assertEquals(
            3,
            count($methods),
            'Incorrect number of method calls on namespace mapping'
        );
        $this->assertContains(
            array('registerNamespace', array('', $base_dir)),
            $methods,
            'Did not find expected method call'
        );
        $this->assertContains(
            array('registerNamespace', array('namespace', $namespace_dir)),
            $methods,
            'Did not find expected method call'
        );
        $this->assertContains(
            array('registerNamespace', array('namespace_file', $namespace_file)),
            $methods,
            'Did not find expected method call'
        );

        // Check the Assetic filter
        $filter  = $container->getDefinition('hearsay_require_js.optimizer_filter');
        $methods = $filter->getMethodCalls();

        $this->assertEquals(
            5,
            count($methods),
            'Incorrect number of method calls on optimizer filter'
        );
        $this->assertContains(
            array('addPath', array('namespace', $namespace_dir)),
            $methods,
            'Did not find expected method call'
        );
        $this->assertContains(
            array('addPath', array('namespace_file', $namespace_file)),
            $methods,
            'Did not find expected method call'
        );

        $paths = array(
            $base_dir,
            $namespace_dir,
            $namespace_file,
        );

        // Check the Assetic resources
        foreach ($paths as $path) {
            /**
             * @var $resource \Symfony\Component\DependencyInjection\ChildDefinition
             */
            $resource = $container->getDefinition('hearsay_require_js.filenames_resource.' . md5($path));

            $this->assertInstanceOf(
                'Symfony\Component\DependencyInjection\ChildDefinition',
                $resource
            );
            $this->assertEquals(
                array($path),
                $resource->getArguments(),
                'Incorrect constructor arguments for Assetic resource'
            );
            $this->assertEquals(
                'hearsay_require_js.filenames_resource',
                $resource->getParent(),
                'Incorrect parent for assetic resource'
            );

            $tag = $resource->getTag('assetic.formula_resource');

            $this->assertEquals(
                array(array('loader' => 'require_js')),
                $tag,
                'Unexpected Assetic formula resource tag'
            );
        }
    }

    public function testAssetsNotHiddenByDefault()
    {
        $config = array(
            'base_dir' => '/home/user/base',
            'paths'          => array(
                'namespace' => '/home/user/path',
            ),
            'optimizer'      => array(
                'path' => '/path/to/optimizer',
            ),
        );

        $container = $this->getContainerBuilder();

        $this->extension->load(array($config), $container);

        // Make sure we have the relevant Assetic resources

        $paths = array(
            '/home/user/base',
            '/home/user/path',
        );

        foreach ($paths as $path) {
            $this->assertTrue($container->hasDefinition('hearsay_require_js.filenames_resource.' . md5($path)));
        }
    }

    public function testAssetsCanBeHidden()
    {
        $config = array(
            'base_dir' => '/home/user/base',
            'paths'          => array(
                'namespace' => '/home/user/path',
            ),
            'optimizer'      => array(
                'path'                    => '/path/to/optimizer',
                'hide_unoptimized_assets' => true,
            ),
        );

        $container = $this->getContainerBuilder();

        $this->extension->load(array($config), $container);

        // Make sure we don't have any Assetic resources

        $paths = array(
            '/home/user/base',
            '/home/user/path',
        );

        foreach ($paths as $path) {
            $this->assertFalse($container->hasDefinition('hearsay_require_js.directory_filename_resource.' . md5($path)));
        }
    }

    public function testOptimizerOmittedIfNotConfigured()
    {
        $config = array(
            'base_dir' => '/home/user/base',
        );

        $container = $this->getContainerBuilder();

        $this->extension->load(array($config), $container);

        $this->assertFalse(
            $container->hasDefinition('hearsay_require_js.optimizer_filter'),
            'Expected optimizer filter not to be defined'
        );
    }

    public function testOptimizerOptionsSet()
    {
        $config = array(
            'base_dir' => '/home/user/base',
            'optimizer'      => array(
                'path'    => '/path/to/r.js',
                'options' => array(
                    'option' => 'value',
                ),
            ),
        );

        $container = $this->getContainerBuilder();

        $this->extension->load(array($config), $container);

        $filter  = $container->getDefinition('hearsay_require_js.optimizer_filter');
        $methods = $filter->getMethodCalls();

        $this->assertEquals(
            4,
            count($methods),
            'Incorrect number of method calls on optimizer'
        );
        $this->assertContains(
            array('addOption', array('option', 'value')),
            $methods,
            'Did not find expected method call'
        );
    }

    public function testExceptionOnUnrecognizedBundle()
    {
        $config = array(
            'base_dir' => '@UnknownBundle/Resources/scripts',
        );

        $container = $this->getContainerBuilder();

        $this->expectException('InvalidArgumentException');

        $this->extension->load(array($config), $container);
    }

    public function testOptimizerWithoutAlmondPath()
    {
        $config = array(
            'base_dir'  => '/home/user/base',
            'optimizer' => array(
                'path' => '/path/to/r.js',
            ),
        );

        $container = $this->getContainerBuilder();

        $this->extension->load(array($config), $container);

        $filter  = $container->getDefinition('hearsay_require_js.optimizer_filter');
        $methods = $filter->getMethodCalls();

        $this->assertNotContains(
            array('setAlmondPath', array()),
            $methods,
            'Did find expected method call'
        );
    }

    public function testOptimizerWithAlmondPath()
    {
        $config = array(
            'base_dir'  => '/home/user/base',
            'optimizer' => array(
                'almond_path' => '/path/to/almond.js',
                'path' => '/path/to/r.js',
            ),
        );

        $container = $this->getContainerBuilder();

        $this->extension->load(array($config), $container);

        $filter  = $container->getDefinition('hearsay_require_js.optimizer_filter');
        $methods = $filter->getMethodCalls();

        $this->assertContains(
            array('setAlmondPath', array('../../path/to/almond')),
            $methods,
            'Did not find expected method call'
        );
    }

    /**
     * @return ContainerBuilder
     */
    private function getContainerBuilder()
    {
        $container = new ContainerBuilder();

        $bundles = array(
            'HearsayRequireJSBundle' => 'Hearsay\RequireJSBundle\HearsayRequireJSBundle',
        );

        $container->setParameter('kernel.bundles', $bundles);

        return $container;
    }
}
