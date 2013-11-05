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

use Symfony\Component\DependencyInjection\ContainerInterface;

use Hearsay\RequireJSBundle\Templating\Helper\RequireJSHelper;

/**
 * Twig extension providing RequireJS functionality.
 * @author Kevin Montag <kevin@hearsay.it>
 */
class RequireJSExtension extends \Twig_Extension
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * The constructor method
     *
     * @param ContainerInterface   $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     */
    public function getFunctions()
    {
        return array(
            'require_js_initialize' => new \Twig_Function_Method(
                $this,
                'initialize',
                array('is_safe' => array('html'))
            ),
            'require_js_src'        => new \Twig_Function_Method($this, 'src'),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getGlobals()
    {
        return array(
            'require_js_config' => $this->getHelper()->config(),
        );
    }

    /**
     * {@inheritDoc}
     * @codeCoverageIgnore
     */
    public function getName()
    {
        return 'require_js';
    }

    /**
     * @see Hearsay\RequireJSBundle\Templating\Helper\RequireJSHelper::initialize()
     */
    public function initialize(array $options = array())
    {
        return $this->getHelper()->initialize($options);
    }

    /**
     * @see Hearsay\RequireJSBundle\Templating\Helper\RequireJSHelper::src()
     */
    public function src()
    {
        return $this->getHelper()->src();
    }

    /**
     * @return RequireJSHelper
     */
    protected function getHelper()
    {
        return $this->container->get('hearsay_require_js.templating_helper');
    }
}
