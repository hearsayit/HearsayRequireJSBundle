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

namespace Hearsay\RequireJSBundle\Assetic\Factory\Resource;

use Symfony\Component\Finder\Finder;

use Assetic\Factory\Resource\ResourceInterface;

/**
 * Assetic resource containing the filenames descendant from some root.
 * @author Kevin Montag <kevin@hearsay.it>
 */
class FilenamesResource implements ResourceInterface
{
    /**
     * Base directory on the file system, or a single filename.
     * @var string
     */
    protected $path;

    /**
     * Standard constructor.
     * @param string $path Filesystem directory path.
     */
    public function __construct($path)
    {
        $this->path = $path;
    }

    /**
     * {@inheritdoc}
     */
    public function getContent()
    {
        if (is_file($this->path)) {
            return strtr($this->path, '\\', '/');
        }

        $files = '';

        foreach (Finder::create()->files()->in($this->path) as $file) {
            $files .= strtr($file, '\\', '/') . "\n";
        }

        return $files;
    }

    /**
     * {@inheritdoc}
     */
    public function isFresh($timestamp)
    {
        return filemtime($this->path) <= $timestamp;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return strtr($this->path, '\\', '/');
    }
}
