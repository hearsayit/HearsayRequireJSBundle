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
 * Mapping of base file paths to Javascript module namespaces.
 * @author Kevin Montag <kevin@hearsay.it>
 */
interface NamespaceMappingInterface
{
    /**
     * Registers a namespace to a filesystem path mapping
     *
     * @param  string                $namespace The namespace
     * @param  string                $path      The path
     * @throws PathNotFoundException            If the path was not found
     */
    public function registerNamespace($namespace, $path);

    /**
     * Gets the module path, e.g. `namespace/modules.js` corresponding to a
     * filesystem path
     *
     * @param  string         $filename The filename
     * @return boolean|string           Returns false on failure, e.g. if the
     *                                  file does not exist or a string that
     *                                  represents the module path
     */
    public function getModulePath($filename);
}
