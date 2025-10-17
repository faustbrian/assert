<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Assert\Assertions;

use Cline\Assert\AssertionFailedException;

use function is_string;
use function mb_trim;
use function sprintf;

/**
 * Null and empty value assertion methods.
 *
 * Dependencies:
 * - Base::createException()
 * - Base::stringify()
 * - Base::generateMessage()
 *
 * @author Brian Faust <brian@cline.sh>
 */
trait NullEmptyAssertions
{
    public const int VALUE_EMPTY = 14;

    public const int VALUE_NULL = 15;

    public const int VALUE_NOT_NULL = 25;

    public const int VALUE_NOT_EMPTY = 205;

    public const int INVALID_NOT_BLANK = 27;

    /**
     * Assert that value is not empty.
     *
     * @param mixed                $value
     * @param null|callable|string $message
     *
     * @psalm-assert !empty $value
     *
     * @throws AssertionFailedException
     */
    public static function notEmpty($value, $message = null, ?string $propertyPath = null): bool
    {
        if (empty($value)) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected a non-empty value. Got: %s'),
                static::stringify($value),
            );

            throw self::createException($value, $message, self::VALUE_EMPTY, $propertyPath);
        }

        return true;
    }

    /**
     * Assert that value is empty.
     *
     * @param mixed                $value
     * @param null|callable|string $message
     *
     * @psalm-assert empty $value
     *
     * @throws AssertionFailedException
     */
    public static function noContent($value, $message = null, ?string $propertyPath = null): bool
    {
        if (!empty($value)) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected an empty value. Got: %s'),
                static::stringify($value),
            );

            throw self::createException($value, $message, self::VALUE_NOT_EMPTY, $propertyPath);
        }

        return true;
    }

    /**
     * Assert that value is null.
     *
     * @param mixed                $value
     * @param null|callable|string $message
     *
     * @psalm-assert null $value
     */
    public static function null($value, $message = null, ?string $propertyPath = null): bool
    {
        if (null !== $value) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected null. Got: %s'),
                static::stringify($value),
            );

            throw self::createException($value, $message, self::VALUE_NOT_NULL, $propertyPath);
        }

        return true;
    }

    /**
     * Assert that value is not null.
     *
     * @param mixed                $value
     * @param null|callable|string $message
     *
     * @psalm-assert !null $value
     *
     * @throws AssertionFailedException
     */
    public static function notNull($value, $message = null, ?string $propertyPath = null): bool
    {
        if (null === $value) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected a value other than null.'),
                static::stringify($value),
            );

            throw self::createException($value, $message, self::VALUE_NULL, $propertyPath);
        }

        return true;
    }

    /**
     * Assert that value is not blank.
     *
     * @param mixed                $value
     * @param null|callable|string $message
     *
     * @throws AssertionFailedException
     */
    public static function notBlank($value, $message = null, ?string $propertyPath = null): bool
    {
        if (false === $value || (empty($value) && '0' !== $value) || (is_string($value) && '' === mb_trim($value))) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected a non-blank value. Got: %s'),
                static::stringify($value),
            );

            throw self::createException($value, $message, self::INVALID_NOT_BLANK, $propertyPath);
        }

        return true;
    }
}
