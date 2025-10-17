<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Assert\Assertions;

use Cline\Assert\AssertionFailedException;
use Closure;
use Exception;
use Throwable;

use function get_class;
use function sprintf;

/**
 * Custom callback-based assertion methods.
 *
 * Dependencies:
 * - TypeAssertions::isCallable() (for callback validation)
 * - Base::createException()
 * - Base::stringify()
 * - Base::generateMessage()
 *
 * @author Brian Faust <brian@cline.sh>
 */
trait CustomAssertions
{
    public const int INVALID_SATISFY = 217;

    public const int INVALID_THROWS = 245;

    /**
     * Assert that the provided value is valid according to a callback.
     *
     * If the callback returns `false` the assertion will fail.
     *
     * @param mixed                $value
     * @param callable             $callback
     * @param null|callable|string $message
     *
     * @throws AssertionFailedException
     */
    public static function satisfy($value, $callback, $message = null, ?string $propertyPath = null): bool
    {
        self::isCallable($callback);

        if (false === $callback($value)) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected value to pass custom rule. Got: %s'),
                static::stringify($value),
            );

            throw self::createException($value, $message, self::INVALID_SATISFY, $propertyPath);
        }

        return true;
    }

    /**
     * Assert that the closure throws an exception of the expected class.
     *
     * @param Closure              $expression
     * @param string               $class
     * @param null|callable|string $message
     *
     * @psalm-param class-string<Throwable> $class
     *
     * @throws AssertionFailedException
     */
    public static function throws(Closure $expression, string $class = 'Exception', $message = null, ?string $propertyPath = null): bool
    {
        self::string($class);

        $actual = 'none';

        try {
            $expression();
        } catch (Exception $e) {
            $actual = get_class($e);

            if ($e instanceof $class) {
                return true;
            }
        } catch (Throwable $e) {
            $actual = get_class($e);

            if ($e instanceof $class) {
                return true;
            }
        }

        $message = sprintf(
            self::generateMessage($message ?: 'Expected to throw "%2$s", got "%3$s"'),
            $expression,
            $class,
            $actual,
        );

        throw self::createException($expression, $message, self::INVALID_THROWS, $propertyPath, ['expected' => $class, 'actual' => $actual]);
    }
}
