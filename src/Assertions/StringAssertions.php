<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Assert\Assertions;

use Cline\Assert\AssertionFailedException;
use Throwable;

use function in_array;
use function mb_strlen;
use function mb_strpos;
use function mb_strripos;
use function preg_match;
use function sprintf;

/**
 * String operation assertion methods.
 *
 * Dependencies:
 * - TypeAssertions::string() (for type checking)
 * - Base::createException()
 * - Base::stringify()
 * - Base::generateMessage()
 *
 * @author Brian Faust <brian@cline.sh>
 */
trait StringAssertions
{
    public const int INVALID_STRING_START = 20;

    public const int INVALID_STRING_END = 238;

    public const int INVALID_STRING_CONTAINS = 21;

    public const int INVALID_STRING_NOT_CONTAINS = 229;

    public const int INVALID_LENGTH = 37;

    public const int INVALID_MIN_LENGTH = 18;

    public const int INVALID_MAX_LENGTH = 19;

    public const int INVALID_REGEX = 17;

    public const int INVALID_NOT_REGEX = 50;

    public const int INVALID_ALNUM = 31;

    /**
     * Assert that value matches a regex.
     *
     * @param mixed                $value
     * @param string               $pattern
     * @param null|callable|string $message
     *
     * @psalm-assert =string $value
     *
     * @throws AssertionFailedException
     */
    public static function regex($value, $pattern, $message = null, ?string $propertyPath = null): bool
    {
        self::string($value, $message, $propertyPath);

        if (in_array(preg_match($pattern, (string) $value), [0, false], true)) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected a value matching regex. Got: %s'),
                static::stringify($value),
            );

