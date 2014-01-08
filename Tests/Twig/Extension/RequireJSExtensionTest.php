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
