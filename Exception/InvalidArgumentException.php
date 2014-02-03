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
 * This class represents the exception that is thrown if an argument does not
 * match with the expected value
 *
 * @author Igor Timoshenko <igor.timoshenko@i.ua>
 * @codeCoverageIgnore
 */
class InvalidArgumentException extends \InvalidArgumentException implements ExceptionInterface
{
}
