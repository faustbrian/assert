<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Assert\Assertions;

use Cline\Assert\AssertionFailedException;
use DateTime;

use const FILTER_FLAG_IPV4;
use const FILTER_FLAG_IPV6;
use const FILTER_VALIDATE_EMAIL;
use const FILTER_VALIDATE_IP;
use const JSON_ERROR_NONE;

use function base64_decode;
use function filter_var;
use function implode;
use function in_array;
use function is_array;
use function is_bool;
use function is_object;
use function is_resource;
use function is_string;
use function json_decode;
use function json_last_error;
use function preg_match;
use function sprintf;
use function str_replace;

/**
 * Validation assertion methods for common formats.
 *
 * Dependencies:
 * - TypeAssertions::string() (for type checking)
 * - Base::createException()
 * - Base::stringify()
 * - Base::generateMessage()
 *
 * @author Brian Faust <brian@cline.sh>
 */
trait ValidationAssertions
{
    public const int INVALID_EMAIL = 201;

    public const int INVALID_URL = 203;

    public const int INVALID_UUID = 40;

    public const int INVALID_IP = 218;

    public const int INVALID_E164 = 48;

    public const int INVALID_BASE64 = 49;

    public const int INVALID_JSON_STRING = 206;

    public const int INVALID_DATE = 214;

    /**
     * Assert that value is an email address (using input_filter/FILTER_VALIDATE_EMAIL).
     *
     * @param mixed                $value
     * @param null|callable|string $message
     *
     * @psalm-assert =string $value
     *
     * @throws AssertionFailedException
     */
    public static function email($value, $message = null, ?string $propertyPath = null): bool
    {
        self::string($value, $message, $propertyPath);

        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected a valid email address. Got: %s'),
                static::stringify($value),
            );

