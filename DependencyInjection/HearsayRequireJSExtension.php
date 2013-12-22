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

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

use Hearsay\RequireJSBundle\Exception\InvalidArgumentException;

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

        $loader = new Loader\YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../Resources/config')
        );
        $loader->load('services.yml');

        $container->setParameter('hearsay_require_js.require_js_src', $config['require_js_src']);
        $container->setParameter('hearsay_require_js.initialize_template', $config['initialize_template']);
        $container->setParameter('hearsay_require_js.base_url', $config['base_url']);
        $container->setParameter('hearsay_require_js.base_dir', $this->getRealPath($config['base_dir'], $container));

        // Add the base directory as an empty namespace
        $config['paths'][''] = array(
            'location' => $config['base_dir'],
            'external' => false,
        );

        $hideUnoptimizedAssets = !isset($config['optimizer']['hide_unoptimized_assets'])
            ? false
            : $config['optimizer']['hide_unoptimized_assets'];

        foreach ($config['paths'] as $path => $settings) {
            $location = $settings['location'];

            if ($settings['external']) {
                $this->addExternalNamespaceMapping($location, $path, $container);
            } else {
                is_array($location) && $location = array_shift($location);

                $this->addNamespaceMapping($location, $path, $container, !$hideUnoptimizedAssets);
            }
        }

        $container->setParameter('hearsay_require_js.shim', $config['shim']);

        $configurationBuilder = $container
            ->getDefinition('hearsay_require_js.configuration_builder');

        if ($container->getParameter('kernel.debug')) {
            $args = isset($config['options']['urlArgs']) ? $config['options']['urlArgs']['value'] : '';
            $config['options']['urlArgs'] = array('value' => trim($args.'&v='.time(), '&'));
        }

        foreach ($config['options'] as $option => $settings) {
            $configurationBuilder->addMethodCall('addOption', array($option, $settings['value']));
        }

        if (!isset($config['optimizer'])) {
            // If the r.js optimizer config isn't provided, don't provide the Assetic filter
            $container->removeDefinition('hearsay_require_js.optimizer_filter');
        } else {
            $container->setParameter('hearsay_require_js.r.path', $this->getRealPath($config['optimizer']['path'], $container));

            $filter = $container->getDefinition('hearsay_require_js.optimizer_filter');
            $filter->addMethodCall('setShim', array($config['shim']));
            $filter->addMethodCall('setTimeout', array($config['optimizer']['timeout']));

            foreach ($config['optimizer']['exclude'] as $exclude) {
                $filter->addMethodCall('addExclude', array($exclude));
            }

            foreach ($config['optimizer']['options'] as $name => $settings) {
                $filter->addMethodCall('addOption', array($name, $settings['value']));
            }
        }
    }

    /**
     * Configure a mapping from a filesystem path to a RequireJS namespace.
     * @param string $location
     * @param string $path
     * @param ContainerBuilder $container
     * @param boolean $generateAssets
     */
    protected function addNamespaceMapping($location, $path, ContainerBuilder $container, $generateAssets = true)
    {
        $location = $this->getRealPath($location, $container);

        // Register the namespace with the configuration
        $mapping = $container->getDefinition('hearsay_require_js.namespace_mapping');
        $mapping->addMethodCall('registerNamespace', array($path, $location));

        $config = $container->getDefinition('hearsay_require_js.configuration_builder');
        $config->addMethodCall('setPath', array($path, $location));

        if ($path && $container->hasDefinition('hearsay_require_js.optimizer_filter')) {
            $filter = $container->getDefinition('hearsay_require_js.optimizer_filter');
            $filter->addMethodCall('addPath', array($path, preg_replace('~\.js$~', '', $location)));
        }

        if ($generateAssets) {
            $resource = new DefinitionDecorator('hearsay_require_js.filenames_resource');
            $resource->setArguments(array($location));
            $resource->addTag('assetic.formula_resource', array('loader' => 'require_js'));
            $container->addDefinitions(array(
                'hearsay_require_js.filenames_resource.' . md5($location) => $resource,
            ));
        }
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

        if ($container->hasDefinition('hearsay_require_js.optimizer_filter')) {
            $filter = $container->getDefinition('hearsay_require_js.optimizer_filter');
            $filter->addMethodCall('addExternal', array($path));
        }
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
                throw new InvalidArgumentException(sprintf('Unrecognized bundle: "%s"', $bundle));
            }
        }

        if (is_file($path . '.js')) {
            $path .= '.js';
        }

        return $path;
    }
}
