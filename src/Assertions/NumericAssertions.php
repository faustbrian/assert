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
 * Numeric comparison and range assertion methods.
 *
 * Dependencies:
 * - TypeAssertions::numeric() (optional, for type checking)
 * - Base::createException()
 * - Base::stringify()
 * - Base::generateMessage()
 *
 * @author Brian Faust <brian@cline.sh>
 */
trait NumericAssertions
{
    public const int INVALID_LESS = 210;

    public const int INVALID_LESS_OR_EQUAL = 211;

    public const int INVALID_GREATER = 212;

    public const int INVALID_GREATER_OR_EQUAL = 213;

    public const int INVALID_BETWEEN = 219;

    public const int INVALID_BETWEEN_EXCLUSIVE = 220;

    public const int INVALID_RANGE = 30;

    public const int INVALID_MIN = 35;

    public const int INVALID_MAX = 36;

    public const int INVALID_POSITIVE_INTEGER = 235;

    public const int INVALID_NATURAL = 236;

    /**
     * Determines if the value is less than given limit.
     *
     * @param mixed                $value
     * @param mixed                $limit
     * @param null|callable|string $message
     *
     * @throws AssertionFailedException
     */
    public static function lessThan($value, $limit, $message = null, ?string $propertyPath = null): bool
    {
        if ($value >= $limit) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected a value less than %2$s. Got: %s'),
                static::stringify($value),
                static::stringify($limit),
            );

