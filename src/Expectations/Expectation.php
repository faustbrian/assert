<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Assert\Expectations;

use BadMethodCallException;
use Cline\Assert\Assertions\Assertion;
use Cline\Assert\Exceptions\InvalidArgumentException;
use stdClass;
use Throwable;

use function call_user_func_array;
use function func_num_args;
use function in_array;
use function is_array;
use function is_callable;
use function is_countable;
use function is_string;
use function sprintf;
use function str_contains;
use function throw_if;
use function throw_unless;

/**
 * Pest-compatible expectation API.
 *
 * Provides fluent, Jest/Pest-style assertions using toXxx() method pattern.
 * All expectations delegate to the underlying Assertion class.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class Expectation
{
    private bool $negate = false;

    public function __construct(
        private readonly mixed $value,
    ) {}

    /**
     * Magic property accessor for modifiers.
     *
     * Supports:
     * - ->not: Negate the next expectation
     * - ->each: Apply expectation to each element in collection
     */
    public function __get(string $name): mixed
    {
        if ($name === 'not') {
            $clone = clone $this;
            $clone->negate = true;

            return $clone;
        }

        if ($name === 'each') {
            return $this->each();
        }

        throw new BadMethodCallException(sprintf("Property '%s' does not exist on Expectation", $name));
    }

    /**
     * Apply expectations to each element in a collection.
     *
     * Can be used as property (->each) or method (->each(callable))
     */
    public function each(?callable $callback = null): mixed
    {
        Assertion::isTraversable($this->value);

        // If callback provided, invoke it for each item
        if ($callback !== null) {
            foreach ($this->value as $key => $item) {
                $expectation = new self($item);
                $callback($expectation, $key);
            }

            return $this;
        }

        // Property access: just apply to all on next method call via callback
        // We return $this and let the next call handle iteration
        // This is a simpler approach - each() as property is just sugar for each(callback)
        $items = $this->value;
        $proxy = new stdClass();
        $proxy->items = $items;

        // Use anonymous class that implements __call
        return new readonly class($items)
        {
            public function __construct(
                private mixed $items,
            ) {}

            public function __call(string $name, array $args): object
            {
                foreach ($this->items as $item) {
                    $expectation = new Expectation($item);
                    call_user_func_array([$expectation, $name], $args);
                }

                return $this;
            }

            // Implement property access for chaining ->not
            public function __get(string $prop): object
            {
                if ($prop === 'not') {
                    return new readonly class($this->items)
                    {
                        public function __construct(
                            private mixed $items,
                        ) {}

                        public function __call(string $name, array $args): object
                        {
                            foreach ($this->items as $item) {
                                $expectation = new Expectation($item);
                                $expectation->not->{$name}(...$args);
                            }

                            return $this;
                        }
                    };
                }

                throw new BadMethodCallException(sprintf('Property %s does not exist', $prop));
            }
        };
    }

    /**
     * Assert that value is strictly equal to expected (===).
     */
    public function toBe(mixed $expected): self
    {
        return $this->invoke('same', [$expected]);
    }

    /**
     * Assert that value is strictly equal to expected (===).
     * Alias for toBe().
     */
    public function toEqual(mixed $expected): self
    {
        return $this->toBe($expected);
    }

    /**
     * Assert that arrays are equal, ignoring element order.
     */
    public function toEqualCanonicalizing(array $expected): self
    {
        throw_unless(is_array($this->value), InvalidArgumentException::class, 'toEqualCanonicalizing() requires an array value', 0, null, $this->value);

        $actualSorted = $this->value;
        $expectedSorted = $expected;

        sort($actualSorted);
        sort($expectedSorted);

        if ($this->negate) {
            $this->negate = false;
            throw_if($actualSorted === $expectedSorted, InvalidArgumentException::class, 'Expected arrays not to be equal (ignoring order)', 0, null, $this->value);

            return $this;
        }

        throw_unless($actualSorted === $expectedSorted, InvalidArgumentException::class, 'Expected arrays to be equal (ignoring order)', 0, null, $this->value);

        return $this;
    }

    /**
     * Assert that numeric values are equal within a delta.
     */
    public function toEqualWithDelta(float|int $expected, float $delta): self
    {
        throw_unless(is_numeric($this->value), InvalidArgumentException::class, 'toEqualWithDelta() requires a numeric value', 0, null, $this->value);

        $diff = abs($this->value - $expected);
        $withinDelta = $diff <= $delta;

        if ($this->negate) {
            $this->negate = false;
            throw_if($withinDelta, InvalidArgumentException::class, sprintf('Expected value not to equal %s within delta %s', $expected, $delta), 0, null, $this->value);

            return $this;
        }

        throw_unless($withinDelta, InvalidArgumentException::class, sprintf('Expected value to equal %s within delta %s, got difference of %s', $expected, $delta, $diff), 0, null, $this->value);

        return $this;
    }

    /**
     * Assert that value is null.
     */
    public function toBeNull(): self
    {
        return $this->invoke('null');
    }

    /**
     * Assert that value is boolean true.
     */
    public function toBeTrue(): self
    {
        return $this->invoke('true');
    }

    /**
     * Assert that value is boolean false.
     */
    public function toBeFalse(): self
    {
        return $this->invoke('false');
    }

    /**
     * Assert that value is truthy (evaluates to true in boolean context).
     */
    public function toBeTruthy(): self
    {
        if ($this->negate) {
            throw_if((bool) $this->value, InvalidArgumentException::class, 'Expected value to be falsy, but got truthy value', 0, null, $this->value);

            $this->negate = false;

            return $this;
        }

        throw_if((bool) $this->value === false, InvalidArgumentException::class, 'Expected value to be truthy, but got falsy value', 0, null, $this->value);

        return $this;
    }

    /**
     * Assert that value is falsy (evaluates to false in boolean context).
     */
    public function toBeFalsy(): self
    {
        if ($this->negate) {
            throw_if((bool) $this->value === false, InvalidArgumentException::class, 'Expected value to be truthy, but got falsy value', 0, null, $this->value);

            $this->negate = false;

            return $this;
        }

        throw_if((bool) $this->value, InvalidArgumentException::class, 'Expected value to be falsy, but got truthy value', 0, null, $this->value);

        return $this;
    }

    /**
     * Assert that value is a string.
     */
    public function toBeString(): self
    {
        return $this->invoke('string');
    }

    /**
     * Assert that value is an integer.
     */
    public function toBeInt(): self
    {
        return $this->invoke('integer');
    }

    /**
     * Assert that value is a float.
     */
    public function toBeFloat(): self
    {
        return $this->invoke('float');
    }

    /**
     * Assert that value is a boolean.
     */
    public function toBeBool(): self
    {
        return $this->invoke('boolean');
    }

    /**
     * Assert that value is an array.
     */
    public function toBeArray(): self
    {
        return $this->invoke('isArray');
    }

    /**
     * Assert that value is an object.
     */
    public function toBeObject(): self
    {
        return $this->invoke('isObject');
    }

    /**
     * Assert that value is callable.
     */
    public function toBeCallable(): self
    {
        return $this->invoke('isCallable');
    }

    /**
     * Assert that value is iterable/traversable.
     */
    public function toBeIterable(): self
    {
        return $this->invoke('isTraversable');
    }

    /**
     * Assert that value is countable.
     */
    public function toBeCountable(): self
    {
        return $this->invoke('isCountable');
    }

    /**
     * Assert that value is numeric.
     */
    public function toBeNumeric(): self
    {
        return $this->invoke('numeric');
    }

    /**
     * Assert that value contains only digits.
     */
    public function toBeDigits(): self
    {
        return $this->invoke('digit');
    }

    /**
     * Assert that value is a scalar.
     */
    public function toBeScalar(): self
    {
        return $this->invoke('scalar');
    }

    /**
     * Assert that value is a resource.
     */
    public function toBeResource(): self
    {
        return $this->invoke('isResource');
    }

    /**
     * Assert that value is empty.
     */
    public function toBeEmpty(): self
    {
        return $this->invoke('noContent');
    }

    /**
     * Assert that value is instance of given class.
     */
    public function toBeInstanceOf(string $className): self
    {
        return $this->invoke('isInstanceOf', [$className]);
    }

    /**
     * Assert that value is a valid URL.
     */
    public function toBeUrl(): self
    {
        return $this->invoke('url');
    }

    /**
     * Assert that value is a valid UUID.
     */
    public function toBeUuid(): self
    {
        return $this->invoke('uuid');
    }

    /**
     * Assert that value is a valid email address.
     */
    public function toBeEmail(): self
    {
        return $this->invoke('email');
    }

    /**
     * Assert that value is a valid JSON string.
     */
    public function toBeJson(): self
    {
        return $this->invoke('isJsonString');
    }

    /**
     * Assert that value is a file.
     */
    public function toBeFile(): self
    {
        return $this->invoke('file');
    }

    /**
     * Assert that value is a readable file.
     */
    public function toBeReadableFile(): self
    {
        Assertion::file($this->value);
        Assertion::readable($this->value);

        return $this;
    }

    /**
     * Assert that value is a writable file.
     */
    public function toBeWritableFile(): self
    {
        Assertion::file($this->value);
        Assertion::writeable($this->value);

        return $this;
    }

    /**
     * Assert that value is a readable directory.
     */
    public function toBeReadableDirectory(): self
    {
        Assertion::directory($this->value);
        Assertion::readable($this->value);

        return $this;
    }

    /**
     * Assert that value is a writable directory.
     */
    public function toBeWritableDirectory(): self
    {
        Assertion::directory($this->value);
        Assertion::writeable($this->value);

        return $this;
    }

    /**
     * Assert that value is greater than limit.
     */
    public function toBeGreaterThan(mixed $limit): self
    {
        return $this->invoke('greaterThan', [$limit]);
    }

    /**
     * Assert that value is greater than or equal to limit.
     */
    public function toBeGreaterThanOrEqual(mixed $limit): self
    {
        return $this->invoke('greaterOrEqualThan', [$limit]);
    }

    /**
     * Assert that value is less than limit.
     */
    public function toBeLessThan(mixed $limit): self
    {
        return $this->invoke('lessThan', [$limit]);
    }

    /**
     * Assert that value is less than or equal to limit.
     */
    public function toBeLessThanOrEqual(mixed $limit): self
    {
        return $this->invoke('lessOrEqualThan', [$limit]);
    }

    /**
     * Assert that value is between min and max (inclusive).
     */
    public function toBeBetween(mixed $min, mixed $max): self
    {
        return $this->invoke('between', [$min, $max]);
    }

    /**
     * Assert that value is infinite.
     */
    public function toBeInfinite(): self
    {
        if ($this->negate) {
            $this->negate = false;
            throw_if(is_infinite($this->value), InvalidArgumentException::class, 'Expected value not to be infinite', 0, null, $this->value);

            return $this;
        }

        throw_unless(is_infinite($this->value), InvalidArgumentException::class, 'Expected value to be infinite', 0, null, $this->value);

        return $this;
    }

    /**
     * Assert that value is NaN (Not a Number).
     */
    public function toBeNan(): self
    {
        if ($this->negate) {
            $this->negate = false;
            throw_if(is_nan($this->value), InvalidArgumentException::class, 'Expected value not to be NaN', 0, null, $this->value);

            return $this;
        }

        throw_unless(is_nan($this->value), InvalidArgumentException::class, 'Expected value to be NaN', 0, null, $this->value);

        return $this;
    }

    /**
     * Assert that string starts with prefix.
     */
    public function toStartWith(string $prefix): self
    {
        return $this->invoke('startsWith', [$prefix]);
    }

    /**
     * Assert that string ends with suffix.
     */
    public function toEndWith(string $suffix): self
    {
        return $this->invoke('endsWith', [$suffix]);
    }

    /**
     * Assert that string contains only alphabetic characters.
     */
    public function toBeAlpha(): self
    {
        if ($this->negate) {
            $this->negate = false;

            throw_if(ctype_alpha($this->value), InvalidArgumentException::class, 'Expected value not to be alphabetic', 0, null, $this->value);

            return $this;
        }

        throw_unless(ctype_alpha($this->value), InvalidArgumentException::class, 'Expected value to be alphabetic', 0, null, $this->value);

        return $this;
    }

    /**
     * Assert that string contains only alphanumeric characters.
     */
    public function toBeAlphaNumeric(): self
    {
        return $this->invoke('alnum');
    }

    /**
     * Assert that string is snake_case.
     */
    public function toBeSnakeCase(): self
    {
        throw_unless(is_string($this->value), InvalidArgumentException::class, 'toBeSnakeCase() requires a string value', 0, null, $this->value);

        $pattern = '/^[a-z]+(_[a-z]+)*$/';
        $isSnakeCase = preg_match($pattern, $this->value) === 1;

        if ($this->negate) {
            $this->negate = false;
            throw_if($isSnakeCase, InvalidArgumentException::class, 'Expected value not to be snake_case', 0, null, $this->value);

            return $this;
        }

        throw_unless($isSnakeCase, InvalidArgumentException::class, 'Expected value to be snake_case', 0, null, $this->value);

        return $this;
    }

    /**
     * Assert that string is kebab-case.
     */
    public function toBeKebabCase(): self
    {
        throw_unless(is_string($this->value), InvalidArgumentException::class, 'toBeKebabCase() requires a string value', 0, null, $this->value);

        $pattern = '/^[a-z]+(-[a-z]+)*$/';
        $isKebabCase = preg_match($pattern, $this->value) === 1;

        if ($this->negate) {
            $this->negate = false;
            throw_if($isKebabCase, InvalidArgumentException::class, 'Expected value not to be kebab-case', 0, null, $this->value);

            return $this;
        }

        throw_unless($isKebabCase, InvalidArgumentException::class, 'Expected value to be kebab-case', 0, null, $this->value);

        return $this;
    }

    /**
     * Assert that string is camelCase.
     */
    public function toBeCamelCase(): self
    {
        throw_unless(is_string($this->value), InvalidArgumentException::class, 'toBeCamelCase() requires a string value', 0, null, $this->value);

        $pattern = '/^[a-z]+([A-Z][a-z]+)*$/';
        $isCamelCase = preg_match($pattern, $this->value) === 1;

        if ($this->negate) {
            $this->negate = false;
            throw_if($isCamelCase, InvalidArgumentException::class, 'Expected value not to be camelCase', 0, null, $this->value);

            return $this;
        }

        throw_unless($isCamelCase, InvalidArgumentException::class, 'Expected value to be camelCase', 0, null, $this->value);

        return $this;
    }

    /**
     * Assert that string is StudlyCase.
     */
    public function toBeStudlyCase(): self
    {
        throw_unless(is_string($this->value), InvalidArgumentException::class, 'toBeStudlyCase() requires a string value', 0, null, $this->value);

        $pattern = '/^[A-Z][a-z]+([A-Z][a-z]+)*$/';
        $isStudlyCase = preg_match($pattern, $this->value) === 1;

        if ($this->negate) {
            $this->negate = false;
            throw_if($isStudlyCase, InvalidArgumentException::class, 'Expected value not to be StudlyCase', 0, null, $this->value);

            return $this;
        }

        throw_unless($isStudlyCase, InvalidArgumentException::class, 'Expected value to be StudlyCase', 0, null, $this->value);

        return $this;
    }

    /**
     * Assert that string is uppercase.
     */
    public function toBeUppercase(): self
    {
        throw_unless(is_string($this->value), InvalidArgumentException::class, 'toBeUppercase() requires a string value', 0, null, $this->value);

        $isUppercase = $this->value === strtoupper($this->value);

        if ($this->negate) {
            $this->negate = false;
            throw_if($isUppercase, InvalidArgumentException::class, 'Expected value not to be uppercase', 0, null, $this->value);

            return $this;
        }

        throw_unless($isUppercase, InvalidArgumentException::class, 'Expected value to be uppercase', 0, null, $this->value);

        return $this;
    }

    /**
     * Assert that string is lowercase.
     */
    public function toBeLowercase(): self
    {
        throw_unless(is_string($this->value), InvalidArgumentException::class, 'toBeLowercase() requires a string value', 0, null, $this->value);

        $isLowercase = $this->value === strtolower($this->value);

        if ($this->negate) {
            $this->negate = false;
            throw_if($isLowercase, InvalidArgumentException::class, 'Expected value not to be lowercase', 0, null, $this->value);

            return $this;
        }

        throw_unless($isLowercase, InvalidArgumentException::class, 'Expected value to be lowercase', 0, null, $this->value);

        return $this;
    }

    /**
     * Assert that string matches regex pattern.
     */
    public function toMatch(string $pattern): self
    {
        return $this->invoke('regex', [$pattern]);
    }

    /**
     * Assert that string has specific length or array has specific count.
     */
    public function toHaveLength(int $length): self
    {
        // For arrays/countables, use count; for strings use length
        if (is_array($this->value) || is_countable($this->value)) {
            return $this->invoke('count', [$length]);
        }

        return $this->invoke('length', [$length]);
    }

    /**
     * Assert that countable has specific count.
     */
    public function toHaveCount(int $count): self
    {
        return $this->invoke('count', [$count]);
    }

    /**
     * Assert that array has specific key.
     */
    public function toHaveKey(string|int $key): self
    {
        return $this->invoke('keyExists', [$key]);
    }

    /**
     * Assert that array/string contains value.
     */
    public function toContain(mixed $needle): self
    {
        // For strings, use contains; for arrays, check if value is in array
        if (is_string($this->value)) {
            return $this->invoke('contains', [$needle]);
        }

        if (is_array($this->value)) {
            // inArray($value, $haystack) but we need to check if haystack contains value
            // So swap: check if $needle is in $this->value array
            if ($this->negate) {
                $this->negate = false;

                throw_if(in_array($needle, $this->value, true), InvalidArgumentException::class, sprintf('Expected array not to contain %s', $needle), 0, null, $this->value);

                return $this;
            }

            throw_unless(in_array($needle, $this->value, true), InvalidArgumentException::class, sprintf('Expected array to contain %s', $needle), 0, null, $this->value);

            return $this;
        }

        throw new InvalidArgumentException(
            'toContain() requires a string or array value',
            0,
            null,
            $this->value,
        );
    }

    /**
     * Assert that array contains value with deep equality check.
     */
    public function toContainEqual(mixed $needle): self
    {
        throw_unless(is_array($this->value), InvalidArgumentException::class, 'toContainEqual() requires an array value', 0, null, $this->value);

        foreach ($this->value as $item) {
            if ($item === $needle || $item == $needle) {
                if ($this->negate) {
                    throw new InvalidArgumentException('Expected array not to contain equal value', 0, null, $this->value);
                }

                return $this;
            }
        }

        if ($this->negate) {
            $this->negate = false;

            return $this;
        }

        throw new InvalidArgumentException('Expected array to contain equal value', 0, null, $this->value);
    }

    /**
     * Assert that value is in the given array.
     */
    public function toBeIn(array $haystack): self
    {
        return $this->invoke('inArray', [$haystack]);
    }

    /**
     * Assert that array is a subset of expected array (ignoring order).
     */
    public function toMatchArray(array $expected): self
    {
        throw_unless(is_array($this->value), InvalidArgumentException::class, 'toMatchArray() requires an array value', 0, null, $this->value);

        foreach ($expected as $key => $value) {
            throw_unless(\array_key_exists($key, $this->value), InvalidArgumentException::class, sprintf('Expected array to have key %s', $key), 0, null, $this->value);
            throw_unless($this->value[$key] === $value, InvalidArgumentException::class, sprintf('Expected array key %s to equal %s', $key, $value), 0, null, $this->value);
        }

        return $this;
    }

    /**
     * Assert that object properties match expected array.
     */
    public function toMatchObject(array $expected): self
    {
        throw_unless(is_object($this->value), InvalidArgumentException::class, 'toMatchObject() requires an object value', 0, null, $this->value);

        foreach ($expected as $property => $value) {
            throw_unless(property_exists($this->value, $property), InvalidArgumentException::class, sprintf('Expected object to have property %s', $property), 0, null, $this->value);
            throw_unless($this->value->{$property} === $value, InvalidArgumentException::class, sprintf('Expected object property %s to equal %s', $property, $value), 0, null, $this->value);
        }

        return $this;
    }

    /**
     * Assert that object/class has property.
     */
    public function toHaveProperty(string $property): self
    {
        // Handle negation manually since parameter order differs
        if ($this->negate) {
            $this->negate = false;

            try {
                Assertion::propertyExists($this->value, $property);

                throw new InvalidArgumentException(
                    sprintf('Expected property %s not to exist', $property),
                    0,
                    null,
                    $this->value,
                );
            } catch (InvalidArgumentException $exception) {
                throw_if(str_contains($exception->getMessage(), 'Expected property'), $exception);

                return $this;
            }
        }

        Assertion::propertyExists($this->value, $property);

        return $this;
    }

    /**
     * Assert that object has multiple properties.
     */
    public function toHaveProperties(array $properties): self
    {
        Assertion::propertiesExist($this->value, $properties);

        return $this;
    }

    /**
     * Assert that array has multiple keys.
     */
    public function toHaveKeys(array $keys): self
    {
        foreach ($keys as $key) {
            Assertion::keyExists($this->value, $key);
        }

        return $this;
    }

    /**
     * Assert that all array keys are snake_case.
     */
    public function toHaveSnakeCaseKeys(): self
    {
        throw_unless(is_array($this->value), InvalidArgumentException::class, 'toHaveSnakeCaseKeys() requires an array value', 0, null, $this->value);

        $pattern = '/^[a-z]+(_[a-z]+)*$/';
        foreach (array_keys($this->value) as $key) {
            throw_unless(is_string($key) && preg_match($pattern, $key) === 1, InvalidArgumentException::class, sprintf('Expected all keys to be snake_case, but found: %s', $key), 0, null, $this->value);
        }

        return $this;
    }

    /**
     * Assert that all array keys are kebab-case.
     */
    public function toHaveKebabCaseKeys(): self
    {
        throw_unless(is_array($this->value), InvalidArgumentException::class, 'toHaveKebabCaseKeys() requires an array value', 0, null, $this->value);

        $pattern = '/^[a-z]+(-[a-z]+)*$/';
        foreach (array_keys($this->value) as $key) {
            throw_unless(is_string($key) && preg_match($pattern, $key) === 1, InvalidArgumentException::class, sprintf('Expected all keys to be kebab-case, but found: %s', $key), 0, null, $this->value);
        }

        return $this;
    }

    /**
     * Assert that all array keys are camelCase.
     */
    public function toHaveCamelCaseKeys(): self
    {
        throw_unless(is_array($this->value), InvalidArgumentException::class, 'toHaveCamelCaseKeys() requires an array value', 0, null, $this->value);

        $pattern = '/^[a-z]+([A-Z][a-z]+)*$/';
        foreach (array_keys($this->value) as $key) {
            throw_unless(is_string($key) && preg_match($pattern, $key) === 1, InvalidArgumentException::class, sprintf('Expected all keys to be camelCase, but found: %s', $key), 0, null, $this->value);
        }

        return $this;
    }

    /**
     * Assert that all array keys are StudlyCase.
     */
    public function toHaveStudlyCaseKeys(): self
    {
        throw_unless(is_array($this->value), InvalidArgumentException::class, 'toHaveStudlyCaseKeys() requires an array value', 0, null, $this->value);

        $pattern = '/^[A-Z][a-z]+([A-Z][a-z]+)*$/';
        foreach (array_keys($this->value) as $key) {
            throw_unless(is_string($key) && preg_match($pattern, $key) === 1, InvalidArgumentException::class, sprintf('Expected all keys to be StudlyCase, but found: %s', $key), 0, null, $this->value);
        }

        return $this;
    }

    /**
     * Assert that two countables have the same size.
     */
    public function toHaveSameSize(array|Countable $expected): self
    {
        throw_unless(is_countable($this->value), InvalidArgumentException::class, 'toHaveSameSize() requires a countable value', 0, null, $this->value);
        throw_unless(is_countable($expected), InvalidArgumentException::class, 'toHaveSameSize() requires a countable expected value', 0, null, $expected);

        $actualCount = count($this->value);
        $expectedCount = count($expected);

        if ($this->negate) {
            $this->negate = false;
            throw_if($actualCount === $expectedCount, InvalidArgumentException::class, sprintf('Expected counts not to be equal, but both are %d', $actualCount), 0, null, $this->value);

            return $this;
        }

        throw_unless($actualCount === $expectedCount, InvalidArgumentException::class, sprintf('Expected count %d but got %d', $expectedCount, $actualCount), 0, null, $this->value);

        return $this;
    }

    /**
     * Assert that array contains only instances of a given class.
     */
    public function toContainOnlyInstancesOf(string $className): self
    {
        throw_unless(is_array($this->value), InvalidArgumentException::class, 'toContainOnlyInstancesOf() requires an array value', 0, null, $this->value);

        foreach ($this->value as $item) {
            throw_unless($item instanceof $className, InvalidArgumentException::class, sprintf('Expected all items to be instances of %s', $className), 0, null, $this->value);
        }

        return $this;
    }

    /**
     * Assert that value matches a PHPUnit constraint.
     */
    public function toMatchConstraint(object $constraint): self
    {
        // For now, basic implementation - can be enhanced with PHPUnit constraint support
        throw_unless(method_exists($constraint, 'evaluate'), InvalidArgumentException::class, 'Constraint must have an evaluate method', 0, null, $constraint);

        try {
            $constraint->evaluate($this->value);
        } catch (\Throwable $e) {
            if ($this->negate) {
                $this->negate = false;

                return $this;
            }

            throw new InvalidArgumentException('Constraint evaluation failed: ' . $e->getMessage(), 0, $e, $this->value);
        }

        if ($this->negate) {
            $this->negate = false;

            throw new InvalidArgumentException('Expected constraint to fail but it passed', 0, null, $this->value);
        }

        return $this;
    }

    /**
     * Assert that object has method.
     */
    public function toHaveMethod(string $method): self
    {
        // Handle negation manually since parameter order differs
        if ($this->negate) {
            $this->negate = false;

            try {
                Assertion::methodExists($method, $this->value);

                throw new InvalidArgumentException(
                    sprintf('Expected method %s not to exist', $method),
                    0,
                    null,
                    $this->value,
                );
            } catch (InvalidArgumentException $exception) {
                throw_if(str_contains($exception->getMessage(), 'Expected method'), $exception);

                return $this;
            }
        }

        Assertion::methodExists($method, $this->value);

        return $this;
    }

    /**
     * Chain expectations on a different value.
     *
     * @param mixed $value New value to create expectation for
     */
    public function and(mixed $value = null): self
    {
        if (func_num_args() === 0) {
            // Continue on same value (syntactic sugar)
            return $this;
        }

        // New value - return new expectation
        return new self($value);
    }

    /**
     * Conditionally apply expectations.
     */
    public function when(bool|callable $condition, callable $callback): self
    {
        $result = is_callable($condition) ? $condition($this->value) : $condition;

        if ($result) {
            $callback($this);
        }

        return $this;
    }

    /**
     * Conditionally apply expectations (inverse of when).
     */
    public function unless(bool|callable $condition, callable $callback): self
    {
        $result = is_callable($condition) ? $condition($this->value) : $condition;

        if (!$result) {
            $callback($this);
        }

        return $this;
    }

    /**
     * Apply different expectations to each element in order (sequence).
     */
    public function sequence(callable ...$callbacks): self
    {
        Assertion::isTraversable($this->value);

        $values = is_array($this->value) ? $this->value : iterator_to_array($this->value);

        foreach ($callbacks as $index => $callback) {
            throw_unless(\array_key_exists($index, $values), InvalidArgumentException::class, sprintf('Sequence expects at least %d items but got %d', $index + 1, \count($values)), 0, null, $this->value);

            $expectation = new self($values[$index]);
            $callback($expectation);
        }

        return $this;
    }

    /**
     * Parse JSON string and return new expectation on decoded value.
     */
    public function json(): self
    {
        Assertion::isJsonString($this->value);
        $decoded = json_decode($this->value, true);

        return new self($decoded);
    }

    /**
     * Match value against multiple patterns.
     */
    public function match(array ...$patterns): self
    {
        foreach ($patterns as [$matcher, $callback]) {
            $matched = false;

            if (is_callable($matcher)) {
                $matched = $matcher($this->value);
            } else {
                $matched = $this->value === $matcher;
            }

            if ($matched) {
                $callback($this);

                return $this;
            }
        }

        throw new InvalidArgumentException('No pattern matched the value', 0, null, $this->value);
    }

    /**
     * Dump and die - output value and stop execution.
     */
    public function dd(mixed ...$args): never
    {
        dump($this->value, ...$args);
        exit(1);
    }

    /**
     * Dump and die only when condition is true.
     */
    public function ddWhen(bool|callable $condition, mixed ...$args): self
    {
        $result = is_callable($condition) ? $condition($this->value) : $condition;

        if ($result) {
            $this->dd(...$args);
        }

        return $this;
    }

    /**
     * Dump and die only when condition is false.
     */
    public function ddUnless(bool|callable $condition, mixed ...$args): self
    {
        $result = is_callable($condition) ? $condition($this->value) : $condition;

        if (!$result) {
            $this->dd(...$args);
        }

        return $this;
    }

    /**
     * Send value to Ray debugger (if available).
     */
    public function ray(?string $label = null): self
    {
        if (\function_exists('ray')) {
            if ($label !== null) {
                ray($label, $this->value);
            } else {
                ray($this->value);
            }
        }

        return $this;
    }

    /**
     * Assert that value (callable) throws an exception.
     */
    public function toThrow(?string $exceptionClass = null, ?string $message = null): self
    {
        throw_unless(is_callable($this->value), InvalidArgumentException::class, 'toThrow() requires a callable value', 0, null, $this->value);

        $thrown = false;
        $actualException = null;

        try {
            ($this->value)();
        } catch (Throwable $throwable) {
            $thrown = true;
            $actualException = $throwable;
        }

        if ($this->negate) {
            $this->negate = false;

            throw_if($thrown, InvalidArgumentException::class, sprintf(
                'Expected callable not to throw but %s was thrown',
                $actualException instanceof Throwable ? $actualException::class : 'exception',
            ), 0, null, $this->value);

            return $this;
        }

        throw_unless($thrown, InvalidArgumentException::class, 'Expected callable to throw an exception but none was thrown', 0, null, $this->value);

        throw_if($exceptionClass !== null && !$actualException instanceof $exceptionClass, InvalidArgumentException::class, sprintf(
            'Expected exception of type %s but got %s',
            $exceptionClass,
            $actualException::class,
        ), 0, null, $this->value);

        throw_if($message !== null && !str_contains($actualException->getMessage(), $message), InvalidArgumentException::class, sprintf(
            'Expected exception message to contain "%s" but got "%s"',
            $message,
            $actualException->getMessage(),
        ), 0, null, $this->value);

        return $this;
    }

    /**
     * Invoke an assertion method, handling negation.
     *
     * @param array<mixed> $args
     */
    private function invoke(string $method, array $args = []): self
    {
        /** @var callable $callable */
        $callable = [Assertion::class, $method];

        if ($this->negate) {
            $this->negate = false;

            try {
                $callable(...[$this->value, ...$args]);

                throw new InvalidArgumentException(
                    sprintf('Expected assertion %s to fail but it passed', $method),
                    0,
                    null,
                    $this->value,
                );
            } catch (InvalidArgumentException $exception) {
                throw_if(str_contains($exception->getMessage(), 'Expected assertion'), $exception);

                // Assertion failed as expected, return success
                return $this;
            }
        }

        $callable(...[$this->value, ...$args]);

        return $this;
    }
}
