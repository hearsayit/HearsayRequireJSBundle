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

namespace Hearsay\RequireJSBundle\Configuration;

use Symfony\Component\DependencyInjection\ContainerInterface;

use Hearsay\RequireJSBundle\Configuration\NamespaceMappingInterface;

/**
 * Helper service to build RequireJS configuration options from the Symfony
 * configuration.
 * @author Kevin Montag <kevin@hearsay.it>
 */
class ConfigurationBuilder
{
    /**
     * The base URL where assets are served, relative to the website root
     * directory
     *
     * @var string
     */
    protected $baseUrl;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var NamespaceMappingInterface
     */
    protected $mapping;

    /**
     * An array of options
     *
     * @var array
     */
    protected $options = array();

    /**
     * An array of paths
     *
     * @var array
     */
    protected $paths = array();

    /**
     * The shim config
     *
     * @var array
     */
    protected $shim  = array();

    /**
     * @var boolean
     */
    protected $useAlmond = false;

    /**
     * The constructor method
     *
     * @param ContainerInterface        $container
     * @param NamespaceMappingInterface $mapping
     * @param string                    $baseUrl    The base URL where assets
     *                                              are served, relative to the
     *                                              website root directory
     * @param array                     $shim       The shim config
     */
    public function __construct(
        ContainerInterface $container,
        NamespaceMappingInterface $mapping,
        $baseUrl = '',
        $shim = array()
    ) {
        $this->container = $container;
        $this->mapping   = $mapping;
        $this->baseUrl   = \ltrim($baseUrl, '/');
        $this->shim      = $shim;
    }

    /**
     * Adds the option
     *
     * @param string $name  The option name
     * @param mixed  $value The option value
     */
    public function addOption($name, $value)
    {
        $this->options[$name] = $value;
    }

    /**
     * Gets the RequireJS configuration options
     *
     * @return array
     */
    public function getConfiguration()
    {
        $config = array(
            'baseUrl' => $this->getScriptUrl(),
            'locale'  => $this->container->get('translator')->getLocale(),
        );

        // almond
        if (!$this->container->getParameter('kernel.debug')
            && $this->useAlmond) {

            $config['almond'] = true;
        }

        if ($this->paths) {
            $config['paths'] = $this->paths;
        }

        if ($this->shim) {
            $config['shim'] = $this->shim;
        }

        return array_merge($config, $this->options);
    }

    /**
     * Sets a path definition to be included in the configuration
     *
     * @param string       $path      The path name
     * @param string|array $locations The actual path locations
     */
    public function setPath($path, $locations)
    {
        if (!is_array($locations)) {
            $locations = (array) $locations;
        }

        foreach ($locations as &$location) {
            if (preg_match('~^(\/\/|http|https)~', $location)) {
                continue;
            }

            $modulePath = $this->mapping->getModulePath($location);

            if ($modulePath) {
                $modulePath = preg_replace('~\.js$~', '', $modulePath);
                $location = $this->getBaseUrl() . '/' . $modulePath;
            }
        }

        unset($location);

        if (count($locations) == 1) {
            $locations = array_shift($locations);
        }

        $this->paths[$path] = $locations;
    }

    /**
     * Gets the base URL
     *
     * @return string
     */
    protected function getBaseUrl()
    {
        if ($this->container->getParameter('assetic.use_controller')
            && $this->container->isScopeActive('request')) {
            return $this->container->get('request')->getBaseUrl();
        }

        $baseUrl = $this->container->isScopeActive('templating.helper.assets')
            ? $this->container->get('templating.helper.assets')->getUrl('')
            : '';

        // Remove ?version from the end of the base URL
        if (($pos = strpos($baseUrl, '?')) !== false) {
            $baseUrl = substr($baseUrl, 0, $pos);
        }

        return $baseUrl;
    }

    /**
     * Gets the URL to script
     *
     * @return string
     */
    protected function getScriptUrl()
    {
        return $this->getBaseUrl() . '/' . $this->baseUrl;
    }

    public function useAlmond($bool)
    {
        $this->useAlmond = (bool) $bool;
    }
}
