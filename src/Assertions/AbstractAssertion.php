<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Assert\Assertions;

use ArrayAccess;
use BadMethodCallException;
use Cline\Assert\Assertions\AssertionInfrastructure;
use Cline\Assert\Exceptions\InvalidArgumentException;
use Cline\Assert\Exceptions\ValidationError;
use Closure;
use DateTime;
use Exception;
use ReflectionClass;
use ReflectionException;
use Throwable;
use Traversable;

use const FILTER_FLAG_IPV4;
use const FILTER_FLAG_IPV6;
use const FILTER_VALIDATE_EMAIL;
use const FILTER_VALIDATE_IP;
use const JSON_ERROR_NONE;
use const LC_CTYPE;
use const PHP_VERSION;

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
 *
 * @phpstan-type MessageType callable|string|null
 *
 * Note: Many methods accept mixed types and validate at runtime, which is intentional.
 * PHPStan errors about argument types are suppressed where the library design requires
 * accepting mixed inputs for runtime validation.
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
     * @throws InvalidArgumentException
     *
     * @return bool|mixed
     */
    public static function __callStatic(string $method, array $args)
    {
        if (str_starts_with($method, 'nullOr')) {
            throw_unless(array_key_exists(0, $args), BadMethodCallException::class, 'Missing the first argument.');

            if (null === $args[0]) {
                return true;
            }

            $method = mb_substr($method, 6);

            /** @var callable $callable */
            $callable = [static::class, $method];

            return call_user_func_array($callable, $args);
        }

        if (str_starts_with($method, 'all')) {
            throw_unless(array_key_exists(0, $args), BadMethodCallException::class, 'Missing the first argument.');

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



    public static function isCountable(mixed $value, callable|string|null $message = null, ?string $propertyPath = null): bool
    {
        if (!is_countable($value)) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected a countable. Got: %s'),
                static::stringify($value),
            );

            throw self::createException($value, $message, ValidationError::InvalidCountable->value, $propertyPath);
        }

        return true;
    }

    public static function count(mixed $countable, int $count, callable|string|null $message = null, ?string $propertyPath = null): bool
    {
        // @phpstan-ignore-next-line argument.type (accepts mixed, validates at runtime)
        if ($count !== count($countable)) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected a collection with exactly %2$d elements, but got %3$d elements. Got: %s'),
                static::stringify($countable),
                static::stringify($count),
                // @phpstan-ignore-next-line argument.type (accepts mixed, validates at runtime)
                static::stringify(count($countable)),
            );

            throw self::createException($countable, $message, ValidationError::InvalidCount->value, $propertyPath, ['count' => $count]);
        }

        return true;
    }

    public static function minCount(mixed $countable, int $count, callable|string|null $message = null, ?string $propertyPath = null): bool
    {
        // @phpstan-ignore-next-line argument.type (accepts mixed, validates at runtime)
        if ($count > count($countable)) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected a collection with at least %2$d elements, but got %3$d elements. Got: %s'),
                static::stringify($countable),
                static::stringify($count),
                // @phpstan-ignore-next-line argument.type (accepts mixed, validates at runtime)
                static::stringify(count($countable)),
            );

            throw self::createException($countable, $message, ValidationError::InvalidMinCount->value, $propertyPath, ['count' => $count]);
        }

        return true;
    }

    public static function maxCount(mixed $countable, int $count, callable|string|null $message = null, ?string $propertyPath = null): bool
    {
        // @phpstan-ignore-next-line argument.type (accepts mixed, validates at runtime)
        if ($count < count($countable)) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected a collection with at most %2$d elements, but got %3$d elements. Got: %s'),
                static::stringify($countable),
                static::stringify($count),
                // @phpstan-ignore-next-line argument.type (accepts mixed, validates at runtime)
                static::stringify(count($countable)),
            );

            throw self::createException($countable, $message, ValidationError::InvalidMaxCount->value, $propertyPath, ['count' => $count]);
        }

        return true;
    }

    public static function isArrayAccessible(mixed $value, callable|string|null $message = null, ?string $propertyPath = null): bool
    {
        if (!is_array($value) && !$value instanceof ArrayAccess) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected an array accessible. Got: %s'),
                static::stringify($value),
            );

            throw self::createException($value, $message, ValidationError::InvalidArrayAccessible->value, $propertyPath);
        }

        return true;
    }

    public static function isTraversable(mixed $value, callable|string|null $message = null, ?string $propertyPath = null): bool
    {
        if (!is_array($value) && !$value instanceof Traversable) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected a traversable. Got: %s'),
                static::stringify($value),
            );

            throw self::createException($value, $message, ValidationError::InvalidTraversable->value, $propertyPath);
        }

        return true;
    }

    public static function keyExists(mixed $value, mixed $key, callable|string|null $message = null, ?string $propertyPath = null): bool
    {
        self::isArray($value, $message, $propertyPath);
        /** @var array<mixed> $value */
        /** @var int|string $key */

        if (!array_key_exists($key, $value)) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected an array with key %2$s. Got: %s'),
                static::stringify($value),
                static::stringify($key),
            );

            throw self::createException($value, $message, ValidationError::InvalidKeyExists->value, $propertyPath, ['key' => $key]);
        }

        return true;
    }

    public static function keyNotExists(mixed $value, mixed $key, callable|string|null $message = null, ?string $propertyPath = null): bool
    {
        self::isArray($value, $message, $propertyPath);
        /** @var array<mixed> $value */
        /** @var int|string $key */

        if (array_key_exists($key, $value)) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected an array without key %2$s. Got: %s'),
                static::stringify($value),
                static::stringify($key),
            );

            throw self::createException($value, $message, ValidationError::InvalidKeyNotExists->value, $propertyPath, ['key' => $key]);
        }

        return true;
    }

    /**
     * @param array<mixed> $value
     */
    public static function keyIsset(array $value, mixed $key, callable|string|null $message = null, ?string $propertyPath = null): bool
    {
        self::isArrayAccessible($value, $message, $propertyPath);
        /** @var int|string $key */

        if (!array_key_exists($key, $value)) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected an array with key %2$s set. Got: %s'),
                static::stringify($value),
                static::stringify($key),
            );

            throw self::createException($value, $message, ValidationError::InvalidKeyIsset->value, $propertyPath, ['key' => $key]);
        }

        return true;
    }

    /**
     * @param array<mixed> $value
     */
    public static function notEmptyKey(array $value, mixed $key, callable|string|null $message = null, ?string $propertyPath = null): bool
    {
        /** @var null|callable|string $message */
        /** @var int|string $key */
        self::keyIsset($value, $key, $message, $propertyPath);
        self::notEmpty($value[$key], $message, $propertyPath);

        return true;
    }

    /**
     * @param array<mixed> $values
     */
    public static function uniqueValues(array $values, callable|string|null $message = null, ?string $propertyPath = null): bool
    {
        foreach ($values as $key => $value) {
            if (array_search($value, $values, true) !== $key) {
                $message = sprintf(
                    self::generateMessage($message ?: 'Expected array to contain only unique values. Got duplicate: %s'),
                    static::stringify($value),
                );

                throw self::createException($value, $message, ValidationError::InvalidUniqueValues->value, $propertyPath, ['value' => $value]);
            }
        }

        return true;
    }

    /**
     * @param array<mixed> $choices
     */
    public static function inArray(mixed $value, array $choices, callable|string|null $message = null, ?string $propertyPath = null): bool
    {
        return self::choice($value, $choices, $message, $propertyPath);
    }

    /**
     * @param array<mixed> $choices
     */
    public static function oneOf(mixed $value, array $choices, callable|string|null $message = null, ?string $propertyPath = null): bool
    {
        return self::inArray($value, $choices, $message, $propertyPath);
    }

    /**
     * @param array<mixed> $choices
     */
    public static function notInArray(mixed $value, array $choices, callable|string|null $message = null, ?string $propertyPath = null): bool
    {
        if (in_array($value, $choices, true)) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected a value not in %2$s. Got: %s'),
                static::stringify($value),
                static::stringify($choices),
            );

            throw self::createException($value, $message, ValidationError::InvalidValueInArray->value, $propertyPath, ['choices' => $choices]);
        }

        return true;
    }

    /**
     * @param array<mixed> $choices
     */
    public static function choice(mixed $value, array $choices, callable|string|null $message = null, ?string $propertyPath = null): bool
    {
        if (!in_array($value, $choices, true)) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected one of %2$s. Got: %s'),
                static::stringify($value),
                implode(', ', array_map(static::stringify(...), $choices)),
            );

            throw self::createException($value, $message, ValidationError::InvalidChoice->value, $propertyPath, ['choices' => $choices]);
        }

        return true;
    }

    /**
     * @param array<mixed> $values
     * @param array<mixed> $choices
     */
    public static function choicesNotEmpty(array $values, array $choices, callable|string|null $message = null, ?string $propertyPath = null): bool
    {
        self::notEmpty($values, $message, $propertyPath);

        /** @var int|string $choice */
        foreach ($choices as $choice) {
            self::notEmptyKey($values, $choice, $message, $propertyPath);
        }

        return true;
    }

    public static function eqArraySubset(mixed $value, mixed $value2, callable|string|null $message = null, ?string $propertyPath = null): bool
    {
        self::isArray($value, $message, $propertyPath);
        self::isArray($value2, $message, $propertyPath);
        /** @var array<mixed> $value */
        /** @var array<mixed> $value2 */

        $patched = array_replace_recursive($value, $value2);
        self::eq($patched, $value, $message, $propertyPath);

        return true;
    }

    public static function isList(mixed $value, callable|string|null $message = null, ?string $propertyPath = null): bool
    {
        if (!is_array($value)) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected list - non-associative array. Got: %s'),
                static::stringify($value),
            );

            throw self::createException($value, $message, ValidationError::InvalidList->value, $propertyPath);
        }

        if (!array_is_list($value)) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected list - non-associative array. Got: %s'),
                static::stringify($value),
            );

            throw self::createException($value, $message, ValidationError::InvalidList->value, $propertyPath);
        }

        return true;
    }

    public static function isNonEmptyList(mixed $value, callable|string|null $message = null, ?string $propertyPath = null): bool
    {
        self::isList($value, $message, $propertyPath);
        self::notEmpty($value, $message, $propertyPath);

        return true;
    }

    public static function isMap(mixed $value, callable|string|null $message = null, ?string $propertyPath = null): bool
    {
        if (
            !is_array($value)
            || array_keys($value) !== array_filter(array_keys($value), is_string(...))
        ) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected map - associative array with string keys. Got: %s'),
                static::stringify($value),
            );

            throw self::createException($value, $message, ValidationError::InvalidMap->value, $propertyPath);
        }

        return true;
    }

    public static function isNonEmptyMap(mixed $value, callable|string|null $message = null, ?string $propertyPath = null): bool
    {
        self::isMap($value, $message, $propertyPath);
        self::notEmpty($value, $message, $propertyPath);

        return true;
    }

    public static function countBetween(mixed $countable, mixed $min, mixed $max, callable|string|null $message = null, ?string $propertyPath = null): bool
    {
        // @phpstan-ignore-next-line argument.type (accepts mixed, validates at runtime)
        $count = count($countable);

        if ($count < $min || $count > $max) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected an array to contain between %2$d and %3$d elements. Got: %4$d'),
                static::stringify($countable),
                static::stringify($min),
                static::stringify($max),
                static::stringify($count),
            );

            throw self::createException($countable, $message, ValidationError::InvalidCountBetween->value, $propertyPath, ['min' => $min, 'max' => $max]);
        }

        return true;
    }

    public static function validArrayKey(mixed $value, callable|string|null $message = null, ?string $propertyPath = null): bool
    {
        if (!is_int($value) && !is_string($value)) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected string or integer. Got: %s'),
                static::stringify($value),
            );

            throw self::createException($value, $message, ValidationError::InvalidArrayKey->value, $propertyPath);
        }

        return true;
    }

    public static function true(mixed $value, callable|string|null $message = null, ?string $propertyPath = null): bool
    {
        if (true !== $value) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected a value to be true. Got: %s'),
                static::stringify($value),
            );

            throw self::createException($value, $message, ValidationError::InvalidTrue->value, $propertyPath);
        }

        return true;
    }

    public static function false(mixed $value, callable|string|null $message = null, ?string $propertyPath = null): bool
    {
        if (false !== $value) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected a value to be false. Got: %s'),
                static::stringify($value),
            );

            throw self::createException($value, $message, ValidationError::InvalidFalse->value, $propertyPath);
        }

        return true;
    }

    public static function notFalse(mixed $value, callable|string|null $message = null, ?string $propertyPath = null): bool
    {
        if (false === $value) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected a value other than false. Got: %s'),
                static::stringify($value),
            );

            throw self::createException($value, $message, ValidationError::InvalidNotFalse->value, $propertyPath);
        }

        return true;
    }

    public static function eq(mixed $value, mixed $value2, callable|string|null $message = null, ?string $propertyPath = null): bool
    {
        if ($value != $value2) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected a value equal to %2$s. Got: %s'),
                static::stringify($value),
                static::stringify($value2),
            );

            throw self::createException($value, $message, ValidationError::InvalidEq->value, $propertyPath, ['expected' => $value2]);
        }

        return true;
    }

    public static function same(mixed $value, mixed $value2, callable|string|null $message = null, ?string $propertyPath = null): bool
    {
        if ($value !== $value2) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected a value identical to %2$s. Got: %s'),
                static::stringify($value),
                static::stringify($value2),
            );

            throw self::createException($value, $message, ValidationError::InvalidSame->value, $propertyPath, ['expected' => $value2]);
        }

        return true;
    }

    public static function notEq(mixed $value1, mixed $value2, callable|string|null $message = null, ?string $propertyPath = null): bool
    {
        if ($value1 == $value2) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected a value not equal to %2$s. Got: %s'),
                static::stringify($value1),
                static::stringify($value2),
            );

            throw self::createException($value1, $message, ValidationError::InvalidNotEq->value, $propertyPath, ['expected' => $value2]);
        }

        return true;
    }

    public static function notSame(mixed $value1, mixed $value2, callable|string|null $message = null, ?string $propertyPath = null): bool
    {
        if ($value1 === $value2) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected a value not identical to %2$s. Got: %s'),
                static::stringify($value1),
                static::stringify($value2),
            );

            throw self::createException($value1, $message, ValidationError::InvalidNotSame->value, $propertyPath, ['expected' => $value2]);
        }

        return true;
    }

    public static function satisfy(mixed $value, mixed $callback, callable|string|null $message = null, ?string $propertyPath = null): bool
    {
        self::isCallable($callback);
        /** @var callable $callback */

        if (false === $callback($value)) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected value to pass custom rule. Got: %s'),
                static::stringify($value),
            );

            throw self::createException($value, $message, ValidationError::InvalidSatisfy->value, $propertyPath);
        }

        return true;
    }

    public static function throws(Closure $expression, string $class = 'Exception', callable|string|null $message = null, ?string $propertyPath = null): bool
    {
        self::string($class);

        $actual = 'none';

        try {
            $expression();
        // @phpstan-ignore-next-line catch.neverThrown (dynamic expression may throw)
        } catch (Exception|Throwable $e) {
            $actual = $e::class;

            if ($e instanceof $class) {
                return true;
            }
        }

        $message = sprintf(
            self::generateMessage($message ?: 'Expected to throw "%2$s", got "%3$s"'),
            $expression,
            $class,
            $actual,
        );

        throw self::createException($expression, $message, ValidationError::InvalidThrows->value, $propertyPath, ['expected' => $class, 'actual' => $actual]);
    }

    public static function extensionLoaded(mixed $value, callable|string|null $message = null, ?string $propertyPath = null): bool
    {
        /** @var string $value */
        if (!extension_loaded($value)) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected extension to be loaded. Got: %s'),
                static::stringify($value),
            );

            throw self::createException($value, $message, ValidationError::InvalidExtension->value, $propertyPath);
        }

        return true;
    }

    public static function version(mixed $version1, mixed $operator, mixed $version2, callable|string|null $message = null, ?string $propertyPath = null): bool
    {
        self::notEmpty($operator, 'versionCompare operator is required and cannot be empty.');

        if (in_array(version_compare($version1, $version2, $operator), [0, false, null], true)) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected version %2$s %3$s. Got: %s'),
                static::stringify($version1),
                static::stringify($operator),
                static::stringify($version2),
            );

            throw self::createException($version1, $message, ValidationError::InvalidVersion->value, $propertyPath, ['operator' => $operator, 'version' => $version2]);
        }

        return true;
    }

    public static function phpVersion(mixed $operator, mixed $version, callable|string|null $message = null, ?string $propertyPath = null): bool
    {
        self::defined('PHP_VERSION');

        return self::version(PHP_VERSION, $operator, $version, $message, $propertyPath);
    }

    public static function extensionVersion(mixed $extension, mixed $operator, mixed $version, callable|string|null $message = null, ?string $propertyPath = null): bool
    {
        self::extensionLoaded($extension, $message, $propertyPath);

        $extensionVersion = phpversion($extension);
        throw_if($extensionVersion === false, self::createException($extension, 'Unable to determine extension version.', ValidationError::InvalidVersion->value, $propertyPath));

        return self::version($extensionVersion, $operator, $version, $message, $propertyPath);
    }

    public static function defined(mixed $constant, callable|string|null $message = null, ?string $propertyPath = null): bool
    {
        /** @var string $constant */
        if (!defined($constant)) {
            $message = sprintf(self::generateMessage($message ?: 'Expected a defined constant. Got: %s'), $constant);

            throw self::createException($constant, $message, ValidationError::InvalidConstant->value, $propertyPath);
        }

        return true;
    }

    public static function file(mixed $value, callable|string|null $message = null, ?string $propertyPath = null): bool
    {
        self::string($value, $message, $propertyPath);
        /** @var string $value */
        self::notEmpty($value, $message, $propertyPath);

        if (!is_file($value)) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected file to exist. Got: %s'),
                static::stringify($value),
            );

            throw self::createException($value, $message, ValidationError::InvalidFile->value, $propertyPath);
        }

        return true;
    }

    public static function directory(mixed $value, callable|string|null $message = null, ?string $propertyPath = null): bool
    {
        self::string($value, $message, $propertyPath);
        /** @var string $value */

        if (!is_dir($value)) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected a directory. Got: %s'),
                static::stringify($value),
            );

            throw self::createException($value, $message, ValidationError::InvalidDirectory->value, $propertyPath);
        }

        return true;
    }

    public static function readable(mixed $value, callable|string|null $message = null, ?string $propertyPath = null): bool
    {
        self::string($value, $message, $propertyPath);
        /** @var string $value */

        if (!is_readable($value)) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected readable path. Got: %s'),
                static::stringify($value),
            );

            throw self::createException($value, $message, ValidationError::InvalidReadable->value, $propertyPath);
        }

        return true;
    }

    public static function writeable(mixed $value, callable|string|null $message = null, ?string $propertyPath = null): bool
    {
        self::string($value, $message, $propertyPath);
        /** @var string $value */

        if (!is_writable($value)) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected writable path. Got: %s'),
                static::stringify($value),
            );

            throw self::createException($value, $message, ValidationError::InvalidWriteable->value, $propertyPath);
        }

        return true;
    }

    public static function notEmpty(mixed $value, callable|string|null $message = null, ?string $propertyPath = null): bool
    {
        if (empty($value)) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected a non-empty value. Got: %s'),
                static::stringify($value),
            );

            throw self::createException($value, $message, ValidationError::ValueEmpty->value, $propertyPath);
        }

        return true;
    }

    public static function noContent(mixed $value, callable|string|null $message = null, ?string $propertyPath = null): bool
    {
        if (!empty($value)) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected an empty value. Got: %s'),
                static::stringify($value),
            );

            throw self::createException($value, $message, ValidationError::ValueNotEmpty->value, $propertyPath);
        }

        return true;
    }

    public static function null(mixed $value, callable|string|null $message = null, ?string $propertyPath = null): bool
    {
        if (null !== $value) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected null. Got: %s'),
                static::stringify($value),
            );

            throw self::createException($value, $message, ValidationError::ValueNotNull->value, $propertyPath);
        }

        return true;
    }

    public static function notNull(mixed $value, callable|string|null $message = null, ?string $propertyPath = null): bool
    {
        if (null === $value) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected a value other than null.'),
                static::stringify($value),
            );

            throw self::createException($value, $message, ValidationError::ValueNull->value, $propertyPath);
        }

        return true;
    }

    public static function notBlank(mixed $value, callable|string|null $message = null, ?string $propertyPath = null): bool
    {
        if (false === $value || (empty($value) && '0' !== $value) || (is_string($value) && '' === mb_trim($value))) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected a non-blank value. Got: %s'),
                static::stringify($value),
            );

            throw self::createException($value, $message, ValidationError::InvalidNotBlank->value, $propertyPath);
        }

        return true;
    }

    public static function lessThan(mixed $value, mixed $limit, callable|string|null $message = null, ?string $propertyPath = null): bool
    {
        if ($value >= $limit) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected a value less than %2$s. Got: %s'),
                static::stringify($value),
                static::stringify($limit),
            );

            throw self::createException($value, $message, ValidationError::InvalidLess->value, $propertyPath, ['limit' => $limit]);
        }

        return true;
    }

    public static function lessOrEqualThan(mixed $value, mixed $limit, callable|string|null $message = null, ?string $propertyPath = null): bool
    {
        if ($value > $limit) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected a value less than or equal to %2$s. Got: %s'),
                static::stringify($value),
                static::stringify($limit),
            );

            throw self::createException($value, $message, ValidationError::InvalidLessOrEqual->value, $propertyPath, ['limit' => $limit]);
        }

        return true;
    }

    public static function greaterThan(mixed $value, mixed $limit, callable|string|null $message = null, ?string $propertyPath = null): bool
    {
        if ($value <= $limit) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected a value greater than %2$s. Got: %s'),
                static::stringify($value),
                static::stringify($limit),
            );

            throw self::createException($value, $message, ValidationError::InvalidGreater->value, $propertyPath, ['limit' => $limit]);
        }

        return true;
    }

    public static function greaterOrEqualThan(mixed $value, mixed $limit, callable|string|null $message = null, ?string $propertyPath = null): bool
    {
        if ($value < $limit) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected a value greater than or equal to %2$s. Got: %s'),
                static::stringify($value),
                static::stringify($limit),
            );

            throw self::createException($value, $message, ValidationError::InvalidGreaterOrEqual->value, $propertyPath, ['limit' => $limit]);
        }

        return true;
    }

    public static function between(mixed $value, mixed $lowerLimit, mixed $upperLimit, callable|string|null $message = null, ?string $propertyPath = null): bool
    {
        if ($lowerLimit > $value || $value > $upperLimit) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected a value between %2$s and %3$s (inclusive). Got: %s'),
                static::stringify($value),
                static::stringify($lowerLimit),
                static::stringify($upperLimit),
            );

            throw self::createException($value, $message, ValidationError::InvalidBetween->value, $propertyPath, ['lower' => $lowerLimit, 'upper' => $upperLimit]);
        }

        return true;
    }

    public static function betweenExclusive(mixed $value, mixed $lowerLimit, mixed $upperLimit, callable|string|null $message = null, ?string $propertyPath = null): bool
    {
        if ($lowerLimit >= $value || $value >= $upperLimit) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected a value between %2$s and %3$s (exclusive). Got: %s'),
                static::stringify($value),
                static::stringify($lowerLimit),
                static::stringify($upperLimit),
            );

            throw self::createException($value, $message, ValidationError::InvalidBetweenExclusive->value, $propertyPath, ['lower' => $lowerLimit, 'upper' => $upperLimit]);
        }

        return true;
    }

    public static function range(mixed $value, mixed $minValue, mixed $maxValue, callable|string|null $message = null, ?string $propertyPath = null): bool
    {
        self::numeric($value, $message, $propertyPath);

        if ($value < $minValue || $value > $maxValue) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected a number between %2$s and %3$s. Got: %s'),
                static::stringify($value),
                static::stringify($minValue),
                static::stringify($maxValue),
            );

            throw self::createException($value, $message, ValidationError::InvalidRange->value, $propertyPath, ['min' => $minValue, 'max' => $maxValue]);
        }

        return true;
    }

    public static function min(mixed $value, mixed $minValue, callable|string|null $message = null, ?string $propertyPath = null): bool
    {
        self::numeric($value, $message, $propertyPath);

        if ($value < $minValue) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected a number at least %2$s. Got: %s'),
                static::stringify($value),
                static::stringify($minValue),
            );

            throw self::createException($value, $message, ValidationError::InvalidMin->value, $propertyPath, ['min' => $minValue]);
        }

        return true;
    }

    public static function max(mixed $value, mixed $maxValue, callable|string|null $message = null, ?string $propertyPath = null): bool
    {
        self::numeric($value, $message, $propertyPath);

        if ($value > $maxValue) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected a number at most %2$s. Got: %s'),
                static::stringify($value),
                static::stringify($maxValue),
            );

            throw self::createException($value, $message, ValidationError::InvalidMax->value, $propertyPath, ['max' => $maxValue]);
        }

        return true;
    }

    public static function positiveInteger(mixed $value, callable|string|null $message = null, ?string $propertyPath = null): bool
    {
        if (!(is_int($value) && $value > 0)) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected a positive integer. Got: %s'),
                static::stringify($value),
            );

            throw self::createException($value, $message, ValidationError::InvalidPositiveInteger->value, $propertyPath);
        }

        return true;
    }

    public static function natural(mixed $value, callable|string|null $message = null, ?string $propertyPath = null): bool
    {
        if (!is_int($value) || $value < 0) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected a non-negative integer. Got: %s'),
                static::stringify($value),
            );

            throw self::createException($value, $message, ValidationError::InvalidNatural->value, $propertyPath);
        }

        return true;
    }

    public static function classExists(mixed $value, callable|string|null $message = null, ?string $propertyPath = null): bool
    {
        if (is_object($value) || is_array($value) || is_resource($value)) {
            $className = '';
        } else {
            /** @var null|bool|float|int|string $value */
            $className = is_string($value) ? $value : (string) (is_bool($value) || null === $value ? '' : $value);
        }

        if (!class_exists($className)) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected an existing class. Got: %s'),
                static::stringify($value),
            );

            throw self::createException($value, $message, ValidationError::InvalidClass->value, $propertyPath);
        }

        return true;
    }

    public static function interfaceExists(mixed $value, callable|string|null $message = null, ?string $propertyPath = null): bool
    {
        if (is_object($value) || is_array($value) || is_resource($value)) {
            $interfaceName = '';
        } else {
            /** @var null|bool|float|int|string $value */
            $interfaceName = is_string($value) ? $value : (string) (is_bool($value) || null === $value ? '' : $value);
        }

        if (!interface_exists($interfaceName)) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected an existing interface. Got: %s'),
                static::stringify($value),
            );

            throw self::createException($value, $message, ValidationError::InvalidInterface->value, $propertyPath);
        }

        return true;
    }

    public static function isInstanceOf(mixed $value, mixed $className, callable|string|null $message = null, ?string $propertyPath = null): bool
    {
        if (!$value instanceof $className) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected an instance of %2$s. Got: %s'),
                static::stringify($value),
                $className,
            );

            throw self::createException($value, $message, ValidationError::InvalidInstanceOf->value, $propertyPath, ['class' => $className]);
        }

        return true;
    }

    public static function notIsInstanceOf(mixed $value, mixed $className, callable|string|null $message = null, ?string $propertyPath = null): bool
    {
        if ($value instanceof $className) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected not an instance of %2$s. Got: %s'),
                static::stringify($value),
                $className,
            );

            throw self::createException($value, $message, ValidationError::InvalidNotInstanceOf->value, $propertyPath, ['class' => $className]);
        }

        return true;
    }

    public static function subclassOf(mixed $value, mixed $className, callable|string|null $message = null, ?string $propertyPath = null): bool
    {
        /** @var class-string|object $value */
        if (!is_subclass_of($value, is_object($className) ? $className::class : $className)) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected a subclass of %2$s. Got: %s'),
                static::stringify($value),
                $className,
            );

            throw self::createException($value, $message, ValidationError::InvalidSubclassOf->value, $propertyPath, ['class' => $className]);
        }

        return true;
    }

    public static function implementsInterface(mixed $class, mixed $interfaceName, callable|string|null $message = null, ?string $propertyPath = null): bool
    {
        try {
            /** @var class-string|object $class */
            $reflection = new ReflectionClass($class);

            /** @var class-string|ReflectionClass<object> $interfaceName */
            if (!$reflection->implementsInterface($interfaceName)) {
                $message = sprintf(
                    self::generateMessage($message ?: 'Expected a class implementing %2$s. Got: %s'),
                    static::stringify($class),
                    static::stringify($interfaceName),
                );

                throw self::createException($class, $message, ValidationError::InterfaceNotImplemented->value, $propertyPath, ['interface' => $interfaceName]);
            }
        } catch (ReflectionException) {
            $message = sprintf(
                self::generateMessage($message ?: 'Class failed reflection. Got: %s'),
                static::stringify($class),
            );

            throw self::createException($class, $message, ValidationError::InterfaceNotImplemented->value, $propertyPath, ['interface' => $interfaceName]);
        }

        return true;
    }

    public static function methodExists(mixed $value, mixed $object, callable|string|null $message = null, ?string $propertyPath = null): bool
    {
        self::isObject($object, $message, $propertyPath);

        if (!method_exists($object, $value)) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected property to exist. Got: %s'),
                static::stringify($value),
            );

            throw self::createException($value, $message, ValidationError::InvalidMethod->value, $propertyPath, ['object' => $object::class]);
        }

        return true;
    }

    public static function objectOrClass(mixed $value, callable|string|null $message = null, ?string $propertyPath = null): bool
    {
        if (!is_object($value)) {
            self::classExists($value, $message, $propertyPath);
        }

        return true;
    }

    public static function propertyExists(mixed $value, mixed $property, callable|string|null $message = null, ?string $propertyPath = null): bool
    {
        self::objectOrClass($value);

        /** @var class-string|object $value */
        if (!property_exists($value, $property)) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected a class with property %2$s. Got: %s'),
                static::stringify($value),
                static::stringify($property),
            );

            throw self::createException($value, $message, ValidationError::InvalidProperty->value, $propertyPath, ['property' => $property]);
        }

        return true;
    }

        /**
     * @param array<string> $properties
     */
    public static function propertiesExist(mixed $value, array $properties, callable|string|null $message = null, ?string $propertyPath = null): bool
    {
        self::objectOrClass($value);
        self::allString($properties, $message, $propertyPath);

        /** @var class-string|object $value */
        $invalidProperties = [];

        /** @var string $property */
        foreach ($properties as $property) {
            if (!property_exists($value, $property)) {
                $invalidProperties[] = $property;
            }
        }

        if ($invalidProperties !== []) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected a class with properties %2$s. Got: %s'),
                static::stringify($value),
                static::stringify(implode(', ', $invalidProperties)),
            );

            throw self::createException($value, $message, ValidationError::InvalidProperty->value, $propertyPath, ['properties' => $properties]);
        }

        return true;
    }

    public static function propertyNotExists(mixed $value, mixed $property, callable|string|null $message = null, ?string $propertyPath = null): bool
    {
        self::objectOrClass($value);

        /** @var class-string|object $value */
        if (property_exists($value, $property)) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected property %2$s to not exist. Got: %s'),
                static::stringify($value),
                static::stringify($property),
            );

            throw self::createException($value, $message, ValidationError::InvalidPropertyNotExists->value, $propertyPath, ['property' => $property]);
        }

        return true;
    }

    public static function methodNotExists(mixed $value, mixed $method, callable|string|null $message = null, ?string $propertyPath = null): bool
    {
        if ((is_string($value) || is_object($value)) && method_exists($value, $method)) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected method %2$s to not exist. Got: %s'),
                static::stringify($value),
                static::stringify($method),
            );

            throw self::createException($value, $message, ValidationError::InvalidMethodNotExists->value, $propertyPath, ['method' => $method]);
        }

        return true;
    }

    public static function regex(mixed $value, mixed $pattern, callable|string|null $message = null, ?string $propertyPath = null): bool
    {
        self::string($value, $message, $propertyPath);
        /** @var string $value */

        // @phpstan-ignore-next-line cast.string (validated via self::string())
        if (in_array(preg_match($pattern, (string) $value), [0, false], true)) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected a value matching regex. Got: %s'),
                static::stringify($value),
            );

            throw self::createException($value, $message, ValidationError::InvalidRegex->value, $propertyPath, ['pattern' => $pattern]);
        }

        return true;
    }

    public static function notRegex(mixed $value, mixed $pattern, callable|string|null $message = null, ?string $propertyPath = null): bool
    {
        self::string($value, $message, $propertyPath);
        /** @var string $value */

        if (preg_match($pattern, (string) $value)) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected a value not matching regex. Got: %s'),
                static::stringify($value),
            );

            throw self::createException($value, $message, ValidationError::InvalidNotRegex->value, $propertyPath, ['pattern' => $pattern]);
        }

        return true;
    }

    public static function length(mixed $value, mixed $length, callable|string|null $message = null, ?string $propertyPath = null, string $encoding = 'utf8'): bool
    {
        self::string($value, $message, $propertyPath);
        /** @var string $value */

        if (mb_strlen((string) $value, $encoding) !== $length) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected string to be exactly %2$d characters long, but got %3$d characters. Got: %s'),
                static::stringify($value),
                $length,
                mb_strlen((string) $value, $encoding),
            );

            throw self::createException($value, $message, ValidationError::InvalidLength->value, $propertyPath, ['length' => $length, 'encoding' => $encoding]);
        }

        return true;
    }

    public static function minLength(mixed $value, mixed $minLength, callable|string|null $message = null, ?string $propertyPath = null, string $encoding = 'utf8'): bool
    {
        self::string($value, $message, $propertyPath);
        /** @var string $value */

        if (mb_strlen((string) $value, $encoding) < $minLength) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected string to be at least %2$d characters long, but got %3$d characters. Got: %s'),
                static::stringify($value),
                $minLength,
                mb_strlen((string) $value, $encoding),
            );

            throw self::createException($value, $message, ValidationError::InvalidMinLength->value, $propertyPath, ['min_length' => $minLength, 'encoding' => $encoding]);
        }

        return true;
    }

    public static function maxLength(mixed $value, mixed $maxLength, callable|string|null $message = null, ?string $propertyPath = null, string $encoding = 'utf8'): bool
    {
        self::string($value, $message, $propertyPath);
        /** @var string $value */

        if (mb_strlen((string) $value, $encoding) > $maxLength) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected string to be at most %2$d characters long, but got %3$d characters. Got: %s'),
                static::stringify($value),
                $maxLength,
                mb_strlen((string) $value, $encoding),
            );

            throw self::createException($value, $message, ValidationError::InvalidMaxLength->value, $propertyPath, ['max_length' => $maxLength, 'encoding' => $encoding]);
        }

        return true;
    }

    public static function betweenLength(mixed $value, mixed $minLength, mixed $maxLength, callable|string|null $message = null, ?string $propertyPath = null, string $encoding = 'utf8'): bool
    {
        self::string($value, $message, $propertyPath);
        /** @var string $value */
        self::minLength($value, $minLength, $message, $propertyPath, $encoding);
        self::maxLength($value, $maxLength, $message, $propertyPath, $encoding);

        return true;
    }

    public static function startsWith(mixed $string, mixed $needle, callable|string|null $message = null, ?string $propertyPath = null, string $encoding = 'utf8'): bool
    {
        self::string($string, $message, $propertyPath);

        /** @var string $string */
        /** @var string $needle */
        if (0 !== mb_strpos((string) $string, (string) $needle, 0, $encoding)) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected string to start with %2$s. Got: %s'),
                static::stringify($string),
                static::stringify($needle),
            );

            throw self::createException($string, $message, ValidationError::InvalidStringStart->value, $propertyPath, ['needle' => $needle, 'encoding' => $encoding]);
        }

        return true;
    }

    public static function endsWith(mixed $string, mixed $needle, callable|string|null $message = null, ?string $propertyPath = null, string $encoding = 'utf8'): bool
    {
        self::string($string, $message, $propertyPath);

        /** @var string $string */
        /** @var string $needle */
        $stringPosition = mb_strlen((string) $string, $encoding) - mb_strlen((string) $needle, $encoding);

        if (mb_strripos((string) $string, (string) $needle, 0, $encoding) !== $stringPosition) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected string to end with %2$s. Got: %s'),
                static::stringify($string),
                static::stringify($needle),
            );

            throw self::createException($string, $message, ValidationError::InvalidStringEnd->value, $propertyPath, ['needle' => $needle, 'encoding' => $encoding]);
        }

        return true;
    }

    public static function contains(mixed $string, mixed $needle, callable|string|null $message = null, ?string $propertyPath = null, string $encoding = 'utf8'): bool
    {
        self::string($string, $message, $propertyPath);

        /** @var string $string */
        /** @var string $needle */
        if (false === mb_strpos((string) $string, (string) $needle, 0, $encoding)) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected string to contain %2$s. Got: %s'),
                static::stringify($string),
                static::stringify($needle),
            );

            throw self::createException($string, $message, ValidationError::InvalidStringContains->value, $propertyPath, ['needle' => $needle, 'encoding' => $encoding]);
        }

        return true;
    }

    public static function notContains(mixed $string, mixed $needle, callable|string|null $message = null, ?string $propertyPath = null, string $encoding = 'utf8'): bool
    {
        self::string($string, $message, $propertyPath);

        /** @var string $string */
        /** @var string $needle */
        if (false !== mb_strpos((string) $string, (string) $needle, 0, $encoding)) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected string to not contain %2$s. Got: %s'),
                static::stringify($string),
                static::stringify($needle),
            );

            throw self::createException($string, $message, ValidationError::InvalidStringNotContains->value, $propertyPath, ['needle' => $needle, 'encoding' => $encoding]);
        }

        return true;
    }

    public static function alnum(mixed $value, callable|string|null $message = null, ?string $propertyPath = null): bool
    {
        try {
            self::regex($value, '(^([a-zA-Z]{1}[a-zA-Z0-9]*)$)', $message, $propertyPath);
        } catch (Throwable) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected an alphanumeric value. Got: %s'),
                static::stringify($value),
            );

            throw self::createException($value, $message, ValidationError::InvalidAlnum->value, $propertyPath);
        }

        return true;
    }

    public static function stringNotEmpty(mixed $value, callable|string|null $message = null, ?string $propertyPath = null): bool
    {
        self::string($value, $message, $propertyPath);
        /** @var string $value */
        self::notEq($value, '', $message, $propertyPath);

        return true;
    }

    public static function startsWithLetter(mixed $value, callable|string|null $message = null, ?string $propertyPath = null): bool
    {
        self::string($value, $message, $propertyPath);
        /** @var string $value */

        $valid = $value !== '';

        if ($valid) {
            $locale = setlocale(LC_CTYPE, 0);
            setlocale(LC_CTYPE, 'C');
            $valid = ctype_alpha((string) $value[0]);
            setlocale(LC_CTYPE, $locale);
        }

        if (!$valid) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected a value to start with a letter. Got: %s'),
                static::stringify($value),
            );

            throw self::createException($value, $message, ValidationError::InvalidStringStart->value, $propertyPath);
        }

        return true;
    }

    public static function unicodeLetters(mixed $value, callable|string|null $message = null, ?string $propertyPath = null): bool
    {
        self::string($value, $message, $propertyPath);
        /** @var string $value */

        if (in_array(preg_match('/^\p{L}+$/u', (string) $value), [0, false], true)) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected a value to contain only Unicode letters. Got: %s'),
                static::stringify($value),
            );

            throw self::createException($value, $message, ValidationError::InvalidRegex->value, $propertyPath);
        }

        return true;
    }

    public static function alpha(mixed $value, callable|string|null $message = null, ?string $propertyPath = null): bool
    {
        self::string($value, $message, $propertyPath);
        /** @var string $value */

        $locale = setlocale(LC_CTYPE, 0);
        setlocale(LC_CTYPE, 'C');
        $valid = !ctype_alpha((string) $value);
        setlocale(LC_CTYPE, $locale);

        if ($valid) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected a value to contain only letters. Got: %s'),
                static::stringify($value),
            );

            throw self::createException($value, $message, ValidationError::InvalidRegex->value, $propertyPath);
        }

        return true;
    }

    public static function digits(mixed $value, callable|string|null $message = null, ?string $propertyPath = null): bool
    {
        $locale = setlocale(LC_CTYPE, 0);
        setlocale(LC_CTYPE, 'C');
        $valid = !ctype_digit((string) $value);
        setlocale(LC_CTYPE, $locale);

        if ($valid) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected a value to contain digits only. Got: %s'),
                static::stringify($value),
            );

            throw self::createException($value, $message, ValidationError::InvalidRegex->value, $propertyPath);
        }

        return true;
    }

    public static function lower(mixed $value, callable|string|null $message = null, ?string $propertyPath = null): bool
    {
        $locale = setlocale(LC_CTYPE, 0);
        setlocale(LC_CTYPE, 'C');
        $valid = !ctype_lower((string) $value);
        setlocale(LC_CTYPE, $locale);

        if ($valid) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected a value to contain lowercase characters only. Got: %s'),
                static::stringify($value),
            );

            throw self::createException($value, $message, ValidationError::InvalidRegex->value, $propertyPath);
        }

        return true;
    }

    public static function upper(mixed $value, callable|string|null $message = null, ?string $propertyPath = null): bool
    {
        $locale = setlocale(LC_CTYPE, 0);
        setlocale(LC_CTYPE, 'C');
        $valid = !ctype_upper((string) $value);
        setlocale(LC_CTYPE, $locale);

        if ($valid) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected a value to contain uppercase characters only. Got: %s'),
                static::stringify($value),
            );

            throw self::createException($value, $message, ValidationError::InvalidRegex->value, $propertyPath);
        }

        return true;
    }

    public static function lengthBetween(mixed $value, mixed $min, mixed $max, callable|string|null $message = null, ?string $propertyPath = null, string $encoding = 'utf8'): bool
    {
        $length = mb_strlen((string) $value, $encoding);

        if ($length < $min || $length > $max) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected a value to contain between %2$s and %3$s characters. Got: %s'),
                static::stringify($value),
                $min,
                $max,
            );

            throw self::createException($value, $message, ValidationError::InvalidLength->value, $propertyPath, ['min' => $min, 'max' => $max, 'encoding' => $encoding]);
        }

        return true;
    }

    public static function notWhitespaceOnly(mixed $value, callable|string|null $message = null, ?string $propertyPath = null): bool
    {
        if (preg_match('/^\s*$/', (string) $value)) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected a non-whitespace string. Got: %s'),
                static::stringify($value),
            );

            throw self::createException($value, $message, ValidationError::InvalidRegex->value, $propertyPath);
        }

        return true;
    }

    public static function integer(mixed $value, callable|string|null $message = null, ?string $propertyPath = null): bool
    {
        if (!is_int($value)) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected an integer. Got: %s'),
                static::stringify($value),
            );

            throw self::createException($value, $message, ValidationError::InvalidInteger->value, $propertyPath);
        }

        return true;
    }

    public static function float(mixed $value, callable|string|null $message = null, ?string $propertyPath = null): bool
    {
        if (!is_float($value)) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected a float. Got: %s'),
                static::stringify($value),
            );

            throw self::createException($value, $message, ValidationError::InvalidFloat->value, $propertyPath);
        }

        return true;
    }

    public static function digit(mixed $value, callable|string|null $message = null, ?string $propertyPath = null): bool
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

            throw self::createException($value, $message, ValidationError::InvalidDigit->value, $propertyPath);
        }

        return true;
    }

    public static function integerish(mixed $value, callable|string|null $message = null, ?string $propertyPath = null): bool
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

            throw self::createException($value, $message, ValidationError::InvalidIntegerish->value, $propertyPath);
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

            throw self::createException($value, $message, ValidationError::InvalidIntegerish->value, $propertyPath);
        }

        return true;
    }

    public static function boolean(mixed $value, callable|string|null $message = null, ?string $propertyPath = null): bool
    {
        if (!is_bool($value)) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected a boolean. Got: %s'),
                static::stringify($value),
            );

            throw self::createException($value, $message, ValidationError::InvalidBoolean->value, $propertyPath);
        }

        return true;
    }

    public static function scalar(mixed $value, callable|string|null $message = null, ?string $propertyPath = null): bool
    {
        if (!is_scalar($value)) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected a scalar. Got: %s'),
                static::stringify($value),
            );

            throw self::createException($value, $message, ValidationError::InvalidScalar->value, $propertyPath);
        }

        return true;
    }

    public static function string(mixed $value, callable|string|null $message = null, ?string $propertyPath = null): bool
    {
        if (!is_string($value)) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected a string. Got: %s'),
                static::stringify($value),
            );

            throw self::createException($value, $message, ValidationError::InvalidString->value, $propertyPath);
        }

        return true;
    }

    public static function numeric(mixed $value, callable|string|null $message = null, ?string $propertyPath = null): bool
    {
        if (!is_numeric($value)) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected a numeric. Got: %s'),
                static::stringify($value),
            );

            throw self::createException($value, $message, ValidationError::InvalidNumeric->value, $propertyPath);
        }

        return true;
    }

    public static function isResource(mixed $value, callable|string|null $message = null, ?string $propertyPath = null): bool
    {
        if (!is_resource($value)) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected a resource. Got: %s'),
                static::stringify($value),
            );

            throw self::createException($value, $message, ValidationError::InvalidResource->value, $propertyPath);
        }

        return true;
    }

    public static function isArray(mixed $value, callable|string|null $message = null, ?string $propertyPath = null): bool
    {
        if (!is_array($value)) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected an array. Got: %s'),
                static::stringify($value),
            );

            throw self::createException($value, $message, ValidationError::InvalidArray->value, $propertyPath);
        }

        return true;
    }

    public static function isObject(mixed $value, callable|string|null $message = null, ?string $propertyPath = null): bool
    {
        if (!is_object($value)) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected an object. Got: %s'),
                static::stringify($value),
            );

            throw self::createException($value, $message, ValidationError::InvalidObject->value, $propertyPath);
        }

        return true;
    }

    public static function isCallable(mixed $value, callable|string|null $message = null, ?string $propertyPath = null): bool
    {
        if (!is_callable($value)) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected a callable. Got: %s'),
                static::stringify($value),
            );

            throw self::createException($value, $message, ValidationError::InvalidCallable->value, $propertyPath);
        }

        return true;
    }

    public static function isIterable(mixed $value, callable|string|null $message = null, ?string $propertyPath = null): bool
    {
        if (!is_array($value) && !($value instanceof Traversable)) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected an iterable. Got: %s'),
                static::stringify($value),
            );

            throw self::createException($value, $message, ValidationError::InvalidIterable->value, $propertyPath);
        }

        return true;
    }

    public static function isInstanceOfAny(mixed $value, array $classes, callable|string|null $message = null, ?string $propertyPath = null): bool
    {
        foreach ($classes as $class) {
            if ($value instanceof $class) {
                return true;
            }
        }

        $message = sprintf(
            self::generateMessage($message ?: 'Expected an instance of any of %2$s. Got: %s'),
            static::stringify($value),
            implode(', ', array_map(static::stringify(...), $classes)),
        );

        throw self::createException($value, $message, ValidationError::InvalidInstanceOfAny->value, $propertyPath);
    }

    public static function isAnyOf(mixed $value, array $classes, callable|string|null $message = null, ?string $propertyPath = null): bool
    {
        foreach ($classes as $class) {
            self::string($class, 'Expected class as a string. Got: %s');

            if (is_a($value, $class, is_string($value))) {
                return true;
            }
        }

        $message = sprintf(
            self::generateMessage($message ?: 'Expected an instance of any of this classes or any of those classes among their parents "%2$s". Got: %s'),
            static::stringify($value),
            implode(', ', $classes),
        );

        throw self::createException($value, $message, ValidationError::InvalidAnyOf->value, $propertyPath);
    }

    public static function isNotA(mixed $value, string $class, callable|string|null $message = null, ?string $propertyPath = null): bool
    {
        self::string($class, 'Expected class as a string. Got: %s');

        if (is_a($value, $class, is_string($value))) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected an instance of this class or to this class among its parents other than "%2$s". Got: %s'),
                static::stringify($value),
                $class,
            );

            throw self::createException($value, $message, ValidationError::InvalidNotA->value, $propertyPath);
        }

        return true;
    }

    public static function email(mixed $value, callable|string|null $message = null, ?string $propertyPath = null): bool
    {
        self::string($value, $message, $propertyPath);
        /** @var string $value */

        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected a valid email address. Got: %s'),
                static::stringify($value),
            );

            throw self::createException($value, $message, ValidationError::InvalidEmail->value, $propertyPath);
        }

        return true;
    }

    public static function url(mixed $value, callable|string|null $message = null, ?string $propertyPath = null): bool
    {
        self::string($value, $message, $propertyPath);
        /** @var string $value */

        $protocols = ['http', 'https'];

        $pattern = '~^
            (%s)://                                                             # protocol
            (([\.\pL\pN-]+:)?([\.\pL\pN-]+)@)?                                  # basic auth
            (
                ([\pL\pN\pS\-\.])+(\.?([\pL\pN]|xn\-\-[\pL\pN-]+)+\.?)          # a domain name
                |                                                               # or
                \d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}                              # an IP address
                |                                                               # or
                \[
                    (?:(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){6})(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:::(?:(?:(?:[0-9a-f]{1,4})):){5})(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:[0-9a-f]{1,4})))?::(?:(?:(?:[0-9a-f]{1,4})):){4})(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,1}(?:(?:[0-9a-f]{1,4})))?::(?:(?:(?:[0-9a-f]{1,4})):){3})(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,2}(?:(?:[0-9a-f]{1,4})))?::(?:(?:(?:[0-9a-f]{1,4})):){2})(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,3}(?:(?:[0-9a-f]{1,4})))?::(?:(?:[0-9a-f]{1,4})):)(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,4}(?:(?:[0-9a-f]{1,4})))?::)(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,5}(?:(?:[0-9a-f]{1,4})))?::)(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,6}(?:(?:[0-9a-f]{1,4})))?::))))
                \]                                                              # an IPv6 address
            )
            (:[0-9]+)?                                                          # a port (optional)
            (?:/ (?:[\pL\pN\-._\~!$&\'()*+,;=:@]|%%[0-9A-Fa-f]{2})* )*          # a path
            (?:\? (?:[\pL\pN\-._\~!$&\'\[\]()*+,;=:@/?]|%%[0-9A-Fa-f]{2})* )?   # a query (optional)
            (?:\# (?:[\pL\pN\-._\~!$&\'()*+,;=:@/?]|%%[0-9A-Fa-f]{2})* )?       # a fragment (optional)
        $~ixu';

        $pattern = sprintf($pattern, implode('|', $protocols));

        if (in_array(preg_match($pattern, (string) $value), [0, false], true)) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected a valid URL. Got: %s'),
                static::stringify($value),
            );

            throw self::createException($value, $message, ValidationError::InvalidUrl->value, $propertyPath);
        }

        return true;
    }

    public static function uuid(mixed $value, callable|string|null $message = null, ?string $propertyPath = null): bool
    {
        $value = str_replace(['urn:', 'uuid:', '{', '}'], '', $value);

        if ('00000000-0000-0000-0000-000000000000' === $value) {
            return true;
        }

        if (in_array(preg_match('/^[0-9A-Fa-f]{8}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{12}$/', $value), [0, false], true)) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected a valid UUID. Got: %s'),
                static::stringify($value),
            );

            throw self::createException($value, $message, ValidationError::InvalidUuid->value, $propertyPath);
        }

        return true;
    }

    public static function ip(mixed $value, mixed $flag = null, callable|string|null $message = null, ?string $propertyPath = null): bool
    {
        self::string($value, $message, $propertyPath);
        /** @var string $value */

        if ($flag === null) {
            $filterVarResult = filter_var($value, FILTER_VALIDATE_IP);
        } else {
            $filterVarResult = filter_var($value, FILTER_VALIDATE_IP, $flag);
        }

        if (!$filterVarResult) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected a valid IP address. Got: %s'),
                static::stringify($value),
            );

            throw self::createException($value, $message, ValidationError::InvalidIp->value, $propertyPath, ['flag' => $flag]);
        }

        return true;
    }

    public static function ipv4(mixed $value, mixed $flag = null, callable|string|null $message = null, ?string $propertyPath = null): bool
    {
        self::ip($value, $flag | FILTER_FLAG_IPV4, self::generateMessage($message ?: 'Expected a valid IPv4 address. Got: %s'), $propertyPath);

        return true;
    }

    public static function ipv6(mixed $value, mixed $flag = null, callable|string|null $message = null, ?string $propertyPath = null): bool
    {
        self::ip($value, $flag | FILTER_FLAG_IPV6, self::generateMessage($message ?: 'Expected a valid IPv6 address. Got: %s'), $propertyPath);

        return true;
    }

    public static function e164(mixed $value, callable|string|null $message = null, ?string $propertyPath = null): bool
    {
        if (in_array(preg_match('/^\+?[1-9]\d{1,14}$/', (string) $value), [0, false], true)) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected a valid E164. Got: %s'),
                static::stringify($value),
            );

            throw self::createException($value, $message, ValidationError::InvalidE164->value, $propertyPath);
        }

        return true;
    }

    public static function base64(mixed $value, callable|string|null $message = null, ?string $propertyPath = null): bool
    {
        if (false === base64_decode((string) $value, true)) {
            $message = sprintf(self::generateMessage($message ?: 'Expected a valid base64 string. Got: %s'), $value);

            throw self::createException($value, $message, ValidationError::InvalidBase64->value, $propertyPath);
        }

        return true;
    }

    public static function isJsonString(mixed $value, callable|string|null $message = null, ?string $propertyPath = null): bool
    {
        if (is_object($value) || is_array($value) || is_resource($value)) {
            $stringValue = '';
        } else {
            /** @var null|bool|float|int|string $value */
            $stringValue = is_string($value) ? $value : (string) (is_bool($value) || null === $value ? '' : $value);
        }

        if (null === json_decode($stringValue) && JSON_ERROR_NONE !== json_last_error()) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected a valid JSON string. Got: %s'),
                static::stringify($value),
            );

            throw self::createException($value, $message, ValidationError::InvalidJsonString->value, $propertyPath);
        }

        return true;
    }

    public static function date(mixed $value, string $format, callable|string|null $message = null, ?string $propertyPath = null): bool
    {
        self::string($value, $message, $propertyPath);
        /** @var string $value */
        self::string($format, $message, $propertyPath);

        $dateTime = DateTime::createFromFormat('!'.$format, $value);

        if (false === $dateTime || $value !== $dateTime->format($format)) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected a date matching format %2$s. Got: %s'),
                static::stringify($value),
                static::stringify($format),
            );

            throw self::createException($value, $message, ValidationError::InvalidDate->value, $propertyPath, ['format' => $format]);
        }

        return true;
    }

}
