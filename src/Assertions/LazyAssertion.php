<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Assert\Assertions;

use Cline\Assert\Assert;
use Cline\Assert\Exceptions\AssertionFailedException;
use Cline\Assert\Exceptions\LazyAssertionException;
use Cline\Assert\Exceptions\LazyAssertionExceptionInterface;
use Closure;
use LogicException;

use function is_subclass_of;
use function throw_if;

/**
 * Lazy assertion builder that collects validation errors before throwing.
 *
 * Unlike standard assertions that fail fast on the first error, lazy assertions
 * collect all validation failures and throw a single exception containing all errors
 * when `verifyNow()` is called. This is useful for form validation where you want
 * to show all errors at once.
 *
 * Created through `Assert::lazy()` factory method rather than direct instantiation.
 *
 * ```php
 * Assert::lazy()
 *     ->that($email, 'email')->email()
 *     ->that($age, 'age')->integer()->min(18)
 *     ->verifyNow();
 * ```
 *
 * @author Benjamin Eberlei <kontakt@beberlei.de>
 *
 * @method LazyAssertion email(string|callable $message = null, string $propertyPath = null)                                                                    Assert that value is an email address (using input_filter/FILTER_VALIDATE_EMAIL).
 * @method LazyAssertion all()                                                                                                                                  Switch chain into validation mode for an array of values.
 * @method LazyAssertion alnum(string|callable $message = null, string $propertyPath = null)                                                                    Assert that value is alphanumeric.
 * @method LazyAssertion inArray(array<mixed> $choices, string|callable $message = null, string $propertyPath = null)                                           Assert that value is in array of choices. This is an alias of Assertion::choice().
 * @method LazyAssertion base64(string|callable $message = null, string $propertyPath = null)                                                                   Assert that a constant is defined.
 * @method LazyAssertion between(mixed $lowerLimit, mixed $upperLimit, string|callable $message = null, string $propertyPath = null)                            Assert that a value is greater or equal than a lower limit, and less than or equal to an upper limit.
 * @method LazyAssertion betweenExclusive(mixed $lowerLimit, mixed $upperLimit, string|callable $message = null, string $propertyPath = null)                   Assert that a value is greater than a lower limit, and less than an upper limit.
 * @method LazyAssertion betweenLength(int $minLength, int $maxLength, string|callable $message = null, string $propertyPath = null, string $encoding = 'utf8') Assert that string length is between min and max lengths.
 * @method LazyAssertion boolean(string|callable $message = null, string $propertyPath = null)                                                                  Assert that value is php boolean.
 * @method LazyAssertion choice(array<mixed> $choices, string|callable $message = null, string $propertyPath = null)                                            Assert that value is in array of choices.
 * @method LazyAssertion choicesNotEmpty(array<mixed> $choices, string|callable $message = null, string $propertyPath = null)                                   Determines if the values array has every choice as key and that this choice has content.
 * @method LazyAssertion classExists(string|callable $message = null, string $propertyPath = null)                                                              Assert that the class exists.
 * @method LazyAssertion contains(string $needle, string|callable $message = null, string $propertyPath = null, string $encoding = 'utf8')                      Assert that string contains a sequence of chars.
 * @method LazyAssertion count(int $count, string|callable $message = null, string $propertyPath = null)                                                        Assert that the count of countable is equal to count.
 * @method LazyAssertion date(string $format, string|callable $message = null, string $propertyPath = null)                                                     Assert that date is valid and corresponds to the given format.
 * @method LazyAssertion defined(string|callable $message = null, string $propertyPath = null)                                                                  Assert that a constant is defined.
 * @method LazyAssertion digit(string|callable $message = null, string $propertyPath = null)                                                                    Validates if an integer or integerish is a digit.
 * @method LazyAssertion directory(string|callable $message = null, string $propertyPath = null)                                                                Assert that a directory exists.
 * @method LazyAssertion e164(string|callable $message = null, string $propertyPath = null)                                                                     Assert that the given string is a valid E164 Phone Number.
 * @method LazyAssertion endsWith(string $needle, string|callable $message = null, string $propertyPath = null, string $encoding = 'utf8')                      Assert that string ends with a sequence of chars.
 * @method LazyAssertion eqArraySubset(mixed $value2, string|callable $message = null, string $propertyPath = null)                                             Assert that the array contains the subset.
 * @method LazyAssertion notEq(mixed $value2, string|callable $message = null, string $propertyPath = null)                                                     Assert that two values are not equal (using ==).
 * @method LazyAssertion extensionLoaded(string|callable $message = null, string $propertyPath = null)                                                          Assert that extension is loaded.
 * @method LazyAssertion extensionVersion(string $operator, mixed $version, string|callable $message = null, string $propertyPath = null)                       Assert that extension is loaded and a specific version is installed.
 * @method LazyAssertion false(string|callable $message = null, string $propertyPath = null)                                                                    Assert that the value is boolean False.
 * @method LazyAssertion file(string|callable $message = null, string $propertyPath = null)                                                                     Assert that a file exists.
 * @method LazyAssertion float(string|callable $message = null, string $propertyPath = null)                                                                    Assert that value is a php float.
 * @method LazyAssertion greaterOrEqualThan(mixed $limit, string|callable $message = null, string $propertyPath = null)                                         Determines if the value is greater or equal than given limit.
 * @method LazyAssertion greaterThan(mixed $limit, string|callable $message = null, string $propertyPath = null)                                                Determines if the value is greater than given limit.
 * @method LazyAssertion implementsInterface(string $interfaceName, string|callable $message = null, string $propertyPath = null)                               Assert that the class implements the interface.
 * @method LazyAssertion integer(string|callable $message = null, string $propertyPath = null)                                                                  Assert that value is a php integer.
 * @method LazyAssertion integerish(string|callable $message = null, string $propertyPath = null)                                                               Assert that value is a php integer'ish.
 * @method LazyAssertion interfaceExists(string|callable $message = null, string $propertyPath = null)                                                          Assert that the interface exists.
 * @method LazyAssertion ip(int $flag = null, string|callable $message = null, string $propertyPath = null)                                                     Assert that value is an IPv4 or IPv6 address.
 * @method LazyAssertion ipv4(int $flag = null, string|callable $message = null, string $propertyPath = null)                                                   Assert that value is an IPv4 address.
 * @method LazyAssertion ipv6(int $flag = null, string|callable $message = null, string $propertyPath = null)                                                   Assert that value is an IPv6 address.
 * @method LazyAssertion isArray(string|callable $message = null, string $propertyPath = null)                                                                  Assert that value is an array.
 * @method LazyAssertion isArrayAccessible(string|callable $message = null, string $propertyPath = null)                                                        Assert that value is an array or an array-accessible object.
 * @method LazyAssertion isCallable(string|callable $message = null, string $propertyPath = null)                                                               Determines that the provided value is callable.
 * @method LazyAssertion isCountable(string|callable $message = null, string $propertyPath = null)                                                              Assert that value is countable.
 * @method LazyAssertion isInstanceOf(string $className, string|callable $message = null, string $propertyPath = null)                                          Assert that value is instance of given class-name.
 * @method LazyAssertion isJsonString(string|callable $message = null, string $propertyPath = null)                                                             Assert that the given string is a valid json string.
 * @method LazyAssertion isObject(string|callable $message = null, string $propertyPath = null)                                                                 Determines that the provided value is an object.
 * @method LazyAssertion isResource(string|callable $message = null, string $propertyPath = null)                                                               Assert that value is a resource.
 * @method LazyAssertion keyIsset(string|int $key, string|callable $message = null, string $propertyPath = null)                                                Assert that key exists in an array/array-accessible object using isset().
 * @method LazyAssertion isTraversable(string|callable $message = null, string $propertyPath = null)                                                            Assert that value is an array or a traversable object.
 * @method LazyAssertion keyExists(string|int $key, string|callable $message = null, string $propertyPath = null)                                               Assert that key exists in an array.
 * @method LazyAssertion keyNotExists(string|int $key, string|callable $message = null, string $propertyPath = null)                                            Assert that key does not exist in an array.
 * @method LazyAssertion length(int $length, string|callable $message = null, string $propertyPath = null, string $encoding = 'utf8')                           Assert that string has a given length.
 * @method LazyAssertion lessOrEqualThan(mixed $limit, string|callable $message = null, string $propertyPath = null)                                            Determines if the value is less or equal than given limit.
 * @method LazyAssertion lessThan(mixed $limit, string|callable $message = null, string $propertyPath = null)                                                   Determines if the value is less than given limit.
 * @method LazyAssertion max(mixed $maxValue, string|callable $message = null, string $propertyPath = null)                                                     Assert that a number is smaller as a given limit.
 * @method LazyAssertion maxCount(int $count, string|callable $message = null, string $propertyPath = null)                                                     Assert that the countable have at most $count elements.
 * @method LazyAssertion maxLength(int $maxLength, string|callable $message = null, string $propertyPath = null, string $encoding = 'utf8')                     Assert that string value is not longer than $maxLength chars.
 * @method LazyAssertion methodExists(mixed $object, string|callable $message = null, string $propertyPath = null)                                              Determines that the named method is defined in the provided object.
 * @method LazyAssertion min(mixed $minValue, string|callable $message = null, string $propertyPath = null)                                                     Assert that a value is at least as big as a given limit.
 * @method LazyAssertion minCount(int $count, string|callable $message = null, string $propertyPath = null)                                                     Assert that the countable have at least $count elements.
 * @method LazyAssertion minLength(int $minLength, string|callable $message = null, string $propertyPath = null, string $encoding = 'utf8')                     Assert that a string is at least $minLength chars long.
 * @method LazyAssertion noContent(string|callable $message = null, string $propertyPath = null)                                                                Assert that value is empty.
 * @method LazyAssertion notBlank(string|callable $message = null, string $propertyPath = null)                                                                 Assert that value is not blank.
 * @method LazyAssertion notContains(string $needle, string|callable $message = null, string $propertyPath = null, string $encoding = 'utf8')                   Assert that string does not contains a sequence of chars.
 * @method LazyAssertion notEmpty(string|callable $message = null, string $propertyPath = null)                                                                 Assert that value is not empty.
 * @method LazyAssertion notEmptyKey(string|int $key, string|callable $message = null, string $propertyPath = null)                                             Assert that key exists in an array/array-accessible object and its value is not empty.
 * @method LazyAssertion notInArray(array<mixed> $choices, string|callable $message = null, string $propertyPath = null)                                        Assert that value is not in array of choices.
 * @method LazyAssertion notIsInstanceOf(string $className, string|callable $message = null, string $propertyPath = null)                                       Assert that value is not instance of given class-name.
 * @method LazyAssertion notNull(string|callable $message = null, string $propertyPath = null)                                                                  Assert that value is not null.
 * @method LazyAssertion notRegex(string $pattern, string|callable $message = null, string $propertyPath = null)                                                Assert that value does not match a regex.
 * @method LazyAssertion null(string|callable $message = null, string $propertyPath = null)                                                                     Assert that value is null.
 * @method LazyAssertion nullOr()                                                                                                                               Switch chain into mode allowing nulls, ignoring further assertions.
 * @method LazyAssertion numeric(string|callable $message = null, string $propertyPath = null)                                                                  Assert that value is numeric.
 * @method LazyAssertion objectOrClass(string|callable $message = null, string $propertyPath = null)                                                            Assert that the value is an object, or a class that exists.
 * @method LazyAssertion phpVersion(mixed $version, string|callable $message = null, string $propertyPath = null)                                               Assert on PHP version.
 * @method LazyAssertion propertiesExist(array<mixed> $properties, string|callable $message = null, string $propertyPath = null)                                Assert that the value is an object or class, and that the properties all exist.
 * @method LazyAssertion propertyExists(string $property, string|callable $message = null, string $propertyPath = null)                                         Assert that the value is an object or class, and that the property exists.
 * @method LazyAssertion range(mixed $minValue, mixed $maxValue, string|callable $message = null, string $propertyPath = null)                                  Assert that value is in range of numbers.
 * @method LazyAssertion readable(string|callable $message = null, string $propertyPath = null)                                                                 Assert that the value is something readable.
 * @method LazyAssertion regex(string $pattern, string|callable $message = null, string $propertyPath = null)                                                   Assert that value matches a regex.
 * @method LazyAssertion same(mixed $value2, string|callable $message = null, string $propertyPath = null)                                                      Assert that two values are the same (using ===).
 * @method LazyAssertion satisfy(callable $callback, string|callable $message = null, string $propertyPath = null)                                              Assert that the provided value is valid according to a callback.
 * @method LazyAssertion scalar(string|callable $message = null, string $propertyPath = null)                                                                   Assert that value is a PHP scalar.
 * @method LazyAssertion startsWith(string $needle, string|callable $message = null, string $propertyPath = null, string $encoding = 'utf8')                    Assert that string starts with a sequence of chars.
 * @method LazyAssertion string(string|callable $message = null, string $propertyPath = null)                                                                   Assert that value is a string.
 * @method LazyAssertion subclassOf(string $className, string|callable $message = null, string $propertyPath = null)                                            Assert that value is subclass of given class-name.
 * @method LazyAssertion true(string|callable $message = null, string $propertyPath = null)                                                                     Assert that the value is boolean True.
 * @method LazyAssertion uniqueValues(string|callable $message = null, string $propertyPath = null)                                                             Assert that values in array are unique (using strict equality).
 * @method LazyAssertion url(string|callable $message = null, string $propertyPath = null)                                                                      Assert that value is an URL.
 * @method LazyAssertion uuid(string|callable $message = null, string $propertyPath = null)                                                                     Assert that the given string is a valid UUID.
 * @method LazyAssertion version(string $operator, string $version2, string|callable $message = null, string $propertyPath = null)                              Assert comparison of two versions.
 * @method LazyAssertion writeable(string|callable $message = null, string $propertyPath = null)                                                                Assert that the value is something writeable.
 */
