<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Assert\Assertions;

use Cline\Assert\AssertionFailedException;

use function ctype_digit;
use function is_array;
use function is_bool;
use function is_callable;
use function is_float;
use function is_int;
use function is_numeric;
use function is_object;
use function is_resource;
use function is_scalar;
use function is_string;
use function mb_ltrim;
use function sprintf;

/**
 * Primitive type assertion methods.
 *
 * Dependencies:
 * - Base::createException()
 * - Base::stringify()
 * - Base::generateMessage()
 *
 * @author Brian Faust <brian@cline.sh>
 */
trait TypeAssertions
{
    public const int INVALID_INTEGER = 10;

    public const int INVALID_FLOAT = 9;

    public const int INVALID_DIGIT = 11;

    public const int INVALID_INTEGERISH = 12;

    public const int INVALID_BOOLEAN = 13;

    public const int INVALID_SCALAR = 209;

    public const int INVALID_STRING = 16;

    public const int INVALID_NUMERIC = 23;

    public const int INVALID_RESOURCE = 243;

    public const int INVALID_ARRAY = 24;

    public const int INVALID_OBJECT = 207;

    public const int INVALID_CALLABLE = 215;

    public const int INVALID_ITERABLE = 239;

    public const int INVALID_INSTANCE_OF_ANY = 240;

    public const int INVALID_ANY_OF = 241;

    public const int INVALID_NOT_A = 242;

    /**
     * Assert that value is a php integer.
     *
     * @param mixed                $value
     * @param null|callable|string $message
     *
     * @psalm-assert int $value
     *
     * @throws AssertionFailedException
     */
    public static function integer($value, $message = null, ?string $propertyPath = null): bool
    {
        if (!is_int($value)) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected an integer. Got: %s'),
                static::stringify($value),
            );

