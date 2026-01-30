<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Assert;

use Cline\Assert\Assert;
use Cline\Assert\Assertions\AssertionChain;
use Cline\Assert\Assertions\LazyAssertion;
use Cline\Assert\Expectations\Expectation;
use Closure;
use Deprecated;

/**
 * Start validation on a value using fluent assertion chain syntax.
 *
 * Creates an AssertionChain instance that provides method chaining for
 * multiple assertions on a single value. The chain is stateful and should
 * not be reused or passed around between different validation contexts.
 *
 * This function provides the traditional Assert-style API for validation.
 * For Jest/Vitest-style expectations, use the expect() function instead.
 *
 * ```php
 * that($value)->notEmpty()->integer()->greaterThan(0);
 * that($email)->nullOr()->string()->email();
 * ```
 *
 * @param mixed               $value               The value to validate
 * @param null|Closure|string $defaultMessage      Default error message or callable returning message
 * @param null|string         $defaultPropertyPath Property path for error reporting context
 *
 * @return AssertionChain Fluent assertion chain for the value
 */
function that(mixed $value, Closure|string|null $defaultMessage = null, ?string $defaultPropertyPath = null): AssertionChain
{
    return Assert::that($value, $defaultMessage, $defaultPropertyPath);
}

/**
 * Start validation on a set of values, applying assertions to each element.
 *
 * Creates an AssertionChain that applies all subsequent assertions to every
 * value in the collection. Each assertion must pass for all values, or the
 * chain throws an exception identifying the failing element.
 *
 * ```php
 * thatAll([1, 2, 3])->integer()->greaterThan(0);
 * thatAll($users)->isInstanceOf(User::class)->satisfy(fn($u) => $u->isActive());
 * ```
 *
 * @param mixed               $values              Collection of values to validate (array or iterable)
 * @param null|Closure|string $defaultMessage      Default error message or callable returning message
 * @param null|string         $defaultPropertyPath Property path for error reporting context
 *
 * @return AssertionChain Fluent assertion chain for the collection
 */
function thatAll(mixed $values, Closure|string|null $defaultMessage = null, ?string $defaultPropertyPath = null): AssertionChain
{
    return Assert::thatAll($values, $defaultMessage, $defaultPropertyPath);
}

/**
 * Start validation that allows NULL values to pass through.
 *
 * Creates an AssertionChain where all assertions are skipped if the value
 * is null. Non-null values proceed through the normal assertion chain.
 * Useful for optional fields that may be null but have constraints when present.
 *
 * ```php
 * thatNullOr($optionalEmail)->string()->email();
 * thatNullOr($age)->integer()->between(0, 150);
 * ```
 *
 * @param  mixed               $value               The value to validate (may be null)
 * @param  null|Closure|string $defaultMessage      Default error message or callable returning message
 * @param  null|string         $defaultPropertyPath Property path for error reporting context
 * @return AssertionChain      Fluent assertion chain that allows null
 */
#[Deprecated(message: 'Use Assert::thatNullOr() instead for better IDE support')]
function thatNullOr(mixed $value, Closure|string|null $defaultMessage = null, ?string $defaultPropertyPath = null): AssertionChain
{
    return Assert::thatNullOr($value, $defaultMessage, $defaultPropertyPath);
}

/**
 * Create a lazy assertion object for deferred validation.
 *
 * LazyAssertion accumulates multiple assertions without throwing immediately,
 * then evaluates all at once via verifyNow(). This enables comprehensive
 * error reporting showing all validation failures instead of just the first.
 *
 * Useful for form validation where you want to display all errors at once
 * rather than stopping at the first failure.
 *
 * ```php
 * $lazy = lazy();
 * $lazy->that($email, 'email')->email();
 * $lazy->that($age, 'age')->integer()->between(0, 150);
 * $lazy->verifyNow(); // Throws with all errors if any failed
 * ```
 *
 * @return LazyAssertion Lazy assertion accumulator
 */
function lazy(): LazyAssertion
{
    return Assert::lazy();
}

/**
 * Start a Jest/Vitest/Pest-style expectation chain for fluent assertions.
 *
 * Creates an Expectation instance providing modern testing framework syntax
 * with toXxx() methods, negation via ->not, logical operators (->or, ->xor),
 * collection quantifiers (->all, ->any, ->none), and asymmetric matchers.
 *
 * When called without arguments, enables static access to asymmetric matcher
 * factory methods via magic method delegation:
 * ```php
 * expect()->any('string')
 * expect()->anything()
 * expect()->stringContaining('test')
 * expect()->arrayContaining(['key' => 'value'])
 * ```
 *
 * When called with a value, creates an Expectation for assertion chaining:
 * ```php
 * expect($value)->toBeString()->toStartWith('Hello');
 * expect($value)->not->toBeNull();
 * expect($items)->toHaveCount(3);
 * expect($user)->toMatchObject(['name' => 'John', 'age' => expect()->any('int')]);
 * ```
 *
 * @param mixed $value Optional value to create expectations for. Pass null to access
 *                     static matcher factory methods.
 *
 * @return Expectation Fluent expectation instance
 */
function expect(mixed $value = null): Expectation
{
    return new Expectation($value);
}
