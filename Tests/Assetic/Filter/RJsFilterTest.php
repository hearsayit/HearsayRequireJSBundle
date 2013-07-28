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

namespace Hearsay\RequireJSBundle\Tests\Filter;

use Symfony\Component\Process\ExecutableFinder;

use Assetic\Asset\StringAsset;

use Hearsay\RequireJSBundle\Assetic\Filter\RJsFilter;

/**
 * @author Kevin Montag <kevin@hearsay.it>
 * @author Igor Timoshenko <igor.timoshenko@i.ua>
 */
class RJsFilterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var RJsFilter
     */
    private $filter;

    /**
     * {@inheritDoc}
     */
    public function setUp()
    {
        parent::setUp();

        $nodePath = $this->getNodePath();

        $this->filter = new RJsFilter($nodePath, __DIR__ . '/r.js', __DIR__);
        $this->filter->addNodePath($nodePath);
    }

    /**
     * @covers Hearsay\RequireJSBundle\Assetic\Filter\RJsFilter::filterDump
     */
    public function testContentOptimized()
    {
        $javascript = <<<JS
alert('Hi there!');
alert('Hi there again!');
JS;

        $this->assertRegExp(
            '/^alert\("Hi there!"\),alert\("Hi there again!"\),define\(".{32}",function\(\)\{\}\);$/',
            $this->getStringAsset($javascript)->dump(),
            'Content was not properly optimized'
        );
    }

    /**
     * @covers Hearsay\RequireJSBundle\Assetic\Filter\RJsFilter::filterDump
     * @covers Hearsay\RequireJSBundle\Assetic\Filter\RJsFilter::addOption
     */
    public function testBaseUrlIncluded()
    {
        $this->filter->addOption('preserveLicenseComments', false);

        $javascript = <<<JS
define(['modules/module'], function(module) {
    console.log('Hello');

    return console.log(module);
});
JS;

        $content = $this->getStringAsset($javascript)->dump();

        $this->assertEquals(
            'define("modules/module",{js:"got it"}),define("',
            substr($content, 0, 47),
            'Unexpected optimized content'
        );
        $this->assertEquals(
            '",["modules/module"],function(e){return console.log("Hello"),console.log(e)});',
            substr($content, 79),
            'Unexpected optimized content'
        );
    }

    /**
     * @covers Hearsay\RequireJSBundle\Assetic\Filter\RJsFilter::filterDump
     */
    public function testUnknownModuleThrowsException()
    {
        $javascript = <<<JS
require(['unknown/module'], function(module) {
    return console.log(module);
});
JS;

        $this->setExpectedException('Assetic\Exception\FilterException');

        $this->getStringAsset($javascript)->dump();
    }

    /**
     * @covers Hearsay\RequireJSBundle\Assetic\Filter\RJsFilter::filterDump
     * @covers Hearsay\RequireJSBundle\Assetic\Filter\RJsFilter::addExclude
     * @covers Hearsay\RequireJSBundle\Assetic\Filter\RJsFilter::addOption
     */
    public function testExclusionsExcluded()
    {
        $this->filter->addExclude('modules/module');
        $this->filter->addOption('preserveLicenseComments', false);

        $javascript = <<<JS
require(['modules/module'], function(module) {
    return console.log(module);
});
JS;

        $content = $this->getStringAsset($javascript)->dump();

        $this->assertEquals(
            'require(["modules/module"],function(e){return console.log(e)}),define("',
            substr($content, 0, 71),
            'Did not expect modules/module to be included in the build'
        );
        $this->assertEquals(
            '",function(){});',
            substr($content, 103),
            'Did not expect modules/module to be included in the build'
        );
    }

    /**
     * @covers Hearsay\RequireJSBundle\Assetic\Filter\RJsFilter::filterDump
     * @covers Hearsay\RequireJSBundle\Assetic\Filter\RJsFilter::addExternal
     */
    public function testExternalsIgnored()
    {
        $this->filter->addExternal('external1');
        $this->filter->addExternal('external2');

        $javascript = <<<JS
require(['external1', 'external2'], function(external1, external2) {
    return console.log(external1);
});
JS;

        $content = $this->getStringAsset($javascript)->dump();

        $this->assertEquals(
            'require(["external1","external2"],function(e,t){return console.log(e)}),define("',
            substr($content, 0, 80),
            'Unexpected output for external dependencies'
        );
        $this->assertEquals(
            '",function(){});',
            substr($content, 112),
            'Unexpected output for external dependencies'
        );
    }

    /**
     * @covers Hearsay\RequireJSBundle\Assetic\Filter\RJsFilter::filterDump
     * @covers Hearsay\RequireJSBundle\Assetic\Filter\RJsFilter::addOption
     * @covers Hearsay\RequireJSBundle\Assetic\Filter\RJsFilter::addPath
     */
    public function testOptionsPassed()
    {
        $this->filter->addPath('modules', __DIR__ . '/modules');
        $this->filter->addOption('preserveLicenseComments', false);
        $this->filter->addOption('skipModuleInsertion', true);

        $javascript = <<<JS
require(['modules/module'], function(module) {
    return console.log(module);
});
JS;

        $this->assertEquals(
            'define("modules/module",{js:"got it"}),require(["modules/module"],function(e){return console.log(e)});',
            $this->getStringAsset($javascript)->dump(),
            'Unexpected optimized content'
        );
    }

    /**
     * Gets the string asset
     *
     * @param  string      $content
     * @return StringAsset
     */
    private function getStringAsset($content)
    {
        $stringAsset = new StringAsset($content);
        $stringAsset->ensureFilter($this->filter);

        return $stringAsset;
    }

    /**
     * Gets the path to the node.js
     *
     * @return string
     */
    private function getNodePath()
    {
        if (isset($_SERVER['NODE_BIN'])) {
            return $_SERVER['NODE_BIN'];
        }

        $finder = new ExecutableFinder();

        if ($executable = $finder->find('node')) {
            return $executable;
        }

        $this->markTestSkipped('The node.js library is not available.');
    }
}
