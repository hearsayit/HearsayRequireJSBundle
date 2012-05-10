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

namespace Hearsay\RequireJSBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

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
        $rootNode = $treeBuilder->root('hearsay_require_js');

        $rootNode
                ->children()
                    ->scalarNode('base_url')
                        ->defaultValue('js')
                    ->end()
                    ->scalarNode('base_directory')
                        ->cannotBeEmpty()
                        ->isRequired()
                    ->end()
                    ->scalarNode('require_js_src')
                        ->defaultValue('//cdnjs.cloudflare.com/ajax/libs/require.js/1.0.5/require.min.js')
                    ->end()
                    ->scalarNode('initialize_template')
                        ->cannotBeEmpty()
                        ->defaultValue('HearsayRequireJSBundle::initialize.html.twig')
                    ->end()
                    ->arrayNode('paths')
                        ->defaultValue(array())
                        ->useAttributeAsKey('path')
                        ->prototype('array')
                            ->beforeNormalization()->ifString()->then(function($v) { return array('location' => $v); })->end()
                            ->children()
                                ->scalarNode('location')
                                    ->isRequired()
                                ->end()
                                ->booleanNode('external')
                                    ->defaultFalse()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                    ->booleanNode('hide_unoptimized_assets')
                        ->defaultFalse()
                    ->end()
                    ->arrayNode('optimizer')
                        ->children()
                            ->scalarNode('path')
                                ->cannotBeEmpty()
                                ->isRequired()
                            ->end()
                            ->arrayNode('excludes')
                                ->defaultValue(array())
                                ->prototype('scalar')->end()
                            ->end()
                            ->arrayNode('options')
                                ->defaultValue(array())
                                ->useAttributeAsKey('name')
                                ->prototype('array')
                                    ->beforeNormalization()->ifTrue(function($v) { return !is_array($v); })->then(function($v) { return array('value' => $v); })->end()
                                    ->children()
                                        ->scalarNode('value')
                                            ->isRequired()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
        ;

        return $treeBuilder;
    }
}
