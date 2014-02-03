<?php

/*
 * This file is part of the HearsayRequireJSBundle package.
 *
 * (c) Hearsay News Products, Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hearsay\RequireJSBundle\Exception;

/**
 * This class represents the exception that is thrown if a path does not match
 * with the expected path
 *
 * @author Igor Timoshenko <igor.timoshenko@i.ua>
 * @codeCoverageIgnore
 */
class InvalidPathException extends InvalidArgumentException implements ExceptionInterface
{
    /**
     * {@inheritDoc}
     */
    public function __construct()
    {
        parent::__construct(
            'The path should NOT include the ".js" file extension.'
        );
    }
}