final class LazyAssertion
{
    /**
     * When true, the current assertion chain has encountered a failure.
     *
     * Used to implement fail-fast behavior per chain unless `tryAll()` is active.
     */
    private bool $currentChainFailed = false;

    /**
     * When true, all assertion chains will execute fully regardless of failures.
     *
     * Set by calling `tryAll()` before any `that()` calls.
     */
    private bool $alwaysTryAll = false;

    /**
     * When true, the current assertion chain will execute fully regardless of failures.
     *
     * Set by calling `tryAll()` after a `that()` call.
     */
    private bool $thisChainTryAll = false;

    /**
     * The current assertion chain being built.
     */
    private mixed $currentChain = null;

    /**
     * Collection of assertion failures encountered during validation.
     *
     * @var array<array-key, AssertionFailedException>
     */
    private array $errors = [];

    /**
     * Assert class used as factory for creating assertion chains.
     *
     * @var class-string<Assert>
     */
    private string $assertClass = Assert::class;

    /**
     * Exception class thrown when validation fails.
     *
     * @var class-string<LazyAssertionExceptionInterface>
     */
    private string $exceptionClass = LazyAssertionException::class;

    /**
     * Magic method to delegate assertion calls to the current chain.
     *
     * Catches assertion failures and adds them to the error collection instead
     * of throwing immediately. Supports fail-fast per chain unless `tryAll()` is active.
     *
     * @param string       $method Name of the assertion method to call
     * @param array<mixed> $args   Arguments for the assertion method
     *
     * @return static Returns this lazy assertion for method chaining
     */
    public function __call(string $method, array $args)
    {
        if (false === $this->alwaysTryAll
            && false === $this->thisChainTryAll
            && $this->currentChainFailed
        ) {
            return $this;
        }

        try {
            /** @var callable $callable */
            $callable = [$this->currentChain, $method];
            $callable(...$args);
        } catch (AssertionFailedException $assertionFailedException) {
            $this->errors[] = $assertionFailedException;
            $this->currentChainFailed = true;
        }

        return $this;
    }

