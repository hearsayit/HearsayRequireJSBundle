<?php

/*
 * This file is part of the HearsayRequireJSBundle package.
 *
 * (c) Hearsay News Products, Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
        $filePath = $this->getRealPath($filename);

        foreach ($this->namespaces as $namespace => $realPath) {
            if (strpos($filePath, $realPath) === 0) {
                $modulePath = $this->basePath . '/' . $namespace;

                if (is_file($filePath)) {
                    $modulePath .= '/' . $this->getBaseName($filePath, $realPath);
                }

                return preg_replace('~[/\\\\]+~', '/', $modulePath);
            }
        }

        return false;
    }

    /**
     * Gets the base name of the given file path
     *
     * @param  string $filePath The file path
     * @param  string $realPath The real path of the namespace
     * @return string           Returns the base name of the given file path
     */
    protected function getBaseName($filePath, $realPath)
    {
        $basename = substr($filePath, strlen($realPath));

        if (!$basename) {
            $basename = basename($filePath);
        }

        // To allow to use the bundle with `.coffee` scripts
        return preg_replace('~\.coffee$~', '.js', $basename);
    }

    /**
     * Gets the real path of the given path
     *
     * @param  string         $path The path
     * @return boolean|string       Returns false on failure, e.g. if the file
     *                              does not exist, or a string that represents
     *                              the real path of the given path
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
