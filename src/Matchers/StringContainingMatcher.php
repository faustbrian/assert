<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Assert\Matchers;

use function str_contains;

/**
 * Matches strings containing a specific substring.
 *
 * Usage: expect()->stringContaining('foo')
 */
final readonly class StringContainingMatcher implements AsymmetricMatcher
{
    public function __construct(
        private string $substring,
    ) {}

    public function matches(mixed $value): bool
    {
        if (!is_string($value)) {
            return false;
        }

        return str_contains($value, $this->substring);
    }

    public function toString(): string
    {
        return "stringContaining('{$this->substring}')";
    }
}