    /**
     * Start a new assertion chain for a value.
     *
     * Creates a new assertion chain and resets the failure state. Each call to `that()`
     * creates an independent validation context within the lazy assertion.
     *
     * @param mixed               $value          The value to validate
     * @param null|string         $propertyPath   Property path for error context (e.g., 'user.email')
     * @param null|Closure|string $defaultMessage Custom error message or callable that generates one
     *
     * @return static Returns this lazy assertion for method chaining
     */
    public function that(mixed $value, ?string $propertyPath = null, Closure|string|null $defaultMessage = null): static
    {
        $this->currentChainFailed = false;
        $this->thisChainTryAll = false;
        $assertClass = $this->assertClass;
        $this->currentChain = $assertClass::that($value, $defaultMessage, $propertyPath);

        return $this;
    }

    /**
     * Enable try-all mode for complete error collection.
     *
     * When called before any `that()` calls, affects all chains. When called after
     * a `that()` call, affects only the current chain. In try-all mode, all assertions
     * execute even after failures to collect maximum error information.
     *
     * @return static Returns this lazy assertion for method chaining
     */
    public function tryAll(): static
    {
        if (!$this->currentChain) {
            $this->alwaysTryAll = true;
        }

        $this->thisChainTryAll = true;

        return $this;
    }

