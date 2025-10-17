<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Assert\Assertions;

use Cline\Assert\AssertionFailedException;

use function is_dir;
use function is_file;
use function is_readable;
use function is_writable;
use function sprintf;

/**
 * File system assertion methods.
 *
 * Dependencies:
 * - TypeAssertions::string() (optional, for type checking)
 * - NullEmptyAssertions::notEmpty() (optional, for file())
 * - Base::createException()
 * - Base::stringify()
 * - Base::generateMessage()
 *
 * @author Brian Faust <brian@cline.sh>
 */
trait FileSystemAssertions
{
    public const int INVALID_FILE = 102;

    public const int INVALID_DIRECTORY = 101;

    public const int INVALID_READABLE = 103;

    public const int INVALID_WRITEABLE = 104;

    /**
     * Assert that a file exists.
     *
     * @param string               $value
     * @param null|callable|string $message
     *
     * @throws AssertionFailedException
     */
    public static function file($value, $message = null, ?string $propertyPath = null): bool
    {
        self::string($value, $message, $propertyPath);
        self::notEmpty($value, $message, $propertyPath);

        if (!is_file($value)) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected file to exist. Got: %s'),
                static::stringify($value),
            );

            throw self::createException($value, $message, self::INVALID_FILE, $propertyPath);
        }

        return true;
    }

    /**
     * Assert that a directory exists.
     *
     * @param string               $value
     * @param null|callable|string $message
     *
     * @throws AssertionFailedException
     */
    public static function directory($value, $message = null, ?string $propertyPath = null): bool
    {
        self::string($value, $message, $propertyPath);

        if (!is_dir($value)) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected a directory. Got: %s'),
                static::stringify($value),
            );

            throw self::createException($value, $message, self::INVALID_DIRECTORY, $propertyPath);
        }

        return true;
    }

    /**
     * Assert that the value is something readable.
     *
     * @param string               $value
     * @param null|callable|string $message
     *
     * @throws AssertionFailedException
     */
    public static function readable($value, $message = null, ?string $propertyPath = null): bool
    {
        self::string($value, $message, $propertyPath);

        if (!is_readable($value)) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected readable path. Got: %s'),
                static::stringify($value),
            );

            throw self::createException($value, $message, self::INVALID_READABLE, $propertyPath);
        }

        return true;
    }

    /**
     * Assert that the value is something writeable.
     *
     * @param string               $value
     * @param null|callable|string $message
     *
     * @throws AssertionFailedException
     */
    public static function writeable($value, $message = null, ?string $propertyPath = null): bool
    {
        self::string($value, $message, $propertyPath);

        if (!is_writable($value)) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected writable path. Got: %s'),
                static::stringify($value),
            );

            throw self::createException($value, $message, self::INVALID_WRITEABLE, $propertyPath);
        }

        return true;
    }
}
