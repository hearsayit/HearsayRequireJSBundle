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

namespace Hearsay\RequireJSBundle\Tests\Templating\Helper;

use Hearsay\RequireJSBundle\Templating\Helper\RequireJSHelper;

/**
 * Unit tests for the templating helper.
 * @author Kevin Montag <kevin@hearsay.it>
 */
class RequireJSHelperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers Hearsay\RequireJSBundle\Templating\Helper\RequireJSHelper::initialize
     */
    public function testDefaultInitialization()
    {
        $engine = $this->getEngineMock(array('option' => 'value'));

        $config = $this->getMockBuilder('Hearsay\RequireJSBundle\Configuration\ConfigurationBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $config->expects($this->once())
            ->method('getConfiguration')
            ->will($this->returnValue(array('option' => 'value')));

        $helper = new RequireJSHelper($engine, $config, 'template', '');

        $this->assertEquals(
            'initialized',
            $helper->initialize(),
            'Incorrect initialization rendered'
        );
    }

    /**
     * @covers Hearsay\RequireJSBundle\Templating\Helper\RequireJSHelper::initialize
     */
    public function testConfigurationSuppressed()
    {
        $engine = $this->getEngineMock(null, null);

        $config = $this->getMockBuilder('Hearsay\RequireJSBundle\Configuration\ConfigurationBuilder')
                ->disableOriginalConstructor()
                ->getMock();
        $config->expects($this->never())
                ->method('getConfiguration');

        $helper = new RequireJSHelper($engine, $config, 'template', '');

        $this->assertEquals(
            'initialized',
            $helper->initialize(array('configure' => false)),
            'Incorrect initialization rendered'
        );
    }

    /**
     * @covers Hearsay\RequireJSBundle\Templating\Helper\RequireJSHelper::initialize
     */
    public function testMainScriptIncluded()
    {
        $engine = $this->getEngineMock(array('option' => 'value'), 'module');

        $config = $this->getMockBuilder('Hearsay\RequireJSBundle\Configuration\ConfigurationBuilder')
                ->disableOriginalConstructor()
                ->getMock();
        $config->expects($this->once())
                ->method('getConfiguration')
                ->will($this->returnValue(array('option' => 'value')));

        $helper = new RequireJSHelper($engine, $config, 'template', '');

        $this->assertEquals(
            'initialized',
            $helper->initialize(array('main' => 'module')),
            'Incorrect initialization rendered'
        );
    }

    /**
     * @param  null|array  $config
     * @param  null|string $main
     * @return mixed
     */
    private function getEngineMock($config = null, $main = null)
    {
        $engine = $this->getMock('Symfony\Component\Templating\EngineInterface');
        $engine->expects($this->once())
            ->method('render')
            ->with('template', array(
                'config' => $config,
                'main'   => $main,
            ))
            ->will($this->returnValue('initialized'));

        return $engine;
    }
}