            throw self::createException($value, $message, self::INVALID_EMAIL, $propertyPath);
        }

        return true;
    }

    /**
     * Assert that value is an URL.
     *
     * This code snipped was taken from the Symfony project and modified to the special demands of this method.
     *
     * @param mixed                $value
     * @param null|callable|string $message
     *
     * @psalm-assert =string $value
     *
     * @throws AssertionFailedException
     *
     * @see https://github.com/symfony/Validator/blob/master/Constraints/UrlValidator.php
     * @see https://github.com/symfony/Validator/blob/master/Constraints/Url.php
     */
    public static function url($value, $message = null, ?string $propertyPath = null): bool
    {
        self::string($value, $message, $propertyPath);

        $protocols = ['http', 'https'];

        $pattern = '~^
            (%s)://                                                             # protocol
            (([\.\pL\pN-]+:)?([\.\pL\pN-]+)@)?                                  # basic auth
            (
                ([\pL\pN\pS\-\.])+(\.?([\pL\pN]|xn\-\-[\pL\pN-]+)+\.?)          # a domain name
                |                                                               # or
                \d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}                              # an IP address
                |                                                               # or
                \[
                    (?:(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){6})(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:::(?:(?:(?:[0-9a-f]{1,4})):){5})(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:[0-9a-f]{1,4})))?::(?:(?:(?:[0-9a-f]{1,4})):){4})(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,1}(?:(?:[0-9a-f]{1,4})))?::(?:(?:(?:[0-9a-f]{1,4})):){3})(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,2}(?:(?:[0-9a-f]{1,4})))?::(?:(?:(?:[0-9a-f]{1,4})):){2})(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,3}(?:(?:[0-9a-f]{1,4})))?::(?:(?:[0-9a-f]{1,4})):)(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,4}(?:(?:[0-9a-f]{1,4})))?::)(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,5}(?:(?:[0-9a-f]{1,4})))?::)(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,6}(?:(?:[0-9a-f]{1,4})))?::))))
                \]                                                              # an IPv6 address
            )
            (:[0-9]+)?                                                          # a port (optional)
            (?:/ (?:[\pL\pN\-._\~!$&\'()*+,;=:@]|%%[0-9A-Fa-f]{2})* )*          # a path
            (?:\? (?:[\pL\pN\-._\~!$&\'\[\]()*+,;=:@/?]|%%[0-9A-Fa-f]{2})* )?   # a query (optional)
            (?:\# (?:[\pL\pN\-._\~!$&\'()*+,;=:@/?]|%%[0-9A-Fa-f]{2})* )?       # a fragment (optional)
        $~ixu';

        $pattern = sprintf($pattern, implode('|', $protocols));

        if (in_array(preg_match($pattern, (string) $value), [0, false], true)) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected a valid URL. Got: %s'),
                static::stringify($value),
            );

            throw self::createException($value, $message, self::INVALID_URL, $propertyPath);
        }

        return true;
    }

    /**
     * Assert that the given string is a valid UUID.
     *
     * Uses code from {@link https://github.com/ramsey/uuid} that is MIT licensed.
     *
     * @param string               $value
     * @param null|callable|string $message
     *
     * @throws AssertionFailedException
     */
    public static function uuid($value, $message = null, ?string $propertyPath = null): bool
    {
        $value = str_replace(['urn:', 'uuid:', '{', '}'], '', $value);

        if ('00000000-0000-0000-0000-000000000000' === $value) {
            return true;
        }

        if (in_array(preg_match('/^[0-9A-Fa-f]{8}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{12}$/', $value), [0, false], true)) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected a valid UUID. Got: %s'),
                static::stringify($value),
            );

            throw self::createException($value, $message, self::INVALID_UUID, $propertyPath);
        }

        return true;
    }

    /**
     * Assert that value is an IPv4 or IPv6 address
     * (using input_filter/FILTER_VALIDATE_IP).
     *
     * @param string               $value
     * @param null|int             $flag
     * @param null|callable|string $message
     *
     * @throws AssertionFailedException
     *
     * @see http://php.net/manual/filter.filters.flags.php
     */
    public static function ip($value, $flag = null, $message = null, ?string $propertyPath = null): bool
    {
        self::string($value, $message, $propertyPath);

        if ($flag === null) {
            $filterVarResult = filter_var($value, FILTER_VALIDATE_IP);
        } else {
            $filterVarResult = filter_var($value, FILTER_VALIDATE_IP, $flag);
        }

        if (!$filterVarResult) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected a valid IP address. Got: %s'),
                static::stringify($value),
            );

            throw self::createException($value, $message, self::INVALID_IP, $propertyPath, ['flag' => $flag]);
        }

        return true;
    }

    /**
     * Assert that value is an IPv4 address
     * (using input_filter/FILTER_VALIDATE_IP).
     *
     * @param string               $value
     * @param null|int             $flag
     * @param null|callable|string $message
     *
     * @throws AssertionFailedException
     *
     * @see http://php.net/manual/filter.filters.flags.php
     */
    public static function ipv4($value, $flag = null, $message = null, ?string $propertyPath = null): bool
    {
        self::ip($value, $flag | FILTER_FLAG_IPV4, self::generateMessage($message ?: 'Expected a valid IPv4 address. Got: %s'), $propertyPath);

        return true;
    }

    /**
     * Assert that value is an IPv6 address
     * (using input_filter/FILTER_VALIDATE_IP).
     *
     * @param string               $value
     * @param null|int             $flag
     * @param null|callable|string $message
     *
     * @throws AssertionFailedException
     *
     * @see http://php.net/manual/filter.filters.flags.php
     */
    public static function ipv6($value, $flag = null, $message = null, ?string $propertyPath = null): bool
    {
        self::ip($value, $flag | FILTER_FLAG_IPV6, self::generateMessage($message ?: 'Expected a valid IPv6 address. Got: %s'), $propertyPath);

        return true;
    }

    /**
     * Assert that the given string is a valid E164 Phone Number.
     *
     * @see https://en.wikipedia.org/wiki/E.164
     *
     * @param string               $value
     * @param null|callable|string $message
     *
     * @throws AssertionFailedException
     */
    public static function e164($value, $message = null, ?string $propertyPath = null): bool
    {
        if (in_array(preg_match('/^\+?[1-9]\d{1,14}$/', $value), [0, false], true)) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected a valid E164. Got: %s'),
                static::stringify($value),
            );

            throw self::createException($value, $message, self::INVALID_E164, $propertyPath);
        }

        return true;
    }

    /**
     * Assert that a constant is defined.
     *
     * @param string               $value
     * @param null|callable|string $message
     *
     * @throws AssertionFailedException
     */
    public static function base64($value, $message = null, ?string $propertyPath = null): bool
    {
        if (false === base64_decode($value, true)) {
            $message = sprintf(self::generateMessage($message ?: 'Expected a valid base64 string. Got: %s'), $value);

            throw self::createException($value, $message, self::INVALID_BASE64, $propertyPath);
        }

        return true;
    }

    /**
     * Assert that the given string is a valid json string.
     *
     * NOTICE:
     * Since this does a json_decode to determine its validity
     * you probably should consider, when using the variable
     * content afterwards, just to decode and check for yourself instead
     * of using this assertion.
     *
     * @param mixed                $value
     * @param null|callable|string $message
     *
     * @psalm-assert =string $value
     *
     * @throws AssertionFailedException
     */
    public static function isJsonString($value, $message = null, ?string $propertyPath = null): bool
    {
        if (is_object($value) || is_array($value) || is_resource($value)) {
            $stringValue = '';
        } else {
            /** @var null|bool|float|int|string $value */
            $stringValue = is_string($value) ? $value : (string) (is_bool($value) || null === $value ? '' : $value);
        }

        if (null === json_decode($stringValue) && JSON_ERROR_NONE !== json_last_error()) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected a valid JSON string. Got: %s'),
                static::stringify($value),
            );

            throw self::createException($value, $message, self::INVALID_JSON_STRING, $propertyPath);
        }

        return true;
    }

    /**
     * Assert that date is valid and corresponds to the given format.
     *
     * @param string               $value
     * @param string               $format  supports all of the options date(), except for the following:
     *                                      N, w, W, t, L, o, B, a, A, g, h, I, O, P, Z, c, r
     * @param null|callable|string $message
     *
     * @throws AssertionFailedException
     *
     * @see http://php.net/manual/function.date.php#refsect1-function.date-parameters
     */
    public static function date($value, string $format, $message = null, ?string $propertyPath = null): bool
    {
        self::string($value, $message, $propertyPath);
        self::string($format, $message, $propertyPath);

        $dateTime = DateTime::createFromFormat('!'.$format, $value);

        if (false === $dateTime || $value !== $dateTime->format($format)) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected a date matching format %2$s. Got: %s'),
                static::stringify($value),
                static::stringify($format),
            );

            throw self::createException($value, $message, self::INVALID_DATE, $propertyPath, ['format' => $format]);
        }

        return true;
    }
}
