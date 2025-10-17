<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Assert\Assertions;

use ArrayAccess;
use Cline\Assert\AssertionFailedException;
use Countable;
use ResourceBundle;
use SimpleXMLElement;
use Traversable;

use function array_filter;
use function array_key_exists;
use function array_keys;
use function array_map;
use function array_replace_recursive;
use function array_search;
use function count;
use function function_exists;
use function implode;
use function in_array;
use function is_array;
use function is_countable;
use function is_int;
use function is_string;
use function sprintf;

/**
 * Array and collection assertion methods.
 *
 * Dependencies:
 * - Base::createException()
 * - Base::stringify()
 * - Base::generateMessage()
 * - TypeAssertions::isArray()
 * - ComparisonAssertions::eq()
 * - TypeAssertions::notEmpty()
 *
 * @author Brian Faust <brian@cline.sh>
 */
trait ArrayAssertions
{
    public const int INVALID_CHOICE = 22;

    public const int INVALID_KEY_EXISTS = 26;

    public const int INVALID_COUNT = 41;

    public const int INVALID_TRAVERSABLE = 44;

    public const int INVALID_ARRAY_ACCESSIBLE = 45;

    public const int INVALID_KEY_ISSET = 46;

    public const int INVALID_VALUE_IN_ARRAY = 47;

    public const int INVALID_KEY_NOT_EXISTS = 216;

    public const int INVALID_COUNTABLE = 226;

    public const int INVALID_MIN_COUNT = 227;

    public const int INVALID_MAX_COUNT = 228;

    public const int INVALID_UNIQUE_VALUES = 230;

    public const int INVALID_LIST = 231;

    public const int INVALID_MAP = 232;

    public const int INVALID_COUNT_BETWEEN = 233;

    public const int INVALID_ARRAY_KEY = 234;

    /**
     * Assert that value is countable.
     *
     * @param mixed                $value
     * @param null|callable|string $message
     *
     * @psalm-assert countable $value
     *
     * @throws AssertionFailedException
     */
    public static function isCountable($value, $message = null, ?string $propertyPath = null): bool
    {
        if (!is_countable($value)) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected a countable. Got: %s'),
                static::stringify($value),
            );

