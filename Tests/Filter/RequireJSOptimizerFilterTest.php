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

use Hearsay\RequireJSBundle\Filter\RequireJSOptimizerFilter;
use Assetic\Asset\StringAsset;

/**
 * Unit tests for the optimizer filter.
 * @author Kevin Montag <kevin@hearsay.it>
 */
class RequireJSOptimizerFilterTest extends \PHPUnit_Framework_TestCase
{

    public function testContentOptimized()
    {
        $filter = new RequireJSOptimizerFilter($this->getNodePath(), __DIR__ . '/../../Resources/scripts/r.js', __DIR__);

        // So we can get consistent output.
        $filter->setOption('skipModuleInsertion', true);

        $asset = new StringAsset('alert("Hi there!");      alert("Hi there again.");');
        $asset->ensureFilter($filter);
        $this->assertEquals('alert("Hi there!"),alert("Hi there again.")', $asset->dump(), 'Content was not properly optimized');
    }

    public function testBaseUrlModulesIncluded()
    {
        // We use the current directory as the base URL
        $filter = new RequireJSOptimizerFilter($this->getNodePath(), __DIR__ . '/../../Resources/scripts/r.js', __DIR__);

        // So we can get consistent output
        $filter->setOption('skipModuleInsertion', true);

        $javascript = <<<'JVS'
require(['modules/module'], function(module) {
    console.log('Hello');
    return console.log(module);
});
JVS;

        $asset = new StringAsset($javascript);
        $this->assertEquals('define("modules/module",{js:"got it"}),require(["modules/module"],function(a){console.log("Hello");return console.log(a)})', $asset->dump($filter), 'Unexpected optimized content');
    }

    public function testOptionsPassed()
    {
        // We use the current directory as the base URL
        $filter = new RequireJSOptimizerFilter($this->getNodePath(), __DIR__ . '/../../Resources/scripts/r.js', __DIR__ . '/../../Resources/scripts');

        // So we can get consistent output
        $filter->setOption('skipModuleInsertion', true);

        // Set an additional path option
        $filter->setOption('paths.modules', __DIR__ . '/modules');

        $javascript = <<<'JVS'
require(['modules/module'], function(module) {
    return console.log(module);
});
JVS;

        $asset = new StringAsset($javascript);
        $this->assertEquals('define("modules/module",{js:"got it"}),require(["modules/module"],function(a){return console.log(a)})', $asset->dump($filter), 'Unexpected optimized content');
    }

    public function testErrorOnBadInput()
    {
        $filter = new RequireJSOptimizerFilter($this->getNodePath(), __DIR__ . '/../../Resources/scripts/r.js', __DIR__ . '/../../Resources/scripts');

        // Try to load a nonexistent module
        $javascript = <<<'JVS'
require(['unknown/module'], function(bad) {
    return console.log(bad);
});
JVS;

        $asset = new StringAsset($javascript);
        $this->setExpectedException('RuntimeException');
        $asset->dump($filter);
    }
    
    /**
     * Helper to fetch the path to node.js.
     * @return string
     */
    protected function getNodePath()
    {   
        if (isset($_SERVER['NODE_BIN'])) {
            return $_SERVER['NODE_BIN'];
        } else {
            // Use a reasonable default
            return '/usr/local/bin/node';
        } 
    }

}
