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

namespace Hearsay\RequireJSBundle\Templating\Helper;

use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\Templating\Helper\Helper;

use Hearsay\RequireJSBundle\Configuration\ConfigurationBuilder;

/**
 * Templating helper for RequireJS inclusion.
 * @author Kevin Montag <kevin@hearsay.it>
 */
class RequireJSHelper extends Helper
{
    /**
     * @var EngineInterface
     */
    protected $engine;

    /**
     * @var ConfigurationBuilder
     */
    protected $configurationBuilder;

    /**
     * @var string
     */
    protected $initializeTemplate;

    /**
     * @var string
     */
    protected $requireJsSrc;

    /**
     * The constructor method
     *
     * @param EngineInterface      $engine
     * @param ConfigurationBuilder $configurationBuilder
     * @param string               $initializeTemplate
     * @param string               $requireJsSrc
     */
    public function __construct(
        EngineInterface $engine,
        ConfigurationBuilder $configurationBuilder,
        $initializeTemplate,
        $requireJsSrc
    ) {
        $this->engine               = $engine;
        $this->configurationBuilder = $configurationBuilder;
        $this->initializeTemplate   = $initializeTemplate;
        $this->requireJsSrc         = $requireJsSrc;
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function getName()
    {
        return 'require_js';
    }

    /**
     * Renders the RequireJS initialization output. Available options are:
     *   main:
     *     A module to load immediately when RequireJS is available, via the
     *     data-main attribute. Defaults to nothing
     *   configure:
     *     Whether to specify the default configuration options before RequireJS
     *     is loaded.  Defaults to true, and should generally be left this way
     *     unless you need to perform Javascript logic to define the
     *     configuration (e.g. specifying a <code>ready</code> function), in
     *     which case the configuration should be specified manually either
     *     before or after RequireJS is loaded
     *
     * @param  array  $options An array of options
     * @return string
     * @link http://requirejs.org/docs/api.html#config
     */
    public function initialize(array $options = array())
    {
        $defaults = array(
            'main'      => null,
            'configure' => true,
        );

        $options = array_merge($defaults, $options);

        return $this->engine->render(
            $this->initializeTemplate,
            array_merge(
                array(
                    'main'   => $options['main'],
                    'config' => $options['configure']
                        ? $this->configurationBuilder->getConfiguration()
                        : null,
                ),
                array_diff_key($options, $defaults)
            )
        );
    }

    /**
     * Gets the RequireJS src
     *
     * @return string Returns a string that represents the RequireJS src
     */
    public function src()
    {
        if ($this->engine->exists($this->requireJsSrc)
            && $this->engine->supports($this->requireJsSrc)) {
            return $this->engine->render($this->requireJsSrc);
        }

        return $this->requireJsSrc;
    }

    /**
     * Gets the RequireJS configuration
     *
     * @return string Returns a string that represents the RequireJS configuration
     */
    public function config()
    {
        return $this->configurationBuilder->getConfiguration();
    }
}
