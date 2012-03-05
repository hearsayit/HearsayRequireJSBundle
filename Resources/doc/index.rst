Introduction
============

This bundle enables easy integration of `RequireJS <http://requirejs.org>`_ into
your Symfony2 projects.

Installation
============

  1. Install the bundle::

        $ git submodule add git://github.com/hearsayit/HearsayRequireJSBundle vendor/bundles/Hearsay/RequireJSBundle

  2. Add the Hearsay namespace to your autoloader::

        // app/autoload.php
        $loader->registerNamespaces(array(
            // ...
            'Hearsay' => __DIR__.'/../vendor/bundles',
            // ...
        ));

  3. Add the bundle to your kernel::
        
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

You can expose directories of Javascript modules for access via ``require``.
You must expose one root directory (from which files will be ``require``'d by 
default), and you may expose as many additional namespaces as you like.  Given a
directory structure like::

        - app/
            - scripts/
                - jquery.js
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

Your configuration might look something like::

        # app/config/config.yml
        hearsay_require_js:
            base_directory: %kernel.root_dir%/scripts
            paths:
                blog: %kernel.root_dir%/../src/Acme/BlogBundle/Resources/scripts
                comment: '@AcmeCommentBundle/Resources/scripts'

This specifies base namespaces for each directory, so you would then reference
modules like::

        require(['jquery', 'comment/three/four', 'blog/module'], function($, four, module) { ... });

You can also specify modules to be loaded from external sources::

        # app/config/config.yml
        hearsay_require_js:
            paths:
                jquery:
                    location: //ajax.googleapis.com/ajax/libs/jquery/1.6.4/jquery.min
                    external: true

Usage
=====

Just output the RequireJS initialization and load files normally::

        {{ require_js_initialize() }}
        <script type='text/javascript'>require(['blog/main'])</script>

Alternately, you can specify a file to be required immediately via the
data-main attribute::

        {{ require_js_initialize({ 'main' : 'blog/main' }) }}

If you need to do anything fancy with the configuration, you can do so
manually by modifying the default configuration, and suppressing config output
when initializing RequireJS::

        <script type='text/javascript'>
            var require = {{ require_js.config|json_encode|raw }};
            require.ready = function() { console.log('DOM ready') };
        </script>
        {{ require_js_initialize({ 'configure' : false })

Optimization
============

The bundle provides an Assetic filter to create minified Javascript files using
the RequireJS optimizer.  This also inlines any module definitions required by 
the file being optimized.  You need to provide a path to the r.js optimizer in
your configuration to use the filter::

        # app/config/config.yml
        hearsay_require_js:
            optimizer:
                path: /path/to/r.js
                excludes: [ excluded/module ] # Modules to exclude from the build (optional)
                options: { skipModuleInsertion: true } # Additional options to pass to the optimizer (optional)

You can then use it like any other filter; for example,
to optimize only in production::

        {% javascripts filter='?requirejs' '@AcmeBlogBundle/Resources/scripts/main.js' %}
            {{ require_js_initialize({ 'main' : asset_url }) }}
        {% endjavascripts %}

Note that your configured path definitions will be incorporated into the
optimizer filter, including the exclusion of external dependencies from the
built file.
