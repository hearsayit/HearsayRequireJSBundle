<?php

/*
 * This file is part of the HearsayRequireJSBundle package.
 *
 * (c) Hearsay News Products, Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hearsay\RequireJSBundle\Tests\Templating\Helper;

use Hearsay\RequireJSBundle\Templating\Helper\RequireJSHelper;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for the templating helper.
 * @author Kevin Montag <kevin@hearsay.it>
 */
class RequireJSHelperTest extends TestCase
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
        $engine = $this->createMock('Symfony\Component\Templating\EngineInterface');
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
