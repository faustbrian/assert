<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Assert\Assertions;

use Cline\Assert\Exceptions\AssertionException;
use Cline\Assert\Exceptions\InvalidArgumentException;
use Closure;
use LogicException;
use ReflectionException;
use ReflectionMethod;
use RuntimeException;

use function array_key_exists;
use function array_unshift;
use function is_string;
use function is_subclass_of;
use function throw_if;
use function throw_unless;

/**
 * Fluent assertion chain for building complex validations.
 *
 * Provides a chainable interface for applying multiple assertions to a single value.
 * Supports modifiers like `all()`, `nullOr()`, and `not()` to adjust validation behavior.
 * The chain is stateful and should not be reused after creation.
 *
 * Created through `Assert::that()` factory method rather than direct instantiation.
 *
 * @author Benjamin Eberlei <kontakt@beberlei.de>
 *
 * @method AssertionChain email(string|callable $message = null, string $propertyPath = null)                                                                    Assert that value is an email address (using input_filter/FILTER_VALIDATE_EMAIL).
 * @method AssertionChain alnum(string|callable $message = null, string $propertyPath = null)                                                                    Assert that value is alphanumeric.
 * @method AssertionChain inArray(array<mixed> $choices, string|callable $message = null, string $propertyPath = null)                                           Assert that value is in array of choices. This is an alias of Assertion::choice().
 * @method AssertionChain base64(string|callable $message = null, string $propertyPath = null)                                                                   Assert that a constant is defined.
 * @method AssertionChain between(mixed $lowerLimit, mixed $upperLimit, string|callable $message = null, string $propertyPath = null)                            Assert that a value is greater or equal than a lower limit, and less than or equal to an upper limit.
 * @method AssertionChain betweenExclusive(mixed $lowerLimit, mixed $upperLimit, string|callable $message = null, string $propertyPath = null)                   Assert that a value is greater than a lower limit, and less than an upper limit.
 * @method AssertionChain betweenLength(int $minLength, int $maxLength, string|callable $message = null, string $propertyPath = null, string $encoding = 'utf8') Assert that string length is between min and max lengths.
 * @method AssertionChain boolean(string|callable $message = null, string $propertyPath = null)                                                                  Assert that value is php boolean.
 * @method AssertionChain choice(array<mixed> $choices, string|callable $message = null, string $propertyPath = null)                                            Assert that value is in array of choices.
 * @method AssertionChain choicesNotEmpty(array<mixed> $choices, string|callable $message = null, string $propertyPath = null)                                   Determines if the values array has every choice as key and that this choice has content.
 * @method AssertionChain classExists(string|callable $message = null, string $propertyPath = null)                                                              Assert that the class exists.
 * @method AssertionChain contains(string $needle, string|callable $message = null, string $propertyPath = null, string $encoding = 'utf8')                      Assert that string contains a sequence of chars.
 * @method AssertionChain count(int $count, string|callable $message = null, string $propertyPath = null)                                                        Assert that the count of countable is equal to count.
 * @method AssertionChain date(string $format, string|callable $message = null, string $propertyPath = null)                                                     Assert that date is valid and corresponds to the given format.
 * @method AssertionChain defined(string|callable $message = null, string $propertyPath = null)                                                                  Assert that a constant is defined.
 * @method AssertionChain digit(string|callable $message = null, string $propertyPath = null)                                                                    Validates if an integer or integerish is a digit.
 * @method AssertionChain directory(string|callable $message = null, string $propertyPath = null)                                                                Assert that a directory exists.
 * @method AssertionChain e164(string|callable $message = null, string $propertyPath = null)                                                                     Assert that the given string is a valid E164 Phone Number.
 * @method AssertionChain endsWith(string $needle, string|callable $message = null, string $propertyPath = null, string $encoding = 'utf8')                      Assert that string ends with a sequence of chars.
 * @method AssertionChain eqArraySubset(mixed $value2, string|callable $message = null, string $propertyPath = null)                                             Assert that the array contains the subset.
 * @method AssertionChain notEq(mixed $value2, string|callable $message = null, string $propertyPath = null)                                                     Assert that two values are not equal (using ==).
 * @method AssertionChain extensionLoaded(string|callable $message = null, string $propertyPath = null)                                                          Assert that extension is loaded.
 * @method AssertionChain extensionVersion(string $operator, mixed $version, string|callable $message = null, string $propertyPath = null)                       Assert that extension is loaded and a specific version is installed.
 * @method AssertionChain false(string|callable $message = null, string $propertyPath = null)                                                                    Assert that the value is boolean False.
 * @method AssertionChain file(string|callable $message = null, string $propertyPath = null)                                                                     Assert that a file exists.
 * @method AssertionChain float(string|callable $message = null, string $propertyPath = null)                                                                    Assert that value is a php float.
 * @method AssertionChain greaterOrEqualThan(mixed $limit, string|callable $message = null, string $propertyPath = null)                                         Determines if the value is greater or equal than given limit.
 * @method AssertionChain greaterThan(mixed $limit, string|callable $message = null, string $propertyPath = null)                                                Determines if the value is greater than given limit.
 * @method AssertionChain implementsInterface(string $interfaceName, string|callable $message = null, string $propertyPath = null)                               Assert that the class implements the interface.
 * @method AssertionChain integer(string|callable $message = null, string $propertyPath = null)                                                                  Assert that value is a php integer.
 * @method AssertionChain integerish(string|callable $message = null, string $propertyPath = null)                                                               Assert that value is a php integer'ish.
 * @method AssertionChain interfaceExists(string|callable $message = null, string $propertyPath = null)                                                          Assert that the interface exists.
 * @method AssertionChain ip(int $flag = null, string|callable $message = null, string $propertyPath = null)                                                     Assert that value is an IPv4 or IPv6 address.
 * @method AssertionChain ipv4(int $flag = null, string|callable $message = null, string $propertyPath = null)                                                   Assert that value is an IPv4 address.
 * @method AssertionChain ipv6(int $flag = null, string|callable $message = null, string $propertyPath = null)                                                   Assert that value is an IPv6 address.
 * @method AssertionChain isArray(string|callable $message = null, string $propertyPath = null)                                                                  Assert that value is an array.
 * @method AssertionChain isArrayAccessible(string|callable $message = null, string $propertyPath = null)                                                        Assert that value is an array or an array-accessible object.
 * @method AssertionChain isCallable(string|callable $message = null, string $propertyPath = null)                                                               Determines that the provided value is callable.
 * @method AssertionChain isCountable(string|callable $message = null, string $propertyPath = null)                                                              Assert that value is countable.
 * @method AssertionChain isInstanceOf(string $className, string|callable $message = null, string $propertyPath = null)                                          Assert that value is instance of given class-name.
 * @method AssertionChain isJsonString(string|callable $message = null, string $propertyPath = null)                                                             Assert that the given string is a valid json string.
 * @method AssertionChain isObject(string|callable $message = null, string $propertyPath = null)                                                                 Determines that the provided value is an object.
 * @method AssertionChain isResource(string|callable $message = null, string $propertyPath = null)                                                               Assert that value is a resource.
 * @method AssertionChain keyIsset(string|int $key, string|callable $message = null, string $propertyPath = null)                                                Assert that key exists in an array/array-accessible object using isset().
 * @method AssertionChain isTraversable(string|callable $message = null, string $propertyPath = null)                                                            Assert that value is an array or a traversable object.
 * @method AssertionChain keyExists(string|int $key, string|callable $message = null, string $propertyPath = null)                                               Assert that key exists in an array.
 * @method AssertionChain keyNotExists(string|int $key, string|callable $message = null, string $propertyPath = null)                                            Assert that key does not exist in an array.
 * @method AssertionChain length(int $length, string|callable $message = null, string $propertyPath = null, string $encoding = 'utf8')                           Assert that string has a given length.
 * @method AssertionChain lessOrEqualThan(mixed $limit, string|callable $message = null, string $propertyPath = null)                                            Determines if the value is less or equal than given limit.
 * @method AssertionChain lessThan(mixed $limit, string|callable $message = null, string $propertyPath = null)                                                   Determines if the value is less than given limit.
 * @method AssertionChain max(mixed $maxValue, string|callable $message = null, string $propertyPath = null)                                                     Assert that a number is smaller as a given limit.
 * @method AssertionChain maxCount(int $count, string|callable $message = null, string $propertyPath = null)                                                     Assert that the countable have at most $count elements.
 * @method AssertionChain maxLength(int $maxLength, string|callable $message = null, string $propertyPath = null, string $encoding = 'utf8')                     Assert that string value is not longer than $maxLength chars.
 * @method AssertionChain methodExists(mixed $object, string|callable $message = null, string $propertyPath = null)                                              Determines that the named method is defined in the provided object.
 * @method AssertionChain min(mixed $minValue, string|callable $message = null, string $propertyPath = null)                                                     Assert that a value is at least as big as a given limit.
 * @method AssertionChain minCount(int $count, string|callable $message = null, string $propertyPath = null)                                                     Assert that the countable have at least $count elements.
 * @method AssertionChain minLength(int $minLength, string|callable $message = null, string $propertyPath = null, string $encoding = 'utf8')                     Assert that a string is at least $minLength chars long.
 * @method AssertionChain noContent(string|callable $message = null, string $propertyPath = null)                                                                Assert that value is empty.
 * @method AssertionChain notBlank(string|callable $message = null, string $propertyPath = null)                                                                 Assert that value is not blank.
 * @method AssertionChain notContains(string $needle, string|callable $message = null, string $propertyPath = null, string $encoding = 'utf8')                   Assert that string does not contains a sequence of chars.
 * @method AssertionChain notEmpty(string|callable $message = null, string $propertyPath = null)                                                                 Assert that value is not empty.
 * @method AssertionChain notEmptyKey(string|int $key, string|callable $message = null, string $propertyPath = null)                                             Assert that key exists in an array/array-accessible object and its value is not empty.
 * @method AssertionChain notInArray(array<mixed> $choices, string|callable $message = null, string $propertyPath = null)                                        Assert that value is not in array of choices.
 * @method AssertionChain notIsInstanceOf(string $className, string|callable $message = null, string $propertyPath = null)                                       Assert that value is not instance of given class-name.
 * @method AssertionChain notNull(string|callable $message = null, string $propertyPath = null)                                                                  Assert that value is not null.
 * @method AssertionChain notRegex(string $pattern, string|callable $message = null, string $propertyPath = null)                                                Assert that value does not match a regex.
 * @method AssertionChain null(string|callable $message = null, string $propertyPath = null)                                                                     Assert that value is null.
 * @method AssertionChain numeric(string|callable $message = null, string $propertyPath = null)                                                                  Assert that value is numeric.
 * @method AssertionChain objectOrClass(string|callable $message = null, string $propertyPath = null)                                                            Assert that the value is an object, or a class that exists.
 * @method AssertionChain phpVersion(mixed $version, string|callable $message = null, string $propertyPath = null)                                               Assert on PHP version.
 * @method AssertionChain propertiesExist(array<mixed> $properties, string|callable $message = null, string $propertyPath = null)                                Assert that the value is an object or class, and that the properties all exist.
 * @method AssertionChain propertyExists(string $property, string|callable $message = null, string $propertyPath = null)                                         Assert that the value is an object or class, and that the property exists.
 * @method AssertionChain range(mixed $minValue, mixed $maxValue, string|callable $message = null, string $propertyPath = null)                                  Assert that value is in range of numbers.
 * @method AssertionChain readable(string|callable $message = null, string $propertyPath = null)                                                                 Assert that the value is something readable.
 * @method AssertionChain regex(string $pattern, string|callable $message = null, string $propertyPath = null)                                                   Assert that value matches a regex.
 * @method AssertionChain same(mixed $value2, string|callable $message = null, string $propertyPath = null)                                                      Assert that two values are the same (using ===).
 * @method AssertionChain satisfy(callable $callback, string|callable $message = null, string $propertyPath = null)                                              Assert that the provided value is valid according to a callback.
 * @method AssertionChain scalar(string|callable $message = null, string $propertyPath = null)                                                                   Assert that value is a PHP scalar.
 * @method AssertionChain startsWith(string $needle, string|callable $message = null, string $propertyPath = null, string $encoding = 'utf8')                    Assert that string starts with a sequence of chars.
 * @method AssertionChain string(string|callable $message = null, string $propertyPath = null)                                                                   Assert that value is a string.
 * @method AssertionChain subclassOf(string $className, string|callable $message = null, string $propertyPath = null)                                            Assert that value is subclass of given class-name.
 * @method AssertionChain true(string|callable $message = null, string $propertyPath = null)                                                                     Assert that the value is boolean True.
 * @method AssertionChain uniqueValues(string|callable $message = null, string $propertyPath = null)                                                             Assert that values in array are unique (using strict equality).
 * @method AssertionChain url(string|callable $message = null, string $propertyPath = null)                                                                      Assert that value is an URL.
 * @method AssertionChain uuid(string|callable $message = null, string $propertyPath = null)                                                                     Assert that the given string is a valid UUID.
 * @method AssertionChain version(string $operator, string $version2, string|callable $message = null, string $propertyPath = null)                              Assert comparison of two versions.
 * @method AssertionChain writeable(string|callable $message = null, string $propertyPath = null)                                                                Assert that the value is something writeable.
 */
