master: [![Build Status](https://travis-ci.org/hearsayit/HearsayRequireJSBundle.png?branch=master)](https://travis-ci.org/hearsayit/HearsayRequireJSBundle)
1.0: [![Build Status](https://travis-ci.org/hearsayit/HearsayRequireJSBundle.png?branch=1.0)](https://travis-ci.org/hearsayit/HearsayRequireJSBundle)

# HearsayRequireJSBundle #

## Overview ##

This bundle provides integration of the [RequireJS][1] library into Symfony2.

## Installation ##

### 1. Using Composer (recommended) ###

To install `HearsayRequireJSBundle` with [Composer][2] just add the following to
your `composer.json` file:

```json
{
    // ...
    "require": {
        // ...
        "hearsay/require-js-bundle": "2.0.*@dev"
        // ...
    }
    // ...
}
```

> Note that the `master` branch is under development and unstable yet. If you
> want to use stable version then specify the `1.0.*` version. However, remember
> that the `1.0` branch no longer provides new features, only bug fixes.

Then, you can install the new dependencies by running Composer's update command
from the directory where your `composer.json` file is located:

```sh
$ php composer.phar update hearsay/require-js-bundle
```

Now, Composer will automatically download all required files, and install them
for you. All that is left to do is to update your `AppKernel.php` file, and
register the new bundle:

```php
<?php
// in AppKernel::registerBundles()
$bundles = array(
    // ...
    new Hearsay\RequireJSBundle\HearsayRequireJSBundle(),
    // ...
);
```

## Configuration ##

This bundle configured under the `hearsay_require_js` key in your application
configuration. This includes settings related to paths, shim, optimization, and
more.

* [require_js_src](#require_js_src)
* [initialize_template](#initialize_template)
* [base_url](#base_url)
* [base_dir](#base_dir)
* [paths](#paths)
* [shim](#shim)
* [options](#options)
* [optimizer](#optimizer)
    * [path](#path)
    * [hide_unoptimized_assets](#hide_unoptimized_assets)
    * [exclude](#exclude)
    * [options](#options)
    * [timeout](#timeout)

#### _require_js_src_ ####

**type**: string **default**: //cdnjs.cloudflare.com/ajax/libs/require.js/2.1.8/require.min.js

This is a string that represents the template name which will render the
RequireJS src or an URL to the RequireJS.

#### _initialize_template_ ####

**type**: string **default**: HearsayRequireJSBundle::initialize.html.twig

This is a string that represents the template name which will render the
RequireJS initialization output. You can pass into this template any extra
options.

#### _base_url_ ####

**type**: string **default**: js **required**

This is a string that represents the base URL where assets are served, relative
to the website root directory. This URL also will exposed as an empty
namespace.

#### _base_dir_ ####

**type**: string **default**: null

This is a string that represents the base URL for the [r.js][3] optimizer, that
is generally actually a filesystem path.

#### _paths_ ####

**type**: array **default**: null

This is a prototype that represents an array of paths. Each path:

* includes the location and the key that determines whether the path is an
external;
* the location can be a string as well as an array of values
([paths fallbacks][4]);
* the location can expose a file as well as a directory, if the location is a
file then MUST NOT ends with the `.js` file extension;
* the location can referred to the files using a path like
`@AcmeDemoBundle/Resources/public/js/src/modules`;
* by default, is not an external;
* if specified as an external, will marked as an [empty][5] for the r.js
optimizer;
* will exposed as a namespace.

#### _shim_ ####

**type**: array **default**: null

This is a prototype that represents an array of shim, corresponds to the
RequireJS [shim config][6].

#### _options_ ####

**type**: array **default**: null

An array of key-value pairs to pass to the RequireJS.

#### _optimizer_ #####

**type**: array **default**: null

This block includes the r.js optimizer configuration options.

##### _path_ #####

**type**: string **default**: null

This is a string that represents the path to the r.js optimizer.

##### _hide_unoptimized_assets_ #####

**type**: boolean **default**: false

This determines whether the r.js optimizer should suppress unoptimized files.

##### _exclude_ #####

**type**: array **default**: []

An array of module names to exclude from the build profile.

##### _options_ #####

**type**: array **default**: null

An array of key-value pairs to pass to the r.js optimizer.

##### _timeout_ #####

**type**: integer **default**: 60

This determines the node.js process timeout, in seconds.

### Full Default Configuration ###

```yaml
hearsay_require_js:
    require_js_src:      //cdnjs.cloudflare.com/ajax/libs/require.js/2.1.8/require.min.js
    initialize_template: HearsayRequireJSBundle::initialize.html.twig
    base_url:            js
    base_dir:            ~ # Required
    paths:
        # Prototype
        path:
            location: ~ # Required
            external: false
    shim:
        # Prototype
        name:
            deps:    []
            exports: ~
    options:
        # Prototype
        name:
            value: ~ # Required
    optimizer:
        path:                    ~ # Required
        hide_unoptimized_assets: false
        exclude:                 []
        options:
            # Prototype
            name:
                value: ~ # Required
        timeout: 60
```

## Usage ##

Just output the RequireJS initialization and load files normally:

```
{{ require_js_initialize() }}

<script type="text/javascript">require(['demo/main'])</script>
```

Alternately, you can specify a file to be required immediately via the
`data-main` attribute:

```
{{ require_js_initialize({ 'main' : 'demo/main' }) }}
```

If you need to do anything fancy with the configuration, you can do so
manually by modifying the default configuration, and suppressing config output
when initializing RequireJS:

```
<script type="text/javascript">
    var require = {{ require_js.config|json_encode|raw }};

    require.ready = function(){console.log('DOM ready');};
</script>

{{ require_js_initialize({ 'configure' : false }) }}
```

To make global changes to the configuration/initialization output, you can
specify an initialization template in your configuration:

```yaml
# app/config/config.yml
hearsay_require_js:
    initialize_template: ::initialize_require_js.html.twig
```

To use this bundle with CoffeeScript, you can specify a path that contains
`.coffee` scripts (means a directory). The scripts will renamed to `.js`
scripts.

>Note that the `r.js` optimizer cannot optimize `.coffee` scripts. However, you
>can apply the [Assetic][7] filter to this scripts by the file extension.

### Optimization ###

This bundle provides the Assetic filter to create minified Javascript files
using by r.js optimizer. This also inlines any module definitions required by
the file being optimized. You need to provide a path to the r.js optimizer in
your configuration to use the filter:

```yaml
# app/config/config.yml
hearsay_require_js:
    optimizer:
        path: %kernel.root_dir%/../r.js
```

You can then use it like any other filter; for example, to optimize only in
production:

```
{% javascripts
    filter='?requirejs'
    '@AcmeDemoBundle/Resources/public/js/src/main.js'
%}
    {{ require_js_initialize({ 'main' : asset_url }) }}
{% endjavascripts %}
```

> Note that your configured path definitions will be incorporated into the
> optimizer filter, including the exclusion of external dependencies from the
> built profile's file.

If you wish to prevent unoptimized assets from being served (in e.g. production),
you can suppress them:

```yaml
# app/config/config.yml
hearsay_require_js:
    optimizer:
        path: %kernel.root_dir%/../r.js
        hide_unoptimized_assets: true
```

If you're doing this, be sure that all the modules you need are bundled into
your optimized assets (i.e. you're not accessing any modules by dynamic name, or
if you are, then you're explicitly including those modules via optimizer
options), otherwise you may see certain assets available in development, but
not in production.

## License ##

This bundle is released under the MIT license. See the complete license in the
bundle:

    Resources/meta/LICENSE

[1]: https://github.com/jrburke/requirejs
[2]: https://github.com/composer/composer
[3]: https://github.com/jrburke/r.js
[4]: http://requirejs.org/docs/api.html#pathsfallbacks
[5]: http://requirejs.org/docs/optimization.html#empty
[6]: http://requirejs.org/docs/api.html#config-shim
[7]: https://github.com/kriswallsmith/assetic