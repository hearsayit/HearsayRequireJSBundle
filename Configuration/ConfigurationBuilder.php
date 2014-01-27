<?php

/*
 * This file is part of the HearsayRequireJSBundle package.
 *
 * (c) Hearsay News Products, Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
     * Flag to control if almond is used or not.
     *
     * @var boolean
     */
    protected $useAlmond = false;

    /**
     * The constructor method
     *
     * @param ContainerInterface        $container
     * @param NamespaceMappingInterface $mapping
     * @param string                    $baseUrl   The base URL where assets
     *                                             are served, relative to the
     *                                             website root directory
     * @param array                     $shim      The shim config
     */
    public function __construct(
        ContainerInterface $container,
        NamespaceMappingInterface $mapping,
        $baseUrl = '',
        $shim = array()
    ) {
        $this->container = $container;
        $this->mapping   = $mapping;
        $this->baseUrl   = ltrim($baseUrl, '/');
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

        if ($this->paths) {
            $config['paths'] = $this->paths;
        }

        if ($this->shim) {
            $config['shim'] = $this->shim;
        }

        if ($this->container->hasParameter('kernel.debug')
            && !$this->container->getParameter('kernel.debug')
            && $this->useAlmond) {

            $config['almond'] = true;
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
        $baseUrl = '';

        if ($this->container->isScopeActive('request')) {
            if ($this->container->getParameter('assetic.use_controller')) {
                $baseUrl = $this->container->get('request')->getBaseUrl();
            } else {
                $baseUrl = $this->container->get('templating.helper.assets')->getUrl('');

                // Remove ?version from the end of the base URL
                if (($pos = strpos($baseUrl, '?')) !== false) {
                    $baseUrl = substr($baseUrl, 0, $pos);
                }
            }
        }

        // Remove trailing slash, if there is one
        return rtrim($baseUrl, '/');
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

    /**
     * Sets if almond is used or not
     *
     * @param boolean $bool The almond used value
     *
     * @return boolean
     */
    public function useAlmond($bool)
    {
        $this->useAlmond = (bool) $bool;
    }
}
