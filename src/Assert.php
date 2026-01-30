<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Assert;

use Cline\Assert\Assertions\Assertion;
use Cline\Assert\Assertions\AssertionChain;
use Cline\Assert\Assertions\LazyAssertion;
use Cline\Assert\Exceptions\LazyAssertionException;
use Closure;

/**
 * Static factory for creating assertion chains and lazy assertions.
 *
 * Provides a fluent interface for value validation through static factory methods.
 * This abstract class should be extended to customize exception and assertion classes.
 *
 * ```php
 * Assert::that($email)->email()->contains('@example.com');
 * Assert::thatAll($values)->integer()->greaterThan(0);
 * Assert::thatNullOr($name)->string()->notEmpty();
 * ```
 *
 * @author Brian Faust <brian@cline.sh>
 */
abstract class Assert
{
    /**
     * Exception class used for lazy assertion failures.
     *
     * @var class-string<LazyAssertionException>
     */
    protected static string $lazyAssertionExceptionClass = LazyAssertionException::class;

    /**
     * Assertion class used for creating assertion chains.
     *
     * @var class-string<Assertion>
     */
    protected static string $assertionClass = Assertion::class;

    /**
     * Start validation on a value, returns an assertion chain.
     *
     * Creates a fluent assertion chain for validating a single value. The chain
     * is stateful and should not be reused or passed around after creation.
     *
     * ```php
     * Assert::that($value)->notEmpty()->integer();
     * Assert::that($value)->nullOr()->string()->startsWith("Foo");
     * ```
     *
     * @param mixed               $value               The value to validate
     * @param null|Closure|string $defaultMessage      Custom error message or callable that generates one
     * @param null|string         $defaultPropertyPath Property path for error context (e.g., 'user.email')
     *
     * @return AssertionChain Fluent assertion chain for the value
     */
    public static function that(mixed $value, Closure|string|null $defaultMessage = null, ?string $defaultPropertyPath = null): AssertionChain
    {
        $assertionChain = new AssertionChain($value, $defaultMessage, $defaultPropertyPath);

        return $assertionChain->setAssertionClassName(static::$assertionClass);
    }

    /**
     * Start validation on a set of values, applying assertions to all elements.
     *
     * Creates an assertion chain that validates each element in an array or traversable.
     * Equivalent to calling `Assert::that($values)->all()`.
     *
     * ```php
     * Assert::thatAll([1, 2, 3])->integer()->greaterThan(0);
     * ```
     *
     * @param mixed               $values              Array or traversable to validate
     * @param null|Closure|string $defaultMessage      Custom error message or callable that generates one
     * @param null|string         $defaultPropertyPath Property path for error context
     *
     * @return AssertionChain Fluent assertion chain configured for array validation
     */
    public static function thatAll(mixed $values, Closure|string|null $defaultMessage = null, ?string $defaultPropertyPath = null): AssertionChain
    {
        return static::that($values, $defaultMessage, $defaultPropertyPath)->all();
    }

    /**
     * Start validation allowing NULL values, bypassing assertions if value is null.
     *
     * Creates an assertion chain that short-circuits if the value is null, allowing
     * optional value validation. Equivalent to calling `Assert::that($value)->nullOr()`.
     *
     * ```php
     * Assert::thatNullOr($optionalEmail)->string()->email();
     * ```
     *
     * @param mixed               $value               The value to validate (null allowed)
     * @param null|Closure|string $defaultMessage      Custom error message or callable that generates one
     * @param null|string         $defaultPropertyPath Property path for error context
     *
     * @return AssertionChain Fluent assertion chain configured to allow nulls
     */
    public static function thatNullOr(mixed $value, Closure|string|null $defaultMessage = null, ?string $defaultPropertyPath = null): AssertionChain
    {
        return static::that($value, $defaultMessage, $defaultPropertyPath)->nullOr();
    }

    /**
     * Create a lazy assertion object for deferred validation.
     *
     * Lazy assertions collect multiple validation failures before throwing,
     * allowing you to gather all errors at once rather than failing fast.
     * Call `verifyNow()` to trigger validation and throw if any assertions failed.
     *
     * ```php
     * Assert::lazy()
     *     ->that($email, 'email')->email()
     *     ->that($age, 'age')->integer()->greaterThan(0)
     *     ->verifyNow();
     * ```
     *
     * @return LazyAssertion Lazy assertion builder for collecting validation errors
     */
    public static function lazy(): LazyAssertion
    {
        $lazyAssertion = new LazyAssertion();

        return $lazyAssertion
            ->setAssertClass(static::class)
            ->setExceptionClass(static::$lazyAssertionExceptionClass);
    }
}
