<?php

/*
 * This file is part of the HearsayRequireJSBundle package.
 *
 * (c) Hearsay News Products, Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hearsay\RequireJSBundle\Tests\Filter;

use Assetic\Asset\FileAsset;
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

        $this->filter = new RJsFilter($nodePath, __DIR__ . '/../../../r.js', __DIR__ . '/baseModules');
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
     * Tests that optimizer correctly handles module includes
     *
     * @covers Hearsay\RequireJSBundle\Assetic\Filter\RJsFilter::filterDump
     * @covers Hearsay\RequireJSBundle\Assetic\Filter\RJsFilter::addModule
     * @covers Hearsay\RequireJSBundle\Assetic\Filter\RJsFilter::addOption
     */
    public function testOptimizerInclusionsIncluded()
    {
        $this->filter->addOption('preserveLicenseComments', false);
        $this->filter->addOption('skipModuleInsertion', true);

        // registering optimizer modules config
        $this->filter->addModule("modules/module2", array("include" => array("modules/module")));

        $asset = new FileAsset(__DIR__ . '/baseModules/modules/module2.js');
        $asset->ensureFilter($this->filter);

        // expecting result contains both module and module2 although module 2 doesn't depend on module
        $this->assertRegExp(
            '/^define\(".{32}",\{js:"got it twice"\}\),define\("modules\/module",\{js:"got it"\}\);$/',
            $asset->dump(),
            'Defined inclusions included incorrectly'
        );
    }

    /**
     * Tests that optimizer correctly excludes exclude-modules (configured in optimizer options for current module)
     * and their includes
     *
     * @covers Hearsay\RequireJSBundle\Assetic\Filter\RJsFilter::filterDump
     * @covers Hearsay\RequireJSBundle\Assetic\Filter\RJsFilter::addModule
     * @covers Hearsay\RequireJSBundle\Assetic\Filter\RJsFilter::addOption
     * @covers Hearsay\RequireJSBundle\Assetic\Filter\RJsFilter::addPath
     */
    public function testOptimizerExclusionsDepsExcluded()
    {
        $this->filter->addOption('preserveLicenseComments', false);

        // registering optimizer modules config
        $this->filter->addModule("modules/module2", array("include" => array("modules/module")));
        $this->filter->addModule("modules/module3", array("exclude" => array("modules/module2")));

        $asset = new FileAsset(__DIR__ . '/baseModules/modules/module3.js');
        $asset->ensureFilter($this->filter);

        // expecting result contains only content of module3 although module3 depends on module2 and module
        $this->assertRegExp(
            '/^require\(\["modules\/module2","modules\/module"\],function\(e,t\)\{return console\.log\(e,t\)\}\),define\(".{32}",function\(\)\{\}\);$/',
            $asset->dump(),
            'Defined exclusions excluded incorrectly'
        );

        $this->filter->addPath("additionalModules", __DIR__ . '/additionalModules');
        $this->filter->addModule("additionalModules/module4", array("exclude" => array("modules/module3")));

        $asset = new FileAsset(__DIR__ . '/additionalModules/module4.js');
        $asset->ensureFilter($this->filter);

        // expecting result contains only content of module4 although module4 depends on module3 and module3 depends on module 2 and module
        $this->assertRegExp(
            '/^require\(\["modules\/module3"\],function\(e\)\{return console\.log\(e\)\}\),define\(".{32}",function\(\)\{\}\);$/',
            $asset->dump(),
            'Defined exclusions for additionalModules excluded incorrectly'
        );
    }

    /**
     * @covers Hearsay\RequireJSBundle\Assetic\Filter\RJsFilter::filterDump
     * @covers Hearsay\RequireJSBundle\Assetic\Filter\RJsFilter::addOption
     * @covers Hearsay\RequireJSBundle\Assetic\Filter\RJsFilter::addPath
     */
    public function testOptionsPassed()
    {
        $this->filter->addPath('modules', __DIR__ . '/baseModules/modules');
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
