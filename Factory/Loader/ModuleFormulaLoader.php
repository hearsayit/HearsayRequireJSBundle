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

namespace Hearsay\RequireJSBundle\Factory\Loader;

use Assetic\Factory\AssetFactory;
use Assetic\Factory\Loader\FormulaLoaderInterface;
use Assetic\Factory\Resource\ResourceInterface;
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
    protected $factory = null;
    
    /**
     * @var NamespaceMappingInterface
     */
    protected $mapping = null;
    
    public function __construct(AssetFactory $factory, NamespaceMappingInterface $mapping)
    {
        $this->factory = $factory;
        $this->mapping = $mapping;
    }
    
    /**
     * {@inheritdoc}
     */
    public function load(ResourceInterface $resource)
    {
        $formulae = array();
        $tokens = preg_split("/\n+/", $resource->getContent());
        foreach ($tokens as $token) {
            if (is_file($token)) {
                $name = $this->factory->generateAssetName($token, array());
                $output = $this->mapping->getModulePath($token);
                if ($output) {
                    $formulae[$name] = array($token, array(), array('output' => $output));
                }
            }
        }
        
        return $formulae;
    }    
}
