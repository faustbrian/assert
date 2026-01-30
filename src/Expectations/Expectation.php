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
use Cline\Assert\Exceptions\AssertionException;
use Cline\Assert\Exceptions\InvalidArgumentException;
use Cline\Assert\Matchers\AnyMatcher;
use Cline\Assert\Matchers\AnythingMatcher;
use Cline\Assert\Matchers\ArrayContainingMatcher;
use Cline\Assert\Matchers\AsymmetricMatcher;
use Cline\Assert\Matchers\StringContainingMatcher;
use Cline\Assert\Schema\SchemaValidator;
use Cline\Assert\Snapshots\SnapshotManager;
use Countable;
use DateTimeInterface;
use stdClass;
use Throwable;

use function array_key_exists;
use function array_keys;
use function array_map;
use function call_user_func_array;
use function count;
use function ctype_alpha;
use function dump;
use function func_num_args;
use function function_exists;
use function gettype;
use function hrtime;
use function implode;
use function in_array;
use function is_array;
use function is_callable;
use function is_countable;
use function is_iterable;
use function is_scalar;
use function is_string;
use function iterator_to_array;
use function json_decode;
use function method_exists;
use function ray;
use function sprintf;
use function str_contains;
use function throw_if;
use function throw_unless;

/**
 * Provides Jest/Vitest/Pest-compatible expectation API for fluent assertions.
 *
 * This class implements a fluent assertion DSL inspired by modern JavaScript testing
 * frameworks. It supports method chaining, negation, logical operators (OR/XOR),
 * collection quantifiers (all/any/none), soft assertions, and asymmetric matchers.
 *
 * All assertions delegate to the underlying Assertion class while providing a more
 * expressive and ergonomic API surface for writing tests.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class Expectation
{
    /**
     * Collection of errors from soft assertions that don't throw immediately.
     *
     * @var array<InvalidArgumentException>
     */
    private static array $softErrors = [];

    /**
     * Whether the next assertion should be negated (inverted).
     */
    private bool $negate = false;

    /**
     * Whether assertions should be collected as soft errors instead of throwing.
     */
    private bool $soft = false;

    /**
     * Collection of OR/XOR groups for logical assertion operations.
     *
     * @var array<array{success: bool, errors: array<InvalidArgumentException>}>
     */
    private array $orGroups = [];

    /**
     * Whether OR mode is active for alternative assertion groups.
     */
    private bool $orMode = false;

    /**
     * Whether XOR mode is active for exclusive OR assertion groups.
     */
    private bool $xorMode = false;

    /**
     * Collection quantifier mode for applying assertions to collection items.
     * Values: 'all', 'any', 'none', or null when not in collection mode.
     */
    private ?string $collectionMode = null;

    /**
     * Required number of passing groups for threshold-based assertions.
     */
    private ?int $thresholdCount = null;

    /**
     * Threshold comparison mode for counting passing assertion groups.
     * Values: 'exactly', 'atLeast', 'atMost', or null when not using thresholds.
     */
    private ?string $thresholdMode = null;

    /**
     * Whether OR/XOR groups have been evaluated to prevent duplicate evaluation.
     */
    private bool $evaluated = false;

    /**
     * Create a new expectation instance for the given value.
     *
     * @param mixed $value The value to run assertions against. Pass null to access
     *                     static matcher methods via __call magic method delegation.
     */
    public function __construct(
        private readonly mixed $value,
    ) {}

    /**
     * Evaluate OR/XOR groups when expectation chain ends.
     *
     * Automatically called when the expectation object is destroyed to ensure
     * all logical operations are properly evaluated before the chain completes.
     */
    public function __destruct()
    {
        if ($this->evaluated) {
            return;
        }

        $this->evaluateOrGroups();
    }

    /**
     * Reset evaluation state when cloning to allow reuse of expectation chains.
     *
     * Deep cloning of arrays happens automatically in PHP. This method ensures
     * the cloned instance can be evaluated independently from the original.
     */
    public function __clone(): void
    {
        $this->evaluated = false;
    }

    /**
     * Forward undefined method calls to static matcher methods when value is null.
     *
     * Enables calling static matcher factory methods like expect()->any('string')
     * when expect() is called without arguments. This provides ergonomic access
     * to asymmetric matchers without verbose class instantiation.
     *
     * @param string       $name      Method name being called
     * @param array<mixed> $arguments Arguments passed to the method
     *
     * @throws BadMethodCallException When method doesn't exist on the class
     * @return mixed                  Result from the static method call
     */
    public function __call(string $name, array $arguments): mixed
    {
        if ($this->value === null && method_exists(self::class, $name)) {
            return self::$name(...$arguments); // @codeCoverageIgnore
        }

        throw new BadMethodCallException(sprintf("Method '%s' does not exist on Expectation", $name));
    }

    /**
     * Magic property accessor for assertion modifiers and operators.
     *
     * Provides ergonomic property-based access to common assertion modifiers
     * without requiring method calls. Returns cloned instances to prevent
     * state mutation of the original expectation chain.
     *
     * Supported modifiers:
     * - ->not: Negate the next expectation
     * - ->and: Continue chaining on same value (alternative to ->and())
     * - ->or: Start new OR group (alternative to ->or())
     * - ->xor: Start new XOR group (alternative to ->xor())
     * - ->all: All items in collection must match assertion
     * - ->any: At least one item in collection must match assertion
     * - ->none: No items in collection should match assertion
     * - ->each: Apply expectation to each element in collection
     * - ->soft: Enable soft assertion mode (collect errors without throwing)
     *
     * @param string $name Property name being accessed
     *
     * @throws BadMethodCallException When property doesn't exist on the class
     * @return mixed                  Cloned expectation with modifier applied, or proxy object for ->each
     */
    public function __get(string $name): mixed
    {
        if ($name === 'not') {
            $clone = clone $this;
            $clone->negate = true;

            return $clone;
        }

        if ($name === 'and') {
            return $this->and();
        }

        if ($name === 'or') {
            return $this->or();
        }

        if ($name === 'xor') {
            return $this->xor();
        }

        if ($name === 'all') {
            $clone = clone $this;
            $clone->collectionMode = 'all';

            return $clone;
        }

        if ($name === 'any') {
            $clone = clone $this;
            $clone->collectionMode = 'any';

            return $clone;
        }

        if ($name === 'none') {
            $clone = clone $this;
            $clone->collectionMode = 'none';

            return $clone;
        }

        if ($name === 'each') {
            return $this->each();
        }

        if ($name === 'soft') {
            $clone = clone $this;
            $clone->soft = true;

            return $clone;
        }

        throw new BadMethodCallException(sprintf("Property '%s' does not exist on Expectation", $name));
    }

    /**
     * Create an asymmetric matcher for any value of specific type.
     *
     * Allows partial matching in assertions where the exact value is unknown
     * but the type must match. Supports both primitive types and class names.
     *
     * @param string $type Type name ('string', 'int', 'array', etc.) or class name
     *
     * @return AnyMatcher Matcher instance for use in expectations
     */
    public static function any(string $type): AnyMatcher
    {
        return new AnyMatcher($type);
    }

    /**
     * Create an asymmetric matcher for any non-null value.
     *
     * Useful for assertions where you want to verify a value exists but don't
     * care about its specific type or content. Rejects only null values.
     *
     * @return AnythingMatcher Matcher instance for use in expectations
     */
    public static function anything(): AnythingMatcher
    {
        return new AnythingMatcher();
    }

    /**
     * Create an asymmetric matcher for strings containing a substring.
     *
     * Enables partial string matching in assertions without requiring exact
     * string equality. Performs case-sensitive substring search.
     *
     * @param string $substring The substring that must appear in the target string
     *
     * @return StringContainingMatcher Matcher instance for use in expectations
     */
    public static function stringContaining(string $substring): StringContainingMatcher
    {
        return new StringContainingMatcher($substring);
    }

    /**
     * Create an asymmetric matcher for arrays containing a subset of key-value pairs.
     *
     * Verifies that an array contains all specified keys with matching values,
     * but allows additional keys not present in the subset. Supports nested
     * asymmetric matchers for complex matching patterns.
     *
     * @param array<mixed> $subset Key-value pairs that must exist in the target array
     *
     * @return ArrayContainingMatcher Matcher instance for use in expectations
     */
    public static function arrayContaining(array $subset): ArrayContainingMatcher
    {
        return new ArrayContainingMatcher($subset);
    }

    /**
     * Assert all soft assertions passed and clear collected errors.
     *
     * Evaluates all soft assertions accumulated via ->soft modifier. If any
     * assertions failed, throws a single exception containing all error messages.
     * Always clears the error collection after evaluation.
     *
     * @throws InvalidArgumentException When one or more soft assertions failed
     */
    public static function assertSoft(): void
    {
        if (self::$softErrors === []) {
            return;
        }

        $errors = self::$softErrors;
        self::$softErrors = [];

        $messages = array_map(fn (Throwable $e): string => $e->getMessage(), $errors);

        throw new AssertionException(
            sprintf("Soft assertions failed:\n- %s", implode("\n- ", $messages)),
            0,
        );
    }

    /**
     * Clear all soft assertion errors without throwing.
     *
     * Resets the soft error collection to an empty state. Useful for test
     * cleanup or when you want to discard accumulated soft assertion failures
     * without evaluating them.
     */
    public static function clearSoftErrors(): void
    {
        self::$softErrors = [];
    }

    /**
     * Apply expectations to each element in a collection.
     *
     * When called with a callback, immediately iterates the collection and invokes
     * the callback with an expectation instance for each item. When accessed as a
     * property (->each), returns a proxy object that applies the next assertion
     * to all collection items.
     *
     * @param null|callable $callback Optional callback receiving (Expectation $item, mixed $key)
     *                                for each collection element. When null, acts as property accessor.
     *
     * @throws InvalidArgumentException When value is not traversable
     * @return object|self              Returns $this when callback provided, or proxy object for property access
     */
    public function each(?callable $callback = null): mixed
    {
        Assertion::isTraversable($this->value);

        // If callback provided, invoke it for each item
        if ($callback !== null) {
            // @phpstan-ignore-next-line foreach.nonIterable (validated by isTraversable)
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

            public function __call(string $name, array $args): self
            {
                // @phpstan-ignore-next-line foreach.nonIterable (validated by isTraversable before proxy creation)
                foreach ($this->items as $item) {
                    $expectation = new Expectation($item);
                    // @phpstan-ignore-next-line argument.type (dynamic method dispatch via __call)
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

                        public function __call(string $name, array $args): self
                        {
                            // @phpstan-ignore-next-line foreach.nonIterable (validated by isTraversable before proxy creation)
                            foreach ($this->items as $item) {
                                $expectation = new Expectation($item);
                                // @phpstan-ignore-next-line property.notFound (magic property via __get)
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
     * Alias for toBe(). Supports asymmetric matchers for partial matching.
     */
    public function toEqual(mixed $expected): self
    {
        // Handle asymmetric matchers for partial equality
        if ($this->containsAsymmetricMatchers($expected)) {
            return $this->matchAsymmetric($expected);
        }

        return $this->toBe($expected);
    }

    /**
     * Assert that value is strictly equal to expected (===) with deep comparison.
     * More explicit alternative to toBe() for complex types.
     */
    public function toStrictEqual(mixed $expected): self
    {
        return $this->invoke('strictEquals', [$expected]);
    }

    /**
     * Assert that arrays are equal, ignoring element order.
     */
    public function toEqualCanonicalizing(array $expected): self
    {
        return $this->invoke('equalCanonicalizing', [$expected]);
    }

    /**
     * Assert that numeric values are equal within a delta.
     */
    public function toEqualWithDelta(float|int $expected, float $delta): self
    {
        return $this->invoke('equalWithDelta', [$expected, $delta]);
    }

    /**
     * Assert that value is null.
     */
    public function toBeNull(): self
    {
        return $this->invoke('null');
    }

    /**
     * Assert that value is defined (not null and isset).
     */
    public function toBeDefined(): self
    {
        return $this->invoke('notUndefined');
    }

    /**
     * Assert that value is undefined (null or not set).
     */
    public function toBeUndefined(): self
    {
        return $this->invoke('undefined');
    }

    /**
     * Assert that value is nullable (allows null or specific type).
     */
    public function toBeNullable(string $type): self
    {
        return $this->invoke('nullable', [$type]);
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
            throw_if((bool) $this->value, AssertionException::class, 'Expected value to be falsy, but got truthy value', 0, null, $this->value);

            $this->negate = false;

            return $this;
        }

        throw_if((bool) $this->value === false, AssertionException::class, 'Expected value to be truthy, but got falsy value', 0, null, $this->value);

        return $this;
    }

    /**
     * Assert that value is falsy (evaluates to false in boolean context).
     */
    public function toBeFalsy(): self
    {
        if ($this->negate) {
            throw_if((bool) $this->value === false, AssertionException::class, 'Expected value to be truthy, but got falsy value', 0, null, $this->value);

            $this->negate = false;

            return $this;
        }

        throw_if((bool) $this->value, AssertionException::class, 'Expected value to be falsy, but got truthy value', 0, null, $this->value);

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
     * Assert that value is a positive number.
     */
    public function toBePositive(): self
    {
        return $this->invoke('positive');
    }

    /**
     * Assert that value is a negative number.
     */
    public function toBeNegative(): self
    {
        return $this->invoke('negative');
    }

    /**
     * Assert that value is an even integer.
     */
    public function toBeEven(): self
    {
        return $this->invoke('even');
    }

    /**
     * Assert that value is an odd integer.
     */
    public function toBeOdd(): self
    {
        return $this->invoke('odd');
    }

    /**
     * Assert that value is divisible by divisor.
     */
    public function toBeDivisibleBy(int|float $divisor): self
    {
        return $this->invoke('divisibleBy', [$divisor]);
    }

    /**
     * Assert that value is a valid date in the specified format.
     */
    public function toBeValidDate(string $format): self
    {
        return $this->invoke('date', [$format]);
    }

    /**
     * Assert that date is before another date.
     */
    public function toBeBefore(DateTimeInterface|string $date): self
    {
        return $this->invoke('before', [$date]);
    }

    /**
     * Assert that date is after another date.
     */
    public function toBeAfter(DateTimeInterface|string $date): self
    {
        return $this->invoke('after', [$date]);
    }

    /**
     * Assert that date is today.
     */
    public function toBeToday(): self
    {
        return $this->invoke('today');
    }

    /**
     * Assert that date is yesterday.
     */
    public function toBeYesterday(): self
    {
        return $this->invoke('yesterday');
    }

    /**
     * Assert that date is tomorrow.
     */
    public function toBeTomorrow(): self
    {
        return $this->invoke('tomorrow');
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
        return $this->invoke('readableFile');
    }

    /**
     * Assert that value is a writable file.
     */
    public function toBeWritableFile(): self
    {
        return $this->invoke('writableFile');
    }

    /**
     * Assert that value is a readable directory.
     */
    public function toBeReadableDirectory(): self
    {
        return $this->invoke('readableDirectory');
    }

    /**
     * Assert that value is a writable directory.
     */
    public function toBeWritableDirectory(): self
    {
        return $this->invoke('writableDirectory');
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
        return $this->invoke('infinite');
    }

    /**
     * Assert that value is NaN (Not a Number).
     */
    public function toBeNan(): self
    {
        return $this->invoke('nan');
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

            throw_if(ctype_alpha((string) $this->value), AssertionException::class, 'Expected value not to be alphabetic', 0, null, $this->value);

            return $this;
        }

        throw_unless(ctype_alpha((string) $this->value), AssertionException::class, 'Expected value to be alphabetic', 0, null, $this->value);

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
        return $this->invoke('snakeCase');
    }

    /**
     * Assert that string is kebab-case.
     */
    public function toBeKebabCase(): self
    {
        return $this->invoke('kebabCase');
    }

    /**
     * Assert that string is camelCase.
     */
    public function toBeCamelCase(): self
    {
        return $this->invoke('camelCase');
    }

    /**
     * Assert that string is StudlyCase.
     */
    public function toBeStudlyCase(): self
    {
        return $this->invoke('studlyCase');
    }

    /**
     * Assert that string is uppercase.
     */
    public function toBeUppercase(): self
    {
        return $this->invoke('uppercase');
    }

    /**
     * Assert that string is lowercase.
     */
    public function toBeLowercase(): self
    {
        return $this->invoke('lowercase');
    }

    /**
     * Assert that string matches regex pattern.
     */
    public function toMatch(string $pattern): self
    {
        return $this->invoke('regex', [$pattern]);
    }

    /**
     * Assert that value satisfies the provided callback.
     */
    public function toSatisfy(callable $callback): self
    {
        return $this->invoke('satisfy', [$callback]);
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

                throw_if(in_array($needle, $this->value, true), AssertionException::class, sprintf('Expected array not to contain %s', $needle), 0, null, $this->value);

                return $this;
            }

            throw_unless(in_array($needle, $this->value, true), AssertionException::class, sprintf('Expected array to contain %s', $needle), 0, null, $this->value);

            return $this;
        }

        throw new AssertionException(
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
        return $this->invoke('containEqual', [$needle]);
    }

    /**
     * Assert that array contains all the given values.
     */
    public function toContainAllValues(array $values): self
    {
        return $this->invoke('containAllValues', [$values]);
    }

    /**
     * Assert that array contains all the given keys.
     */
    public function toContainAllKeys(array $keys): self
    {
        return $this->invoke('containAllKeys', [$keys]);
    }

    /**
     * Assert that value is in the given array.
     */
    public function toBeIn(array $haystack): self
    {
        return $this->invoke('inArray', [$haystack]);
    }

    /**
     * Assert that value is one of the given options.
     * Alias for toBeIn().
     */
    public function toBeOneOf(array $options): self
    {
        return $this->toBeIn($options);
    }

    public function toContainValue(mixed $value): self
    {
        return $this->toContain($value);
    }

    public function toContainKey(string|int $key): self
    {
        return $this->toHaveKey($key);
    }

    public function toBeWithinRange(int|float $min, int|float $max): self
    {
        return $this->toBeBetween($min, $max);
    }

    /**
     * Assert that numeric values are close within precision.
     * Alias for toEqualWithDelta().
     */
    public function toBeCloseTo(float|int $expected, float $delta = 0.01): self
    {
        return $this->toEqualWithDelta($expected, $delta);
    }

    /**
     * Assert that array is a subset of expected array (ignoring order).
     */
    public function toMatchArray(array $expected): self
    {
        return $this->invoke('matchArray', [$expected]);
    }

    /**
     * Assert that object properties match expected array.
     */
    public function toMatchObject(array $expected): self
    {
        return $this->invoke('matchObject', [$expected]);
    }

    /**
     * Assert that value matches the given schema.
     *
     * @param array<string, mixed> $schema
     */
    public function toMatchSchema(array $schema): self
    {
        $errors = SchemaValidator::validate($this->value, $schema);

        throw_unless($errors === [], AssertionException::class, sprintf("Schema validation failed:\n- %s", implode("\n- ", $errors)), 0);

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

                throw new AssertionException(
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

        // Soft assertion mode
        if ($this->soft) {
            try {
                Assertion::propertyExists($this->value, $property);
            } catch (InvalidArgumentException $exception) {
                self::$softErrors[] = $exception;
            }

            return $this;
        }

        Assertion::propertyExists($this->value, $property);

        return $this;
    }

    /**
     * Assert that object has multiple properties.
     *
     * @param array<int|string, string> $properties
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
        return $this->invoke('snakeCaseKeys');
    }

    /**
     * Assert that all array keys are kebab-case.
     */
    public function toHaveKebabCaseKeys(): self
    {
        return $this->invoke('kebabCaseKeys');
    }

    /**
     * Assert that all array keys are camelCase.
     */
    public function toHaveCamelCaseKeys(): self
    {
        return $this->invoke('camelCaseKeys');
    }

    /**
     * Assert that all array keys are StudlyCase.
     */
    public function toHaveStudlyCaseKeys(): self
    {
        return $this->invoke('studlyCaseKeys');
    }

    /**
     * Assert that two countables have the same size.
     */
    public function toHaveSameSize(array|Countable $expected): self
    {
        return $this->invoke('sameSize', [$expected]);
    }

    /**
     * Assert that two arrays have the same keys.
     */
    public function toHaveSameKeys(array $expected): self
    {
        return $this->invoke('sameKeys', [$expected]);
    }

    /**
     * Assert that array is a subset of another array.
     */
    public function toBeSubsetOf(array $superset): self
    {
        return $this->invoke('subsetOf', [$superset]);
    }

    /**
     * Assert that array has only unique values (no duplicates).
     */
    public function toHaveUniqueValues(): self
    {
        return $this->invoke('uniqueValues');
    }

    /**
     * Assert that array is sorted in ascending order.
     */
    public function toBeSorted(): self
    {
        return $this->invoke('sorted');
    }

    /**
     * Assert that array is sorted in descending order.
     */
    public function toBeSortedDesc(): self
    {
        return $this->invoke('sortedDesc');
    }

    /**
     * Assert that array contains only instances of a given class.
     */
    public function toContainOnlyInstancesOf(string $className): self
    {
        return $this->invoke('containOnlyInstancesOf', [$className]);
    }

    /**
     * Assert that value matches a PHPUnit constraint.
     */
    public function toMatchConstraint(object $constraint): self
    {
        // For now, basic implementation - can be enhanced with PHPUnit constraint support
        throw_unless(method_exists($constraint, 'evaluate'), AssertionException::class, 'Constraint must have an evaluate method', 0, null, $constraint);

        try {
            $constraint->evaluate($this->value);
        } catch (Throwable $throwable) {
            if ($this->negate) {
                $this->negate = false;

                return $this;
            }

            throw new AssertionException('Constraint evaluation failed: '.$throwable->getMessage(), 0, null, $this->value);
        }

        if ($this->negate) {
            $this->negate = false;

            throw new AssertionException('Expected constraint to fail but it passed', 0, null, $this->value);
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

                throw new AssertionException(
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
     * Create alternative expectation groups - if any group passes, the entire expectation passes.
     *
     * Each call to or() starts a new group. All assertions between or() calls
     * belong to the same group. If any complete group passes without throwing,
     * the entire chain succeeds.
     */
    public function or(): self
    {
        // Start new group with clean state (assumes success until an assertion fails)
        $this->orMode = true;
        $this->orGroups[] = ['success' => true, 'errors' => []];

        return $this;
    }

    /**
     * XOR operator - creates alternative expectation groups where exactly one must pass.
     *
     * Each call to xor() starts a new group. All assertions between xor() calls
     * belong to the same group. Exactly one complete group must pass without throwing
     * for the entire chain to succeed.
     */
    public function xor(): self
    {
        // Start new group with clean state (assumes success until an assertion fails)
        $this->xorMode = true;
        $this->orGroups[] = ['success' => true, 'errors' => []];

        return $this;
    }

    /**
     * Exactly n groups must pass.
     */
    public function exactly(int $count): self
    {
        $this->thresholdMode = 'exactly';
        $this->thresholdCount = $count;
        $this->orMode = true;

        return $this;
    }

    /**
     * At least n groups must pass.
     */
    public function atLeast(int $count): self
    {
        $this->thresholdMode = 'atLeast';
        $this->thresholdCount = $count;
        $this->orMode = true;

        return $this;
    }

    /**
     * At most n groups must pass.
     */
    public function atMost(int $count): self
    {
        $this->thresholdMode = 'atMost';
        $this->thresholdCount = $count;
        $this->orMode = true;

        return $this;
    }

    /**
     * Inspect value without breaking chain (debugging helper).
     */
    public function tap(callable $callback): self
    {
        $callback($this->value);

        return $this;
    }

    /**
     * Transform value in chain.
     */
    public function pipe(callable $callback): self
    {
        $transformed = $callback($this->value);

        return new self($transformed);
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
     * Apply different expectations to each element in order (sequence).
     */
    public function sequence(callable ...$callbacks): self
    {
        Assertion::isTraversable($this->value);

        // @phpstan-ignore-next-line argument.type (validated by isTraversable)
        $values = is_array($this->value) ? $this->value : iterator_to_array($this->value);

        foreach ($callbacks as $index => $callback) {
            // @phpstan-ignore-next-line binaryOp.invalid (index is numeric from variadic array)
            throw_unless(array_key_exists((string) $index, $values), AssertionException::class, sprintf('Sequence expects at least %d items but got %d', $index + 1, count($values)), 0, null, $this->value);

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
        $decoded = json_decode((string) $this->value, true);

        return new self($decoded);
    }

    /**
     * Match value against multiple patterns.
     */
    public function match(array ...$patterns): self
    {
        foreach ($patterns as [$matcher, $callback]) {
            $matched = is_callable($matcher) ? $matcher($this->value) : $this->value === $matcher;

            if ($matched) {
                // @phpstan-ignore-next-line callable.nonCallable (pattern structure enforces callable in second position)
                $callback($this);

                return $this;
            }
        }

        throw new AssertionException('No pattern matched the value', 0, null, $this->value);
    }

    /**
     * Dump value and continue chain (debugging helper).
     *
     * @codeCoverageIgnore
     */
    public function dump(mixed ...$args): self
    {
        dump($this->value, ...$args);

        return $this;
    }

    /**
     * Dump and die - output value and stop execution.
     *
     * @codeCoverageIgnore
     */
    public function dd(mixed ...$args): never
    {
        dump($this->value, ...$args);

        exit(1);
    }

    /**
     * Dump and die only when condition is true.
     *
     * @codeCoverageIgnore
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
     *
     * @codeCoverageIgnore
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
     *
     * @codeCoverageIgnore
     */
    public function ray(?string $label = null): self
    {
        if (function_exists('ray')) {
            if ($label !== null) {
                ray($label, $this->value);
            } else {
                ray($this->value);
            }
        }

        return $this;
    }

    /**
     * Assert that value matches a stored snapshot.
     */
    public function toMatchSnapshot(string $testName): self
    {
        $formatted = SnapshotManager::formatValue($this->value);

        if (!SnapshotManager::hasSnapshot($testName)) {
            SnapshotManager::saveSnapshot($testName, $formatted);

            return $this;
        }

        $snapshot = SnapshotManager::getSnapshot($testName);

        throw_if($formatted !== $snapshot, AssertionException::class, sprintf("Snapshot \"%s\" does not match:\n\nExpected:\n%s\n\nReceived:\n%s", $testName, $snapshot, $formatted), 0);

        return $this;
    }

    /**
     * Assert that value matches an inline snapshot.
     */
    public function toMatchInlineSnapshot(string $expected): self
    {
        $formatted = SnapshotManager::formatValue($this->value);

        throw_if($formatted !== $expected, AssertionException::class, sprintf("Inline snapshot does not match:\n\nExpected:\n%s\n\nReceived:\n%s", $expected, $formatted), 0);

        return $this;
    }

    /**
     * Assert that value (callable) throws an exception.
     */
    public function toThrow(?string $exceptionClass = null, ?string $message = null): self
    {
        throw_unless(is_callable($this->value), AssertionException::class, 'toThrow() requires a callable value', 0, null, $this->value);

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

            throw_if($thrown, AssertionException::class, sprintf(
                'Expected callable not to throw but %s was thrown',
                $actualException instanceof Throwable ? $actualException::class : 'exception',
            ), 0, null, $this->value);

            return $this;
        }

        throw_unless($thrown, AssertionException::class, 'Expected callable to throw an exception but none was thrown', 0, null, $this->value);

        throw_if($exceptionClass !== null && !$actualException instanceof $exceptionClass, AssertionException::class, sprintf(
            'Expected exception of type %s but got %s',
            $exceptionClass,
            $actualException::class,
        ), 0, null, $this->value);

        throw_if($message !== null && !str_contains($actualException->getMessage(), $message), AssertionException::class, sprintf(
            'Expected exception message to contain "%s" but got "%s"',
            $message,
            $actualException->getMessage(),
        ), 0, null, $this->value);

        return $this;
    }

    /**
     * Assert that value (callable) throws an error.
     * Alias for toThrow().
     */
    public function toThrowError(?string $exceptionClass = null, ?string $message = null): self
    {
        return $this->toThrow($exceptionClass, $message);
    }

    /**
     * Assert that callable completes within specified time (in milliseconds).
     */
    public function toCompleteWithin(int $milliseconds): self
    {
        throw_unless(is_callable($this->value), AssertionException::class, 'toCompleteWithin() requires a callable value', 0, null, $this->value);

        $start = hrtime(true);
        ($this->value)();
        $elapsed = (hrtime(true) - $start) / 1_000_000; // Convert nanoseconds to milliseconds

        throw_if(
            $elapsed > $milliseconds,
            AssertionException::class,
            sprintf('Expected callable to complete within %dms but took %.2fms', $milliseconds, $elapsed),
            0,
            null,
            $this->value,
        );

        return $this;
    }

    /**
     * Evaluate OR/XOR groups - if any group succeeded, return success (OR) or check exactly one (XOR).
     * Otherwise throw combined errors from all groups.
     */
    private function evaluateOrGroups(): void
    {
        if ((!$this->orMode && !$this->xorMode) || $this->orGroups === []) {
            $this->evaluated = true;

            return;
        }

        // Count successful groups
        $successCount = 0;

        foreach ($this->orGroups as $group) {
            if (!$group['success']) {
                continue;
            }

            ++$successCount;
        }

        // Threshold mode: check if success count meets threshold criteria
        if ($this->thresholdMode !== null && $this->thresholdCount !== null) {
            $expected = $this->thresholdCount;
            $mode = $this->thresholdMode;

            $passes = match ($mode) {
                'exactly' => $successCount === $expected,
                'atLeast' => $successCount >= $expected,
                'atMost' => $successCount <= $expected,
                default => false,
            };

            if ($passes) {
                $this->evaluated = true;

                return; // Success!
            }

            $this->evaluated = true;

            throw new AssertionException(
                sprintf('Threshold assertion failed: expected %s %d group(s) to pass, but %d passed', $mode, $expected, $successCount),
                0,
                null,
                $this->value,
            );
        }

        // XOR mode: exactly one group must succeed
        if ($this->xorMode) {
            if ($successCount === 1) {
                $this->evaluated = true;

                return; // Success!
            }

            $this->evaluated = true;

            // Build error message
            $messages = [];

            if ($successCount === 0) {
                foreach ($this->orGroups as $index => $group) {
                    if (empty($group['errors'])) {
                        continue;
                    }

                    $messages[] = sprintf('Group %d: %s', $index + 1, $group['errors'][0]->getMessage());
                }

                throw new AssertionException(
                    sprintf("All XOR groups failed (expected exactly one to pass):\n%s", implode("\n", $messages)),
                    0,
                    null,
                    $this->value,
                );
            }

            throw new AssertionException(
                sprintf('XOR assertion failed: expected exactly one group to pass, but %d groups passed', $successCount),
                0,
                null,
                $this->value,
            );
        }

        // OR mode: at least one group must succeed
        if ($successCount > 0) {
            $this->evaluated = true;

            return; // Success!
        }

        // All groups failed - throw combined error
        $this->evaluated = true;
        $messages = [];

        foreach ($this->orGroups as $index => $group) {
            if (empty($group['errors'])) {
                continue;
            }

            $messages[] = sprintf('Group %d: %s', $index + 1, $group['errors'][0]->getMessage());
        }

        throw new AssertionException(
            sprintf("All OR groups failed:\n%s", implode("\n", $messages)),
            0,
            null,
            $this->value,
        );
    }

    /**
     * Check if a value contains asymmetric matchers.
     */
    private function containsAsymmetricMatchers(mixed $value): bool
    {
        if ($value instanceof AsymmetricMatcher) {
            return true;
        }

        if (is_array($value)) {
            foreach ($value as $item) {
                if ($this->containsAsymmetricMatchers($item)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Match value against expected structure with asymmetric matchers.
     */
    private function matchAsymmetric(mixed $expected): self
    {
        if (!$this->matchesAsymmetric($this->value, $expected)) {
            $valueStr = is_scalar($this->value) ? (string) $this->value : gettype($this->value);

            throw new AssertionException(
                'Expected value to match asymmetric pattern. Got: '.$valueStr,
                0,
            );
        }

        return $this;
    }

    /**
     * Recursively match values with asymmetric matchers.
     */
    private function matchesAsymmetric(mixed $actual, mixed $expected): bool
    {
        // Direct matcher check
        if ($expected instanceof AsymmetricMatcher) {
            return $expected->matches($actual);
        }

        // Array deep matching
        if (is_array($expected) && is_array($actual)) {
            foreach ($expected as $key => $expectedValue) {
                if (!array_key_exists((string) $key, $actual)) {
                    return false;
                }

                if (!$this->matchesAsymmetric($actual[$key], $expectedValue)) {
                    return false;
                }
            }

            return true;
        }

        // Strict equality fallback
        return $actual === $expected;
    }

    /**
     * Invoke an assertion method, handling negation and OR/XOR groups.
     *
     * @param array<mixed> $args
     */
    private function invoke(string $method, array $args = []): self
    {
        /** @var callable $callable */
        $callable = [Assertion::class, $method];

        // OR/XOR mode - collect assertions into groups
        if ($this->orMode || $this->xorMode) {
            // Ensure we have at least one group
            if ($this->orGroups === []) {
                $this->orGroups[] = ['success' => true, 'errors' => []]; // @codeCoverageIgnore
            }

            $lastGroupIndex = count($this->orGroups) - 1;

            // If this group already failed, don't bother running more assertions
            if (!($this->orGroups[$lastGroupIndex]['success'] ?? true)) {
                return $this;
            }

            try {
                if ($this->negate) {
                    $this->negate = false;

                    try {
                        $callable(...[$this->value, ...$args]);

                        throw new AssertionException(
                            sprintf('Expected assertion %s to fail but it passed', $method),
                            0,
                            null,
                            $this->value,
                        );
                    } catch (InvalidArgumentException $exception) {
                        throw_if(str_contains($exception->getMessage(), 'Expected assertion'), $exception);

                        // Assertion failed as expected - continue with group
                        return $this;
                    }
                }

                $callable(...[$this->value, ...$args]);

                // Assertion passed - group remains successful (assuming it was)
            } catch (InvalidArgumentException $exception) {
                // Assertion failed - mark group as failed and add error
                $this->orGroups[$lastGroupIndex]['success'] = false;
                $this->orGroups[$lastGroupIndex]['errors'][] = $exception;
            }

            return $this;
        }

        if ($this->negate) {
            $this->negate = false;

            try {
                $callable(...[$this->value, ...$args]);

                throw new AssertionException(
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

        // Collection mode - apply assertion to collection items
        if ($this->collectionMode !== null) {
            return $this->handleCollectionMode($method, $args);
        }

        // Soft assertion mode - collect errors instead of throwing
        if ($this->soft) {
            try {
                $callable(...[$this->value, ...$args]);
            } catch (InvalidArgumentException $exception) {
                self::$softErrors[] = $exception;
            }

            return $this;
        }

        $callable(...[$this->value, ...$args]);

        return $this;
    }

    /**
     * Handle collection quantifier modes (all/any/none).
     */
    private function handleCollectionMode(string $method, array $args): self
    {
        throw_unless(is_iterable($this->value), AssertionException::class, sprintf('Collection mode (%s) requires an iterable value. Got: %s', $this->collectionMode, gettype($this->value)), 0, null, $this->value);

        /** @var callable $callable */
        $callable = [Assertion::class, $method];
        $items = is_array($this->value) ? $this->value : iterator_to_array($this->value);
        $matchedCount = 0;
        $errors = [];

        foreach ($items as $key => $item) {
            try {
                $callable(...[$item, ...$args]);
                ++$matchedCount;
            } catch (InvalidArgumentException $exception) {
                $errors[$key] = $exception;
            }
        }

        $mode = $this->collectionMode;
        $totalCount = count($items);

        // Evaluate based on mode
        if ($mode === 'all') {
            if ($matchedCount !== $totalCount) {
                $failedKeys = array_keys($errors);

                throw new AssertionException(
                    sprintf('Expected all items to match. %d/%d items failed at keys: %s', $totalCount - $matchedCount, $totalCount, implode(', ', $failedKeys)),
                    0,
                    null,
                    $this->value,
                );
            }
        } elseif ($mode === 'any') {
            throw_if($matchedCount === 0, AssertionException::class, sprintf('Expected at least one item to match. All %d items failed', $totalCount), 0, null, $this->value);
        } elseif ($mode === 'none') {
            throw_if($matchedCount > 0, AssertionException::class, sprintf('Expected no items to match. %d/%d items matched', $matchedCount, $totalCount), 0, null, $this->value);
        }

        return $this;
    }
}
