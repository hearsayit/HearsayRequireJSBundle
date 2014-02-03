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

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\Scope;

use Hearsay\RequireJSBundle\Twig\Extension\RequireJSExtension;

/**
 * @author Igor Timoshenko <igor.timoshenko@i.ua>
 */
class RequireJSExtensionTest extends \PHPUnit_Framework_TestCase
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
        $this->container->addScope(new Scope('request'));
        $this->container->enterScope('request');

        $this->assertEquals(
            array('require_js' => array('config' => array())),
            $this->extension->getGlobals()
        );
    }
}
