<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Assert\Assertions;

use ReflectionClass;
use Throwable;

use function array_key_exists;
use function debug_backtrace;
use function get_resource_type;
use function gettype;
use function is_array;
use function is_bool;
use function is_callable;
use function is_object;
use function is_resource;
use function is_scalar;
use function mb_strlen;
use function mb_substr;
use function sprintf;

/**
 * Core infrastructure methods for assertions.
 *
 * Provides reusable helper methods used across all assertion trait categories:
 * - Exception creation with standardized structure
 * - Value stringification for human-readable error messages
 * - Dynamic message generation with callable support
 *
 * This trait is used by AbstractAssertion and should not be used directly.
 *
 * @author Brian Faust <brian@cline.sh>
 *
 * @internal
 */
trait AssertionInfrastructure
{
    /**
     * Create an assertion exception with standardized structure.
     *
     * Factory method that instantiates the configured exception class with
     * consistent parameters including value, constraints, and property path context.
     *
     * @param mixed        $value        The value that failed validation
     * @param string       $message      Error message describing the failure
     * @param int          $code         Validation error code (from ValidationError enum)
     * @param null|string  $propertyPath Property path providing context (e.g., 'user.email')
     * @param array<mixed> $constraints  Constraint values used in the assertion (e.g., min/max limits)
     *
     * @return Throwable The configured exception instance
     */
    protected static function createException($value, $message, $code, $propertyPath = null, array $constraints = []): Throwable
    {
        $exceptionClass = static::$exceptionClass;

        /** @var Throwable $exception */
        $exception = new $exceptionClass($message, $code, $propertyPath, $value, $constraints);

        return $exception;
    }

    /**
     * Convert any value to a human-readable string representation.
     *
     * Transforms values into concise string representations suitable for error messages.
     * Long strings are truncated to 100 characters. Special handling for various types:
     * - Booleans: `<TRUE>` or `<FALSE>`
     * - Null: `<NULL>`
     * - Arrays: `<ARRAY>`
     * - Objects: Fully qualified class name
     * - Resources: Resource type name
     * - Scalars: String representation (truncated if needed)
     *
     * @param mixed $value The value to stringify
     *
     * @return string Human-readable string representation
     */
    protected static function stringify($value): string
    {
        $result = gettype($value);

        if (is_bool($value)) {
            $result = $value ? '<TRUE>' : '<FALSE>';
        } elseif (is_scalar($value)) {
            $val = (string) $value;

            if (mb_strlen($val) > 100) {
                $val = mb_substr($val, 0, 97).'...';
            }

            $result = $val;
        } elseif (is_array($value)) {
            $result = '<ARRAY>';
        } elseif (is_object($value)) {
            $result = $value::class;
        } elseif (is_resource($value)) {
            $result = get_resource_type($value);
        } elseif (null === $value) {
            $result = '<NULL>';
        }

        return $result;
    }

    /**
     * Generate the final error message from string or callable.
     *
     * If message is a callable, invokes it with assertion metadata including:
     * - All assertion parameters and their values
     * - `::assertion` key containing the fully qualified method signature
     *
     * This allows dynamic error messages that adapt to assertion context.
     *
     * @param null|callable|string $message String template or callable that generates message
     *
     * @return string The final error message to display
     */
    protected static function generateMessage($message): string
    {
        if (is_callable($message)) {
            $traces = debug_backtrace(0);

            $parameters = [];

            try {
                /** @var class-string<static> $className */
                $className = static::class;
                $functionName = $traces[1]['function'] ?? '';
                $args = $traces[1]['args'] ?? [];

                $reflection = new ReflectionClass($className);
                $method = $reflection->getMethod($functionName);

                foreach ($method->getParameters() as $index => $parameter) {
                    if ('message' === $parameter->getName()) {
                        continue;
                    }

                    $parameters[$parameter->getName()] = array_key_exists($index, $args)
                        ? $args[$index]
                        : $parameter->getDefaultValue();
                }

                $type = $traces[1]['type'] ?? '';
                $parameters['::assertion'] = sprintf('%s%s%s', $className, $type, $functionName);

                $message = $message(...[$parameters]);
            } // @codeCoverageIgnoreStart
            catch (Throwable $exception) {
                $message = sprintf('Unable to generate message : %s', $exception->getMessage());
            } // @codeCoverageIgnoreEnd
        }

        /** @var string $message */
        return (string) $message;
    }
}
