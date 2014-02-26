/*
 * This file is part of the HearsayRequireJSBundle package.
 *
 * (c) Hearsay News Products, Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require(['modules/module2', "modules/module"], function(module2,module) {
    return console.log(module2, module);
});