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
     * @var string
     */
    protected $baseUrl = null;
    /**
     * @var array
     */
    protected $additionalConfig = null;
    
    /**
     * Standard constructor.
     * @param TranslatorInterface $translator For getting the current locale.
     * @param string $baseUrl Base URL where assets are served.
     * @param array $additionalConfig Additional RequireJS options.
     */
    public function __construct(TranslatorInterface $translator, $baseUrl = '', array $additionalConfig = array())
    {
        $this->translator = $translator;
        $this->baseUrl = $baseUrl;
        $this->additionalConfig = $additionalConfig;
    }
    
    /**
     * Get the RequireJS configuration options.
     * @return array
     */
    public function getConfiguration()
    {
        $config = array(
            'baseUrl' => $this->baseUrl,
            'locale' => $this->translator->getLocale(),
        );
        return array_merge($config, $this->additionalConfig);
    }    

}