            throw self::createException($value, $message, self::INVALID_LESS, $propertyPath, ['limit' => $limit]);
        }

        return true;
    }

    /**
     * Determines if the value is less or equal than given limit.
     *
     * @param mixed                $value
     * @param mixed                $limit
     * @param null|callable|string $message
     *
     * @throws AssertionFailedException
     */
    public static function lessOrEqualThan($value, $limit, $message = null, ?string $propertyPath = null): bool
    {
        if ($value > $limit) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected a value less than or equal to %2$s. Got: %s'),
                static::stringify($value),
                static::stringify($limit),
            );

            throw self::createException($value, $message, self::INVALID_LESS_OR_EQUAL, $propertyPath, ['limit' => $limit]);
        }

        return true;
    }

    /**
     * Determines if the value is greater than given limit.
     *
     * @param mixed                $value
     * @param mixed                $limit
     * @param null|callable|string $message
     *
     * @throws AssertionFailedException
     */
    public static function greaterThan($value, $limit, $message = null, ?string $propertyPath = null): bool
    {
        if ($value <= $limit) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected a value greater than %2$s. Got: %s'),
                static::stringify($value),
                static::stringify($limit),
            );

            throw self::createException($value, $message, self::INVALID_GREATER, $propertyPath, ['limit' => $limit]);
        }

        return true;
    }

    /**
     * Determines if the value is greater or equal than given limit.
     *
     * @param mixed                $value
     * @param mixed                $limit
     * @param null|callable|string $message
     *
     * @throws AssertionFailedException
     */
    public static function greaterOrEqualThan($value, $limit, $message = null, ?string $propertyPath = null): bool
    {
        if ($value < $limit) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected a value greater than or equal to %2$s. Got: %s'),
                static::stringify($value),
                static::stringify($limit),
            );

            throw self::createException($value, $message, self::INVALID_GREATER_OR_EQUAL, $propertyPath, ['limit' => $limit]);
        }

        return true;
    }

    /**
     * Assert that a value is greater or equal than a lower limit, and less than or equal to an upper limit.
     *
     * @param mixed                $value
     * @param mixed                $lowerLimit
     * @param mixed                $upperLimit
     * @param null|callable|string $message
     *
     * @throws AssertionFailedException
     */
    public static function between($value, $lowerLimit, $upperLimit, $message = null, ?string $propertyPath = null): bool
    {
        if ($lowerLimit > $value || $value > $upperLimit) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected a value between %2$s and %3$s (inclusive). Got: %s'),
                static::stringify($value),
                static::stringify($lowerLimit),
                static::stringify($upperLimit),
            );

            throw self::createException($value, $message, self::INVALID_BETWEEN, $propertyPath, ['lower' => $lowerLimit, 'upper' => $upperLimit]);
        }

        return true;
    }

    /**
     * Assert that a value is greater than a lower limit, and less than an upper limit.
     *
     * @param mixed                $value
     * @param mixed                $lowerLimit
     * @param mixed                $upperLimit
     * @param null|callable|string $message
     *
     * @throws AssertionFailedException
     */
    public static function betweenExclusive($value, $lowerLimit, $upperLimit, $message = null, ?string $propertyPath = null): bool
    {
        if ($lowerLimit >= $value || $value >= $upperLimit) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected a value between %2$s and %3$s (exclusive). Got: %s'),
                static::stringify($value),
                static::stringify($lowerLimit),
                static::stringify($upperLimit),
            );

            throw self::createException($value, $message, self::INVALID_BETWEEN_EXCLUSIVE, $propertyPath, ['lower' => $lowerLimit, 'upper' => $upperLimit]);
        }

        return true;
    }

    /**
     * Assert that a number is at least as big as a given limit.
     *
     * @param mixed                $value
     * @param mixed                $minValue
     * @param mixed                $maxValue
     * @param null|callable|string $message
     *
     * @psalm-assert =numeric $value
     *
     * @throws AssertionFailedException
     */
    public static function range($value, $minValue, $maxValue, $message = null, ?string $propertyPath = null): bool
    {
        self::numeric($value, $message, $propertyPath);

        if ($value < $minValue || $value > $maxValue) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected a number between %2$s and %3$s. Got: %s'),
                static::stringify($value),
                static::stringify($minValue),
                static::stringify($maxValue),
            );

            throw self::createException($value, $message, self::INVALID_RANGE, $propertyPath, ['min' => $minValue, 'max' => $maxValue]);
        }

        return true;
    }

    /**
     * Assert that a value is at least as big as a given limit.
     *
     * @param mixed                $value
     * @param mixed                $minValue
     * @param null|callable|string $message
     *
     * @psalm-assert =numeric $value
     *
     * @throws AssertionFailedException
     */
    public static function min($value, $minValue, $message = null, ?string $propertyPath = null): bool
    {
        self::numeric($value, $message, $propertyPath);

        if ($value < $minValue) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected a number at least %2$s. Got: %s'),
                static::stringify($value),
                static::stringify($minValue),
            );

            throw self::createException($value, $message, self::INVALID_MIN, $propertyPath, ['min' => $minValue]);
        }

        return true;
    }

    /**
     * Assert that a number is smaller as a given limit.
     *
     * @param mixed                $value
     * @param mixed                $maxValue
     * @param null|callable|string $message
     *
     * @psalm-assert =numeric $value
     *
     * @throws AssertionFailedException
     */
    public static function max($value, $maxValue, $message = null, ?string $propertyPath = null): bool
    {
        self::numeric($value, $message, $propertyPath);

        if ($value > $maxValue) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected a number at most %2$s. Got: %s'),
                static::stringify($value),
                static::stringify($maxValue),
            );

            throw self::createException($value, $message, self::INVALID_MAX, $propertyPath, ['max' => $maxValue]);
        }

        return true;
    }

    /**
     * Assert that a value is a positive (non-zero) integer.
     *
     * @param mixed                $value
     * @param null|callable|string $message
     *
     * @psalm-assert positive-int $value
     *
     * @throws AssertionFailedException
     */
    public static function positiveInteger($value, $message = null, ?string $propertyPath = null): bool
    {
        if (!(\is_int($value) && $value > 0)) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected a positive integer. Got: %s'),
                static::stringify($value),
            );

            throw self::createException($value, $message, self::INVALID_POSITIVE_INTEGER, $propertyPath);
        }

        return true;
    }

    /**
     * Assert that a value is a non-negative integer (0 or positive).
     *
     * @param mixed                $value
     * @param null|callable|string $message
     *
     * @psalm-assert positive-int|0 $value
     *
     * @throws AssertionFailedException
     */
    public static function natural($value, $message = null, ?string $propertyPath = null): bool
    {
        if (!\is_int($value) || $value < 0) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected a non-negative integer. Got: %s'),
                static::stringify($value),
            );

            throw self::createException($value, $message, self::INVALID_NATURAL, $propertyPath);
        }

        return true;
    }
}
