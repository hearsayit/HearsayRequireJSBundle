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

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\Scope;

use Hearsay\RequireJSBundle\Configuration\ConfigurationBuilder;

/**
 * Unit tests for the helper to generate RequireJS configuration.
 * @author Kevin Montag <kevin@hearsay.it>
 */
class ConfigurationBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Container
     */
    private $container;

    /**
     * {@inheritDoc}
     */
    public function setUp()
    {
        parent::setUp();

        $this->container = new Container();
        $this->container->addScope(new Scope('request'));
        $this->container->enterScope('request');

        $translator = $this
            ->getMock('Symfony\Component\Translation\TranslatorInterface');
        $translator
            ->expects($this->any())
            ->method('getLocale')
            ->will($this->returnValue('fr_FR'));

        $this->container->set('translator', $translator);
    }

    /**
     * @covers Hearsay\RequireJSBundle\Configuration\ConfigurationBuilder::__construct
     * @covers Hearsay\RequireJSBundle\Configuration\ConfigurationBuilder::addOption
     * @covers Hearsay\RequireJSBundle\Configuration\ConfigurationBuilder::getConfiguration
     */
    public function testConfigurationGenerated()
    {
        $mapping = $this
            ->getMock('Hearsay\RequireJSBundle\Configuration\NamespaceMappingInterface');

        $this->setRequestMock('/base');
        $this->container->setParameter('assetic.use_controller', true);

        $builder = new ConfigurationBuilder($this->container, $mapping, 'js');

        $builder->addOption('option', 'value');

        $expected = array(
            'locale'  => 'fr_FR',
            'baseUrl' => '/base/js',
            'option'  => 'value',
        );

        $this->assertEquals(
            $expected,
            $builder->getConfiguration(),
            'Unexpected configuration generated'
        );
    }

    /**
     * @covers Hearsay\RequireJSBundle\Configuration\ConfigurationBuilder::__construct
     * @covers Hearsay\RequireJSBundle\Configuration\ConfigurationBuilder::getConfiguration
     * @covers Hearsay\RequireJSBundle\Configuration\ConfigurationBuilder::getBaseUrl
     */
    public function testBaseUrlSlashesTrimmed()
    {
        $mapping = $this
            ->getMock('Hearsay\RequireJSBundle\Configuration\NamespaceMappingInterface');

        $this->setRequestMock('/base');
        $this->container->setParameter('assetic.use_controller', true);

        $builder = new ConfigurationBuilder($this->container, $mapping, '/js');
        $config  = $builder->getConfiguration();

        $this->assertEquals(
            '/base/js',
            $config['baseUrl'],
            'Expected slashes to be trimmed when generating base URL'
        );
    }

    /**
     * @covers Hearsay\RequireJSBundle\Configuration\ConfigurationBuilder::__construct
     * @covers Hearsay\RequireJSBundle\Configuration\ConfigurationBuilder::getConfiguration
     * @covers Hearsay\RequireJSBundle\Configuration\ConfigurationBuilder::getBaseUrl
     */
    public function testRootUrlIgnoredIfAppropriate()
    {
        $mapping = $this
            ->getMock('Hearsay\RequireJSBundle\Configuration\NamespaceMappingInterface');

        $this->container->setParameter('assetic.use_controller', false);

        $builder = new ConfigurationBuilder($this->container, $mapping, '/js');
        $config  = $builder->getConfiguration();

        $this->assertEquals(
            '/js',
            $config['baseUrl'],
            'Did not expect to pull the base URL from the request object'
        );
    }

    /**
     * @covers Hearsay\RequireJSBundle\Configuration\ConfigurationBuilder::__construct
     * @covers Hearsay\RequireJSBundle\Configuration\ConfigurationBuilder::setPath
     * @covers Hearsay\RequireJSBundle\Configuration\ConfigurationBuilder::getConfiguration
     */
    public function testPathsAdded()
    {
        $mapping = $this
            ->getMock('Hearsay\RequireJSBundle\Configuration\NamespaceMappingInterface');

        $this->container->leaveScope('request');
        $this->container->setParameter('assetic.use_controller', false);
        $this->setAssetsHelperMock('base');

        $builder = new ConfigurationBuilder($this->container, $mapping, 'js');
        $builder->setPath('namespace', '/path/to/namespace');

        $config = $builder->getConfiguration();

        $expected = array(
            'namespace' => '/path/to/namespace',
        );

        $this->assertEquals(
            $expected,
            $config['paths'],
            'Did not find expected paths configuration'
        );
    }

    /**
     * @covers Hearsay\RequireJSBundle\Configuration\ConfigurationBuilder::__construct
     * @covers Hearsay\RequireJSBundle\Configuration\ConfigurationBuilder::getConfiguration
     * @covers Hearsay\RequireJSBundle\Configuration\ConfigurationBuilder::getBaseUrl
     */
    public function testAssetsBaseUrlUsed()
    {
        $this->setRequestMock('/base');
        $this->setAssetsHelperMock('/assets/?1234');
        $mapping = $this
            ->getMock('Hearsay\RequireJSBundle\Configuration\NamespaceMappingInterface');

        $this->container->setParameter('assetic.use_controller', false);

        $builder = new ConfigurationBuilder($this->container, $mapping, '/js');
        $config  = $builder->getConfiguration();

        $this->assertEquals(
            '/assets/js',
            $config['baseUrl'],
            'Assets base URL not used correctly'
        );
    }

    /**
     * @param string $filename
     */
    private function setAssetsHelperMock($filename)
    {
        $assetsHelper = $this
            ->getMockBuilder('Symfony\Component\Templating\Helper\AssetsHelper')
            ->disableOriginalConstructor()
            ->getMock();
        $assetsHelper
            ->expects($this->any())
            ->method('getUrl')
            ->will($this->returnValue($filename));

        $this->container->set('templating.helper.assets', $assetsHelper);
    }

    /**
     * @param string $baseUrl
     */
    private function setRequestMock($baseUrl)
    {
        $request = $this
            ->getMock('Symfony\Component\HttpFoundation\Request');
        $request
            ->expects($this->any())
            ->method('getBaseUrl')
            ->will($this->returnValue($baseUrl));

        $this->container->set('request', $request);
    }
}
