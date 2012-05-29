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

/**
 * Concrete module namespace map.
 * @author Kevin Montag <kevin@hearsay.it>
 */
class NamespaceMapping implements NamespaceMappingInterface
{

    /**
     * Internal namespace map.
     * @var array
     */
    private $namespaces = array();

    /**
     * @var string
     */
    protected $basePath = null;

    /**
     * Standard constructor.
     * @param string $basePath The base path to serve resources.
     */
    public function __construct($basePath)
    {
        $this->basePath = $basePath;
    }

    /**
     * Register a directory-to-namespace mapping.
     * @param string $path
     * @param string $namespace
     */
    public function registerNamespace($path, $namespace)
    {
        $this->namespaces[realpath($path)] = $namespace;
    }

    /**
     * {@inheritdoc}
     */
    public function getModulePath($filename)
    {
        $filename = realpath($filename);
        foreach ($this->namespaces as $path => $namespace) {
            if (strpos($filename, $path) === 0) {
                return preg_replace('#[/\\\\]+#', '/', $this->basePath . '/' . $namespace . '/' . substr($filename, strlen($path)));
            }
        }
        return false;
    }
}
