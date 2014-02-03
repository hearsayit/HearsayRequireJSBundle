<?php

/*
 * This file is part of the HearsayRequireJSBundle package.
 *
 * (c) Hearsay News Products, Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hearsay\RequireJSBundle\Assetic\Filter;

use Assetic\Asset\AssetInterface;
use Assetic\Exception\FilterException;
use Assetic\Filter\BaseNodeFilter;

use Hearsay\RequireJSBundle\Exception\InvalidArgumentException;
use Hearsay\RequireJSBundle\Exception\RuntimeException;

/**
 * This class represents the r.js filter for Assetic
 *
 * @author Kevin Montag <kevin@hearsay.it>
 * @author Igor Timoshenko <igor.timoshenko@i.ua>
 */
class RJsFilter extends BaseNodeFilter
{
    /**
     * The base URL, named for consistency with the r.js API, note that this is
     * generally actually a filesystem path
     *
     * @var string
     */
    protected $baseUrl;

    /**
     * An array of modules to exclude
     *
     * @var array
     */
    protected $exclude = array();

    /**
     * An array of modules to treat as externals which don't need to be loaded
     *
     * @var array
     */
    protected $external = array();

    /**
     * The absolute path to the node.js
     *
     * @var string
     */
    protected $nodePath;

    /**
     * An array of options
     *
     * @var array
     */
    protected $options = array();

    /**
     * An array of paths
     *
     * @var array
     */
    protected $paths = array();

    /**
     * The absolute path to the r.js
     *
     * @var string
     */
    protected $rPath;

    /**
     * The shim config
     *
     * @var array
     */
    protected $shim = array();

    /**
     * Modules to load
     *
     * @var array
     */
    protected $modules = array();

    /**
     * The constructor method
     *
     * @param string $nodePath The absolute path to the node.js
     * @param string $rPath    The absolute path to the r.js
     * @param string $baseUrl  The base URL
     */
    public function __construct($nodePath, $rPath, $baseUrl)
    {
        $this->nodePath = $nodePath;
        $this->rPath    = $rPath;
        $this->baseUrl  = $baseUrl;
    }

    /**
     * {@inheritDoc}
     * @codeCoverageIgnore
     */
    public function filterLoad(AssetInterface $asset)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function filterDump(AssetInterface $asset)
    {
        $pb = $this->createProcessBuilder(
            $this->nodePath
            ? array($this->nodePath, $this->rPath)
            : array($this->rPath)
        );

        // Input and output files
        $input  = tempnam(sys_get_temp_dir(), 'input');
        $output = tempnam(sys_get_temp_dir(), 'output');

        file_put_contents($input . '.js', $asset->getContent());

        $buildProfile = $this->makeBuildProfile($input, $output, $this->getModuleName($asset));

        $pb->add('-o')->add($buildProfile);

        $proc = $pb->getProcess();
        $code = $proc->run();

        unlink($input);

        if ($code !== 0) {
            if (file_exists($output)) {
                unlink($output);
            }

            if (file_exists($buildProfile)) {
                unlink($buildProfile);
            }

            if ($code === 127) {
                throw new RuntimeException(
                    'Path to node executable could not be resolved.'
                );
            }

            throw FilterException::fromProcess($proc)->setInput($asset->getContent());
        }

        if (!file_exists($output)) {
            throw new RuntimeException('Error creating output file.');
        }

        $asset->setContent(file_get_contents($output));

        unlink($output);
        unlink($buildProfile);
    }

    /**
     * Adds the module to exclude
     *
     * @param string $module The module name
     */
    public function addExclude($module)
    {
        $this->exclude[] = $module;
    }

    /**
     * Adds the module to treat as an external that don't need to be loaded
     *
     * @param string $module The module name
     */
    public function addExternal($module)
    {
        $this->external[] = $module;
    }

    /**
     * Adds the option
     *
     * @param string $name  The option name
     * @param mixed  $value The option value
     */
    public function addOption($name, $value)
    {
        $this->options[$name] = $value;
    }

