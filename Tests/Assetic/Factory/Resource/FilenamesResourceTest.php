<?php

/*
 * This file is part of the HearsayRequireJSBundle package.
 *
 * (c) Hearsay News Products, Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
