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

namespace Hearsay\RequireJSBundle\Filter;

use Assetic\Asset\AssetInterface;
use Assetic\Filter\FilterInterface;
use Assetic\Util\ProcessBuilder;

/**
 * Assetic filter for RequireJS optimization.
 * @author Kevin Montag <kevin@hearsay.it>
 */
class RequireJSOptimizerFilter implements FilterInterface
{

    /**
     * @var string
     */
    protected $nodePath = null;
    /**
     * @var string
     */
    protected $rPath = null;
    /**
     * Base URL option (this is generally actually a filesystem path).
     * @var string
     */
    protected $baseUrl = null;
    /**
     * Plugin to apply when processing content.
     * @var string
     */
    protected $plugin = null;
    /**
     * Extension to add to temporary files for processing (generally dependent
     * on the plugin being used).
     * @var string
     */
    protected $extension = 'js';
    /**
     * Options to set in the build.
     * @var array
     */
    protected $options = array();

    public function __construct($nodePath, $rPath, $baseUrl)
    {
        $this->nodePath = $nodePath;
        $this->rPath = $rPath;
        $this->baseUrl = $baseUrl;
    }

    /**
     * Set an additional option for the build.
     * @param string $name
     * @param string $value
     */
    public function setOption($name, $value)
    {
        $this->options[$name] = $value;
    }

    /**
     * Set the plugin to apply to content before processing.
     * @param string $plugin The plugin module name.
     * @param string $extension The file extension to add to temporary files, to
     * enable them to be loaded by this plugin.
     */
    public function setPlugin($plugin, $extension)
    {
        $this->plugin = $plugin;
        $this->extension = $extension;
    }

    /**
     * {@inheritdoc}
     */
    public function filterLoad(AssetInterface $asset)
    {
        $input = tempnam(sys_get_temp_dir(), 'assetic_requirejs');
        file_put_contents($input . '.' . $this->extension, $asset->getContent());

        $output = tempnam(sys_get_temp_dir(), 'assetic_requirejs');

        // Generate a name for the module, for which we'll configure a path
        $name = md5($input);

        $pb = new ProcessBuilder();
        $pb
                ->add($this->nodePath)
                ->add($this->rPath)
                ->add('-o') // Optimize
                // Configure the primary input
                ->add('paths.' . $name . '=' . $input)
                ->add('name=' . $name)

                // Configure the output
                ->add('out=' . $output)

                // Configure the base URL
                ->add('baseUrl=' . $this->baseUrl)
        ;

        // Additional options
        foreach ($this->options as $option => $value) {
            $pb->add($option . '=' . $value);
        }

        $proc = $pb->getProcess();
        $proc->run();

        $asset->setContent(file_get_contents($output));
    }

    /**
     * {@inheritdoc}
     */
    public function filterDump(AssetInterface $asset)
    {
        // No-op
    }

}
