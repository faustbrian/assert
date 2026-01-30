<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Assert\Matchers;

use function is_string;
use function sprintf;
use function str_contains;

/**
 * Asymmetric matcher for strings containing a specific substring.
 *
 * Enables partial string matching in assertions without requiring exact equality.
 * Performs case-sensitive substring search using PHP's native str_contains().
 *
 * ```php
 * expect('Hello, World!')->toEqual(expect()->stringContaining('World'));
 * expect($message)->toMatchObject(['subject' => expect()->stringContaining('Invoice')]);
 * ```
 *
 * @author Brian Faust <brian@cline.sh>
 *
 * @psalm-immutable
 */
final readonly class StringContainingMatcher implements AsymmetricMatcher
{
    /**
     * Create a new substring matcher.
     *
     * @param string $substring The case-sensitive substring that must appear in the target string
     */
    public function __construct(
        private string $substring,
    ) {}

    /**
     * Check if the given value is a string containing the specified substring.
     *
     * Performs case-sensitive substring search. Returns false for non-string values.
     *
     * @param mixed $value The value to check, must be a string to match
     *
     * @return bool True if value is a string containing the substring, false otherwise
     */
    public function matches(mixed $value): bool
    {
        if (!is_string($value)) {
            return false;
        }

        return str_contains($value, $this->substring);
    }

    /**
     * Get string representation of this matcher for error messages.
     *
     * @return string Human-readable matcher description with the substring
     */
    public function toString(): string
    {
        return sprintf("stringContaining('%s')", $this->substring);
    }
}