            throw self::createException($value, $message, self::INVALID_REGEX, $propertyPath, ['pattern' => $pattern]);
        }

        return true;
    }

    /**
     * Assert that value does not match a regex.
     *
     * @param mixed                $value
     * @param string               $pattern
     * @param null|callable|string $message
     *
     * @psalm-assert !=string $value
     *
     * @throws AssertionFailedException
     */
    public static function notRegex($value, $pattern, $message = null, ?string $propertyPath = null): bool
    {
        self::string($value, $message, $propertyPath);

        if (preg_match($pattern, (string) $value)) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected a value not matching regex. Got: %s'),
                static::stringify($value),
            );

            throw self::createException($value, $message, self::INVALID_NOT_REGEX, $propertyPath, ['pattern' => $pattern]);
        }

        return true;
    }

    /**
     * Assert that string has a given length.
     *
     * @param mixed                $value
     * @param int                  $length
     * @param null|callable|string $message
     * @param string               $encoding
     *
     * @psalm-assert =string $value
     *
     * @throws AssertionFailedException
     */
    public static function length($value, $length, $message = null, ?string $propertyPath = null, $encoding = 'utf8'): bool
    {
        self::string($value, $message, $propertyPath);

        if (mb_strlen((string) $value, $encoding) !== $length) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected string to be exactly %2$d characters long, but got %3$d characters. Got: %s'),
                static::stringify($value),
                $length,
                mb_strlen((string) $value, $encoding),
            );

            throw self::createException($value, $message, self::INVALID_LENGTH, $propertyPath, ['length' => $length, 'encoding' => $encoding]);
        }

        return true;
    }

    /**
     * Assert that a string is at least $minLength chars long.
     *
     * @param mixed                $value
     * @param int                  $minLength
     * @param null|callable|string $message
     * @param string               $encoding
     *
     * @psalm-assert =string $value
     *
     * @throws AssertionFailedException
     */
    public static function minLength($value, $minLength, $message = null, ?string $propertyPath = null, $encoding = 'utf8'): bool
    {
        self::string($value, $message, $propertyPath);

        if (mb_strlen((string) $value, $encoding) < $minLength) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected string to be at least %2$d characters long, but got %3$d characters. Got: %s'),
                static::stringify($value),
                $minLength,
                mb_strlen((string) $value, $encoding),
            );

            throw self::createException($value, $message, self::INVALID_MIN_LENGTH, $propertyPath, ['min_length' => $minLength, 'encoding' => $encoding]);
        }

        return true;
    }

    /**
     * Assert that string value is not longer than $maxLength chars.
     *
     * @param mixed                $value
     * @param int                  $maxLength
     * @param null|callable|string $message
     * @param string               $encoding
     *
     * @psalm-assert =string $value
     *
     * @throws AssertionFailedException
     */
    public static function maxLength($value, $maxLength, $message = null, ?string $propertyPath = null, $encoding = 'utf8'): bool
    {
        self::string($value, $message, $propertyPath);

        if (mb_strlen((string) $value, $encoding) > $maxLength) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected string to be at most %2$d characters long, but got %3$d characters. Got: %s'),
                static::stringify($value),
                $maxLength,
                mb_strlen((string) $value, $encoding),
            );

            throw self::createException($value, $message, self::INVALID_MAX_LENGTH, $propertyPath, ['max_length' => $maxLength, 'encoding' => $encoding]);
        }

        return true;
    }

    /**
     * Assert that string length is between min and max lengths.
     *
     * @param mixed                $value
     * @param int                  $minLength
     * @param int                  $maxLength
     * @param null|callable|string $message
     * @param string               $encoding
     *
     * @psalm-assert =string $value
     *
     * @throws AssertionFailedException
     */
    public static function betweenLength($value, $minLength, $maxLength, $message = null, ?string $propertyPath = null, $encoding = 'utf8'): bool
    {
        self::string($value, $message, $propertyPath);
        self::minLength($value, $minLength, $message, $propertyPath, $encoding);
        self::maxLength($value, $maxLength, $message, $propertyPath, $encoding);

        return true;
    }

    /**
     * Assert that string starts with a sequence of chars.
     *
     * @param mixed                $string
     * @param string               $needle
     * @param null|callable|string $message
     * @param string               $encoding
     *
     * @psalm-assert =string $string
     *
     * @throws AssertionFailedException
     */
    public static function startsWith($string, $needle, $message = null, ?string $propertyPath = null, $encoding = 'utf8'): bool
    {
        self::string($string, $message, $propertyPath);

        if (0 !== mb_strpos((string) $string, $needle, 0, $encoding)) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected string to start with %2$s. Got: %s'),
                static::stringify($string),
                static::stringify($needle),
            );

            throw self::createException($string, $message, self::INVALID_STRING_START, $propertyPath, ['needle' => $needle, 'encoding' => $encoding]);
        }

        return true;
    }

    /**
     * Assert that string ends with a sequence of chars.
     *
     * @param mixed                $string
     * @param string               $needle
     * @param null|callable|string $message
     * @param string               $encoding
     *
     * @psalm-assert =string $string
     *
     * @throws AssertionFailedException
     */
    public static function endsWith($string, $needle, $message = null, ?string $propertyPath = null, $encoding = 'utf8'): bool
    {
        self::string($string, $message, $propertyPath);

        $stringPosition = mb_strlen((string) $string, $encoding) - mb_strlen($needle, $encoding);

        if (mb_strripos((string) $string, $needle, 0, $encoding) !== $stringPosition) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected string to end with %2$s. Got: %s'),
                static::stringify($string),
                static::stringify($needle),
            );

            throw self::createException($string, $message, self::INVALID_STRING_END, $propertyPath, ['needle' => $needle, 'encoding' => $encoding]);
        }

        return true;
    }

    /**
     * Assert that string contains a sequence of chars.
     *
     * @param mixed                $string
     * @param string               $needle
     * @param null|callable|string $message
     * @param string               $encoding
     *
     * @psalm-assert =string $string
     *
     * @throws AssertionFailedException
     */
    public static function contains($string, $needle, $message = null, ?string $propertyPath = null, $encoding = 'utf8'): bool
    {
        self::string($string, $message, $propertyPath);

        if (false === mb_strpos((string) $string, $needle, 0, $encoding)) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected string to contain %2$s. Got: %s'),
                static::stringify($string),
                static::stringify($needle),
            );

            throw self::createException($string, $message, self::INVALID_STRING_CONTAINS, $propertyPath, ['needle' => $needle, 'encoding' => $encoding]);
        }

        return true;
    }

    /**
     * Assert that string does not contains a sequence of chars.
     *
     * @param mixed                $string
     * @param string               $needle
     * @param null|callable|string $message
     * @param string               $encoding
     *
     * @psalm-assert =string $string
     *
     * @throws AssertionFailedException
     */
    public static function notContains($string, $needle, $message = null, ?string $propertyPath = null, $encoding = 'utf8'): bool
    {
        self::string($string, $message, $propertyPath);

        if (false !== mb_strpos((string) $string, $needle, 0, $encoding)) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected string to not contain %2$s. Got: %s'),
                static::stringify($string),
                static::stringify($needle),
            );

            throw self::createException($string, $message, self::INVALID_STRING_NOT_CONTAINS, $propertyPath, ['needle' => $needle, 'encoding' => $encoding]);
        }

        return true;
    }

    /**
     * Assert that value is alphanumeric.
     *
     * @param mixed                $value
     * @param null|callable|string $message
     *
     * @throws AssertionFailedException
     */
    public static function alnum($value, $message = null, ?string $propertyPath = null): bool
    {
        try {
            self::regex($value, '(^([a-zA-Z]{1}[a-zA-Z0-9]*)$)', $message, $propertyPath);
        } catch (Throwable) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected an alphanumeric value. Got: %s'),
                static::stringify($value),
            );

            throw self::createException($value, $message, self::INVALID_ALNUM, $propertyPath);
        }

        return true;
    }

    /**
     * Assert that value is a non-empty string.
     *
     * @param mixed                $value
     * @param null|callable|string $message
     *
     * @psalm-assert non-empty-string $value
     *
     * @throws AssertionFailedException
     */
    public static function stringNotEmpty($value, $message = null, ?string $propertyPath = null): bool
    {
        self::string($value, $message, $propertyPath);
        self::notEq($value, '', $message, $propertyPath);

        return true;
    }

    /**
     * Assert that string starts with a letter.
     *
     * @param mixed                $value
     * @param null|callable|string $message
     *
     * @psalm-assert string $value
     *
     * @throws AssertionFailedException
     */
    public static function startsWithLetter($value, $message = null, ?string $propertyPath = null): bool
    {
        self::string($value, $message, $propertyPath);

        $valid = isset($value[0]);

        if ($valid) {
            $locale = \setlocale(\LC_CTYPE, 0);
            \setlocale(\LC_CTYPE, 'C');
            $valid = \ctype_alpha($value[0]);
            \setlocale(\LC_CTYPE, $locale);
        }

        if (!$valid) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected a value to start with a letter. Got: %s'),
                static::stringify($value),
            );

            throw self::createException($value, $message, self::INVALID_STRING_START, $propertyPath);
        }

        return true;
    }

    /**
     * Assert that string contains Unicode letters only.
     *
     * @param mixed                $value
     * @param null|callable|string $message
     *
     * @psalm-assert string $value
     *
     * @throws AssertionFailedException
     */
    public static function unicodeLetters($value, $message = null, ?string $propertyPath = null): bool
    {
        self::string($value, $message, $propertyPath);

        if (!\preg_match('/^\p{L}+$/u', $value)) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected a value to contain only Unicode letters. Got: %s'),
                static::stringify($value),
            );

            throw self::createException($value, $message, self::INVALID_REGEX, $propertyPath);
        }

        return true;
    }

    /**
     * Assert that string contains letters only.
     *
     * @param mixed                $value
     * @param null|callable|string $message
     *
     * @psalm-assert string $value
     *
     * @throws AssertionFailedException
     */
    public static function alpha($value, $message = null, ?string $propertyPath = null): bool
    {
        self::string($value, $message, $propertyPath);

        $locale = \setlocale(\LC_CTYPE, 0);
        \setlocale(\LC_CTYPE, 'C');
        $valid = !\ctype_alpha($value);
        \setlocale(\LC_CTYPE, $locale);

        if ($valid) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected a value to contain only letters. Got: %s'),
                static::stringify($value),
            );

            throw self::createException($value, $message, self::INVALID_REGEX, $propertyPath);
        }

        return true;
    }

    /**
     * Assert that string contains digits only.
     *
     * @param mixed                $value
     * @param null|callable|string $message
     *
     * @psalm-assert string $value
     *
     * @throws AssertionFailedException
     */
    public static function digits($value, $message = null, ?string $propertyPath = null): bool
    {
        $locale = \setlocale(\LC_CTYPE, 0);
        \setlocale(\LC_CTYPE, 'C');
        $valid = !\ctype_digit($value);
        \setlocale(\LC_CTYPE, $locale);

        if ($valid) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected a value to contain digits only. Got: %s'),
                static::stringify($value),
            );

            throw self::createException($value, $message, self::INVALID_REGEX, $propertyPath);
        }

        return true;
    }

    /**
     * Assert that string contains lowercase characters only.
     *
     * @param mixed                $value
     * @param null|callable|string $message
     *
     * @psalm-assert lowercase-string $value
     *
     * @throws AssertionFailedException
     */
    public static function lower($value, $message = null, ?string $propertyPath = null): bool
    {
        $locale = \setlocale(\LC_CTYPE, 0);
        \setlocale(\LC_CTYPE, 'C');
        $valid = !\ctype_lower($value);
        \setlocale(\LC_CTYPE, $locale);

        if ($valid) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected a value to contain lowercase characters only. Got: %s'),
                static::stringify($value),
            );

            throw self::createException($value, $message, self::INVALID_REGEX, $propertyPath);
        }

        return true;
    }

    /**
     * Assert that string contains uppercase characters only.
     *
     * @param mixed                $value
     * @param null|callable|string $message
     *
     * @psalm-assert !lowercase-string $value
     *
     * @throws AssertionFailedException
     */
    public static function upper($value, $message = null, ?string $propertyPath = null): bool
    {
        $locale = \setlocale(\LC_CTYPE, 0);
        \setlocale(\LC_CTYPE, 'C');
        $valid = !\ctype_upper($value);
        \setlocale(\LC_CTYPE, $locale);

        if ($valid) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected a value to contain uppercase characters only. Got: %s'),
                static::stringify($value),
            );

            throw self::createException($value, $message, self::INVALID_REGEX, $propertyPath);
        }

        return true;
    }

    /**
     * Assert that string length is within a range (inclusive).
     *
     * @param mixed                $value
     * @param int|float            $min
     * @param int|float            $max
     * @param null|callable|string $message
     * @param string               $encoding
     *
     * @psalm-assert string $value
     *
     * @throws AssertionFailedException
     */
    public static function lengthBetween($value, $min, $max, $message = null, ?string $propertyPath = null, $encoding = 'utf8'): bool
    {
        $length = mb_strlen((string) $value, $encoding);

        if ($length < $min || $length > $max) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected a value to contain between %2$s and %3$s characters. Got: %s'),
                static::stringify($value),
                $min,
                $max,
            );

            throw self::createException($value, $message, self::INVALID_LENGTH, $propertyPath, ['min' => $min, 'max' => $max, 'encoding' => $encoding]);
        }

        return true;
    }

    /**
     * Assert that string is not whitespace-only.
     *
     * @param mixed                $value
     * @param null|callable|string $message
     *
     * @psalm-assert string $value
     *
     * @throws AssertionFailedException
     */
    public static function notWhitespaceOnly($value, $message = null, ?string $propertyPath = null): bool
    {
        if (\preg_match('/^\s*$/', $value)) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected a non-whitespace string. Got: %s'),
                static::stringify($value),
            );

            throw self::createException($value, $message, self::INVALID_REGEX, $propertyPath);
        }

        return true;
    }
}
