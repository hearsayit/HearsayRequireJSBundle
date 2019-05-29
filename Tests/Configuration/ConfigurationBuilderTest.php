<?php

/*
 * This file is part of the HearsayRequireJSBundle package.
 *
 * (c) Hearsay News Products, Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hearsay\RequireJSBundle\Tests\Configuration;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Container;

use Hearsay\RequireJSBundle\Configuration\ConfigurationBuilder;

/**
 * Unit tests for the helper to generate RequireJS configuration.
 * @author Kevin Montag <kevin@hearsay.it>
 */
class ConfigurationBuilderTest extends TestCase
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

        $translator = $this
            ->createMock('Symfony\Component\Translation\TranslatorInterface');
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
            ->createMock('Hearsay\RequireJSBundle\Configuration\NamespaceMappingInterface');

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
     * @covers Hearsay\RequireJSBundle\Configuration\ConfigurationBuilder::getBaseUrl
     * @covers Hearsay\RequireJSBundle\Configuration\ConfigurationBuilder::getConfiguration
     */
    public function testBaseUrlSlashesTrimmed()
    {
        $mapping = $this
            ->createMock('Hearsay\RequireJSBundle\Configuration\NamespaceMappingInterface');

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
     * @covers Hearsay\RequireJSBundle\Configuration\ConfigurationBuilder::getBaseUrl
     * @covers Hearsay\RequireJSBundle\Configuration\ConfigurationBuilder::getConfiguration
     */
    public function testBaseUrlIgnoredIfAppropriate()
    {
        $mapping = $this
            ->createMock('Hearsay\RequireJSBundle\Configuration\NamespaceMappingInterface');

        $this->setAssetsHelperMock(null);
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
            ->createMock('Hearsay\RequireJSBundle\Configuration\NamespaceMappingInterface');

        $requestStack = $this
            ->getMockBuilder('Symfony\Component\HttpFoundation\RequestStack')
            ->getMock();

        $requestStack
            ->expects($this->any())
            ->method('getCurrentRequest')
            ->willReturn(null);

        $this->container->set('request_stack', $requestStack);
        $this->container->setParameter('assetic.use_controller', false);

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
     * @covers Hearsay\RequireJSBundle\Configuration\ConfigurationBuilder::getBaseUrl
     * @covers Hearsay\RequireJSBundle\Configuration\ConfigurationBuilder::getConfiguration
     */
    public function testAssetsBaseUrlUsed()
    {
        $mapping = $this
            ->createMock('Hearsay\RequireJSBundle\Configuration\NamespaceMappingInterface');

        $this->setRequestMock('/base');
        $this->setAssetsHelperMock('/assets/?123');
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
     * @covers Hearsay\RequireJSBundle\Configuration\ConfigurationBuilder::__construct
     * @covers Hearsay\RequireJSBundle\Configuration\ConfigurationBuilder::getBaseUrl
     * @covers Hearsay\RequireJSBundle\Configuration\ConfigurationBuilder::setUseAlmond
     * @covers Hearsay\RequireJSBundle\Configuration\ConfigurationBuilder::getConfiguration
     */
    public function testUseAlmondDevEnvironment()
    {
        $mapping = $this
            ->createMock('Hearsay\RequireJSBundle\Configuration\NamespaceMappingInterface');

        $this->setRequestMock('/base');
        $this->container->setParameter('assetic.use_controller', true);
        $this->container->setParameter('kernel.debug', true);

        $builder = new ConfigurationBuilder($this->container, $mapping);
        $builder->setUseAlmond(true);

        $config = $builder->getConfiguration();

        $this->assertArrayNotHasKey('almond', $config, 'Almond key should not exists');
    }

    /**
     * @covers Hearsay\RequireJSBundle\Configuration\ConfigurationBuilder::__construct
     * @covers Hearsay\RequireJSBundle\Configuration\ConfigurationBuilder::getBaseUrl
     * @covers Hearsay\RequireJSBundle\Configuration\ConfigurationBuilder::setUseAlmond
     * @covers Hearsay\RequireJSBundle\Configuration\ConfigurationBuilder::getConfiguration
     */
    public function testUseAlmondProdEnvironment()
    {
        $mapping = $this
            ->createMock('Hearsay\RequireJSBundle\Configuration\NamespaceMappingInterface');

        $this->setRequestMock('/base');
        $this->container->setParameter('assetic.use_controller', true);
        $this->container->setParameter('kernel.debug', false);

        $builder = new ConfigurationBuilder($this->container, $mapping);
        $builder->setUseAlmond(true);

        $config = $builder->getConfiguration();

        $this->assertArrayHasKey('almond', $config, 'Almond key should exists');
        $this->assertTrue($config['almond'], 'Almond value should be true');
    }

    /**
     * @param string $filename
     */
    private function setAssetsHelperMock($filename)
    {
        $assetsHelper = $this
            ->getMockBuilder('Symfony\Bundle\FrameworkBundle\Templating\Helper\AssetsHelper')
            ->disableOriginalConstructor()
            ->getMock();
        $assetsHelper
            ->expects($this->any())
            ->method('getUrl')
            ->with($this->equalTo(''))
            ->will($this->returnValue($filename));

        $this->container->set('templating.helper.assets', $assetsHelper);
    }

    /**
     * @param string $baseUrl
     */
    private function setRequestMock($baseUrl)
    {
        $request = $this
            ->createMock('Symfony\Component\HttpFoundation\Request');
        $request
            ->expects($this->any())
            ->method('getBaseUrl')
            ->will($this->returnValue($baseUrl));

        $requestStack = $this
            ->getMockBuilder('Symfony\Component\HttpFoundation\RequestStack')
            ->getMock();

        $requestStack
            ->expects($this->any())
            ->method('getCurrentRequest')
            ->willReturn($request);

        $this->container->set('request_stack', $requestStack);
    }
}
