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

namespace Hearsay\RequireJSBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Loader;

/**
 * Bundle setup.
 * @author Kevin Montag <kevin@hearsay.it>
 */
class HearsayRequireJSExtension extends Extension
{

    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');

        $container->setParameter('hearsay_require_js.base_url', $config['base_url']);
        $container->setParameter('hearsay_require_js.base_directory', $this->getRealPath($config['base_directory'], $container));

        // Set optimizer options
        $container->setParameter('hearsay_require_js.r.path', $this->getRealPath($config['optimizer']['path'], $container));
        foreach ($config['optimizer']['options'] as $name => $settings) {
            $value = $settings['value'];
            $container->getDefinition('hearsay_require_js.optimizer_filter')->addMethodCall('setOption', array($name, $value));
        }

        // Add the configured namespaces
        foreach ($config['paths'] as $path => $settings) {
            if ($settings['external']) {
                $this->addExternalNamespaceMapping($settings['location'], $path, $container);
            } else {
                $this->addNamespaceMapping($settings['location'], $path, $container);
            }
        }

        // Add root directory with an empty namespace
        $this->addNamespaceMapping($config['base_directory'], '', $container);
    }

    /**
     * Configure a mapping from a filesystem path to a RequireJS namespace.
     * @param string $location
     * @param string $path
     * @param ContainerBuilder $container 
     */
    protected function addNamespaceMapping($location, $path, ContainerBuilder $container)
    {
        $location = $this->getRealPath($location, $container);

        // Register the namespace with the configuration
        $mapping = $container->getDefinition('hearsay_require_js.namespace_mapping');
        $mapping->addMethodCall('registerNamespace', array($location, $path));

        // And with the optimizer filter
        if ($path) {
            $container->getDefinition('hearsay_require_js.optimizer_filter')->addMethodCall('setOption', array('paths.' . $path, $location));
        }

        // Create the assetic resource
        $resource = new DefinitionDecorator('hearsay_require_js.directory_filename_resource');
        $resource->setArguments(array($location));
        $resource->addTag('assetic.formula_resource', array('loader' => 'require_js'));
        $container->addDefinitions(array(
            'hearsay_require_js.directory_filename_resource.' . md5($location) => $resource,
        ));
    }

    /**
     * Configure a mapping from an external URL to a RequireJS namespace/path.
     * @param string $location
     * @param string $path
     * @param ContainerBuilder $container
     */
    protected function addExternalNamespaceMapping($location, $path, ContainerBuilder $container)
    {
        $config = $container->getDefinition('hearsay_require_js.configuration_builder');
        $config->addMethodCall('setPath', array($path, $location));

        $filter = $container->getDefinition('hearsay_require_js.optimizer_filter');
        $filter->addMethodCall('addExternalDependency', array($path));
    }

    /**
     * Helper to convert bundle-notation paths to filesystem paths.
     * @param string $path
     * @param ContainerBuilder $container
     * @return string 
     */
    private function getRealPath($path, ContainerBuilder $container)
    {
        // Expand bundle notation (snagged from the Assetic bundle)
        if ($path[0] == '@' && strpos($path, '/') !== false) {

            // Extract the bundle name and the directory within the bundle
            $bundle = substr($path, 1);
            $directory = '';
            if (($pos = strpos($bundle, '/')) !== false) {
                $directory = substr($bundle, $pos);
                $bundle = substr($bundle, 0, $pos);
            }

            // Get loaded bundles
            $bundles = $container->getParameter('kernel.bundles');

            // Reconstruct the path
            if (isset($bundles[$bundle])) {
                $rc = new \ReflectionClass($bundles[$bundle]);
                $path = dirname($rc->getFileName()) . $directory;
            } else {
                throw new \InvalidArgumentException(sprintf('Unrecognized bundle: "%s"', $bundle));
            }
        }

        return $path;
    }

}
