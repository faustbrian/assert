<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Assert\Matchers;

use function is_a;
use function is_array;
use function is_bool;
use function is_callable;
use function is_float;
use function is_int;
use function is_numeric;
use function is_object;
use function is_resource;
use function is_scalar;
use function is_string;
use function sprintf;

/**
 * Asymmetric matcher for type-based value matching.
 *
 * Provides flexible type checking for assertions where the exact value is unknown
 * but the type must match. Supports both PHP primitive types (string, int, etc.)
 * and class/interface names for instanceof checks.
 *
 * ```php
 * expect(['id' => 123])->toEqual(['id' => expect()->any('int')]);
 * expect($user)->toEqual(['name' => expect()->any('string'), 'role' => expect()->any(Role::class)]);
 * ```
 *
 * @author Brian Faust <brian@cline.sh>
 *
 * @psalm-immutable
 */
final readonly class AnyMatcher implements AsymmetricMatcher
{
    /**
     * Create a new type matcher for the specified type.
     *
     * @param string $type Type name ('string', 'int', 'bool', 'array', 'object', 'callable',
     *                     'numeric', 'scalar', 'resource', 'null') or fully qualified class name
     */
    public function __construct(
        private string $type,
    ) {}

    /**
     * Check if the given value matches the specified type.
     *
     * Performs type checking using PHP's native type functions for primitives,
     * and instanceof checks for class/interface names. The default case handles
     * custom classes by delegating to is_a() for class hierarchy checks.
     *
     * @param mixed $value The value to check against the type constraint
     *
     * @return bool True if value matches the type, false otherwise
     */
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
            // @phpstan-ignore-next-line argument.type
            default => (is_object($value) || is_string($value)) && is_a($value, $this->type),
        };
    }

    /**
     * Get string representation of this matcher for error messages.
     *
     * @return string Human-readable matcher description
     */
    public function toString(): string
    {
        return sprintf('any(%s)', $this->type);
    }
}
