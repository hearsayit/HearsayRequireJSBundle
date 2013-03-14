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
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Helper service to build RequireJS configuration options from the Symfony
 * configuration.
 * @author Kevin Montag <kevin@hearsay.it>
 */
class ConfigurationBuilder
{

    /**
     * @var TranslatorInterface
     */
    protected $translator = null;
    /**
     * @var ContainerInterface
     */
    protected $container = null;
    /**
     * @var boolean
     */
    protected $useControllerForAssets = false;
    /**
     * @var string
     */
    protected $baseUrl = null;
    /**
     * @var array
     */
    protected $paths = null;
    /**
     * @var array
     */
    protected $additionalConfig = array();
    
    /**
     * Standard constructor.
     * @param TranslatorInterface $translator For getting the current locale.
     * @param ContainerInterface $container For getting the request object,
     * which gives us the site root.
     * @param boolean $useControllerForAssets True if we should serve assets
     * by pointing to the site root (e.g. /app.php/{asset URL}), false if the
     * assets should be served directly from the filesystem.
     * @param string $baseUrl Base URL where assets are served, relative to the
     * site root.
     */
    public function __construct(TranslatorInterface $translator, ContainerInterface $container, $useControllerForAssets, $baseUrl = '')
    {
        $this->translator = $translator;
        $this->container = $container;
        $this->useControllerForAssets = $useControllerForAssets;
        $this->baseUrl = $baseUrl;
    }

    /**
     * Set a path definition to be included in the configuration.
     * @param string $path The path name.
     * @param string $location The actual path location.
     */
    public function setPath($path, $location)
    {
        if ($this->paths === null) {
            $this->paths = array();
        }
        $this->paths[$path] = $location;
    }

    /**
     * Set an additional option to output in the config.
     * @param string $option The option name.
     * @param mixed $value The option value.
     */
    public function setOption($option, $value)
    {
        $this->additionalConfig[$option] = $value;
    }
    
    /**
     * Get the RequireJS configuration options.
     * @return array
     */
    public function getConfiguration()
    {
        $baseUrl = $this->container->get('templating.helper.assets')->getUrl(\ltrim($this->baseUrl, '/'));
        // remove ?version from end of URL
        if (($p = strpos($baseUrl, '?')) !== false) {
            $baseUrl = substr($baseUrl, 0, $p);
        }

        $config = array(
            'baseUrl' => $baseUrl,
            'locale' => $this->translator->getLocale(),
        );
        if ($this->paths !== null) {
            $config['paths'] = $this->paths;
        }
        return array_merge($config, $this->additionalConfig);
    }    

}
