<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Assert\Matchers;

use function array_key_exists;
use function is_array;
use function json_encode;

/**
 * Asymmetric matcher for arrays containing a subset of key-value pairs.
 *
 * Verifies that an array contains all specified keys with matching values,
 * while allowing additional keys not present in the subset. Supports nested
 * asymmetric matchers for complex, composable matching patterns.
 *
 * ```php
 * expect(['id' => 1, 'name' => 'John', 'age' => 30])
 *     ->toEqual(expect()->arrayContaining(['name' => 'John']));
 *
 * expect($response)->toEqual(expect()->arrayContaining([
 *     'user' => expect()->arrayContaining(['email' => expect()->any('string')])
 * ]));
 * ```
 *
 * @author Brian Faust <brian@cline.sh>
 *
 * @psalm-immutable
 */
final readonly class ArrayContainingMatcher implements AsymmetricMatcher
{
    /**
     * Create a new array subset matcher.
     *
     * @param array<mixed> $subset Key-value pairs that must exist in the target array.
     *                             Values can be primitives or nested asymmetric matchers.
     */
    public function __construct(
        private array $subset,
    ) {}

    /**
     * Check if the given value is an array containing all subset key-value pairs.
     *
     * Performs recursive matching for nested structures. When a subset value is
     * itself an asymmetric matcher, delegates matching to that matcher. Otherwise
     * uses strict equality (===) for value comparison.
     *
     * @param mixed $value The value to check, must be an array to match
     *
     * @return bool True if value is an array containing all subset pairs, false otherwise
     */
    public function matches(mixed $value): bool
    {
        if (!is_array($value)) {
            return false;
        }

        foreach ($this->subset as $key => $expectedValue) {
            if (!array_key_exists((string) $key, $value)) {
                return false;
            }

            if ($expectedValue instanceof AsymmetricMatcher) {
                if (!$expectedValue->matches($value[$key])) {
                    return false;
                }
            } elseif ($value[$key] !== $expectedValue) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get string representation of this matcher for error messages.
     *
     * @return string Human-readable matcher description with JSON-encoded subset
     */
    public function toString(): string
    {
        return 'arrayContaining('.json_encode($this->subset).')';
    }
}
