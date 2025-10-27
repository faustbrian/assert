<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Assert\Matchers;

/**
 * Matches any value of a specific type.
 *
 * Usage: expect()->any('string'), expect()->any('integer')
 */
final readonly class AnyMatcher implements AsymmetricMatcher
{
    public function __construct(
        private string $type,
    ) {}

    public function matches(mixed $value): bool
    {
        return match ($this->type) {
            'string' => is_string($value),
            'integer', 'int' => is_int($value),
            'float', 'double' => is_float($value),
            'boolean', 'bool' => is_bool($value),
            'array' => is_array($value),
            'object' => is_object($value),
            'resource' => is_resource($value),
            'null' => $value === null,
            'numeric' => is_numeric($value),
            'scalar' => is_scalar($value),
            'callable' => is_callable($value),
            default => is_a($value, $this->type),
        };
    }

    public function toString(): string
    {
        return "any({$this->type})";
    }
}
