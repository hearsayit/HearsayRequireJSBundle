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
        $filter = new RequireJSOptimizerFilter($this->getNodePath(), $this->getRPath(), __DIR__);

        $asset = new StringAsset('alert("Hi there!");      alert("Hi there again");');
        $asset->ensureFilter($filter);
        $content = $asset->dump();
        
        // The named module definition will have an arbitrary MD5 as its name
        $this->assertRegExp('/^alert\("Hi there!"\),alert\("Hi there again"\),define\(".{32}",function\(\)\{\}\)$/', $content, 'Content was not properly optimized');
    }

    public function testBaseUrlModulesIncluded()
    {
        // We use the current directory as the base URL
        $filter = new RequireJSOptimizerFilter($this->getNodePath(), $this->getRPath(), __DIR__);
        

        $javascript = <<<'JVS'
define(['modules/module'], function(module) {
    console.log('Hello');
    return console.log(module);
});
JVS;

        $asset = new StringAsset($javascript);
        $asset->ensureFilter($filter);
        $content = $asset->dump();

        // The named module definition will have an arbitrary MD5 as its name
        $this->assertEquals('define("modules/module",{js:"got it"}),define("', substr($content, 0, 47), 'Unexpected optimized content');
        $this->assertEquals('",["modules/module"],function(a){console.log("Hello");return console.log(a)})', substr($content, 79), 'Unexpected optimized content');
    }

    public function testOptionsPassed()
    {
        // We use the current directory as the base URL
        $filter = new RequireJSOptimizerFilter($this->getNodePath(), $this->getRPath(), __DIR__);

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
        $filter = new RequireJSOptimizerFilter($this->getNodePath(), $this->getRPath(), __DIR__);

        // Try to load a nonexistent module
        $javascript = <<<'JVS'
require(['unknown/module'], function(bad) {
    return console.log(bad);
});
JVS;

        $asset = new StringAsset($javascript);
        $asset->ensureFilter($filter);
        $this->setExpectedException('RuntimeException');
        $asset->dump();
    }
    
    public function testExternalsIgnored()
    {
        $filter = new RequireJSOptimizerFilter($this->getNodePath(), $this->getRPath(), __DIR__);
        $filter->addExternal('external1');
        $filter->addExternal('external2');
        
        // Load a module with the external dependencies
        $javascript = <<<'JVS'
require(['external1', 'external2'], function(external1, external2) {
    return console.log(external1);
});
JVS;

        $asset = new StringAsset($javascript);
        $asset->ensureFilter($filter);
        $content = $asset->dump();
        
        // The named module definition will have an arbitrary MD5 as its name
        $this->assertEquals('require(["external1","external2"],function(a,b){return console.log(a)}),define("', substr($content, 0, 80), 'Unexpected output for external dependencies');
        $this->assertEquals('",function(){})', substr($content, 112), 'Unexpected output for external dependencies');
    }
    
    public function testExclusionsExcluded()
    {
        $filter = new RequireJSOptimizerFilter($this->getNodePath(), $this->getRPath(), __DIR__);
        $filter->addExclude('modules/module');
        
        $javascript = <<<'JVS'
require(['modules/module'], function(module) {
    return console.log(module);
});
JVS;
        $asset = new StringAsset($javascript);
        $asset->ensureFilter($filter);
        $content = $asset->dump();
        
        $this->assertEquals('require(["modules/module"],function(a){return console.log(a)}),define("', substr($content, 0, 71), 'Did not expect modules/module to be included in build');
        $this->assertEquals('",function(){})', substr($content, 103), 'Did not expect modules/module to be included in build');
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

    /**
     * Get the optimizer path.
     * @return string
     */
    protected function getRPath()
    {
        return __DIR__ . '/r.js';
    }

}
