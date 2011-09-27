Introduction
============

This bundle enables easy integration of [RequireJS](http://requirejs.org) into
your Symfony2 projects.

Installation
============

  1. Install the bundle:

        $ git submodule add git://github.com/hearsayit/HearsayRequireJSBundle vendor/bundles/Hearsay/RequireJSBundle

  2. Add the Hearsay namespace to your autoloader:

        // app/autoload.php
        $loader->registerNamespaces(array(
            // ...
            'Hearsay' => __DIR__.'/../vendor/bundles',
            // ...
        ));

  3. Add the bundle to your kernel:
        
        // app/AppKernel.php
        public function registerBundles()
        {
            return array(
                // ...
                new Hearsay\RequireJSBundle\HearsayRequireJSBundle(),
                // ...
            );
        }

Configuration
=============

You can expose directories of Javascript modules for access via require.  Given
a directory structure like:

        - src/
            - Acme/
                - BlogBundle/
                    - Resources/
                        - scripts/
                            - main.js
                            - module.js
                            - one/
                                - two.js
                - CommentBundle/
                    - Resources/
                        - scripts/
                            - three/
                                - four.js

Your configuration might look something like:

        # app/config/config.yml
        hearsay_require_js:
            namespaces:
                blog: %kernel_root%/../src/Acme/BlogBundle/Resources/scripts
                comment: '@AcmeCommentBundle/Resources/scripts'

This specifies base namespaces for each directory, so you would then reference
modules like:

        require(['comment/three/four', 'blog/module'], function(four, module) { ... });

Usage
=====

Just output the RequireJS initialization and load files normally:

        {{ require_js_initialize() }}
        <script type='text/javascript'>require('blog/main')</script>

Alternately, you can specify a file to be required immediately via the
data-main attribute:

        {{ require_js_initialize({ 'main' : 'blog/main' }) }}

If you need to do anything fancy with the configuration, you can do so
manually by modifying the default configuration, and suppressing config output
when initializing RequireJS:

        <script type='text/javascript'>
            var require = {{ require_js.config|json_encode|raw }};
            require.ready = function() { console.log('DOM ready') };
        </script>
        {{ require_js_initialize({ 'configure' : false })

Optimization
============

The bundle provides an Assetic filter to create minified Javascript files using
the RequireJS optimizer.  By default, this also inlines any module definitions
required by the file being optimized.  You can use it like any other filter:

        {% javascripts filter='requirejs' '@AcmeBlogBundle/Resources/scripts/main.js' %}
            {{ require_js_initialize({ 'main' : asset_url }) }}
        {% endjavascripts %}