            throw self::createException($value, $message, self::INVALID_COUNTABLE, $propertyPath);
        }

        return true;
    }

    /**
     * Assert that the count of countable is equal to count.
     *
     * @param array<mixed>|Countable|ResourceBundle|SimpleXMLElement $countable
     * @param int                                                    $count
     * @param null|callable|string                                   $message
     *
     * @throws AssertionFailedException
     */
    public static function count($countable, $count, $message = null, ?string $propertyPath = null): bool
    {
        if ($count !== count($countable)) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected a collection with exactly %2$d elements, but got %3$d elements. Got: %s'),
                static::stringify($countable),
                static::stringify($count),
                static::stringify(count($countable)),
            );

            throw self::createException($countable, $message, self::INVALID_COUNT, $propertyPath, ['count' => $count]);
        }

        return true;
    }

    /**
     * Assert that the countable have at least $count elements.
     *
     * @param array<mixed>|Countable|ResourceBundle|SimpleXMLElement $countable
     * @param int                                                    $count
     * @param null|callable|string                                   $message
     *
     * @throws AssertionFailedException
     */
    public static function minCount($countable, $count, $message = null, ?string $propertyPath = null): bool
    {
        if ($count > count($countable)) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected a collection with at least %2$d elements, but got %3$d elements. Got: %s'),
                static::stringify($countable),
                static::stringify($count),
                static::stringify(count($countable)),
            );

            throw self::createException($countable, $message, self::INVALID_MIN_COUNT, $propertyPath, ['count' => $count]);
        }

        return true;
    }

    /**
     * Assert that the countable have at most $count elements.
     *
     * @param array<mixed>|Countable|ResourceBundle|SimpleXMLElement $countable
     * @param int                                                    $count
     * @param null|callable|string                                   $message
     *
     * @throws AssertionFailedException
     */
    public static function maxCount($countable, $count, $message = null, ?string $propertyPath = null): bool
    {
        if ($count < count($countable)) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected a collection with at most %2$d elements, but got %3$d elements. Got: %s'),
                static::stringify($countable),
                static::stringify($count),
                static::stringify(count($countable)),
            );

            throw self::createException($countable, $message, self::INVALID_MAX_COUNT, $propertyPath, ['count' => $count]);
        }

        return true;
    }

    /**
     * Assert that value is an array or an array-accessible object.
     *
     * @param mixed                $value
     * @param null|callable|string $message
     *
     * @throws AssertionFailedException
     */
    public static function isArrayAccessible($value, $message = null, ?string $propertyPath = null): bool
    {
        if (!is_array($value) && !$value instanceof ArrayAccess) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected an array accessible. Got: %s'),
                static::stringify($value),
            );

            throw self::createException($value, $message, self::INVALID_ARRAY_ACCESSIBLE, $propertyPath);
        }

        return true;
    }

    /**
     * Assert that value is an array or a traversable object.
     *
     * @param mixed                $value
     * @param null|callable|string $message
     *
     * @phpstan-assert iterable<array-key, mixed> $value
     *
     * @psalm-assert iterable $value
     *
     * @throws AssertionFailedException
     */
    public static function isTraversable($value, $message = null, ?string $propertyPath = null): bool
    {
        if (!is_array($value) && !$value instanceof Traversable) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected a traversable. Got: %s'),
                static::stringify($value),
            );

            throw self::createException($value, $message, self::INVALID_TRAVERSABLE, $propertyPath);
        }

        return true;
    }

    /**
     * Assert that key exists in an array.
     *
     * @param mixed                $value
     * @param int|string           $key
     * @param null|callable|string $message
     *
     * @throws AssertionFailedException
     */
    public static function keyExists($value, $key, $message = null, ?string $propertyPath = null): bool
    {
        self::isArray($value, $message, $propertyPath);

        if (!array_key_exists($key, $value)) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected an array with key %2$s. Got: %s'),
                static::stringify($value),
                static::stringify($key),
            );

            throw self::createException($value, $message, self::INVALID_KEY_EXISTS, $propertyPath, ['key' => $key]);
        }

        return true;
    }

    /**
     * Assert that key does not exist in an array.
     *
     * @param mixed                $value
     * @param int|string           $key
     * @param null|callable|string $message
     *
     * @throws AssertionFailedException
     */
    public static function keyNotExists($value, $key, $message = null, ?string $propertyPath = null): bool
    {
        self::isArray($value, $message, $propertyPath);

        if (array_key_exists($key, $value)) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected an array without key %2$s. Got: %s'),
                static::stringify($value),
                static::stringify($key),
            );

            throw self::createException($value, $message, self::INVALID_KEY_NOT_EXISTS, $propertyPath, ['key' => $key]);
        }

        return true;
    }

    /**
     * Assert that key exists in an array/array-accessible object using isset().
     *
     * @param array<mixed>         $value
     * @param int|string           $key
     * @param null|callable|string $message
     *
     * @throws AssertionFailedException
     */
    public static function keyIsset(array $value, $key, $message = null, ?string $propertyPath = null): bool
    {
        self::isArrayAccessible($value, $message, $propertyPath);

        if (!array_key_exists($key, $value)) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected an array with key %2$s set. Got: %s'),
                static::stringify($value),
                static::stringify($key),
            );

            throw self::createException($value, $message, self::INVALID_KEY_ISSET, $propertyPath, ['key' => $key]);
        }

        return true;
    }

    /**
     * Assert that key exists in an array/array-accessible object and its value is not empty.
     *
     * @param array<array-key, mixed> $value
     * @param int|string              $key
     * @param null|callable|string    $message
     *
     * @throws AssertionFailedException
     */
    public static function notEmptyKey(array $value, $key, $message = null, ?string $propertyPath = null): bool
    {
        /** @var null|callable|string $message */
        self::keyIsset($value, $key, $message, $propertyPath);
        self::notEmpty($value[$key], $message, $propertyPath);

        return true;
    }

    /**
     * Assert that values in array are unique (using strict equality).
     *
     * @param array<mixed>         $values
     * @param null|callable|string $message
     *
     * @throws AssertionFailedException
     */
    public static function uniqueValues(array $values, $message = null, ?string $propertyPath = null): bool
    {
        foreach ($values as $key => $value) {
            if (array_search($value, $values, true) !== $key) {
                $message = sprintf(
                    self::generateMessage($message ?: 'Expected array to contain only unique values. Got duplicate: %s'),
                    static::stringify($value),
                );

                throw self::createException($value, $message, self::INVALID_UNIQUE_VALUES, $propertyPath, ['value' => $value]);
            }
        }

        return true;
    }

    /**
     * Assert that value is in array of choices.
     *
     * This is an alias of {@see choice()}.
     *
     * @param mixed                $value
     * @param array<mixed>         $choices
     * @param null|callable|string $message
     *
     * @throws AssertionFailedException
     */
    public static function inArray($value, array $choices, $message = null, ?string $propertyPath = null): bool
    {
        return self::choice($value, $choices, $message, $propertyPath);
    }

    /**
     * A more human-readable alias of inArray().
     *
     * @param mixed                $value
     * @param array<mixed>         $choices
     * @param null|callable|string $message
     *
     * @throws AssertionFailedException
     */
    public static function oneOf($value, array $choices, $message = null, ?string $propertyPath = null): bool
    {
        return self::inArray($value, $choices, $message, $propertyPath);
    }

    /**
     * Assert that value is not in array of choices.
     *
     * @param mixed                $value
     * @param array<mixed>         $choices
     * @param null|callable|string $message
     *
     * @throws AssertionFailedException
     */
    public static function notInArray($value, array $choices, $message = null, ?string $propertyPath = null): bool
    {
        if (in_array($value, $choices, true)) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected a value not in %2$s. Got: %s'),
                static::stringify($value),
                static::stringify($choices),
            );

            throw self::createException($value, $message, self::INVALID_VALUE_IN_ARRAY, $propertyPath, ['choices' => $choices]);
        }

        return true;
    }

    /**
     * Assert that value is in array of choices.
     *
     * @param mixed                $value
     * @param array<mixed>         $choices
     * @param null|callable|string $message
     *
     * @throws AssertionFailedException
     */
    public static function choice($value, array $choices, $message = null, ?string $propertyPath = null): bool
    {
        if (!in_array($value, $choices, true)) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected one of %2$s. Got: %s'),
                static::stringify($value),
                implode(', ', array_map(static::stringify(...), $choices)),
            );

            throw self::createException($value, $message, self::INVALID_CHOICE, $propertyPath, ['choices' => $choices]);
        }

        return true;
    }

    /**
     * Determines if the values array has every choice as key and that this choice has content.
     *
     * @param array<mixed>         $values
     * @param array<int|string>    $choices
     * @param null|callable|string $message
     *
     * @throws AssertionFailedException
     */
    public static function choicesNotEmpty(array $values, array $choices, $message = null, ?string $propertyPath = null): bool
    {
        self::notEmpty($values, $message, $propertyPath);

        /** @var int|string $choice */
        foreach ($choices as $choice) {
            self::notEmptyKey($values, $choice, $message, $propertyPath);
        }

        return true;
    }

    /**
     * Assert that the array contains the subset.
     *
     * @param mixed                $value
     * @param mixed                $value2
     * @param null|callable|string $message
     *
     * @throws AssertionFailedException
     */
    public static function eqArraySubset($value, $value2, $message = null, ?string $propertyPath = null): bool
    {
        self::isArray($value, $message, $propertyPath);
        self::isArray($value2, $message, $propertyPath);

        $patched = array_replace_recursive($value, $value2);
        self::eq($patched, $value, $message, $propertyPath);

        return true;
    }

    /**
     * Assert that value is a list (non-associative array with sequential integer keys starting from 0).
     *
     * @param mixed                $value
     * @param null|callable|string $message
     *
     * @psalm-assert list $value
     *
     * @throws AssertionFailedException
     */
    public static function isList($value, $message = null, ?string $propertyPath = null): bool
    {
        if (!is_array($value)) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected list - non-associative array. Got: %s'),
                static::stringify($value),
            );

            throw self::createException($value, $message, self::INVALID_LIST, $propertyPath);
        }

        if (function_exists('array_is_list')) {
            if (!\array_is_list($value)) {
                $message = sprintf(
                    self::generateMessage($message ?: 'Expected list - non-associative array. Got: %s'),
                    static::stringify($value),
                );

                throw self::createException($value, $message, self::INVALID_LIST, $propertyPath);
            }

            return true;
        }

        if ([] === $value) {
            return true;
        }

        $keys = array_keys($value);
        if (array_keys($keys) !== $keys) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected list - non-associative array. Got: %s'),
                static::stringify($value),
            );

            throw self::createException($value, $message, self::INVALID_LIST, $propertyPath);
        }

        return true;
    }

    /**
     * Assert that value is a non-empty list.
     *
     * @param mixed                $value
     * @param null|callable|string $message
     *
     * @psalm-assert non-empty-list $value
     *
     * @throws AssertionFailedException
     */
    public static function isNonEmptyList($value, $message = null, ?string $propertyPath = null): bool
    {
        self::isList($value, $message, $propertyPath);
        self::notEmpty($value, $message, $propertyPath);

        return true;
    }

    /**
     * Assert that value is a map (associative array with string keys only).
     *
     * @template T
     *
     * @param mixed                $value
     * @param null|callable|string $message
     *
     * @psalm-param mixed|array<T> $value
     *
     * @psalm-assert array<string, T> $value
     *
     * @throws AssertionFailedException
     */
    public static function isMap($value, $message = null, ?string $propertyPath = null): bool
    {
        if (
            !is_array($value)
            || array_keys($value) !== array_filter(array_keys($value), 'is_string')
        ) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected map - associative array with string keys. Got: %s'),
                static::stringify($value),
            );

            throw self::createException($value, $message, self::INVALID_MAP, $propertyPath);
        }

        return true;
    }

    /**
     * Assert that value is a non-empty map.
     *
     * @template T
     *
     * @param mixed                $value
     * @param null|callable|string $message
     *
     * @psalm-param mixed|array<T> $value
     *
     * @psalm-assert array<string, T> $value
     * @psalm-assert !empty $value
     *
     * @throws AssertionFailedException
     */
    public static function isNonEmptyMap($value, $message = null, ?string $propertyPath = null): bool
    {
        self::isMap($value, $message, $propertyPath);
        self::notEmpty($value, $message, $propertyPath);

        return true;
    }

    /**
     * Assert that array count is within range (inclusive).
     *
     * @param array<mixed>|Countable|ResourceBundle|SimpleXMLElement $countable
     * @param int                                                    $min
     * @param int                                                    $max
     * @param null|callable|string                                   $message
     *
     * @throws AssertionFailedException
     */
    public static function countBetween($countable, $min, $max, $message = null, ?string $propertyPath = null): bool
    {
        $count = count($countable);

        if ($count < $min || $count > $max) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected an array to contain between %2$d and %3$d elements. Got: %4$d'),
                static::stringify($countable),
                static::stringify($min),
                static::stringify($max),
                static::stringify($count),
            );

            throw self::createException($countable, $message, self::INVALID_COUNT_BETWEEN, $propertyPath, ['min' => $min, 'max' => $max]);
        }

        return true;
    }

    /**
     * Assert that value is a valid array key (int or string).
     *
     * @param mixed                $value
     * @param null|callable|string $message
     *
     * @psalm-assert array-key $value
     *
     * @throws AssertionFailedException
     */
    public static function validArrayKey($value, $message = null, ?string $propertyPath = null): bool
    {
        if (!(is_int($value) || is_string($value))) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected string or integer. Got: %s'),
                static::stringify($value),
            );

            throw self::createException($value, $message, self::INVALID_ARRAY_KEY, $propertyPath);
        }

        return true;
    }
}
