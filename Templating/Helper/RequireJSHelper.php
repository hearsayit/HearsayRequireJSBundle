<?php

/*
 * This file is part of the HearsayRequireJSBundle package.
 *
 * (c) Hearsay News Products, Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
        if ($this->engine->supports($this->requireJsSrc)
            && $this->engine->exists($this->requireJsSrc)) {
            return $this->engine->render($this->requireJsSrc);
        }

        return $this->requireJsSrc;
    }
}
