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
