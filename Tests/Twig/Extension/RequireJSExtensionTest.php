<?php

/*
 * This file is part of the HearsayRequireJSBundle package.
 *
 * (c) Hearsay News Products, Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hearsay\RequireJSBundle\Tests\Twig\Extension;

use Hearsay\RequireJSBundle\Configuration\ConfigurationBuilder;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Container;

use Hearsay\RequireJSBundle\Twig\Extension\RequireJSExtension;

/**
 * @author Igor Timoshenko <igor.timoshenko@i.ua>
 */
class RequireJSExtensionTest extends TestCase
{
    /**
     * @var Container
     */
    private $container;

    /**
     * @var RequireJSExtension
     */
    private $extension;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->container = new Container();

        $configurationBuilder = $this
            ->getMockBuilder('Hearsay\RequireJSBundle\Configuration\ConfigurationBuilder')
            ->disableOriginalConstructor()
            ->getMock();

        $configurationBuilder
            ->expects($this->any())
            ->method('getConfiguration')
            ->will($this->returnValue(array()));

        $this->extension = new RequireJSExtension(
            $this->container,
            $configurationBuilder
        );
    }

    /**
     * @covers Hearsay\RequireJSBundle\Twig\Extension\RequireJSExtension::getGlobals
     */
    public function testGetGlobalsInInactiveRequestScope()
    {
        $this->assertEquals(array(), $this->extension->getGlobals());
    }

    /**
     * @covers Hearsay\RequireJSBundle\Twig\Extension\RequireJSExtension::getGlobals
     */
    public function testGetGlobalsInActiveRequestScope()
    {
        $request = $this->createMock('Symfony\Component\HttpFoundation\Request');

        $requestStack = $this
            ->getMockBuilder('Symfony\Component\HttpFoundation\RequestStack')
            ->getMock();

        $requestStack
            ->expects($this->any())
            ->method('getCurrentRequest')
            ->willReturn($request);

        $this->container->set('request_stack', $requestStack);

        $this->assertEquals(
            array('require_js' => array('config' => array())),
            $this->extension->getGlobals()
        );
    }
}
