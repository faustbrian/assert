<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Assert\Exceptions;

use function count;
use function sprintf;

/**
 * Exception thrown when lazy assertions fail validation.
 *
 * Aggregates multiple assertion failures into a single exception, providing
 * a comprehensive error report when using lazy assertion mode. The exception
 * message includes all failures with their property paths and descriptions.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class LazyAssertionException extends InvalidArgumentException implements LazyAssertionExceptionInterface
{
    /**
     * Create a new lazy assertion exception.
     *
     * @param string                          $message Error message summarizing all failures
     * @param array<InvalidArgumentException> $errors  Collection of individual assertion failures
     */
    public function __construct(
        string $message,
        private readonly array $errors,
    ) {
        parent::__construct($message, 0);
    }

    /**
     * Create exception from a collection of assertion failures.
     *
     * Factory method that constructs a formatted error message listing all
     * assertion failures with their property paths and individual messages.
     *
     * @param array<InvalidArgumentException> $errors Collection of assertion failures
     *
     * @return static Lazy assertion exception containing all errors
     */
    public static function fromErrors(array $errors): static
    {
        $message = sprintf('The following %d assertions failed:', count($errors))."\n";

        $i = 1;

        foreach ($errors as $error) {
            $message .= sprintf("%d) %s: %s\n", $i++, $error->getPropertyPath(), $error->getMessage());
        }

        return new self($message, $errors);
    }

    /**
     * Get all individual assertion failure exceptions.
     *
     * Returns the collection of assertion failures that were accumulated
     * during lazy validation, allowing detailed error inspection.
     *
     * @return array<InvalidArgumentException> Array of assertion failure exceptions
     */
    public function getErrorExceptions(): array
    {
        return $this->errors;
    }
}
