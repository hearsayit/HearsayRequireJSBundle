<?php

/*
 * This file is part of the HearsayRequireJSBundle package.
 *
 * (c) Hearsay News Products, Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hearsay\RequireJSBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

use Hearsay\RequireJSBundle\Exception\InvalidPathException;
use Hearsay\RequireJSBundle\Exception\InvalidTypeException;

/**
 * Bundle configuration definitions.
 * @author Kevin Montag <kevin@hearsay.it>
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode    = $treeBuilder->root('hearsay_require_js');

        $rootNode
                ->children()
                    ->scalarNode('require_js_src')
                        ->cannotBeEmpty()
                        ->defaultValue('//cdnjs.cloudflare.com/ajax/libs/require.js/2.1.8/require.min.js')
                    ->end()
                    ->scalarNode('initialize_template')
                        ->cannotBeEmpty()
                        ->defaultValue('HearsayRequireJSBundle::initialize.html.twig')
                    ->end()
                    ->scalarNode('base_url')
                        ->defaultValue('js')
                    ->end()
                    ->scalarNode('base_dir')
                        ->isRequired()
                        ->cannotBeEmpty()
                    ->end()
                    ->arrayNode('paths')
                        ->defaultValue(array())
                        ->useAttributeAsKey('path')
                        ->normalizeKeys(false)
                        ->prototype('array')
                            ->beforeNormalization()
                                ->ifString()
                                ->then(function ($v) {
                                    return array('location' => $v);
                                })
                            ->end()
                            ->children()
                                ->variableNode('location')
                                    ->isRequired()
                                    ->cannotBeEmpty()
                                    ->validate()
                                        ->always(function ($v) {
                                            if (!is_string($v) && !is_array($v)) {
                                                throw new InvalidTypeException();
                                            }

                                            $vs = !is_array($v) ? (array) $v : $v;
                                            $er = preg_grep('~\.js$~', $vs);

                                            if ($er) {
                                                throw new InvalidPathException();
                                            }

                                            return $v;
                                        })
                                    ->end()
                                ->end()
                                ->booleanNode('external')
                                    ->defaultFalse()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode('shim')
                        ->defaultValue(array())
                        ->useAttributeAsKey('name')
                        ->normalizeKeys(false)
                        ->prototype('array')
                            ->children()
                                ->arrayNode('deps')
                                    ->defaultValue(array())
                                    ->prototype('scalar')
                                    ->end()
                                ->end()
                                ->scalarNode('exports')
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode('options')
                        ->defaultValue(array())
                        ->useAttributeAsKey('name')
                        ->prototype('array')
                            ->beforeNormalization()
                                ->always(function ($v) {
                                    return array('value' => $v);
                                })
                            ->end()
                            ->children()
                                ->variableNode('value')
                                    ->isRequired()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode('optimizer')
                        ->children()
                            ->scalarNode('path')
                                ->isRequired()
                                ->cannotBeEmpty()
                            ->end()
                            ->booleanNode('hide_unoptimized_assets')
                                ->defaultFalse()
                            ->end()
                            ->booleanNode('declare_module_name')
                                ->defaultFalse()
                            ->end()
                            ->arrayNode('exclude')
                                ->defaultValue(array())
                                ->prototype('scalar')
                                ->end()
                            ->end()
                            ->arrayNode('modules')
                                ->defaultValue(array())
                                ->useAttributeAsKey('name')
                                ->prototype('array')
                                    ->children()
                                        ->scalarNode('name')
                                            ->cannotBeEmpty()
                                        ->end()
                                        ->arrayNode('include')
                                            ->defaultValue(array())
                                            ->prototype('scalar')
                                            ->end()
                                        ->end()
                                        ->arrayNode('exclude')
                                            ->defaultValue(array())
                                            ->prototype('scalar')
                                            ->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                            ->arrayNode('options')
                                ->defaultValue(array())
                                ->useAttributeAsKey('name')
                                ->prototype('array')
                                    ->beforeNormalization()
                                        ->always(function ($v) {
                                            return array('value' => $v);
                                        })
                                        ->end()
                                        ->children()
                                            ->variableNode('value')
                                            ->isRequired()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                            ->scalarNode('timeout')
                                ->cannotBeEmpty()
                                ->validate()
                                    ->ifTrue(function ($v) {
                                        return !(is_int($v) || ctype_digit($v));
                                    })
                                    ->thenInvalid('Invalid number of seconds "%s"')
                                ->end()
                                ->defaultValue(60)
                            ->end()
                            ->scalarNode('almond_path')
                                ->defaultFalse()
                            ->end()
                        ->end()
                    ->end()
                ->end();

        return $treeBuilder;
    }
}
