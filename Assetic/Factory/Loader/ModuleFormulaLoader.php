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

namespace Hearsay\RequireJSBundle\Assetic\Factory\Loader;

use Assetic\Factory\Loader\FormulaLoaderInterface;
use Assetic\Factory\Resource\ResourceInterface;
use Assetic\Factory\AssetFactory;

use Hearsay\RequireJSBundle\Configuration\NamespaceMappingInterface;

/**
 * Formula loader for Javascript modules.
 * @author Kevin Montag <kevin@hearsay.it>
 */
class ModuleFormulaLoader implements FormulaLoaderInterface
{
    /**
     * @var AssetFactory
     */
    protected $factory;

    /**
     * @var NamespaceMappingInterface
     */
    protected $mapping;

    public function __construct(AssetFactory $factory, NamespaceMappingInterface $mapping)
    {
        $this->factory = $factory;
        $this->mapping = $mapping;
    }

    /**
     * {@inheritDoc}
     */
    public function load(ResourceInterface $resource)
    {
        $formulae  = array();
        $filenames = preg_split("/\n+/", $resource->getContent());

        foreach ($filenames as $filename) {
            if (is_file($filename)) {
                $name   = $this->factory->generateAssetName($filename, array());
                $output = $this->mapping->getModulePath($filename);

                if ($output) {
                    // @see \Assetic\Factory\AssetFactory::createAsset()
                    $formulae[$name] = array(
                        $filename,
                        array(),
                        array('output' => $output)
                    );
                }
            }
        }

        return $formulae;
    }
}
