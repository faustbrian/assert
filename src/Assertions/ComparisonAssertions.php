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
 * Comparison assertion methods.
 *
 * Dependencies:
 * - Base::createException()
 * - Base::stringify()
 * - Base::generateMessage()
 *
 * @author Brian Faust <brian@cline.sh>
 */
trait ComparisonAssertions
{
    public const int INVALID_EQ = 33;

    public const int INVALID_SAME = 34;

    public const int INVALID_NOT_EQ = 42;

    public const int INVALID_NOT_SAME = 43;

    /**
     * Assert that two values are equal (using ==).
     *
     * @param mixed                $value
     * @param mixed                $value2
     * @param null|callable|string $message
     *
     * @throws AssertionFailedException
     */
    public static function eq($value, $value2, $message = null, ?string $propertyPath = null): bool
    {
        if ($value != $value2) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected a value equal to %2$s. Got: %s'),
                static::stringify($value),
                static::stringify($value2),
            );

            throw self::createException($value, $message, self::INVALID_EQ, $propertyPath, ['expected' => $value2]);
        }

        return true;
    }

    /**
     * Assert that two values are the same (using ===).
     *
     * @param mixed                $value
     * @param mixed                $value2
     * @param null|callable|string $message
     *
     * @psalm-template ExpectedType
     *
     * @psalm-param ExpectedType $value2
     *
     * @psalm-assert =ExpectedType $value
     *
     * @throws AssertionFailedException
     */
    public static function same($value, $value2, $message = null, ?string $propertyPath = null): bool
    {
        if ($value !== $value2) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected a value identical to %2$s. Got: %s'),
                static::stringify($value),
                static::stringify($value2),
            );

            throw self::createException($value, $message, self::INVALID_SAME, $propertyPath, ['expected' => $value2]);
        }

        return true;
    }

    /**
     * Assert that two values are not equal (using ==).
     *
     * @param mixed                $value1
     * @param mixed                $value2
     * @param null|callable|string $message
     *
     * @throws AssertionFailedException
     */
    public static function notEq($value1, $value2, $message = null, ?string $propertyPath = null): bool
    {
        if ($value1 == $value2) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected a value not equal to %2$s. Got: %s'),
                static::stringify($value1),
                static::stringify($value2),
            );

            throw self::createException($value1, $message, self::INVALID_NOT_EQ, $propertyPath, ['expected' => $value2]);
        }

        return true;
    }

    /**
     * Assert that two values are not the same (using ===).
     *
     * @param mixed                $value1
     * @param mixed                $value2
     * @param null|callable|string $message
     *
     * @psalm-template ExpectedType
     *
     * @psalm-param ExpectedType $value2
     *
     * @psalm-assert !=ExpectedType $value1
     *
     * @throws AssertionFailedException
     */
    public static function notSame($value1, $value2, $message = null, ?string $propertyPath = null): bool
    {
        if ($value1 === $value2) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected a value not identical to %2$s. Got: %s'),
                static::stringify($value1),
                static::stringify($value2),
            );

            throw self::createException($value1, $message, self::INVALID_NOT_SAME, $propertyPath, ['expected' => $value2]);
        }

        return true;
    }
}