    /**
     * Verify all assertions and throw if any failures occurred.
     *
     * Triggers validation by checking the collected errors. If any assertions
     * failed, throws a LazyAssertionException containing all failures. Returns
     * true if all assertions passed.
     *
     * @throws LazyAssertionException When one or more assertions failed
     * @return bool                   True if all assertions passed
     */
    public function verifyNow(): bool
    {
        /** @var callable(array<array-key, AssertionFailedException>): LazyAssertionException $callable */
        $callable = [$this->exceptionClass, 'fromErrors'];
        throw_if($this->errors, $callable($this->errors));

        return true;
    }

    /**
     * Set the Assert class used as factory for assertion chains.
     *
     * Allows customization of the assertion implementation. The provided class
     * must extend the base Assert class.
     *
     * @param string $className The Assert class name (must extend Assert)
     *
     * @throws LogicException When className doesn't extend Assert
     * @return self           Returns this lazy assertion for method chaining
     */
    public function setAssertClass(string $className): self
    {
        throw_if(Assert::class !== $className && !is_subclass_of($className, Assert::class), LogicException::class, $className.' is not (a subclass of) '.Assert::class);

        $this->assertClass = $className;

        return $this;
    }

    /**
     * Set the exception class thrown when validation fails.
     *
     * Allows customization of the exception type. The provided class must
     * extend LazyAssertionException.
     *
     * @param string $className The exception class name (must implement LazyAssertionExceptionInterface)
     *
     * @throws LogicException When className doesn't implement LazyAssertionExceptionInterface
     * @return self           Returns this lazy assertion for method chaining
     */
    public function setExceptionClass(string $className): self
    {
        throw_if(!is_subclass_of($className, LazyAssertionExceptionInterface::class), LogicException::class, $className.' does not implement '.LazyAssertionExceptionInterface::class);

        $this->exceptionClass = $className;

        return $this;
    }
}
