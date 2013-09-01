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
 * Mapping of base file paths to Javascript module namespaces.
 * @author Kevin Montag <kevin@hearsay.it>
 */
interface NamespaceMappingInterface
{
    /**
     * Registers a namespace to a filesystem path mapping
     *
     * @param string                 $namespace The namespace
     * @param string                 $path      The path
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
