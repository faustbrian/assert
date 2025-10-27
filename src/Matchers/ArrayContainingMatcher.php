<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Assert\Matchers;

use function json_encode;

/**
 * Matches arrays containing all specified key-value pairs.
 *
 * Usage: expect()->arrayContaining(['key' => 'value'])
 */
final readonly class ArrayContainingMatcher implements AsymmetricMatcher
{
    public function __construct(
        private array $subset,
    ) {}

    public function matches(mixed $value): bool
    {
        if (!is_array($value)) {
            return false;
        }

        foreach ($this->subset as $key => $expectedValue) {
            if (!array_key_exists($key, $value)) {
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

    public function toString(): string
    {
        return 'arrayContaining('.json_encode($this->subset).')';
    }
}
