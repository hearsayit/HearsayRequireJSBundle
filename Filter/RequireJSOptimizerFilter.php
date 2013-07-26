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
     * Path to an optional build profile containing optimizer configuration
     * @var string
     */
    protected $buildProfile = null;
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
     * Shims to load if no buildProfile is provided
     * @var array
     */
    protected $shim = array();
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
    /**
     * Timeout for the node process
     */
    private $timeout = null;

    public function __construct($nodePath, $rPath, $baseUrl)
    {
        $this->nodePath = $nodePath;
        $this->rPath = $rPath;
        $this->baseUrl = $baseUrl;
    }

    /**
     * @param string $path
     */
    public function setBuildProfile($path)
    {
        $this->buildProfile = $path;
    }

    /**
     * Add a shim
     * @param array $shim Shim information.
     */
    public function addShim($shim)
    {
        $this->shim[] = $shim;
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
     * Set the process timeout.
     *
     * @param int $timeout The timeout for the process
     */
    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;
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
     * Creates a new process builder.
     */
    protected function createProcessBuilder()
    {
        // Figure out which ProcessBuilder class is available to us
        // (https://github.com/kriswallsmith/assetic/commit/0c7158ac6c480eb1dcc9f1c4b5795680d49e2577)
        $pb_class = 'Assetic\\Util\\ProcessBuilder';
        if (!class_exists($pb_class)) {
            $pb_class = 'Symfony\\Component\\Process\\ProcessBuilder';
        }
        if (!class_exists($pb_class)) {
            throw new \RuntimeException('Cannot find an acceptable ProcessBuilder class');
        }

        $pb = new $pb_class();

        if (null !== $this->timeout) {
            $pb->setTimeout($this->timeout);
        }

        return $pb;
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
        $pb = $this->createProcessBuilder();
        $pb
            ->add($this->nodePath)
            ->add($this->rPath)
            ->add('-o') // Optimize
        ;

        if (!$this->buildProfile) {
            $this->buildProfile = tempnam(sys_get_temp_dir(), 'profile').'.js';
            $data = "({ shim: {";
            foreach ($this->shim as $shim) {
                $data .= '"' . $shim['name'] . '": {';

                $data .= 'deps: ["' . implode($shim['deps'], '","') . '"]';

                if (isset($shim['exports'])) {
                    $data .= ',exports: "' . $shim['exports'] . '"';
                }

                $data .= '},';
            }

            $data .= '}})';

            file_put_contents($this->buildProfile, $data);
        }

        // Build profile path, if set, needs to be provided after the optimize flag
        $buildProfile = $this->buildProfile;
        if (! file_exists($buildProfile)) {
            throw new \RuntimeException("Build profile does not exist at ".$buildProfile);
        }
        $pb->add($buildProfile);

        $pb
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
            if ($option == 'insertRequire')
            {
              $value = $name;
            }
            $pb->add($option . '=' . $value);
        }

        $proc = $pb->getProcess();
        $proc->run();

        if (!$proc->isSuccessful()) {
            $message = "Optimization failed";

            $output = $proc->getErrorOutput();
            if (strlen($output) === 0) {
                $output = $proc->getOutput();
            }
            if (strlen($output)) {
                $message .= ": ".$output;
            }

            throw new \RuntimeException($message);
        }

        $asset->setContent(file_get_contents($output));
    }

}