    /**
     * Adds the module path
     *
     * @param string $module The module name
     * @param string $path   The module path
     */
    public function addPath($module, $path)
    {
        $this->paths[$module] = $path;
    }

    /**
     * Adds a module information
     *
     * @param string $name module name
     * @param array $module Module information
     *
     * @throws InvalidArgumentException in case of attempt to add module info twice
     */
    public function addModule($name, array $module)
    {
        if (array_key_exists($name, $this->modules)) {
            throw new InvalidArgumentException("Cannot handle $name module twice");
        }
        $this->modules[$name] = $module;
    }

    /**
     * Sets the shim config
     *
     * @param array $shim The shim config
     */
    public function setShim(array $shim)
    {
        $this->shim = $shim;
    }

    /**
     * Makes the build profile's file
     *
     * @param  string $input The input file
     * @param  string $output The output file
     * @param  string $moduleName current r.js module name
     *
     * @return string Returns the build profile's file name
     */
    protected function makeBuildProfile($input, $output, $moduleName)
    {
        $buildProfile = tempnam(sys_get_temp_dir(), 'build_profile') . '.js';

        $name = md5($input);

        // The basic build profile
        $content = (object) array(
            'baseUrl' => $this->baseUrl,
            'paths'   => new \stdClass(),
            'name'    => $name,
            'out'     => $output,
        );

        // @link http://requirejs.org/docs/optimization.html#empty
        foreach ($this->external as $external) {
            $content->paths->$external = 'empty:';
        }

        $content->paths->$name = $input;

        foreach ($this->paths as $path => $location) {
            $content->paths->$path = $location;
        }

        // Duplicate the shim config
        foreach ($this->shim as &$shim) {
            $shim = (object) $shim;
        }

        unset($shim);

        $content->shim = (object) $this->shim;

        $excludedDeps = $this->getExcludedDependencies($moduleName);
        // merge optimizer excludes with global ones
        $content->exclude = array_merge($this->exclude, $excludedDeps);

        $content->include = $this->getModuleIncludes($moduleName);

        foreach ($this->options as $option => $value) {
            // @link https://github.com/jrburke/requirejs/wiki/Upgrading-to-RequireJS-2.0#wiki-delayed
            if ($option == 'insertRequire') {
                $value = $name;
            }

            $content->$option = $value;
        }

        file_put_contents($buildProfile, '(' . json_encode($content) . ')');

        return $buildProfile;
    }

    /**
     * Returns r.js module name for current asset
     *
     * @param AssetInterface $asset current asset
     *
     * @return string
     */
    protected function getModuleName(AssetInterface $asset)
    {
        $fullPath = $asset->getSourceRoot() . '/' . $asset->getSourcePath();
        $relPath  = str_replace($this->baseUrl . '/', '', $fullPath);

        return substr_replace($relPath, '', strpos($relPath, '.js'), 3);
    }

    /**
     * Get the list of the dependencies of any excluded modules for a given one.
     *
     * @param string $module current module name
     *
     * @return array modules to exclude
     */
    private function getExcludedDependencies($module)
    {
        // Find excluded modules
        $excludedModules = array();
        if (isset($this->modules[$module]['exclude'])) {
            $excludedModules = $this->modules[$module]['exclude'];
        }

        // Find dependencies of excluded modules
        $excludedDebs = array();
        foreach ($excludedModules as $exclude) {
            if (isset($this->modules[$exclude]['include'])) {
                $excludedDebs = array_merge($excludedDebs, $this->modules[$exclude]['include']);
            }
        }

        return array_merge($excludedModules, $excludedDebs);
    }

    /**
     * Get the list of included modules for a given one.
     *
     * @param string $module module name
     *
     * @return array Included modules
     */
    private function getModuleIncludes($module)
    {
        return !isset($this->modules[$module]['include']) ? array() : $this->modules[$module]['include'];
    }
}
