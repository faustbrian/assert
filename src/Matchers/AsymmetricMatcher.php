<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Assert\Matchers;

/**
 * Base interface for asymmetric matchers used in partial equality checks.
 *
 * Asymmetric matchers allow flexible assertions without requiring exact equality.
 * Inspired by Jest/Vitest expect.any(), expect.stringContaining(), etc.
 *
 * @author Brian Faust <brian@cline.sh>
 */
interface AsymmetricMatcher
{
    /**
     * Check if the given value matches this matcher's criteria.
     */
    public function matches(mixed $value): bool;

    /**
     * Get a string representation of this matcher for error messages.
     */
    public function toString(): string;
}
