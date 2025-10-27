<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Assert\Matchers;

/**
 * Matches any value except null.
 *
 * Usage: expect()->anything()
 */
final readonly class AnythingMatcher implements AsymmetricMatcher
{
    public function matches(mixed $value): bool
    {
        return $value !== null;
    }

    public function toString(): string
    {
        return 'anything()';
    }
}
