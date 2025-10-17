<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Assert;

use BadMethodCallException;
use Cline\Assert\Assertions\AssertionInfrastructure;

use function array_key_exists;
use function array_merge;
use function array_shift;
use function call_user_func_array;
use function mb_substr;
use function str_starts_with;
use function throw_unless;

/**
 * Abstract base class for building custom assertion classes.
 *
 * Provides core infrastructure for assertion composition:
 * - Dynamic nullOr* and all* variant generation via __callStatic()
 * - Exception handling infrastructure
 * - Helper methods for message generation and value stringification
 *
 * To create custom assertion classes, extend this class and use only
 * the assertion trait categories you need.
 *
 * @example
 * ```php
 * use Cline\Assert\AbstractAssertion;
 * use Cline\Assert\Assertions\TypeAssertions;
 * use Cline\Assert\Assertions\StringAssertions;
 *
 * class MyAssertion extends AbstractAssertion
 * {
 *     use TypeAssertions;
 *     use StringAssertions;
 * }
 * ```
 */
abstract class AbstractAssertion
{
    use AssertionInfrastructure;

    /**
     * Exception to throw when an assertion failed.
     *
     * @var string
     */
    protected static $exceptionClass = InvalidArgumentException::class;

    /**
     * Static call handler to implement:
     *  - "null or assertion" delegation
     *  - "all" delegation.
     *
     * @param array<mixed> $args
     *
     * @throws AssertionFailedException
     *
     * @return bool|mixed
     */
    public static function __callStatic(string $method, array $args)
    {
        if (str_starts_with($method, 'nullOr')) {
            throw_unless(array_key_exists(0, $args), new BadMethodCallException('Missing the first argument.'));

            if (null === $args[0]) {
                return true;
            }

            $method = mb_substr($method, 6);

            /** @var callable $callable */
            $callable = [static::class, $method];

            return call_user_func_array($callable, $args);
        }

        if (str_starts_with($method, 'all')) {
            throw_unless(array_key_exists(0, $args), new BadMethodCallException('Missing the first argument.'));

            self::isTraversable($args[0]);

            $method = mb_substr($method, 3);
            $values = array_shift($args);
            $calledClass = static::class;

            /** @var iterable<mixed> $values */
            foreach ($values as $value) {
                /** @var callable $callable */
                $callable = [$calledClass, $method];
                call_user_func_array($callable, array_merge([$value], $args));
            }

            return true;
        }

        throw new BadMethodCallException('No assertion Assertion#'.$method.' exists.');
    }
}
