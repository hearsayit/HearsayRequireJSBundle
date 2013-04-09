Introduction
============

This bundle enables easy integration of `RequireJS <http://requirejs.org>`_ into
your Symfony2 projects.

Installation
============
If you're using composer, just require `hearsay/require-js-bundle` in your project.
To install the bundle manually:

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
                            - libs/
                                - text.js

Your configuration might look something like::

        # app/config/config.yml
        hearsay_require_js:
            base_directory: %kernel.root_dir%/scripts
            paths:
                blog: %kernel.root_dir%/../src/Acme/BlogBundle/Resources/scripts
                comment: '@AcmeCommentBundle/Resources/scripts'

You can also expose files directly using the following syntax. In this
example the file that otherwise would be exposed as `/js/libs/text.js`
(according to the configuration above) will now be exposed as `/js/text.js`.

        # app/config/config.yml
        hearsay_require_js:
            paths:
                text: '@AcmeCommentBundle/Resources/scripts/libs/text.js'

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

To make global changes to the configuration/initialization output, you can
specify an initialization template in your configuration::

        # app/config/config.yml
        hearsay_require_js:
            initialize_template: '::initialize_require_js.html.twig'

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

If you wish to provide configuration using a `build profile <http://github.com/jrburke/r.js/blob/master/build/example.build.js>`_::

        # app/config/config.yml
        hearsay_require_js:
            optimizer:
                path: /path/to/r.js
                build_profile: /path/to/app.build.js # Build profile location (filename is arbitrary)
                options: { skipModuleInsertion: true } # Additional options to pass to the optimizer (optional)

Note that any command line options will take precedence over matching corresponding build profile configuration.

If you wish to prevent unoptimized assets from being served (in e.g. a
production environment), you can suppress them::

        # app/config/config.yml
        hearsay_require_js:
            optimizer:
                path: /path/to/r.js
                hide_unoptimized_assets: true

If you're doing this, be sure that all the modules you need are bundled into
your optimized assets (i.e. you're not accessing any modules by dynamic name, or
if you are, then you're explicitly including those modules via optimizer
options) - otherwise, you may see certain assets available in development, but
not production.

If you have issues while running the optimiser on a slow machine,
you can still override the timeout of the node process::

        # app/config/config.yml
        hearsay_require_js:
            optimizer:
                timeout: 120

