<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Assert\Matchers;

/**
 * Base interface for asymmetric matchers used in partial equality assertions.
 *
 * Asymmetric matchers enable flexible, pattern-based assertions without requiring
 * exact equality. They allow matching on properties like type, substring presence,
 * or structural subset inclusion rather than concrete values.
 *
 * Inspired by Jest/Vitest testing frameworks where matchers like expect.any(),
 * expect.stringContaining(), and expect.arrayContaining() provide ergonomic
 * ways to assert on partial data structures.
 *
 * Implementations should be immutable and stateless to ensure predictable
 * matching behavior across multiple assertions.
 *
 * @author Brian Faust <brian@cline.sh>
 */
interface AsymmetricMatcher
{
    /**
     * Check if the given value matches this matcher's criteria.
     *
     * Implementations should return true when the value satisfies the matcher's
     * pattern or constraint, and false otherwise. This method must be pure and
     * not modify any state.
     *
     * @param mixed $value The value to test against this matcher's criteria
     *
     * @return bool True if value matches, false otherwise
     */
    public function matches(mixed $value): bool;

    /**
     * Get a string representation of this matcher for error messages.
     *
     * Used to provide helpful debugging information when assertions fail.
     * Should return a concise, human-readable description of the matcher's
     * criteria, typically matching the factory method syntax.
     *
     * @return string Human-readable matcher description (e.g., "any(string)", "stringContaining('foo')")
     */
    public function toString(): string;
}
