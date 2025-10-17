<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Assert\Assertions;

use Cline\Assert\AssertionFailedException;

use const PHP_VERSION;

use function defined;
use function extension_loaded;
use function in_array;
use function phpversion;
use function sprintf;
use function throw_if;
use function version_compare;

/**
 * Environment and system assertion methods.
 *
 * Dependencies:
 * - NullEmptyAssertions::notEmpty() (for version())
 * - Base::createException()
 * - Base::stringify()
 * - Base::generateMessage()
 *
 * @author Brian Faust <brian@cline.sh>
 */
trait EnvironmentAssertions
{
    public const int INVALID_EXTENSION = 222;

    public const int INVALID_CONSTANT = 221;

    public const int INVALID_VERSION = 223;

    /**
     * Assert that extension is loaded.
     *
     * @param mixed                $value
     * @param null|callable|string $message
     *
     * @throws AssertionFailedException
     */
    public static function extensionLoaded($value, $message = null, ?string $propertyPath = null): bool
    {
        /** @var string $value */
        if (!extension_loaded($value)) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected extension to be loaded. Got: %s'),
                static::stringify($value),
            );

            throw self::createException($value, $message, self::INVALID_EXTENSION, $propertyPath);
        }

        return true;
    }

    /**
     * @param string               $version1
     * @param string               $operator
     * @param string               $version2
     * @param null|callable|string $message
     */
    public static function version($version1, $operator, $version2, $message = null, ?string $propertyPath = null): bool
    {
        self::notEmpty($operator, 'versionCompare operator is required and cannot be empty.');

        if (in_array(version_compare($version1, $version2, $operator), [0, false, null], true)) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected version %2$s %3$s. Got: %s'),
                static::stringify($version1),
                static::stringify($operator),
                static::stringify($version2),
            );

            throw self::createException($version1, $message, self::INVALID_VERSION, $propertyPath, ['operator' => $operator, 'version' => $version2]);
        }

        return true;
    }

    /**
     * Assert on PHP version.
     *
     * @param string               $operator
     * @param string               $version
     * @param null|callable|string $message
     *
     * @throws AssertionFailedException
     */
    public static function phpVersion($operator, $version, $message = null, ?string $propertyPath = null): bool
    {
        self::defined('PHP_VERSION');

        return self::version(PHP_VERSION, $operator, $version, $message, $propertyPath);
    }

    /**
     * Assert that extension is loaded and a specific version is installed.
     *
     * @param string               $extension
     * @param string               $operator
     * @param string               $version
     * @param null|callable|string $message
     *
     * @throws AssertionFailedException
     */
    public static function extensionVersion($extension, $operator, $version, $message = null, ?string $propertyPath = null): bool
    {
        self::extensionLoaded($extension, $message, $propertyPath);

        $extensionVersion = phpversion($extension);
        throw_if($extensionVersion === false, self::createException($extension, 'Unable to determine extension version.', self::INVALID_VERSION, $propertyPath));

        return self::version($extensionVersion, $operator, $version, $message, $propertyPath);
    }

    /**
     * Assert that a constant is defined.
     *
     * @param mixed                $constant
     * @param null|callable|string $message
     */
    public static function defined($constant, $message = null, ?string $propertyPath = null): bool
    {
        /** @var string $constant */
        if (!defined($constant)) {
            $message = sprintf(self::generateMessage($message ?: 'Expected a defined constant. Got: %s'), $constant);

            throw self::createException($constant, $message, self::INVALID_CONSTANT, $propertyPath);
        }

        return true;
    }
}
