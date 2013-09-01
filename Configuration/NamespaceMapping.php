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

namespace Hearsay\RequireJSBundle\Configuration;

use Hearsay\RequireJSBundle\Exception\PathNotFoundException;

/**
 * Concrete module namespace map.
 * @author Kevin Montag <kevin@hearsay.it>
 */
class NamespaceMapping implements NamespaceMappingInterface
{
    /**
     * The base path to serve resources
     *
     * @var string
     */
    protected $basePath;

    /**
     * An internal namespace map
     *
     * @var array
     */
    protected $namespaces = array();

    /**
     * The constructor method
     *
     * @param string $basePath The base path to serve resources
     */
    public function __construct($basePath)
    {
        $this->basePath = $basePath;
    }

    /**
     * {@inheritDoc}
     */
    public function registerNamespace($namespace, $path)
    {
        if (!$realPath = $this->getRealPath($path)) {
            throw new PathNotFoundException(
                sprintf('The path `%s` was not found.', $path)
            );
        }

        $this->namespaces[$namespace] = $realPath;
    }

    /**
     * {@inheritDoc}
     */
    public function getModulePath($filename)
    {
        $filename = $this->getRealPath($filename);

        foreach ($this->namespaces as $namespace => $realPath) {
            if (strpos($filename, $realPath) === 0) {
                $basename   = substr($filename, strlen($realPath));
                $modulePath = $this->basePath . '/' . $namespace;

                if (is_dir($realPath) && $basename) {
                    // To allow to use the bundle with `.coffee` scripts
                    $basename = preg_replace('#\.[^.]+$#', '.js', $basename);

                    $modulePath .= '/' . $basename;
                }

                return preg_replace('~[/\\\\]+~', '/', $modulePath);
            }
        }

        return false;
    }

    /**
     * Gets canonicalized absolute pathname
     *
     * @param  string         $path The path
     * @return boolean|string       Returns false on failure, e.g. if the file
     *                              does not exist, or a string that represents
     *                              the canonicalized absolute pathname
     */
    protected function getRealPath($path)
    {
        if (is_file($path . '.js')) {
            $path .= '.js';
        }

        if (!$realPath = realpath($path)) {
            return false;
        }

        return $realPath;
    }
}