            throw self::createException($value, $message, self::INVALID_INTEGER, $propertyPath);
        }

        return true;
    }

    /**
     * Assert that value is a php float.
     *
     * @param mixed                $value
     * @param null|callable|string $message
     *
     * @psalm-assert float $value
     *
     * @throws AssertionFailedException
     */
    public static function float($value, $message = null, ?string $propertyPath = null): bool
    {
        if (!is_float($value)) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected a float. Got: %s'),
                static::stringify($value),
            );

            throw self::createException($value, $message, self::INVALID_FLOAT, $propertyPath);
        }

        return true;
    }

    /**
     * Validates if an integer or integerish is a digit.
     *
     * @param mixed                $value
     * @param null|callable|string $message
     *
     * @psalm-assert =numeric $value
     *
     * @throws AssertionFailedException
     */
    public static function digit($value, $message = null, ?string $propertyPath = null): bool
    {
        if (is_object($value) || is_array($value) || is_resource($value)) {
            $stringValue = '';
        } else {
            /** @var null|bool|float|int|string $value */
            $stringValue = is_string($value) ? $value : (string) (is_bool($value) || null === $value ? '' : $value);
        }

        if (!ctype_digit($stringValue)) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected a digit. Got: %s'),
                static::stringify($value),
            );

            throw self::createException($value, $message, self::INVALID_DIGIT, $propertyPath);
        }

        return true;
    }

    /**
     * Assert that value is a php integer'ish.
     *
     * @param mixed                $value
     * @param null|callable|string $message
     *
     * @throws AssertionFailedException
     */
    public static function integerish($value, $message = null, ?string $propertyPath = null): bool
    {
        if (
            is_resource($value)
            || is_object($value)
            || is_bool($value)
            || null === $value
            || is_array($value)
            || (is_string($value) && '' === $value)
        ) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected an integerish value. Got: %s'),
                static::stringify($value),
            );

            throw self::createException($value, $message, self::INVALID_INTEGERISH, $propertyPath);
        }

        // At this point, $value is int, float, or non-empty string
        /** @var float|int|string $value */
        $intValue = (int) $value;
        $stringValue = (string) $value;
        $intAsString = (string) $intValue;

        $trimmedStringValue = mb_ltrim($stringValue, '0');

        if (
            $intAsString !== $stringValue
            && $intAsString !== $trimmedStringValue
            // @phpstan-ignore notIdentical.alwaysTrue
            && '' !== $intAsString
            && '' !== $trimmedStringValue
        ) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected an integerish value. Got: %s'),
                static::stringify($value),
            );

            throw self::createException($value, $message, self::INVALID_INTEGERISH, $propertyPath);
        }

        return true;
    }

    /**
     * Assert that value is php boolean.
     *
     * @param mixed                $value
     * @param null|callable|string $message
     *
     * @psalm-assert bool $value
     *
     * @throws AssertionFailedException
     */
    public static function boolean($value, $message = null, ?string $propertyPath = null): bool
    {
        if (!is_bool($value)) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected a boolean. Got: %s'),
                static::stringify($value),
            );

            throw self::createException($value, $message, self::INVALID_BOOLEAN, $propertyPath);
        }

        return true;
    }

    /**
     * Assert that value is a PHP scalar.
     *
     * @param mixed                $value
     * @param null|callable|string $message
     *
     * @psalm-assert scalar $value
     *
     * @throws AssertionFailedException
     */
    public static function scalar($value, $message = null, ?string $propertyPath = null): bool
    {
        if (!is_scalar($value)) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected a scalar. Got: %s'),
                static::stringify($value),
            );

            throw self::createException($value, $message, self::INVALID_SCALAR, $propertyPath);
        }

        return true;
    }

    /**
     * Assert that value is a string.
     *
     * @param mixed                $value
     * @param null|callable|string $message
     *
     * @psalm-assert string $value
     *
     * @throws AssertionFailedException
     */
    public static function string($value, $message = null, ?string $propertyPath = null): bool
    {
        if (!is_string($value)) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected a string. Got: %s'),
                static::stringify($value),
            );

            throw self::createException($value, $message, self::INVALID_STRING, $propertyPath);
        }

        return true;
    }

    /**
     * Assert that value is numeric.
     *
     * @param mixed                $value
     * @param null|callable|string $message
     *
     * @psalm-assert numeric $value
     *
     * @throws AssertionFailedException
     */
    public static function numeric($value, $message = null, ?string $propertyPath = null): bool
    {
        if (!is_numeric($value)) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected a numeric. Got: %s'),
                static::stringify($value),
            );

            throw self::createException($value, $message, self::INVALID_NUMERIC, $propertyPath);
        }

        return true;
    }

    /**
     * Assert that value is a resource.
     *
     * @param mixed                $value
     * @param null|callable|string $message
     *
     * @psalm-assert resource $value
     */
    public static function isResource($value, $message = null, ?string $propertyPath = null): bool
    {
        if (!is_resource($value)) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected a resource. Got: %s'),
                static::stringify($value),
            );

            throw self::createException($value, $message, self::INVALID_RESOURCE, $propertyPath);
        }

        return true;
    }

    /**
     * Assert that value is an array.
     *
     * @param mixed                $value
     * @param null|callable|string $message
     *
     * @phpstan-assert array<array-key, mixed> $value
     *
     * @psalm-assert array $value
     *
     * @throws AssertionFailedException
     */
    public static function isArray($value, $message = null, ?string $propertyPath = null): bool
    {
        if (!is_array($value)) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected an array. Got: %s'),
                static::stringify($value),
            );

            throw self::createException($value, $message, self::INVALID_ARRAY, $propertyPath);
        }

        return true;
    }

    /**
     * Determines that the provided value is an object.
     *
     * @param mixed                $value
     * @param null|callable|string $message
     *
     * @psalm-assert object $value
     *
     * @throws AssertionFailedException
     */
    public static function isObject($value, $message = null, ?string $propertyPath = null): bool
    {
        if (!is_object($value)) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected an object. Got: %s'),
                static::stringify($value),
            );

            throw self::createException($value, $message, self::INVALID_OBJECT, $propertyPath);
        }

        return true;
    }

    /**
     * Determines that the provided value is callable.
     *
     * @param mixed                $value
     * @param null|callable|string $message
     *
     * @psalm-assert callable $value
     *
     * @throws AssertionFailedException
     */
    public static function isCallable($value, $message = null, ?string $propertyPath = null): bool
    {
        if (!is_callable($value)) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected a callable. Got: %s'),
                static::stringify($value),
            );

            throw self::createException($value, $message, self::INVALID_CALLABLE, $propertyPath);
        }

        return true;
    }

    /**
     * Assert that value is iterable (array or Traversable).
     *
     * @param mixed                $value
     * @param null|callable|string $message
     *
     * @psalm-assert iterable $value
     *
     * @throws AssertionFailedException
     */
    public static function isIterable($value, $message = null, ?string $propertyPath = null): bool
    {
        if (!is_array($value) && !($value instanceof \Traversable)) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected an iterable. Got: %s'),
                static::stringify($value),
            );

            throw self::createException($value, $message, self::INVALID_ITERABLE, $propertyPath);
        }

        return true;
    }

    /**
     * Assert that value is instance of any class in array.
     *
     * @param mixed                $value
     * @param array<class-string>  $classes
     * @param null|callable|string $message
     *
     * @psalm-assert object $value
     *
     * @throws AssertionFailedException
     */
    public static function isInstanceOfAny($value, array $classes, $message = null, ?string $propertyPath = null): bool
    {
        foreach ($classes as $class) {
            if ($value instanceof $class) {
                return true;
            }
        }

        $message = sprintf(
            self::generateMessage($message ?: 'Expected an instance of any of %2$s. Got: %s'),
            static::stringify($value),
            implode(', ', array_map([static::class, 'stringify'], $classes)),
        );

        throw self::createException($value, $message, self::INVALID_INSTANCE_OF_ANY, $propertyPath);
    }

    /**
     * Assert that value is_a() any of the provided classes.
     *
     * @param object|string        $value
     * @param array<class-string>  $classes
     * @param null|callable|string $message
     *
     * @throws AssertionFailedException
     */
    public static function isAnyOf($value, array $classes, $message = null, ?string $propertyPath = null): bool
    {
        foreach ($classes as $class) {
            self::string($class, 'Expected class as a string. Got: %s');

            if (\is_a($value, $class, is_string($value))) {
                return true;
            }
        }

        $message = sprintf(
            self::generateMessage($message ?: 'Expected an instance of any of this classes or any of those classes among their parents "%2$s". Got: %s'),
            static::stringify($value),
            implode(', ', $classes),
        );

        throw self::createException($value, $message, self::INVALID_ANY_OF, $propertyPath);
    }

    /**
     * Assert that value is not is_a() the provided class.
     *
     * @param object|string        $value
     * @param class-string         $class
     * @param null|callable|string $message
     *
     * @throws AssertionFailedException
     */
    public static function isNotA($value, string $class, $message = null, ?string $propertyPath = null): bool
    {
        self::string($class, 'Expected class as a string. Got: %s');

        if (\is_a($value, $class, is_string($value))) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected an instance of this class or to this class among its parents other than "%2$s". Got: %s'),
                static::stringify($value),
                $class,
            );

            throw self::createException($value, $message, self::INVALID_NOT_A, $propertyPath);
        }

        return true;
    }
}
