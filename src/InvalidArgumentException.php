<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Assert;

/**
 * @author Brian Faust <brian@cline.sh>
 */
class InvalidArgumentException extends \InvalidArgumentException implements AssertionFailedException
{
    /**
     * @param mixed        $message
     * @param mixed        $code
     * @param mixed        $value
     * @param array<mixed> $constraints
     */
    public function __construct(
        $message,
        $code,
        private readonly ?string $propertyPath = null,
        private $value = null,
        private readonly array $constraints = [],
    ) {
        $messageString = is_string($message) ? $message : (is_scalar($message) ? (string) $message : '');
        $codeInt = is_int($code) ? $code : (is_numeric($code) ? (int) $code : 0);

        parent::__construct($messageString, $codeInt);
    }

    /**
     * User controlled way to define a sub-property causing
     * the failure of a currently asserted objects.
     *
     * Useful to transport information about the nature of the error
     * back to higher layers.
     */
    public function getPropertyPath(): ?string
    {
        return $this->propertyPath;
    }

    /**
     * Get the value that caused the assertion to fail.
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Get the constraints that applied to the failed assertion.
     *
     * @return array<mixed>
     */
    public function getConstraints(): array
    {
        return $this->constraints;
    }
}
