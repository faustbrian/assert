<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Assert\Matchers;

/**
 * Factory for creating asymmetric matchers with ergonomic syntax.
 *
 * Provides Jest/Vitest-style matcher factory methods that enable flexible,
 * partial matching in assertions. All methods return immutable matcher instances
 * that can be composed and nested for complex assertion patterns.
 *
 * Typically accessed via the expect() function's static method delegation:
 *
 * ```php
 * expect($data)->toEqual(['id' => expect()->any('int')]);
 * expect($user)->toMatchObject(['name' => expect()->stringContaining('John')]);
 * ```
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class MatcherFactory
{
    /**
     * Create a matcher for any value of a specific type.
     *
     * Enables type-based matching where the exact value is unknown but the
     * type must match. Supports both primitive types and class/interface names.
     *
     * @param string $type Type name ('string', 'int', 'array', 'object', 'callable',
     *                     'numeric', 'scalar', 'resource', 'null') or class name
     *
     * @return AnyMatcher Matcher instance for type-based assertions
     */
    public static function any(string $type): AnyMatcher
    {
        return new AnyMatcher($type);
    }

    /**
     * Create a matcher for any non-null value.
     *
     * Useful for verifying a value exists without constraints on type or content.
     * Only rejects null values.
     *
     * @return AnythingMatcher Matcher instance for non-null value assertions
     */
    public static function anything(): AnythingMatcher
    {
        return new AnythingMatcher();
    }

    /**
     * Create a matcher for strings containing a specific substring.
     *
     * Performs case-sensitive substring search. Useful for partial string
     * matching in assertions where exact equality is too strict.
     *
     * @param string $substring The substring that must appear in the target string
     *
     * @return StringContainingMatcher Matcher instance for substring assertions
     */
    public static function stringContaining(string $substring): StringContainingMatcher
    {
        return new StringContainingMatcher($substring);
    }

    /**
     * Create a matcher for arrays containing specified key-value pairs.
     *
     * Verifies that an array contains all specified keys with matching values,
     * while allowing additional keys. Supports nested asymmetric matchers for
     * complex, composable matching patterns.
     *
     * @param array<mixed> $subset Key-value pairs that must exist in the target array
     *
     * @return ArrayContainingMatcher Matcher instance for array subset assertions
     */
    public static function arrayContaining(array $subset): ArrayContainingMatcher
    {
        return new ArrayContainingMatcher($subset);
    }
}
