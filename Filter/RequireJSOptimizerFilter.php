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
use Symfony\Component\Process\ProcessBuilder;

/**
 * Assetic filter for RequireJS optimization.
 * @author Kevin Montag <kevin@hearsay.it>
 */
class RequireJSOptimizerFilter implements FilterInterface
{
    /**
     * Absolute path to node.js.
     * @var string
     */
    protected $nodePath = null;
    /**
     * Absolute path to the r.js optimizer.
     * @var string
     */
    protected $rPath = null;
    /**
     * Base URL option to the optimizer (named for consistency with the r.js
     * API; note this is generally actually a filesystem path).
     * @var string
     */
    protected $baseUrl = null;
    /**
     * Extension to add to temporary files for processing.
     * @var string
     */
    protected $extension = 'js';
    /**
     * Modules to treat as externals which don't need to be loaded.
     * @var array
     */
    protected $externals = array();
    /**
     * Modules to exclude from the build.
     * @var array
     */
    protected $excludes = array();
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
     * Set a RequireJS path which should be excluded from the build.
     * @param string $path The excluded path.
     */
    public function addExclude($path)
    {
        $this->excludes[] = $path;
    }

    /**
     * Set a RequireJS path which should be treated as an external module that
     * doesn't need to be included in the build.
     * @param string $path The external path.
     */
    public function addExternal($path)
    {
        $this->externals[] = $path;
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
     * {@inheritdoc}
     */
    public function filterLoad(AssetInterface $asset)
    {
        // No-op
    }

    /**
     * {@inheritdoc}
     */
    public function filterDump(AssetInterface $asset)
    {
        $input = tempnam(sys_get_temp_dir(), 'assetic_requirejs');
        $inputFilename = $input . ($this->extension ? '.' . $this->extension : '');
        file_put_contents($inputFilename, $asset->getContent());

        $output = tempnam(sys_get_temp_dir(), 'assetic_requirejs');

        // Get a name for the module, for which we'll configure a path
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

                // Configure the input base URL
                ->add('baseUrl=' . $this->baseUrl)
        ;

        $excludesString = '';

        // Exclude externals and create temporary files
        foreach ($this->externals as $external) {
            // Make a new temporary file
            $placeholder = tempnam(sys_get_temp_dir(), 'assetic_requirejs');
            touch($placeholder . '.js');
            $pb->add('paths.' . $external . '=' . $placeholder);
            $excludesString .= $external . ',';
        }

        // Exclude standard excludes without temporary files
        foreach ($this->excludes as $exclude) {
            $excludesString .= $exclude . ',';
        }

        $pb->add('exclude=' . $excludesString);

        // Additional options
        foreach ($this->options as $option => $value) {
            $pb->add($option . '=' . $value);
        }

        $proc = $pb->getProcess();
        $proc->run();

        if (!$proc->isSuccessful()) {
            throw new \RuntimeException($proc->getErrorOutput());
        }

        $asset->setContent(file_get_contents($output));
    }

}
