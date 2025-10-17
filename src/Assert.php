<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Assert;

/**
 * AssertionChain factory.
 *
 * @author Brian Faust <brian@cline.sh>
 */
abstract class Assert
{
    /** @var string */
    protected static $lazyAssertionExceptionClass = LazyAssertionException::class;

    /** @var string */
    protected static $assertionClass = Assertion::class;

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
     *  Assert::that($value)->notEmpty()->integer();
     *  Assert::that($value)->nullOr()->string()->startsWith("Foo");
     *
     * The assertion chain can be stateful, that means be careful when you reuse
     * it. You should never pass around the chain.
     */
    public static function that($value, $defaultMessage = null, ?string $defaultPropertyPath = null): AssertionChain
    {
        $assertionChain = new AssertionChain($value, $defaultMessage, $defaultPropertyPath);

        return $assertionChain->setAssertionClassName(static::$assertionClass);
    }

    /**
     * Start validation on a set of values, returns {@link AssertionChain}.
     *
     * @param mixed                $values
     * @param null|callable|string $defaultMessage
     */
    public static function thatAll($values, $defaultMessage = null, ?string $defaultPropertyPath = null): AssertionChain
    {
        return static::that($values, $defaultMessage, $defaultPropertyPath)->all();
    }

    /**
     * Start validation and allow NULL, returns {@link AssertionChain}.
     *
     * @param mixed                $value
     * @param null|callable|string $defaultMessage
     */
    public static function thatNullOr($value, $defaultMessage = null, ?string $defaultPropertyPath = null): AssertionChain
    {
        return static::that($value, $defaultMessage, $defaultPropertyPath)->nullOr();
    }

    /**
     * Create a lazy assertion object.
     */
    public static function lazy(): LazyAssertion
    {
        $lazyAssertion = new LazyAssertion();

        return $lazyAssertion
            ->setAssertClass(static::class)
            ->setExceptionClass(static::$lazyAssertionExceptionClass);
    }
}
