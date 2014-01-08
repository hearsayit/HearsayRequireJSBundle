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

namespace Hearsay\RequireJSBundle\Tests\Assetic\Factory\Resource;

use Hearsay\RequireJSBundle\Assetic\Factory\Resource\FilenamesResource;

/**
 * Unit tests for the filename resource.
 * @author Kevin Montag <kevin@hearsay.it>
 */
class FilenamesResourceTest extends \PHPUnit_Framework_TestCase
{
    public function testFilenamesRetrieved()
    {
        $resource = new FilenamesResource(__DIR__);
        $content = $resource->getContent();

        $filenames = preg_split("/\s+/", trim($content));
        $this->assertEquals(3, count($filenames), 'Incorrect number of files pulled');
        $this->assertContains(strtr(__DIR__, '\\', '/') . '/FilenamesResourceTest.php', $filenames, 'Did not find expected filename');
        $this->assertContains(strtr(__DIR__, '\\', '/') . '/dir/file', $filenames, 'Did not find expected filename');
        $this->assertContains(strtr(__DIR__, '\\', '/') . '/dir/sub_dir/file', $filenames, 'Did not find expected filename');
    }
    
    public function testSingleFilenameRetrieved()
    {
        $resource = new FilenamesResource(__FILE__);
        $content = $resource->getContent();
        $this->assertEquals(strtr(__FILE__, '\\', '/'), $content, 'Did not find expected filename');
    }

    public function testStringConversion()
    {
        $resource = new FilenamesResource(__DIR__);
        $this->assertEquals(strtr(__DIR__, '\\', '/'), (string)$resource, 'Incorrect string conversion');
    }

    public function testFreshCheck()
    {
        $dir = sys_get_temp_dir() . '/' . uniqid('hearsay_requirejs_fresh', true);
        $this->assertTrue(mkdir($dir), 'There was a problem creating the temporary directory');
        sleep(2);

        $time = time() - 1;
        if (filemtime($dir) > $time) {
            $this->markTestSkipped('Cache dir is too recently modified for testing');
        }

        $resource = new FilenamesResource($dir);
        $this->assertTrue($resource->isFresh($time), 'Cache dir is not fresh');

        $file = tempnam($dir, 'requirejs_test');
        file_put_contents($file, 'temp');

        $this->assertGreaterThan($time, filemtime($file), 'Sanity check failed; new file has outdated timestamp');
        $this->assertFalse($resource->isFresh($time), sprintf('System temp dir is still fresh after adding a file (%d < %d', filemtime($dir), $time));

        // Clean up
        unlink($file);
    }
}
