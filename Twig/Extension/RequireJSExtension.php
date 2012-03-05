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

namespace Hearsay\RequireJSBundle\Twig\Extension;

use Hearsay\RequireJSBundle\Configuration\ConfigurationBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Twig extension providing RequireJS functionality.
 * @author Kevin Montag <kevin@hearsay.it>
 */
class RequireJSExtension extends \Twig_Extension
{

    /**
     * @var ContainerInterface
     */
    protected $container = null;
    /**
     * @var ConfigurationBuilder
     */
    protected $configurationBuilder = null;
    /**
     * @var string
     */
    protected $requireJsSrc = null;

    /**
     * Standard constructor.
     * @param ContainerInterface $container Container to get the templating
     * helper.
     * @param ConfigurationBuilder $configurationBuilder For generating the
     * initial Javascript config array.
     * @param string $requireJsSrc Default URL to use for loading RequireJS.
     */
    public function __construct(ContainerInterface $container, ConfigurationBuilder $configurationBuilder, $requireJsSrc)
    {
        $this->container = $container;
        $this->configurationBuilder = $configurationBuilder;
        $this->requireJsSrc = $requireJsSrc;
    }

    /**
     * Get the templating helper to delegate actual work.
     * @return RequireJSHelper
     */
    protected function getHelper()
    {
        return $this->container->get('hearsay_require_js.helper');
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            'require_js_initialize' => new \Twig_Function_Method($this, 'renderInitialize', array('is_safe' => array('html'))),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getGlobals()
    {
        return array(
            'require_js' => array(
                'config' => $this->configurationBuilder->getConfiguration(),
                'src' => $this->requireJsSrc,
            ),
        );
    }

    /**
     * Render the RequireJS initialization markup.  Options are as specified in
     * the helper documentation.
     * @see Hearsay\RequireJSBundle\Templating\Helper\RequireJSHelper
     * @param array $options Initialization options.
     * @return string
     */
    public function renderInitialize(array $options = array())
    {
        return $this->getHelper()->initialize($options);
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function getName()
    {
        return 'require_js';
    }

}
