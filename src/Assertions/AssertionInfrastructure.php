<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Assert\Assertions;

use Cline\Assert\AssertionFailedException;
use ReflectionClass;
use Throwable;
use Traversable;

use function array_key_exists;
use function assert;
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
 * Provides helper methods used across all assertion traits:
 * - createException(): Exception factory
 * - stringify(): Value stringification for error messages
 * - generateMessage(): Message generation with callable support
 *
 * @author Brian Faust <brian@cline.sh>
 */
trait AssertionInfrastructure
{
    /**
     * Create an assertion exception.
     *
     * @param mixed        $value
     * @param string       $message
     * @param int          $code
     * @param null|string  $propertyPath
     * @param array<mixed> $constraints
     */
    protected static function createException($value, $message, $code, $propertyPath = null, array $constraints = []): Throwable
    {
        $exceptionClass = static::$exceptionClass;
        $exception = new $exceptionClass($message, $code, $propertyPath, $value, $constraints);

        assert($exception instanceof Throwable);

        return $exception;
    }

    /**
     * Make a string version of a value.
     *
     * @param mixed $value
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
     * Generate the message.
     *
     * @param null|callable|string $message
     */
    protected static function generateMessage($message): string
    {
        if (is_callable($message)) {
            $traces = debug_backtrace(0);

            $parameters = [];

            try {
                /** @var class-string $className */
                $className = $traces[1]['class'] ?? '';
                $functionName = $traces[1]['function'] ?? '';
                $args = $traces[1]['args'] ?? [];

                $reflection = new ReflectionClass($className);
                $method = $reflection->getMethod($functionName);

                foreach ($method->getParameters() as $index => $parameter) {
                    if ('message' !== $parameter->getName()) {
                        $parameters[$parameter->getName()] = array_key_exists($index, $args)
                            ? $args[$index]
                            : $parameter->getDefaultValue();
                    }
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

    /**
     * Assert that value is an array or a traversable object.
     *
     * Required by AbstractAssertion::__callStatic for all* variants.
     *
     * @param mixed                $value
     * @param null|callable|string $message
     *
     * @phpstan-assert iterable<array-key, mixed> $value
     *
     * @psalm-assert iterable $value
     *
     * @throws AssertionFailedException
     */
    protected static function isTraversable($value, $message = null, ?string $propertyPath = null): bool
    {
        if (!is_array($value) && !$value instanceof Traversable) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected a traversable. Got: %s'),
                static::stringify($value),
            );

            throw self::createException($value, $message, 44, $propertyPath);
        }

        return true;
    }
}
