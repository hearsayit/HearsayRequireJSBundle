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

namespace Hearsay\RequireJSBundle\Tests\Configuration;

use Hearsay\RequireJSBundle\Configuration\ConfigurationBuilder;

/**
 * Unit tests for the helper to generate RequireJS configuration.
 * @author Kevin Montag <kevin@hearsay.it>
 */
class ConfigurationBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param $container
     * @param $filename
     */
    protected function setMockAssetTwigExtension($container, $filename)
    {

        $mockAssetsHelper = $this
            ->getMockBuilder('Symfony\Component\Templating\Helper\AssetsHelper')
            ->disableOriginalConstructor()
            ->getMock();

        $mockAssetsHelper
            ->expects($this->any())
            ->method('getUrl')
            ->will($this->returnValue($filename));

        $container->set('templating.helper.assets', $mockAssetsHelper);
    }

    public function testConfigurationGenerated()
    {
        $container = new \Symfony\Component\DependencyInjection\Container();
        $requestScope = new \Symfony\Component\DependencyInjection\Scope('request');

        // Assets twig funtion only available in request scope, so mock it out
        $this->setMockAssetTwigExtension($container, 'base');

        $translator = $this->getMock('Symfony\Component\Translation\TranslatorInterface');
        $translator->expects($this->any())
                ->method('getLocale')
                ->will($this->returnValue('fr_FR'));
        $request = $this->getMock('Symfony\Component\HttpFoundation\Request');
        $request->expects($this->any())
                ->method('getBaseUrl')
                ->will($this->returnValue('/base'));

        $container->addScope($requestScope);
        $container->enterScope('request');
        $container->set('request', $request);
        
        $builder = new ConfigurationBuilder($translator, $container, true, 'js');
        $builder->setOption('option', 'value');
        
        $expected = array(
            'locale' => 'fr_FR',
            'baseUrl' => '/base/js',
            'option' => 'value',
        );
        $this->assertEquals($expected, $builder->getConfiguration(), 'Unexpected configuration generated');
    }
    
    public function testBaseUrlSlashesTrimmed()
    {
        $translator = $this->getMock('Symfony\Component\Translation\TranslatorInterface');
        $request = $this->getMock('Symfony\Component\HttpFoundation\Request');
        $request->expects($this->any())
                ->method('getBaseUrl')
                ->will($this->returnValue('/base'));
        $container = new \Symfony\Component\DependencyInjection\Container();
        $requestScope = new \Symfony\Component\DependencyInjection\Scope('request');
        $container->addScope($requestScope);
        $container->enterScope('request');
        $container->set('request', $request);
        
        $builder = new ConfigurationBuilder($translator, $container, true, '/js');

        $configuration = $builder->getConfiguration();
        $this->assertEquals('/base/js', $configuration['baseUrl'], 'Expected slashes to be trimmed when generating base URL');
    }
    
    public function testRootUrlIgnoredIfAppropriate()
    {
        $container = new \Symfony\Component\DependencyInjection\Container();
        $requestScope = new \Symfony\Component\DependencyInjection\Scope('request');

        // Assets twig funtion only available in request scope, so mock it out
        $this->setMockAssetTwigExtension($container, '/js');

        $translator = $this->getMock('Symfony\Component\Translation\TranslatorInterface');
        $request = $this->getMock('Symfony\Component\HttpFoundation\Request');
        $request->expects($this->never())
                ->method('getBaseUrl');

        $container->addScope($requestScope);
        $container->enterScope('request');
        $container->set('request', $request);
        
        // Use-controller parameter is false
        $builder = new ConfigurationBuilder($translator, $container, false, '/js');

        $configuration = $builder->getConfiguration();
        $this->assertEquals('/js', $configuration['baseUrl'], 'Did not expect to pull the base URL from the request object');
    }
    
    public function testPathsAdded()
    {
        $container = new \Symfony\Component\DependencyInjection\Container();
        $requestScope = new \Symfony\Component\DependencyInjection\Scope('request');

        // Assets twig funtion only available in request scope, so mock it out
        $this->setMockAssetTwigExtension($container, 'base');

        $translator = $this->getMock('Symfony\Component\Translation\TranslatorInterface');
        $translator->expects($this->any())
                ->method('getLocale')
                ->will($this->returnValue('fr_FR'));

        $builder = new ConfigurationBuilder($translator, $container, false, 'js');
        $builder->setPath('namespace', '/path/to/namespace');
       
        $config = $builder->getConfiguration();
        $this->assertEquals(array('namespace' => '/path/to/namespace'), $config['paths'], 'Did not find expected paths configuration');        
    }
}
