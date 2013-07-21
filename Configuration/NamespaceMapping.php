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
     * Registers a directory-to-namespace mapping
     *
     * @param string                 $path      The path
     * @param string                 $namespace The namespace
     * @param boolean                $isDir     Determines if the path is a
     *                                          directory
     * @throws PathNotFoundException            If the path was not found
     */
    public function registerNamespace($path, $namespace, $isDir = true)
    {
        if (file_exists($path . '.js')) {
            $path .= '.js';
        }

        if (!$path = realpath($path)) {
            throw new PathNotFoundException();
        }

        $namespaceName = preg_replace('~\.js$~', '', realpath($path));

        $this->namespaces[$namespaceName] = array(
            'is_dir'    => $isDir,
            'namespace' => $namespace,
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getModulePath($filename)
    {
        if (file_exists($filename . '.js')) {
            $filename .= '.js';
        }

        if (!$filename = realpath($filename)) {
            return false;
        }

        foreach ($this->namespaces as $path => $settings) {
            if (strpos($filename, $path) === 0) {
                $actualPath = substr($filename, strlen($path));
                $modulePath = $this->basePath . '/' . $settings['namespace'];

                if ($settings['is_dir'] && $actualPath) {
                    $modulePath .= '/' . $actualPath;
                }

                return preg_replace('~[/\\\\]+~', '/', $modulePath);
            }
        }

        return false;
    }
}