final class AssertionChain
{
    /**
     * When true, all assertions pass without validation.
     *
     * Set automatically when `nullOr()` encounters a null value, causing
     * the chain to short-circuit and skip remaining assertions.
     */
    private bool $alwaysValid = false;

    /**
     * When true, assertions apply to every element in an array or traversable.
     *
     * Activated by calling the `all()` modifier on the chain.
     */
    private bool $all = false;

    /**
     * When true, the next assertion will be negated.
     *
     * Activated by calling the `not()` modifier, causing the next assertion
     * to fail if it would normally pass. Resets after use.
     */
    private bool $negate = false;

    /**
     * Assertion class used for executing validation methods.
     *
     * @var class-string<AbstractAssertion>
     */
    private string $assertionClassName = Assertion::class;

    /**
     * Create a new assertion chain for a value.
     *
     * @param mixed               $value               The value to validate through this chain
     * @param null|Closure|string $defaultMessage      Default error message for all assertions
     * @param null|string         $defaultPropertyPath Property path prefix for error messages (e.g., 'user.email')
     */
    public function __construct(
        private readonly mixed $value,
        private readonly Closure|string|null $defaultMessage = null,
        private readonly ?string $defaultPropertyPath = null,
    ) {}

    /**
     * Magic method to invoke assertion methods on the chained value.
     *
     * Automatically injects the value being validated as the first argument and
     * applies default message/property path if not provided. Handles negation
     * through the `not()` modifier and array validation through the `all()` modifier.
     *
     * @param string       $methodName Name of the assertion method to call
     * @param array<mixed> $args       Arguments for the assertion (value auto-injected)
     *
     * @throws InvalidArgumentException When the assertion fails
     * @throws RuntimeException         When the assertion method doesn't exist
     * @return self                     Returns this chain for method chaining
     */
    public function __call(string $methodName, array $args): self
    {
        if ($this->alwaysValid) {
            return $this;
        }

        try {
            $method = new ReflectionMethod($this->assertionClassName, $methodName);
        } catch (ReflectionException) {
            throw new RuntimeException("Assertion '".$methodName."' does not exist.");
        }

        array_unshift($args, $this->value);
        $params = $method->getParameters();

        foreach ($params as $idx => $param) {
            if (array_key_exists($idx, $args)) {
                continue;
            }

            switch ($param->getName()) {
                case 'message':
                    $args[$idx] = $this->defaultMessage;

                    break;

                case 'propertyPath':
                    $args[$idx] = $this->defaultPropertyPath;

                    break;
            }
        }

        if ($this->all) {
            $methodName = 'all'.$methodName;
        }

        /** @var callable $callable */
        $callable = [$this->assertionClassName, $methodName];

        if ($this->negate) {
            $this->negate = false;

            try {
                $callable(...$args);

                throw new AssertionException('Expected assertion to fail but it passed', 0, null, $this->value);
            } catch (InvalidArgumentException $exception) {
                throw_if($exception->getMessage() === 'Expected assertion to fail but it passed', $exception);
            }
        } else {
            $callable(...$args);
        }

        return $this;
    }

