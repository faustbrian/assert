<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Assert\Exceptions;

/**
 * Contract for lazy assertion exceptions that aggregate multiple failures.
 *
 * Defines the interface for exceptions that collect and report multiple
 * assertion failures from lazy validation mode.
 *
 * @author Brian Faust <brian@cline.sh>
 */
interface LazyAssertionExceptionInterface
{
    /**
     * Create exception from a collection of assertion failures.
     *
     * @param array<InvalidArgumentException> $errors Collection of assertion failures
     *
     * @return static Lazy assertion exception containing all errors
     */
    public static function fromErrors(array $errors): static;

    /**
     * Get all individual assertion failure exceptions.
     *
     * @return array<InvalidArgumentException> Array of assertion failure exceptions
     */
    public function getErrorExceptions(): array;
}
