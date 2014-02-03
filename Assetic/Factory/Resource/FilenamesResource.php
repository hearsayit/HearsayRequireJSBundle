<?php

/*
 * This file is part of the HearsayRequireJSBundle package.
 *
 * (c) Hearsay News Products, Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
