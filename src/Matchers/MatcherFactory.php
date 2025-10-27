<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Assert\Matchers;

/**
 * Factory for creating asymmetric matchers.
 *
 * Provides Jest/Vitest-style matcher creation functions.
 */
final class MatcherFactory
{
    /**
     * Match any value of a specific type.
     *
     * @param string $type Type name ('string', 'int', 'array', etc.) or class name
     */
    public static function any(string $type): AnyMatcher
    {
        return new AnyMatcher($type);
    }

    /**
     * Match any non-null value.
     */
    public static function anything(): AnythingMatcher
    {
        return new AnythingMatcher();
    }

    /**
     * Match strings containing a substring.
     */
    public static function stringContaining(string $substring): StringContainingMatcher
    {
        return new StringContainingMatcher($substring);
    }

    /**
     * Match arrays containing specified key-value pairs.
     */
    public static function arrayContaining(array $subset): ArrayContainingMatcher
    {
        return new ArrayContainingMatcher($subset);
    }
}
