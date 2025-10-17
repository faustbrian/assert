<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Assert;

use Deprecated;

/**
 * Start validation on a value, returns {@link AssertionChain}.
 *
 * The invocation of this method starts an assertion chain
 * that is happening on the passed value.
 *
 * @param mixed                $value
 * @param null|callable|string $defaultMessage
 *
 * @example
 *
 *  \Assert\that($value)->notEmpty()->integer();
 *  \Assert\that($value)->nullOr()->string()->startsWith("Foo");
 *
 * The assertion chain can be stateful, that means be careful when you reuse
 * it. You should never pass around the chain.
 */
function that($value, $defaultMessage = null, ?string $defaultPropertyPath = null): AssertionChain
{
    return Assert::that($value, $defaultMessage, $defaultPropertyPath);
}

/**
 * Start validation on a set of values, returns {@link AssertionChain}.
 *
 * @param mixed                $values
 * @param null|callable|string $defaultMessage
 */
function thatAll($values, $defaultMessage = null, ?string $defaultPropertyPath = null): AssertionChain
{
    return Assert::thatAll($values, $defaultMessage, $defaultPropertyPath);
}

/**
 * Start validation and allow NULL, returns {@link AssertionChain}.
 *
 * @param mixed                $value
 * @param null|callable|string $defaultMessage
 */
#[Deprecated(message: 'In favour of Assert::thatNullOr($value, $defaultMessage = null, $defaultPropertyPath = null)')]
function thatNullOr($value, $defaultMessage = null, ?string $defaultPropertyPath = null): AssertionChain
{
    return Assert::thatNullOr($value, $defaultMessage, $defaultPropertyPath);
}

/**
 * Create a lazy assertion object.
 */
function lazy(): LazyAssertion
{
    return Assert::lazy();
}
