<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Assert\Matchers;

/**
 * Asymmetric matcher that accepts any non-null value.
 *
 * Provides a simple matcher for assertions where you want to verify a value
 * exists but don't care about its specific type or content. Only rejects null.
 *
 * ```php
 * expect(['status' => 'active'])->toEqual(['status' => expect()->anything()]);
 * expect($response)->toMatchObject(['id' => expect()->anything(), 'name' => 'John']);
 * ```
 *
 * @author Brian Faust <brian@cline.sh>
 *
 * @psalm-immutable
 */
final readonly class AnythingMatcher implements AsymmetricMatcher
{
    /**
     * Check if the given value is non-null.
     *
     * @param mixed $value The value to check
     *
     * @return bool True if value is not null, false otherwise
     */
    public function matches(mixed $value): bool
    {
        return $value !== null;
    }

    /**
     * Get string representation of this matcher for error messages.
     *
     * @return string Human-readable matcher description
     */
    public function toString(): string
    {
        return 'anything()';
    }
}
