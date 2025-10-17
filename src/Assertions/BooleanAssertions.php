<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Assert\Assertions;

use Cline\Assert\AssertionFailedException;

use function sprintf;

/**
 * Boolean value assertion methods.
 *
 * Dependencies:
 * - Base::createException()
 * - Base::stringify()
 * - Base::generateMessage()
 *
 * @author Brian Faust <brian@cline.sh>
 */
trait BooleanAssertions
{
    public const int INVALID_TRUE = 32;

    public const int INVALID_FALSE = 38;

    public const int INVALID_NOT_FALSE = 39;

    /**
     * Assert that the value is boolean True.
     *
     * @param mixed                $value
     * @param null|callable|string $message
     *
     * @psalm-assert true $value
     *
     * @throws AssertionFailedException
     */
    public static function true($value, $message = null, ?string $propertyPath = null): bool
    {
        if (true !== $value) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected a value to be true. Got: %s'),
                static::stringify($value),
            );

            throw self::createException($value, $message, self::INVALID_TRUE, $propertyPath);
        }

        return true;
    }

    /**
     * Assert that the value is boolean False.
     *
     * @param mixed                $value
     * @param null|callable|string $message
     *
     * @psalm-assert false $value
     *
     * @throws AssertionFailedException
     */
    public static function false($value, $message = null, ?string $propertyPath = null): bool
    {
        if (false !== $value) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected a value to be false. Got: %s'),
                static::stringify($value),
            );

            throw self::createException($value, $message, self::INVALID_FALSE, $propertyPath);
        }

        return true;
    }

    /**
     * Assert that the value is not boolean False.
     *
     * @param mixed                $value
     * @param null|callable|string $message
     *
     * @psalm-assert !false $value
     *
     * @throws AssertionFailedException
     */
    public static function notFalse($value, $message = null, ?string $propertyPath = null): bool
    {
        if (false === $value) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected a value other than false. Got: %s'),
                static::stringify($value),
            );

            throw self::createException($value, $message, self::INVALID_NOT_FALSE, $propertyPath);
        }

        return true;
    }
}