    /**
     * Enable validation for all elements in an array or traversable.
     *
     * Switches the chain into "all" mode where each subsequent assertion applies
     * to every element in the value being validated.
     *
     * ```php
     * Assert::that($ids)->all()->integer()->greaterThan(0);
     * ```
     *
     * @return self Returns this chain for method chaining
     */
    public function all(): self
    {
        $this->all = true;

        return $this;
    }

    /**
     * Allow null values and skip validation if value is null.
     *
     * When the value is null, sets `alwaysValid` flag causing all subsequent
     * assertions to be bypassed. If value is not null, assertions proceed normally.
     *
     * ```php
     * Assert::that($optionalEmail)->nullOr()->string()->email();
     * ```
     *
     * @return self Returns this chain for method chaining
     */
    public function nullOr(): self
    {
        if (null === $this->value) {
            $this->alwaysValid = true;
        }

        return $this;
    }

    /**
     * Negate the next assertion in the chain.
     *
     * Causes the next assertion to fail if it would pass and vice versa.
     * The negation flag resets automatically after the next assertion executes.
     *
     * ```php
     * Assert::that($value)->not()->null();  // Value must not be null
     * ```
     *
     * @return self Returns this chain for method chaining
     */
    public function not(): self
    {
        $this->negate = true;

        return $this;
    }

    /**
     * Set the assertion class used for executing validation methods.
     *
     * Allows customization of the assertion implementation used by this chain.
     * The provided class must extend AbstractAssertion.
     *
     * @param mixed $className The assertion class name (must be string extending AbstractAssertion)
     *
     * @throws LogicException When className is not a string or doesn't extend AbstractAssertion
     * @return self           Returns this chain for method chaining
     */
    public function setAssertionClassName(mixed $className): self
    {
        throw_unless(is_string($className), LogicException::class, 'Exception class name must be passed as a string');

        throw_if(AbstractAssertion::class !== $className && !is_subclass_of($className, AbstractAssertion::class), LogicException::class, $className.' is not (a subclass of) '.AbstractAssertion::class);

        $this->assertionClassName = $className;

        return $this;
    }
}
