<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Assert\Exceptions;

use Throwable;

/**
 * Contract for exceptions thrown when assertions fail.
 *
 * Defines the interface that all assertion failure exceptions must implement,
 * providing access to validation context including the invalid value, property
 * path, and constraint information for detailed error reporting.
 *
 * @author Brian Faust <brian@cline.sh>
 */
interface AssertionFailedException extends Throwable
{
    /**
     * Get the property path where the assertion failed.
     *
     * Returns the contextual path to the property that failed validation,
     * useful for nested object validation (e.g., 'user.profile.email').
     *
     * @return null|string Property path or null if not specified
     */
    public function getPropertyPath(): ?string;

    /**
     * Get the value that caused the assertion to fail.
     *
     * Returns the actual value that was validated and failed the assertion,
     * useful for debugging and error reporting.
     *
     * @return mixed The invalid value
     */
    public function getValue(): mixed;

    /**
     * Get the constraint values that were applied in the failed assertion.
     *
     * Returns an array of constraint parameters (e.g., min/max values, regex patterns)
     * that were used during validation, providing context about why the assertion failed.
     *
     * @return array<mixed> Array of constraint values
     */
    public function getConstraints(): array;
}
