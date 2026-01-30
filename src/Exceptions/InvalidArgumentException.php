<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Assert\Exceptions;

use Cline\Assert\Exceptions\AssertionFailedException;

use function is_int;
use function is_numeric;
use function is_scalar;
use function is_string;

/**
 * Standard exception thrown when assertions fail.
 *
 * Extends PHP's InvalidArgumentException while implementing AssertionFailedException
 * interface to provide rich validation context including property paths, constraint
 * values, and the invalid value itself.
 *
 * @author Brian Faust <brian@cline.sh>
 */
abstract class InvalidArgumentException extends \InvalidArgumentException implements AssertionFailedException
{
    /**
     * Create a new assertion failure exception.
     *
     * @param mixed        $message      Error message or value to convert to string
     * @param mixed        $code         Error code (converted to int, typically from ValidationError enum)
     * @param null|string  $propertyPath Property path providing context (e.g., 'user.email')
     * @param mixed        $value        The value that failed validation
     * @param array<mixed> $constraints  Constraint parameters used in the assertion (e.g., min/max values)
     */
    public function __construct(
        mixed $message,
        mixed $code,
        private readonly ?string $propertyPath = null,
        private readonly mixed $value = null,
        private readonly array $constraints = [],
    ) {
        $messageString = is_string($message) ? $message : (is_scalar($message) ? (string) $message : '');
        $codeInt = is_int($code) ? $code : (is_numeric($code) ? (int) $code : 0);

        parent::__construct($messageString, $codeInt);
    }

    /**
     * Get the property path where validation failed.
     *
     * Returns the hierarchical path to the property that failed validation,
     * enabling precise error location in nested object structures and providing
     * context for error reporting to higher application layers.
     *
     * @return null|string Property path (e.g., 'user.profile.email') or null
     */
    public function getPropertyPath(): ?string
    {
        return $this->propertyPath;
    }

    /**
     * Get the value that caused the assertion to fail.
     *
     * Returns the actual value that was tested and failed validation,
     * useful for debugging and displaying context-aware error messages.
     *
     * @return mixed The invalid value
     */
    public function getValue(): mixed
    {
        return $this->value;
    }

    /**
     * Get the constraint parameters that were applied.
     *
     * Returns the validation constraint values (such as min/max bounds,
     * regex patterns, or allowed choices) that were used when the assertion
     * failed, providing full context about validation requirements.
     *
     * @return array<mixed> Array of constraint parameters
     */
    public function getConstraints(): array
    {
        return $this->constraints;
    }
}
