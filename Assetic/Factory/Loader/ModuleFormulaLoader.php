<?php

/*
 * This file is part of the HearsayRequireJSBundle package.
 *
 * (c) Hearsay News Products, Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
