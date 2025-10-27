<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Assert\Assertions\Assertion;
use Cline\Assert\Exceptions\AssertionFailedException;
use Cline\Assert\Exceptions\ValidationError;
use Illuminate\Support\Facades\Date;
use Tests\Fixtures\ChildStdClass;
use Tests\Fixtures\CustomAssertion;
use Tests\Fixtures\OneCountable;

describe('Numeric Assertions', function (): void {
    describe('Happy Paths', function (): void {
        test('accepts valid positive integer values', function (): void {
            expect(Assertion::positiveInteger(1))->toBeTrue();
            expect(Assertion::positiveInteger(100))->toBeTrue();
            expect(Assertion::positiveInteger(\PHP_INT_MAX))->toBeTrue();
        });

        test('accepts valid natural number values', function (): void {
            expect(Assertion::natural(0))->toBeTrue();
            expect(Assertion::natural(1))->toBeTrue();
            expect(Assertion::natural(100))->toBeTrue();
            expect(Assertion::natural(\PHP_INT_MAX))->toBeTrue();
        });
    });

    describe('Sad Paths', function (): void {
        test('rejects zero in positive integer validation', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidPositiveInteger->value);
            Assertion::positiveInteger(0);
        });

        test('rejects negative integers in positive integer validation', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidPositiveInteger->value);
            Assertion::positiveInteger(-1);
        });

        test('rejects float values in positive integer validation', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidPositiveInteger->value);
            Assertion::positiveInteger(1.5);
        });

        test('rejects string values in positive integer validation', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidPositiveInteger->value);
            Assertion::positiveInteger('1');
        });

        test('rejects null in positive integer validation', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidPositiveInteger->value);
            Assertion::positiveInteger(null);
        });

        test('rejects object in positive integer validation', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidPositiveInteger->value);
            Assertion::positiveInteger(
                new stdClass(),
            );
        });

        test('rejects negative integers in natural number validation', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidNatural->value);
            Assertion::natural(-1);
        });

        test('rejects float values in natural number validation', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidNatural->value);
            Assertion::natural(1.5);
        });

        test('rejects string values in natural number validation', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidNatural->value);
            Assertion::natural('0');
        });

        test('rejects null in natural number validation', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidNatural->value);
            Assertion::natural(null);
        });

        test('rejects object in natural number validation', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidNatural->value);
            Assertion::natural(
                new stdClass(),
            );
        });
    });
});

describe('Type Assertions', function (): void {
    describe('Happy Paths', function (): void {
        test('accepts valid float values', function (): void {
            expect(Assertion::float(1.0))->toBeTrue();
            expect(Assertion::float(0.1))->toBeTrue();
            expect(Assertion::float(-1.1))->toBeTrue();
        });

        test('accepts valid integer values', function (): void {
            expect(Assertion::integer(10))->toBeTrue();
            expect(Assertion::integer(0))->toBeTrue();
        });

        test('accepts valid integerish values', function ($value): void {
            expect(Assertion::integerish($value))->toBeTrue();
        })->with('dataValidIntergerish');

        test('accepts valid boolean values', function (): void {
            expect(Assertion::boolean(true))->toBeTrue();
            expect(Assertion::boolean(false))->toBeTrue();
        });

        test('accepts valid scalar values', function (): void {
            expect(Assertion::scalar('foo'))->toBeTrue();
            expect(Assertion::scalar(52))->toBeTrue();
            expect(Assertion::scalar(12.34))->toBeTrue();
            expect(Assertion::scalar(false))->toBeTrue();
        });

        test('accepts valid numeric values', function (): void {
            expect(Assertion::numeric('1'))->toBeTrue();
            expect(Assertion::numeric(1))->toBeTrue();
            expect(Assertion::numeric(1.23))->toBeTrue();
        });

        test('accepts valid digit values', function (): void {
            expect(Assertion::digit(1))->toBeTrue();
            expect(Assertion::digit(0))->toBeTrue();
            expect(Assertion::digit('0'))->toBeTrue();
        });

        test('accepts float values coerced to string in digit validation', function (): void {
            expect(Assertion::digit(3.0))->toBeTrue();
            expect(Assertion::digit(0.0))->toBeTrue();
        });

        test('accepts valid alphanumeric strings', function (): void {
            expect(Assertion::alnum('a'))->toBeTrue();
            expect(Assertion::alnum('a1'))->toBeTrue();
            expect(Assertion::alnum('aasdf1234'))->toBeTrue();
            expect(Assertion::alnum('a1b2c3'))->toBeTrue();
        });

        test('accepts valid iterable values', function (): void {
            expect(Assertion::isIterable([]))->toBeTrue();
            expect(Assertion::isIterable([1, 2, 3]))->toBeTrue();
            expect(Assertion::isIterable(['key' => 'value']))->toBeTrue();
        });

        test('accepts ArrayIterator as iterable', function (): void {
            expect(Assertion::isIterable(
                new ArrayIterator([1, 2, 3]),
            ))->toBeTrue();
        });

        test('accepts generators as iterable', function (): void {
            $generator = (function () {
                yield 1;

                yield 2;

                yield 3;
            })();

            expect(Assertion::isIterable($generator))->toBeTrue();
        });

        test('accepts object matching one of multiple classes', function (): void {
            $object = new stdClass();

            expect(Assertion::isInstanceOfAny($object, [stdClass::class, Exception::class]))->toBeTrue();
            expect(Assertion::isInstanceOfAny($object, [Exception::class, stdClass::class]))->toBeTrue();
        });

        test('accepts object matching any class in array', function (): void {
            $arrayObject = new ArrayObject();

            expect(Assertion::isInstanceOfAny($arrayObject, [ArrayObject::class, stdClass::class]))->toBeTrue();
        });

        test('accepts objects matching one of multiple classes with isAnyOf', function (): void {
            expect(Assertion::isAnyOf(
                new ChildStdClass(),
                [stdClass::class, Exception::class],
            ))->toBeTrue();
        });

        test('accepts class strings matching one of multiple classes with isAnyOf', function (): void {
            expect(Assertion::isAnyOf(ChildStdClass::class, [stdClass::class, Exception::class]))->toBeTrue();
        });

        test('accepts objects with inheritance using isAnyOf', function (): void {
            expect(Assertion::isAnyOf(
                new ArrayIterator(),
                [Traversable::class],
            ))->toBeTrue();
        });

        test('accepts objects not matching the specified class', function (): void {
            expect(Assertion::isNotA(
                new stdClass(),
                Exception::class,
            ))->toBeTrue();
        });

        test('accepts class strings not matching the specified class', function (): void {
            expect(Assertion::isNotA(stdClass::class, Exception::class))->toBeTrue();
        });

        test('accepts objects not in inheritance chain', function (): void {
            expect(Assertion::isNotA(
                new stdClass(),
                ArrayIterator::class,
            ))->toBeTrue();
        });
    });

    describe('Sad Paths', function (): void {
        test('rejects invalid float values', function ($nonFloat): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidFloat->value);
            Assertion::float($nonFloat);
        })->with('dataInvalidFloat');

        test('rejects invalid integer values', function ($nonInteger): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidInteger->value);
            Assertion::integer($nonInteger);
        })->with('dataInvalidInteger');

        test('rejects invalid integerish values', function ($nonInteger): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidIntegerish->value);
            Assertion::integerish($nonInteger);
        })->with('dataInvalidIntegerish');

        test('rejects invalid boolean values', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidBoolean->value);
            Assertion::boolean(1);
        });

        test('rejects invalid scalar values', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidScalar->value);
            Assertion::scalar(
                new stdClass(),
            );
        });

        test('rejects invalid numeric values', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidNumeric->value);
            Assertion::numeric('foo');
        });

        test('rejects invalid digit values', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidDigit->value);
            Assertion::digit(-1);
        });

        test('rejects float values with decimal places in digit validation', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidDigit->value);
            Assertion::digit(3.14);
        });

        test('rejects object in digit validation', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidDigit->value);
            Assertion::digit(
                new stdClass(),
            );
        });

        test('rejects array in digit validation', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidDigit->value);
            Assertion::digit([1, 2, 3]);
        });

        test('rejects resource in digit validation', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidDigit->value);
            $resource = fopen('php://memory', 'rb');

            try {
                Assertion::digit($resource);
            } finally {
                fclose($resource);
            }
        });

        test('rejects invalid alphanumeric strings', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidAlnum->value);
            Assertion::alnum('1a');
        });

        test('rejects non-iterable values', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidIterable->value);
            Assertion::isIterable('not iterable');
        });

        test('rejects objects as iterable', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidIterable->value);
            Assertion::isIterable(
                new stdClass(),
            );
        });

        test('rejects primitives as iterable', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidIterable->value);
            Assertion::isIterable(123);
        });

        test('rejects object not matching any of multiple classes', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidInstanceOfAny->value);
            Assertion::isInstanceOfAny(
                new stdClass(),
                [Exception::class, ArrayObject::class],
            );
        });

        test('rejects string value in isInstanceOfAny', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidInstanceOfAny->value);
            Assertion::isInstanceOfAny('not an object', [stdClass::class]);
        });

        test('rejects primitive value in isInstanceOfAny', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidInstanceOfAny->value);
            Assertion::isInstanceOfAny(123, [stdClass::class, Exception::class]);
        });

        test('rejects object not matching any class with isAnyOf', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidAnyOf->value);
            Assertion::isAnyOf(
                new stdClass(),
                [Exception::class, ArrayObject::class],
            );
        });

        test('rejects class string not matching any class with isAnyOf', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidAnyOf->value);
            Assertion::isAnyOf(stdClass::class, [Exception::class, ArrayObject::class]);
        });

        test('rejects primitive value in isAnyOf', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidAnyOf->value);
            Assertion::isAnyOf(123, [stdClass::class, Exception::class]);
        });

        test('rejects object matching the specified class with isNotA', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidNotA->value);
            Assertion::isNotA(
                new stdClass(),
                stdClass::class,
            );
        });

        test('rejects class string matching the specified class with isNotA', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidNotA->value);
            Assertion::isNotA(stdClass::class, stdClass::class);
        });

        test('rejects object in inheritance chain with isNotA', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidNotA->value);
            Assertion::isNotA(
                new ChildStdClass(),
                stdClass::class,
            );
        });

        test('rejects class string in inheritance chain with isNotA', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidNotA->value);
            Assertion::isNotA(ChildStdClass::class, stdClass::class);
        });
    });
});

describe('Empty and Null Assertions', function (): void {
    describe('Happy Paths', function (): void {
        test('accepts non-empty values', function (): void {
            expect(Assertion::notEmpty('test'))->toBeTrue();
            expect(Assertion::notEmpty(1))->toBeTrue();
            expect(Assertion::notEmpty(true))->toBeTrue();
            expect(Assertion::notEmpty(['foo']))->toBeTrue();
        });

        test('accepts empty values', function (): void {
            expect(Assertion::noContent(''))->toBeTrue();
            expect(Assertion::noContent(0))->toBeTrue();
            expect(Assertion::noContent(false))->toBeTrue();
            expect(Assertion::noContent([]))->toBeTrue();
        });

        test('accepts null values', function (): void {
            expect(Assertion::null(null))->toBeTrue();
        });

        test('accepts not null values', function (): void {
            expect(Assertion::notNull('1'))->toBeTrue();
            expect(Assertion::notNull(1))->toBeTrue();
            expect(Assertion::notNull(0))->toBeTrue();
            expect(Assertion::notNull([]))->toBeTrue();
            expect(Assertion::notNull(false))->toBeTrue();
        });

        test('accepts not blank values', function (): void {
            expect(Assertion::notBlank('foo'))->toBeTrue();
        });
    });

    describe('Sad Paths', function (): void {
        test('rejects empty values when not empty expected', function ($value): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::ValueEmpty->value);
            Assertion::notEmpty($value);
        })->with('dataInvalidNotEmpty');

        test('rejects non-empty values when empty expected', function ($value): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::ValueNotEmpty->value);
            Assertion::noContent($value);
        })->with('dataInvalidEmpty');

        test('rejects non-null values when null expected', function ($value): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::ValueNotNull->value);
            Assertion::null($value);
        })->with('dataInvalidNull');

        test('rejects null values when not null expected', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::ValueNull->value);
            Assertion::notNull(null);
        });

        test('rejects blank values when not blank expected', function ($notBlank): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidNotBlank->value);
            Assertion::notBlank($notBlank);
        })->with('dataInvalidNotBlank');
    });
});

describe('String Assertions', function (): void {
    describe('Happy Paths', function (): void {
        test('accepts valid strings', function (): void {
            expect(Assertion::string('test-string'))->toBeTrue();
            expect(Assertion::string(''))->toBeTrue();
        });

        test('matches valid regex patterns', function (): void {
            expect(Assertion::regex('some string', '/.*/'))->toBeTrue();
        });

        test('accepts strings not matching regex', function (): void {
            expect(Assertion::notRegex('some string', '/\d+/'))->toBeTrue();
        });

        test('validates minimum string length', function (): void {
            expect(Assertion::minLength('foo', 3))->toBeTrue();
            expect(Assertion::minLength('foo', 1))->toBeTrue();
            expect(Assertion::minLength('foo', 0))->toBeTrue();
            expect(Assertion::minLength('', 0))->toBeTrue();
            expect(Assertion::minLength('址址', 2))->toBeTrue();
        });

        test('validates maximum string length', function (): void {
            expect(Assertion::maxLength('foo', 10))->toBeTrue();
            expect(Assertion::maxLength('foo', 3))->toBeTrue();
            expect(Assertion::maxLength('', 0))->toBeTrue();
            expect(Assertion::maxLength('址址', 2))->toBeTrue();
        });

        test('validates string length between bounds', function (): void {
            expect(Assertion::betweenLength('foo', 0, 3))->toBeTrue();
            expect(Assertion::betweenLength('址址', 2, 2))->toBeTrue();
        });

        test('validates string starts with prefix', function (): void {
            expect(Assertion::startsWith('foo', 'foo'))->toBeTrue();
            expect(Assertion::startsWith('foo', 'fo'))->toBeTrue();
            expect(Assertion::startsWith('foo', 'f'))->toBeTrue();
            expect(Assertion::startsWith('址foo', '址'))->toBeTrue();
        });

        test('validates string ends with suffix', function (): void {
            expect(Assertion::endsWith('foo', 'foo'))->toBeTrue();
            expect(Assertion::endsWith('sonderbar', 'bar'))->toBeTrue();
            expect(Assertion::endsWith('opp', 'p'))->toBeTrue();
            expect(Assertion::endsWith('foo址', '址'))->toBeTrue();
        });

        test('validates string contains substring', function (): void {
            expect(Assertion::contains('foo', 'foo'))->toBeTrue();
            expect(Assertion::contains('foo', 'oo'))->toBeTrue();
        });

        test('validates string does not contain substring', function (): void {
            expect(Assertion::notContains('foo', 'bar'))->toBeTrue();
            expect(Assertion::notContains('foo', 'p'))->toBeTrue();
        });

        test('validates exact string length', function (): void {
            expect(Assertion::length('asdf', 4))->toBeTrue();
            expect(Assertion::length('', 0))->toBeTrue();
        });

        test('validates exact string length for utf8 characters', function ($value, $expected): void {
            expect(Assertion::length($value, $expected))->toBeTrue();
        })->with('dataLengthUtf8Characters');

        test('validates exact string length for given encoding', function (): void {
            expect(Assertion::length('址', 1, null, null, 'utf8'))->toBeTrue();
        });

        test('accepts non-empty strings', function (): void {
            expect(Assertion::stringNotEmpty('test'))->toBeTrue();
            expect(Assertion::stringNotEmpty('a'))->toBeTrue();
            expect(Assertion::stringNotEmpty(' '))->toBeTrue();
            expect(Assertion::stringNotEmpty('0'))->toBeTrue();
        });

        test('accepts strings starting with a letter', function (): void {
            expect(Assertion::startsWithLetter('abc'))->toBeTrue();
            expect(Assertion::startsWithLetter('Test'))->toBeTrue();
            expect(Assertion::startsWithLetter('z123'))->toBeTrue();
            expect(Assertion::startsWithLetter('A'))->toBeTrue();
        });

        test('accepts strings with Unicode letters only', function (): void {
            expect(Assertion::unicodeLetters('abc'))->toBeTrue();
            expect(Assertion::unicodeLetters('ABC'))->toBeTrue();
            expect(Assertion::unicodeLetters('café'))->toBeTrue();
            expect(Assertion::unicodeLetters('Ω'))->toBeTrue();
            expect(Assertion::unicodeLetters('日本語'))->toBeTrue();
        });

        test('accepts strings with letters only', function (): void {
            expect(Assertion::alpha('abc'))->toBeTrue();
            expect(Assertion::alpha('ABC'))->toBeTrue();
            expect(Assertion::alpha('abcDEF'))->toBeTrue();
            expect(Assertion::alpha('z'))->toBeTrue();
        });

        test('accepts strings with digits only', function (): void {
            expect(Assertion::digits('123'))->toBeTrue();
            expect(Assertion::digits('0'))->toBeTrue();
            expect(Assertion::digits('999'))->toBeTrue();
            expect(Assertion::digits('1234567890'))->toBeTrue();
        });

        test('accepts lowercase strings', function (): void {
            expect(Assertion::lower('abc'))->toBeTrue();
            expect(Assertion::lower('hello'))->toBeTrue();
            expect(Assertion::lower('z'))->toBeTrue();
            expect(Assertion::lower('test'))->toBeTrue();
        });

        test('accepts uppercase strings', function (): void {
            expect(Assertion::upper('ABC'))->toBeTrue();
            expect(Assertion::upper('HELLO'))->toBeTrue();
            expect(Assertion::upper('Z'))->toBeTrue();
            expect(Assertion::upper('TEST'))->toBeTrue();
        });

        test('validates lengthBetween with different ranges', function (): void {
            expect(Assertion::lengthBetween('foo', 1, 5))->toBeTrue();
            expect(Assertion::lengthBetween('hello', 5, 10))->toBeTrue();
            expect(Assertion::lengthBetween('test', 4, 4))->toBeTrue();
            expect(Assertion::lengthBetween('址址', 2, 3))->toBeTrue();
        });

        test('accepts non-whitespace-only strings', function (): void {
            expect(Assertion::notWhitespaceOnly('test'))->toBeTrue();
            expect(Assertion::notWhitespaceOnly('a'))->toBeTrue();
            expect(Assertion::notWhitespaceOnly('hello world'))->toBeTrue();
            expect(Assertion::notWhitespaceOnly('  text  '))->toBeTrue();
        });
    });

    describe('Sad Paths', function (): void {
        test('rejects invalid string types', function ($invalidString): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidString->value);
            Assertion::string($invalidString);
        })->with('dataInvalidString');

        test('rejects invalid regex patterns', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidRegex->value);
            Assertion::regex('foo', '(bar)');
        });

        test('rejects non-string values in regex validation', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidString->value);
            Assertion::regex(['foo'], '(bar)');
        });

        test('rejects strings matching regex when not expected', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidNotRegex->value);
            Assertion::notRegex('some string', '/.*/');
        });

        test('rejects strings shorter than minimum length', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidMinLength->value);
            Assertion::minLength('foo', 4);
        });

        test('rejects strings longer than maximum length', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidMaxLength->value);
            Assertion::maxLength('foo', 2);
        });

        test('rejects strings shorter than minimum betweenLength bound', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidMinLength->value);
            Assertion::betweenLength('foo', 4, 100);
        });

        test('rejects strings longer than maximum betweenLength bound', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidMaxLength->value);
            Assertion::betweenLength('foo', 0, 2);
        });

        test('rejects strings not starting with expected prefix', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidStringStart->value);
            Assertion::startsWith('foo', 'bar');
        });

        test('rejects strings not ending with expected suffix', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidStringEnd->value);
            Assertion::endsWith('foo', 'bar');
        });

        test('rejects strings not containing expected substring', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidStringContains->value);
            Assertion::contains('foo', 'bar');
        });

        test('rejects strings containing unexpected substring', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidStringNotContains->value);
            Assertion::notContains('foo', 'o');
        });

        test('rejects strings with incorrect length', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidLength->value);
            Assertion::length('asdf', 3);
        });

        test('rejects empty strings in stringNotEmpty', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidNotEq->value);
            Assertion::stringNotEmpty('');
        });

        test('rejects strings not starting with a letter', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidStringStart->value);
            Assertion::startsWithLetter('123');
        });

        test('rejects empty strings in startsWithLetter', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidStringStart->value);
            Assertion::startsWithLetter('');
        });

        test('rejects strings with special characters in startsWithLetter', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidStringStart->value);
            Assertion::startsWithLetter('!abc');
        });

        test('rejects strings with numbers in unicodeLetters', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidRegex->value);
            Assertion::unicodeLetters('abc123');
        });

        test('rejects strings with spaces in unicodeLetters', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidRegex->value);
            Assertion::unicodeLetters('hello world');
        });

        test('rejects empty strings in unicodeLetters', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidRegex->value);
            Assertion::unicodeLetters('');
        });

        test('rejects strings with numbers in alpha', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidRegex->value);
            Assertion::alpha('abc123');
        });

        test('rejects strings with spaces in alpha', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidRegex->value);
            Assertion::alpha('hello world');
        });

        test('rejects empty strings in alpha', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidRegex->value);
            Assertion::alpha('');
        });

        test('rejects strings with letters in digits', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidRegex->value);
            Assertion::digits('123abc');
        });

        test('rejects empty strings in digits', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidRegex->value);
            Assertion::digits('');
        });

        test('rejects uppercase characters in lower', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidRegex->value);
            Assertion::lower('Hello');
        });

        test('rejects mixed case in lower', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidRegex->value);
            Assertion::lower('hElLo');
        });

        test('rejects lowercase characters in upper', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidRegex->value);
            Assertion::upper('Hello');
        });

        test('rejects mixed case in upper', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidRegex->value);
            Assertion::upper('HeLLo');
        });

        test('rejects strings shorter than minimum in lengthBetween', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidLength->value);
            Assertion::lengthBetween('ab', 3, 5);
        });

        test('rejects strings longer than maximum in lengthBetween', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidLength->value);
            Assertion::lengthBetween('toolong', 1, 5);
        });

        test('rejects whitespace-only strings', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidRegex->value);
            Assertion::notWhitespaceOnly('   ');
        });

        test('rejects empty strings in notWhitespaceOnly', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidRegex->value);
            Assertion::notWhitespaceOnly('');
        });

        test('rejects tab-only strings in notWhitespaceOnly', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidRegex->value);
            Assertion::notWhitespaceOnly("\t\t\t");
        });

        test('rejects newline-only strings in notWhitespaceOnly', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidRegex->value);
            Assertion::notWhitespaceOnly("\n\n");
        });
    });

    describe('Edge Cases', function (): void {
        test('rejects startsWith with wrong encoding', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidStringStart->value);
            Assertion::startsWith('址', '址址', null, null, 'ASCII');
        });

        test('rejects endsWith with wrong encoding', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidStringEnd->value);
            Assertion::endsWith('址', '址址', null, null, 'ASCII');
        });

        test('rejects length validation with wrong encoding', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidLength->value);
            Assertion::length('址', 1, null, null, 'ASCII');
        });
    });
});

describe('Array Assertions', function (): void {
    describe('Happy Paths', function (): void {
        test('accepts valid arrays', function (): void {
            expect(Assertion::isArray([]))->toBeTrue();
            expect(Assertion::isArray([1, 2, 3]))->toBeTrue();
            expect(Assertion::isArray([[], []]))->toBeTrue();
        });

        test('validates key exists in array', function (): void {
            expect(Assertion::keyExists(['foo' => 'bar'], 'foo'))->toBeTrue();
        });

        test('validates key does not exist in array', function (): void {
            expect(Assertion::keyNotExists(['foo' => 'bar'], 'baz'))->toBeTrue();
        });

        test('validates unique array values', function (array $array): void {
            expect(Assertion::uniqueValues($array, 'baz'))->toBeTrue();
        })->with('dataValidUniqueValues');

        test('validates value in array choices', function (): void {
            expect(Assertion::choice('foo', ['foo']))->toBeTrue();
        });

        test('validates value in array', function (): void {
            expect(Assertion::inArray('foo', ['foo']))->toBeTrue();
        });

        test('validates value is one of choices (oneOf alias)', function (): void {
            expect(Assertion::oneOf('foo', ['foo', 'bar', 'baz']))->toBeTrue();
            expect(Assertion::oneOf(2, [1, 2, 3]))->toBeTrue();
        });

        test('validates value not in array', function (): void {
            expect(Assertion::notInArray(6, range(1, 5)))->toBeTrue();
            expect(Assertion::notInArray('a', range('b', 'z')))->toBeTrue();
        });

        test('validates array count', function (): void {
            expect(Assertion::count(['Hi'], 1))->toBeTrue();
            expect(Assertion::count(['Hi', 'There'], 2))->toBeTrue();
            expect(Assertion::count(
                new OneCountable(),
                1,
            ))->toBeTrue();
            expect(Assertion::count(
                new SimpleXMLElement('<a><b /><c /></a>'),
                2,
            ))->toBeTrue();
        });

        test('validates array count with intl resource bundle', function (): void {
            // Test ResourceBundle counting using resources generated for PHP testing of ResourceBundle
            // https://github.com/php/php-src/commit/8f4337f2551e28d98290752e9ca99fc7f87d93b5
            expect(Assertion::count(
                new ResourceBundle('en_US', dirname(__DIR__).'/_files/ResourceBundle'),
                6,
            ))->toBeTrue();
        });

        test('validates minimum array count', function (): void {
            expect(Assertion::minCount(['Hi'], 1))->toBeTrue();
            expect(Assertion::minCount(['Hi', 'There'], 1))->toBeTrue();
            expect(Assertion::minCount(
                new OneCountable(),
                1,
            ))->toBeTrue();
            expect(Assertion::minCount(
                new SimpleXMLElement('<a><b /><c /></a>'),
                1,
            ))->toBeTrue();
        });

        test('validates minimum array count with intl resource bundle', function (): void {
            expect(Assertion::minCount(
                new ResourceBundle('en_US', dirname(__DIR__).'/_files/ResourceBundle'),
                2,
            ))->toBeTrue();
        });

        test('validates maximum array count', function (): void {
            expect(Assertion::maxCount(['Hi'], 1))->toBeTrue();
            expect(Assertion::maxCount(['Hi', 'There'], 2))->toBeTrue();
            expect(Assertion::maxCount(
                new OneCountable(),
                1,
            ))->toBeTrue();
            expect(Assertion::maxCount(
                new SimpleXMLElement('<a><b /><c /></a>'),
                3,
            ))->toBeTrue();
        });

        test('validates maximum array count with intl resource bundle', function (): void {
            expect(Assertion::maxCount(
                new ResourceBundle('en_US', dirname(__DIR__).'/_files/ResourceBundle'),
                7,
            ))->toBeTrue();
        });

        test('validates non-empty choices exist in array', function (): void {
            expect(Assertion::choicesNotEmpty(
                ['tux' => 'linux', 'Gnu' => 'dolphin'],
                ['tux'],
            ))->toBeTrue();
        });

        test('validates not empty key exists', function (): void {
            expect(Assertion::notEmptyKey(['keyExists' => 'notEmpty'], 'keyExists'))->toBeTrue();
        });

        test('validates array subset equality', function (): void {
            expect(Assertion::eqArraySubset(
                [
                    'a' => [
                        'a1' => 'a2',
                        'a3' => 'a4',
                    ],
                    'b' => [
                        'b1' => 'b2',
                    ],
                ],
                [
                    'a' => [
                        'a1' => 'a2',
                    ],
                ],
            ))->toBeTrue();
        });

        test('validates list arrays', function (): void {
            expect(Assertion::isList([]))->toBeTrue();
            expect(Assertion::isList([1, 2, 3]))->toBeTrue();
            expect(Assertion::isList(['a', 'b', 'c']))->toBeTrue();
            expect(Assertion::isList([0 => 'a', 1 => 'b', 2 => 'c']))->toBeTrue();
        });

        test('validates non-empty list arrays', function (): void {
            expect(Assertion::isNonEmptyList([1]))->toBeTrue();
            expect(Assertion::isNonEmptyList([1, 2, 3]))->toBeTrue();
            expect(Assertion::isNonEmptyList(['a', 'b', 'c']))->toBeTrue();
        });

        test('validates map arrays', function (): void {
            expect(Assertion::isMap([]))->toBeTrue();
            expect(Assertion::isMap(['foo' => 'bar']))->toBeTrue();
            expect(Assertion::isMap(['a' => 1, 'b' => 2]))->toBeTrue();
            expect(Assertion::isMap(['key1' => 'value1', 'key2' => 'value2']))->toBeTrue();
        });

        test('validates non-empty map arrays', function (): void {
            expect(Assertion::isNonEmptyMap(['foo' => 'bar']))->toBeTrue();
            expect(Assertion::isNonEmptyMap(['a' => 1, 'b' => 2]))->toBeTrue();
        });

        test('validates array count between bounds', function (): void {
            expect(Assertion::countBetween([1, 2], 1, 3))->toBeTrue();
            expect(Assertion::countBetween([1, 2, 3], 3, 5))->toBeTrue();
            expect(Assertion::countBetween([1], 1, 1))->toBeTrue();
            expect(Assertion::countBetween([], 0, 5))->toBeTrue();
            expect(Assertion::countBetween(
                new OneCountable(),
                1,
                2,
            ))->toBeTrue();
        });

        test('validates valid array keys', function (): void {
            expect(Assertion::validArrayKey(0))->toBeTrue();
            expect(Assertion::validArrayKey(1))->toBeTrue();
            expect(Assertion::validArrayKey(-1))->toBeTrue();
            expect(Assertion::validArrayKey('foo'))->toBeTrue();
            expect(Assertion::validArrayKey('bar'))->toBeTrue();
            expect(Assertion::validArrayKey(''))->toBeTrue();
        });
    });

    describe('Sad Paths', function (): void {
        test('rejects invalid array types', function ($value): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidArray->value);
            Assertion::isArray($value);
        })->with('dataInvalidArray');

        test('rejects missing key in array', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidKeyExists->value);
            Assertion::keyExists(['foo' => 'bar'], 'baz');
        });

        test('rejects existing key when not expected', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidKeyNotExists->value);
            Assertion::keyNotExists(['foo' => 'bar'], 'foo');
        });

        test('rejects non-unique array values', function (array $array): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidUniqueValues->value);
            Assertion::uniqueValues($array, 'quux');
        })->with('dataInvalidUniqueValues');

        test('rejects value not in choices', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidChoice->value);
            Assertion::choice('foo', ['bar', 'baz']);
        });

        test('rejects value not in array', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidChoice->value);
            Assertion::inArray('bar', ['baz']);
        });

        test('rejects value found in array when not expected', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidValueInArray->value);
            Assertion::notInArray(1, range(1, 5));
        });

        test('rejects incorrect array count', function ($countable, int $count): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidCount->value);
            $this->expectExceptionMessageMatches('/Expected a collection with exactly \d+ elements, but got \d+ elements\. Got: .*/');
            Assertion::count($countable, $count);
        })->with('dataInvalidCount');

        test('rejects array count below minimum', function ($countable, int $count): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidMinCount->value);
            $this->expectExceptionMessageMatches('/Expected a collection with at least \d+ elements, but got \d+ elements\. Got: .*/');
            Assertion::minCount($countable, $count);
        })->with('dataInvalidMinCount');

        test('rejects array count above maximum', function ($countable, int $count): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidMaxCount->value);
            $this->expectExceptionMessageMatches('/Expected a collection with at most \d+ elements, but got \d+ elements\. Got: .*/');
            Assertion::maxCount($countable, $count);
        })->with('dataInvalidMaxCount');

        test('rejects empty choice values', function (array $values, array $choices): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::ValueEmpty->value);
            Assertion::choicesNotEmpty($values, $choices);
        })->with('invalidChoicesProvider');

        test('rejects invalid choice keys', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidKeyIsset->value);
            Assertion::choicesNotEmpty(['tux' => ''], ['invalidChoice']);
        });

        test('rejects empty key value when not empty expected', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::ValueEmpty->value);
            Assertion::notEmptyKey(['keyExists' => ''], 'keyExists');
        });

        test('rejects missing key when not empty expected', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidKeyIsset->value);
            Assertion::notEmptyKey(['key' => 'notEmpty'], 'keyNotExists');
        });

        test('rejects invalid array types for subset comparison', function ($value, $value2): void {
            $this->expectException('InvalidArgumentException');
            $this->expectExceptionCode(ValidationError::InvalidArray->value);
            Assertion::eqArraySubset($value, $value2);
        })->with('invalidEqArraySubsetProvider');

        test('rejects mismatching array subsets', function (): void {
            $this->expectException('InvalidArgumentException');
            $this->expectExceptionCode(ValidationError::InvalidEq->value);
            Assertion::eqArraySubset(
                [
                    'a' => 'b',
                ],
                [
                    'c' => 'd',
                ],
            );
        })->with('invalidEqArraySubsetProvider');

        test('rejects associative arrays as lists', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidList->value);
            Assertion::isList(['foo' => 'bar']);
        });

        test('rejects associative arrays as lists with custom message', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionMessage('Custom list message');
            Assertion::isList(['foo' => 'bar'], 'Custom list message');
        });

        test('rejects arrays with non-sequential keys as lists', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidList->value);
            Assertion::isList([0 => 'a', 2 => 'b']);
        });

        test('rejects associative arrays with sequential keys as lists', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidList->value);
            Assertion::isList([1 => 'a', 2 => 'b', 3 => 'c']);
        });

        test('rejects non-array values as lists', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidList->value);
            Assertion::isList('not an array');
        });

        test('rejects empty arrays as non-empty lists', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::ValueEmpty->value);
            Assertion::isNonEmptyList([]);
        });

        test('rejects associative arrays as non-empty lists', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidList->value);
            Assertion::isNonEmptyList(['foo' => 'bar']);
        });

        test('rejects lists with integer keys as maps', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidMap->value);
            Assertion::isMap([1, 2, 3]);
        });

        test('rejects mixed key types as maps', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidMap->value);
            Assertion::isMap(['foo' => 'bar', 0 => 'baz']);
        });

        test('rejects non-array values as maps', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidMap->value);
            Assertion::isMap('not an array');
        });

        test('rejects empty arrays as non-empty maps', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::ValueEmpty->value);
            Assertion::isNonEmptyMap([]);
        });

        test('rejects lists as non-empty maps', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidMap->value);
            Assertion::isNonEmptyMap([1, 2, 3]);
        });

        test('rejects count below minimum in countBetween', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidCountBetween->value);
            Assertion::countBetween([1], 2, 5);
        });

        test('rejects count above maximum in countBetween', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidCountBetween->value);
            Assertion::countBetween([1, 2, 3, 4], 1, 3);
        });

        test('rejects float values as array keys', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidArrayKey->value);
            Assertion::validArrayKey(1.5);
        });

        test('rejects boolean values as array keys', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidArrayKey->value);
            Assertion::validArrayKey(true);
        });

        test('rejects null values as array keys', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidArrayKey->value);
            Assertion::validArrayKey(null);
        });

        test('rejects array values as array keys', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidArrayKey->value);
            Assertion::validArrayKey([]);
        });

        test('rejects object values as array keys', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidArrayKey->value);
            Assertion::validArrayKey(
                new stdClass(),
            );
        });
    });
});

describe('Comparison Assertions', function (): void {
    describe('Happy Paths', function (): void {
        test('validates identical values with same', function (): void {
            expect(Assertion::same(1, 1))->toBeTrue();
            expect(Assertion::same('foo', 'foo'))->toBeTrue();
            expect(Assertion::same($obj = new stdClass(), $obj))->toBeTrue();
        });

        test('validates equal values with eq', function (): void {
            expect(Assertion::eq(1, '1'))->toBeTrue();
            expect(Assertion::eq('foo', true))->toBeTrue();
            expect(Assertion::eq($obj = new stdClass(), $obj))->toBeTrue();
        });

        test('validates not equal values', function (): void {
            expect(Assertion::notEq('1', false))->toBeTrue();
            expect(Assertion::notEq(
                new stdClass(),
                [],
            ))->toBeTrue();
        });

        test('validates not same values', function (): void {
            expect(Assertion::notSame('1', 2))->toBeTrue();
            expect(Assertion::notSame(
                new stdClass(),
                [],
            ))->toBeTrue();
        });

        test('validates minimum value', function (): void {
            expect(Assertion::min(1, 1))->toBeTrue();
            expect(Assertion::min(2, 1))->toBeTrue();
            expect(Assertion::min(2.5, 1))->toBeTrue();
        });

        test('validates maximum value', function (): void {
            expect(Assertion::max(1, 1))->toBeTrue();
            expect(Assertion::max(0.5, 1))->toBeTrue();
            expect(Assertion::max(0, 1))->toBeTrue();
        });

        test('validates value in range', function (): void {
            expect(Assertion::range(1, 1, 2))->toBeTrue();
            expect(Assertion::range(2, 1, 2))->toBeTrue();
            expect(Assertion::range(2, 0, 100))->toBeTrue();
            expect(Assertion::range(2.5, 2.25, 2.75))->toBeTrue();
        });

        test('validates less than comparison', function (): void {
            expect(Assertion::lessThan(1, 2))->toBeTrue();
            expect(Assertion::lessThan('aaa', 'bbb'))->toBeTrue();
            expect(Assertion::lessThan('aaa', 'aaaa'))->toBeTrue();
            expect(Assertion::lessThan(Date::today(), Date::tomorrow()))->toBeTrue();
        });

        test('validates less or equal than comparison', function (): void {
            expect(Assertion::lessOrEqualThan(1, 2))->toBeTrue();
            expect(Assertion::lessOrEqualThan(1, 1))->toBeTrue();
            expect(Assertion::lessOrEqualThan('aaa', 'bbb'))->toBeTrue();
            expect(Assertion::lessOrEqualThan('aaa', 'aaaa'))->toBeTrue();
            expect(Assertion::lessOrEqualThan('aaa', 'aaa'))->toBeTrue();
            expect(Assertion::lessOrEqualThan(Date::today(), Date::tomorrow()))->toBeTrue();
            expect(Assertion::lessOrEqualThan(Date::today(), Date::today()))->toBeTrue();
        });

        test('validates greater than comparison', function (): void {
            expect(Assertion::greaterThan(2, 1))->toBeTrue();
            expect(Assertion::greaterThan('bbb', 'aaa'))->toBeTrue();
            expect(Assertion::greaterThan('aaaa', 'aaa'))->toBeTrue();
            expect(Assertion::greaterThan(Date::tomorrow(), Date::today()))->toBeTrue();
        });

        test('validates greater or equal than comparison', function (): void {
            expect(Assertion::greaterOrEqualThan(2, 1))->toBeTrue();
            expect(Assertion::greaterOrEqualThan(1, 1))->toBeTrue();
            expect(Assertion::greaterOrEqualThan('bbb', 'aaa'))->toBeTrue();
            expect(Assertion::greaterOrEqualThan('aaaa', 'aaa'))->toBeTrue();
            expect(Assertion::greaterOrEqualThan('aaa', 'aaa'))->toBeTrue();
            expect(Assertion::greaterOrEqualThan(Date::tomorrow(), Date::today()))->toBeTrue();
            expect(Assertion::greaterOrEqualThan(Date::today(), Date::today()))->toBeTrue();
        });

        test('validates value between bounds inclusively', function ($value, $lowerLimit, $upperLimit): void {
            expect(Assertion::between($value, $lowerLimit, $upperLimit))->toBeTrue();
        })->with('providerValidBetween');

        test('validates value between bounds exclusively', function ($value, $lowerLimit, $upperLimit): void {
            expect(Assertion::betweenExclusive($value, $lowerLimit, $upperLimit))->toBeTrue();
        })->with('providerValidBetweenExclusive');

        test('validates true values', function (): void {
            expect(Assertion::true(1 === 1))->toBeTrue();
        });

        test('validates false values', function (): void {
            expect(Assertion::false(1 === 0))->toBeTrue();
        });

        test('validates not false values', function (): void {
            expect(Assertion::notFalse(true))->toBeTrue();
            expect(Assertion::notFalse(1))->toBeTrue();
            expect(Assertion::notFalse('false'))->toBeTrue();
            expect(Assertion::notFalse(null))->toBeTrue();
            expect(Assertion::notFalse(0))->toBeTrue();
            expect(Assertion::notFalse([]))->toBeTrue();
            expect(Assertion::notFalse(''))->toBeTrue();
        });
    });

    describe('Sad Paths', function (): void {
        test('rejects different objects as same', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidSame->value);
            Assertion::same(
                new stdClass(),
                new stdClass(),
            );
        });

        test('rejects non-equal values', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidEq->value);
            Assertion::eq('2', 1);
        });

        test('rejects equal values when not equal expected', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidNotEq->value);
            Assertion::notEq('1', 1);
        });

        test('rejects identical values when not same expected', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidNotSame->value);
            Assertion::notSame(1, 1);
        });

        test('rejects values below minimum', function ($value, $min): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidMin->value);
            $this->expectExceptionMessageMatches('/Expected a number at least [\d.]+\. Got: [\d.]+/');
            Assertion::min($value, $min);
        })->with('dataInvalidMin');

        test('rejects values above maximum', function ($value, $min): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidMax->value);
            $this->expectExceptionMessageMatches('/Expected a number at most [\d.]+\. Got: [\d.]+/');
            Assertion::max($value, $min);
        })->with('dataInvalidMax');

        test('rejects values outside range', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidRange->value);
            Assertion::range(1, 2, 3);
            Assertion::range(1.5, 2, 3);
        });

        test('rejects values not less than limit', function ($value, $limit): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidLess->value);
            Assertion::lessThan($value, $limit);
        })->with('invalidLessProvider');

        test('rejects values not less or equal than limit', function ($value, $limit): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidLessOrEqual->value);
            Assertion::lessOrEqualThan($value, $limit);
        })->with('invalidLessOrEqualProvider');

        test('rejects values not greater than limit', function ($value, $limit): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidGreater->value);
            Assertion::greaterThan($value, $limit);
        })->with('invalidGreaterProvider');

        test('rejects values not greater or equal than limit', function ($value, $limit): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidGreaterOrEqual->value);
            Assertion::greaterOrEqualThan($value, $limit);
        })->with('invalidGreaterOrEqualProvider');

        test('rejects values outside inclusive bounds', function ($value, $lowerLimit, $upperLimit): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidBetween->value);
            Assertion::between($value, $lowerLimit, $upperLimit);
        })->with('providerInvalidBetween');

        test('rejects values outside exclusive bounds', function ($value, $lowerLimit, $upperLimit): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidBetweenExclusive->value);
            Assertion::betweenExclusive($value, $lowerLimit, $upperLimit);
        })->with('providerInvalidBetweenExclusive');

        test('rejects non-true values', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidTrue->value);
            Assertion::true(false);
        });

        test('rejects non-false values', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidFalse->value);
            Assertion::false(true);
        });

        test('rejects false value when not false expected', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidNotFalse->value);
            Assertion::notFalse(false);
        });
    });
});

describe('Validation Assertions', function (): void {
    describe('Happy Paths', function (): void {
        test('validates email addresses', function (): void {
            expect(Assertion::email('123hello+world@email.provider.com'))->toBeTrue();
        });

        test('validates url formats', function ($url): void {
            expect(Assertion::url($url))->toBeTrue();
        })->with('dataValidUrl');

        test('validates uuid formats', function ($uuid): void {
            expect(Assertion::uuid($uuid))->toBeTrue();
        })->with('providesValidUuids');

        test('validates e164 phone numbers', function ($e164): void {
            expect(Assertion::e164($e164))->toBeTrue();
        })->with('providesValidE164s');

        test('validates json strings', function ($content): void {
            expect(Assertion::isJsonString($content))->toBeTrue();
        })->with('isJsonStringDataprovider');

        test('validates float values coerced to json strings', function (): void {
            expect(Assertion::isJsonString(3.14))->toBeTrue();
            expect(Assertion::isJsonString(42.0))->toBeTrue();
        });

        test('validates ip addresses', function ($value): void {
            expect(Assertion::ip($value))->toBeTrue();
        })->with('validIpProvider');

        test('validates ipv4 addresses', function (): void {
            expect(Assertion::ipv4('109.188.127.26'))->toBeTrue();
        });

        test('validates ipv6 addresses', function (): void {
            expect(Assertion::ipv6('2001:db8:85a3:8d3:1319:8a2e:370:7348'))->toBeTrue();
        });

        test('validates date formats', function ($value, string $format): void {
            expect(Assertion::date($value, $format))->toBeTrue();
        })->with('validDateProvider');

        test('validates base64 encoded strings', function (): void {
            $base64String = base64_encode('content');

            expect(Assertion::base64($base64String))->toBeTrue();
        });
    });

    describe('Sad Paths', function (): void {
        test('rejects invalid email addresses', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidEmail->value);
            Assertion::email('foo');
        });

        test('rejects invalid url formats', function ($url): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidUrl->value);
            Assertion::url($url);
        })->with('dataInvalidUrl');

        test('rejects invalid uuid formats', function ($uuid): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidUuid->value);
            Assertion::uuid($uuid);
        })->with('providesInvalidUuids');

        test('rejects invalid e164 phone numbers', function ($e164): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidE164->value);
            Assertion::e164($e164);
        })->with('providesInvalidE164s');

        test('rejects invalid json strings', function ($invalidString): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidJsonString->value);
            Assertion::isJsonString($invalidString);
        })->with('isJsonStringInvalidStringDataprovider');

        test('rejects object in json string validation', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidJsonString->value);
            Assertion::isJsonString(
                new stdClass(),
            );
        });

        test('rejects array in json string validation', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidJsonString->value);
            Assertion::isJsonString([1, 2, 3]);
        });

        test('rejects resource in json string validation', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidJsonString->value);
            $resource = fopen('php://memory', 'rb');

            try {
                Assertion::isJsonString($resource);
            } finally {
                fclose($resource);
            }
        });

        test('rejects invalid ip addresses', function ($value, $flag = null): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidIp->value);
            Assertion::ip($value, $flag);
        })->with('invalidIpProvider');

        test('rejects ipv6 addresses when ipv4 expected', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidIp->value);
            Assertion::ipv4('2001:db8:85a3:8d3:1319:8a2e:370:7348');
        });

        test('rejects ipv4 addresses when ipv6 expected', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidIp->value);
            Assertion::ipv6('109.188.127.26');
        });

        test('rejects invalid date formats', function ($value, string $format): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidDate->value);
            Assertion::date($value, $format);
        })->with('invalidDateProvider');

        test('rejects invalid base64 strings', function (): void {
            $this->expectException('InvalidArgumentException');
            $this->expectExceptionCode(ValidationError::InvalidBase64->value);
            Assertion::base64('wrong-content');
        });
    });
});

describe('Object Assertions', function (): void {
    describe('Happy Paths', function (): void {
        test('validates objects', function (): void {
            expect(Assertion::isObject(
                new stdClass(),
            ))->toBeTrue();
        });

        test('validates instance of class', function (): void {
            expect(Assertion::isInstanceOf(
                new stdClass(),
                stdClass::class,
            ))->toBeTrue();
        });

        test('validates not instance of class', function (): void {
            expect(Assertion::notIsInstanceOf(
                new stdClass(),
                new class() {},
            ))->toBeTrue();
        });

        test('validates subclass of parent', function (): void {
            expect(Assertion::subclassOf(
                new ChildStdClass(),
                stdClass::class,
            ))->toBeTrue();
        });

        test('validates class exists', function (): void {
            expect(Assertion::classExists(Exception::class))->toBeTrue();
        });

        test('validates interface exists', function (): void {
            expect(Assertion::interfaceExists(Countable::class))->toBeTrue();
        });

        test('validates class implements interface', function (): void {
            expect(Assertion::implementsInterface(ArrayIterator::class, Traversable::class))->toBeTrue();
        });

        test('validates object implements interface', function (): void {
            $class = new ArrayObject();

            expect(Assertion::implementsInterface($class, Traversable::class))->toBeTrue();
        });

        test('validates method exists on object', function (): void {
            expect(Assertion::methodExists('methodExists', new CustomAssertion()))->toBeTrue();
        });

        test('validates closure throws expected exception', function (): void {
            expect(Assertion::throws(
                fn () => throw new InvalidArgumentException('test'),
                InvalidArgumentException::class,
            ))->toBeTrue();
        });

        test('validates callable functions', function (): void {
            expect(Assertion::isCallable('\is_callable'))->toBeTrue();
            expect(Assertion::isCallable('Tests\\Fixtures\\someCallable'))->toBeTrue();
            expect(Assertion::isCallable([new OneCountable(), 'count']))->toBeTrue();
            expect(Assertion::isCallable(CustomAssertion::clearCalls(...)))->toBeTrue();
            expect(Assertion::isCallable(
                function (): void {},
            ))->toBeTrue();
        });

        test('validates custom satisfy callback', function (): void {
            // Should not fail with true return
            expect(Assertion::satisfy(
                null,
                fn ($value): bool => null === $value,
            ))->toBeTrue();

            // Should not fail with void return
            expect(Assertion::satisfy(
                true,
                /**
                 * @param  mixed     $value
                 * @return bool|void
                 */
                function ($value) {
                    if (!is_bool($value)) {
                        return false;
                    }
                },
            ))->toBeTrue();
        });

        test('validates traversable objects', function (): void {
            expect(Assertion::isTraversable(
                new ArrayObject(),
            ))->toBeTrue();
        });

        test('validates countable objects', function (): void {
            expect(Assertion::isCountable([]))->toBeTrue();
            expect(Assertion::isCountable(
                new ArrayObject(),
            ))->toBeTrue();
        });

        test('validates array accessible objects', function (): void {
            expect(Assertion::isArrayAccessible(
                new ArrayObject(),
            ))->toBeTrue();
        });

        test('validates object or class name', function (): void {
            self::assertTrue(Assertion::objectOrClass(
                new stdClass(),
            ));
            self::assertTrue(Assertion::objectOrClass(stdClass::class));
        });

        test('validates property exists on object', function (): void {
            self::assertTrue(Assertion::propertyExists(
                new Exception(),
                'message',
            ));
        });

        test('validates multiple properties exist on object', function (): void {
            self::assertTrue(Assertion::propertiesExist(
                new Exception(),
                ['message', 'code', 'previous'],
            ));
        });

        test('validates property does not exist on object', function (): void {
            self::assertTrue(Assertion::propertyNotExists(
                new stdClass(),
                'nonExistentProperty',
            ));
        });

        test('validates property does not exist on class', function (): void {
            self::assertTrue(Assertion::propertyNotExists(
                stdClass::class,
                'nonExistentProperty',
            ));
        });

        test('validates method does not exist on object', function (): void {
            self::assertTrue(Assertion::methodNotExists(
                new stdClass(),
                'nonExistentMethod',
            ));
        });

        test('validates method does not exist on class', function (): void {
            self::assertTrue(Assertion::methodNotExists(
                stdClass::class,
                'nonExistentMethod',
            ));
        });
    });

    describe('Sad Paths', function (): void {
        test('rejects non-object values', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidObject->value);
            Assertion::isObject('notAnObject');
        });

        test('rejects incorrect instance of class', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidInstanceOf->value);

            Assertion::isInstanceOf(
                new stdClass(),
                new class() implements Stringable
                {
                    public function __toString(): string
                    {
                        return 'Anonymous';
                    }
                },
            );
        });

        test('rejects same instance when not expected', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidNotInstanceOf->value);
            Assertion::notIsInstanceOf(
                new stdClass(),
                stdClass::class,
            );
        });

        test('rejects incorrect subclass of parent', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidSubclassOf->value);

            Assertion::subclassOf(
                new stdClass(),
                new class() implements Stringable
                {
                    public function __toString(): string
                    {
                        return 'Anonymous';
                    }
                },
            );
        });

        test('rejects non-existent class', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidClass->value);
            Assertion::classExists('Foo');
        });

        test('rejects float values coerced to string in class validation', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidClass->value);
            Assertion::classExists(3.14);
        });

        test('rejects object in class validation', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidClass->value);
            Assertion::classExists(
                new stdClass(),
            );
        });

        test('rejects array in class validation', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidClass->value);
            Assertion::classExists([1, 2, 3]);
        });

        test('rejects resource in class validation', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidClass->value);
            $resource = fopen('php://memory', 'rb');

            try {
                Assertion::classExists($resource);
            } finally {
                fclose($resource);
            }
        });

        test('rejects non-existent interface', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidInterface->value);
            Assertion::interfaceExists('Foo');
        });

        test('rejects float values coerced to string in interface validation', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidInterface->value);
            Assertion::interfaceExists(3.14);
        });

        test('rejects object in interface validation', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidInterface->value);
            Assertion::interfaceExists(
                new stdClass(),
            );
        });

        test('rejects array in interface validation', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidInterface->value);
            Assertion::interfaceExists([1, 2, 3]);
        });

        test('rejects resource in interface validation', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidInterface->value);
            $resource = fopen('php://memory', 'rb');

            try {
                Assertion::interfaceExists($resource);
            } finally {
                fclose($resource);
            }
        });

        test('rejects class not implementing interface', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InterfaceNotImplemented->value);
            Assertion::implementsInterface(Exception::class, Traversable::class);
        });

        test('rejects object not implementing interface', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InterfaceNotImplemented->value);
            $class = new ArrayObject();

            Assertion::implementsInterface($class, SplObserver::class);
        });

        test('rejects invalid class in interface check', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InterfaceNotImplemented->value);
            $this->expectExceptionMessageMatches('/Class failed reflection\. Got: .*/');
            expect(Assertion::implementsInterface('not_a_class', Traversable::class))->toBeTrue();

            Assertion::implementsInterface(Exception::class, Traversable::class);
        });

        test('rejects non-existent method on object', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidMethod->value);
            Assertion::methodExists(
                'methodNotExists',
                new stdClass(),
            );
        });

        test('rejects non-existent method with custom message', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionMessage('Custom method message');
            Assertion::methodExists(
                'methodNotExists',
                new stdClass(),
                'Custom method message',
            );
        });

        test('rejects non-callable values', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidCallable->value);
            Assertion::isCallable('nonExistingFunction');
        });

        test('rejects values not satisfying callback', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidSatisfy->value);
            Assertion::satisfy(
                null,
                fn ($value): bool => null !== $value,
            );
        });

        test('rejects closure that does not throw exception', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidThrows->value);
            Assertion::throws(
                fn (): string => 'no exception',
                Exception::class,
            );
        });

        test('rejects closure that throws wrong exception type', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidThrows->value);
            Assertion::throws(
                fn () => throw new InvalidArgumentException('test'),
                RuntimeException::class,
            );
        });

        test('rejects closure throwing parent when child expected', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidThrows->value);
            Assertion::throws(
                fn () => throw new Exception('test'),
                InvalidArgumentException::class,
            );
        });

        test('rejects non-traversable values', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidTraversable->value);
            Assertion::isTraversable('not traversable');
        });

        test('rejects non-traversable values with custom message', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionMessage('Custom traversable message');
            Assertion::isTraversable('not traversable', 'Custom traversable message');
        });

        test('rejects non-countable values', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidCountable->value);
            Assertion::isCountable('not countable');
        });

        test('rejects non-array-accessible values', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidArrayAccessible->value);
            Assertion::isArrayAccessible('not array accessible');
        });

        test('rejects invalid class name', function (): void {
            $this->expectException('InvalidArgumentException');
            Assertion::objectOrClass('InvalidClassName');
        });

        test('rejects non-existent property on object', function (): void {
            $this->expectException('InvalidArgumentException');
            $this->expectExceptionCode(ValidationError::InvalidProperty->value);
            Assertion::propertyExists(
                new Exception(),
                'invalidProperty',
            );
        });

        test('rejects when some properties do not exist on object', function (array $properties): void {
            $this->expectException('InvalidArgumentException');
            $this->expectExceptionCode(ValidationError::InvalidProperty->value);
            Assertion::propertiesExist(
                new Exception(),
                $properties,
            );
        })->with('invalidPropertiesExistProvider');

        test('rejects existing property when not expected on object', function (): void {
            $this->expectException('InvalidArgumentException');
            $this->expectExceptionCode(ValidationError::InvalidPropertyNotExists->value);
            Assertion::propertyNotExists(
                new Exception(),
                'message',
            );
        });

        test('rejects existing property when not expected on class', function (): void {
            $this->expectException('InvalidArgumentException');
            $this->expectExceptionCode(ValidationError::InvalidPropertyNotExists->value);
            Assertion::propertyNotExists(
                Exception::class,
                'message',
            );
        });

        test('rejects existing method when not expected on object', function (): void {
            $this->expectException('InvalidArgumentException');
            $this->expectExceptionCode(ValidationError::InvalidMethodNotExists->value);
            Assertion::methodNotExists(
                new CustomAssertion(),
                'methodExists',
            );
        });

        test('rejects existing method when not expected on class', function (): void {
            $this->expectException('InvalidArgumentException');
            $this->expectExceptionCode(ValidationError::InvalidMethodNotExists->value);
            Assertion::methodNotExists(
                CustomAssertion::class,
                'methodExists',
            );
        });
    });
});

describe('System Assertions', function (): void {
    describe('Happy Paths', function (): void {
        test('validates file exists', function (): void {
            expect(Assertion::file(__FILE__))->toBeTrue();
        });

        test('validates directory exists', function (): void {
            expect(Assertion::directory(__DIR__))->toBeTrue();
        });

        test('validates file is readable', function (): void {
            expect(Assertion::readable(__FILE__))->toBeTrue();
        });

        test('validates directory is writeable', function (): void {
            expect(Assertion::writeable(sys_get_temp_dir()))->toBeTrue();
        });

        test('validates extension is loaded', function (): void {
            expect(Assertion::extensionLoaded('date'))->toBeTrue();
        });

        test('validates constant is defined', function (): void {
            expect(Assertion::defined('PHP_VERSION'))->toBeTrue();
        });

        test('validates version comparison', function (): void {
            expect(Assertion::version('1.0.0', '<', '2.0.0'))->toBeTrue();
        });

        test('validates php version comparison', function (): void {
            expect(Assertion::phpVersion('>', '4.0.0'))->toBeTrue();
        });

        test('validates extension version comparison', function (): void {
            expect(Assertion::extensionVersion('json', '>', '1.0.0'))->toBeTrue();
        });

        test('validates resource type', function (): void {
            self::assertTrue(Assertion::isResource(fopen('php://memory', 'wb')));
        });
    });

    describe('Sad Paths', function (): void {
        test('rejects empty filename', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::ValueEmpty->value);
            Assertion::file('');
        });

        test('rejects non-existent file', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidFile->value);
            Assertion::file(__DIR__.'/does-not-exists');
        });

        test('rejects non-existent directory', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidDirectory->value);
            Assertion::directory(__DIR__.'/does-not-exist');
        });

        test('rejects non-readable path', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidReadable->value);
            Assertion::readable(__DIR__.'/does-not-exist');
        });

        test('rejects non-writeable path', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidWriteable->value);
            Assertion::writeable(__DIR__.'/does-not-exist');
        });

        test('rejects extension not loaded', function (): void {
            $this->expectException('InvalidArgumentException');
            Assertion::extensionLoaded('NOT_LOADED');
        });

        test('rejects undefined constant', function (): void {
            $this->expectException('InvalidArgumentException');
            Assertion::defined('NOT_A_CONSTANT');
        });

        test('rejects invalid version comparison', function (): void {
            $this->expectException('InvalidArgumentException');
            Assertion::version('1.0.0', 'eq', '2.0.0');
        });

        test('rejects invalid version operator', function (): void {
            $this->expectException('InvalidArgumentException');
            Assertion::version('1.0.0', null, '2.0.0');
        });

        test('rejects invalid php version comparison', function (): void {
            $this->expectException('InvalidArgumentException');
            Assertion::phpVersion('<', '5.0.0');
        });

        test('rejects invalid extension version comparison', function (): void {
            $this->expectException('InvalidArgumentException');
            Assertion::extensionVersion('json', '<', '0.1.0');
        });

        test('rejects non-resource values', function (): void {
            $this->expectException('InvalidArgumentException');
            Assertion::isResource(
                new stdClass(),
            );
        });
    });
});

describe('Advanced Assertions', function (): void {
    describe('Happy Paths', function (): void {
        test('applies assertion to all array elements', function (): void {
            expect(Assertion::allTrue([true, true]))->toBeTrue();
        });

        test('applies complex assertion to all array elements', function (): void {
            expect(Assertion::allIsInstanceOf([new stdClass(), new stdClass()], stdClass::class))->toBeTrue();
        });

        test('allows null or validates value', function (): void {
            expect(Assertion::nullOrMax(null, 1))->toBeTrue();
            expect(Assertion::nullOrMax(null, 2))->toBeTrue();
        });
    });

    describe('Sad Paths', function (): void {
        test('rejects when any array element fails assertion', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidTrue->value);
            Assertion::allTrue([true, false]);
        });

        test('rejects when any array element fails complex assertion', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidInstanceOf->value);
            Assertion::allIsInstanceOf(
                [new stdClass(), new stdClass()],
                new class() {},
                'Assertion failed',
                'foos',
            );
        });

        test('rejects all assertion without value', function (): void {
            $this->expectException('BadMethodCallException');
            Assertion::allTrue();
        });

        test('rejects all assertion with non-traversable value', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidTraversable->value);
            Assertion::allTrue('not-traversable');
        });

        test('rejects nullOr assertion without value', function (): void {
            $this->expectException('BadMethodCallException');
            $this->expectExceptionMessage('Missing the first argument.');
            Assertion::nullOrMax();
        });

        test('rejects failed nullOr method call', function (): void {
            $this->expectException('BadMethodCallException');
            $this->expectExceptionMessage('No assertion');
            Assertion::nullOrAssertionDoesNotExist('');
        });
    });
});

describe('Exception Details', function (): void {
    describe('Edge Cases', function (): void {
        test('provides value and constraints in exception', function (): void {
            try {
                Assertion::range(0, 10, 20);

                $this->fail('Exception expected');
            } catch (AssertionFailedException $assertionFailedException) {
                expect($assertionFailedException->getValue())->toEqual(0);
                expect($assertionFailedException->getConstraints())->toEqual(['min' => 10, 'max' => 20]);
            }
        });
    });
});

describe('Stringify Behavior', function (): void {
    describe('Edge Cases', function (): void {
        test('truncates string values longer than 100 characters', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidFloat->value);

            $string = str_repeat('1234567890', 11);

            expect(Assertion::float($string))->toBeTrue();
        });

        test('truncates multibyte strings correctly', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidFloat->value);
            $this->expectExceptionMessage('ငါကနံပါတ်မဟုတ်ဘူးငါကနံပါတ်မဟုတ်ဘူးငါကနံပါတ်မဟုတ်ဘူးငါကနံပါတ်မဟုတ်ဘူးငါကနံပါတ်မဟုတ်ဘူးငါကနံပါတ်မဟု...');

            $string = str_repeat('ငါကနံပါတ်မဟုတ်ဘူး', 11);

            expect(Assertion::float($string))->toBeTrue();
        });

        test('reports resource type in error messages', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidFloat->value);
            $this->expectExceptionMessage('stream');
            expect(Assertion::float(fopen('php://stdin', 'rb')))->toBeTrue();
        });
    });
});

describe('Equality Assertions', function (): void {
    describe('Happy Paths', function (): void {
        test('strictEquals passes with identical values', function (): void {
            expect(Assertion::strictEquals(42, 42))->toBeTrue();
            expect(Assertion::strictEquals('test', 'test'))->toBeTrue();
            $obj = new stdClass();
            expect(Assertion::strictEquals($obj, $obj))->toBeTrue();
        });
    });

    describe('Sad Paths', function (): void {
        test('strictEquals throws exception with different values', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidSame->value);
            Assertion::strictEquals(42, '42');
        });

        test('strictEquals throws exception with different objects', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidSame->value);
            Assertion::strictEquals(
                new stdClass(),
                new stdClass(),
            );
        });
    });
});

describe('Null Assertions', function (): void {
    describe('Happy Paths', function (): void {
        test('notUndefined passes with non-null values', function (): void {
            expect(Assertion::notUndefined(0))->toBeTrue();
            expect(Assertion::notUndefined(''))->toBeTrue();
            expect(Assertion::notUndefined(false))->toBeTrue();
            expect(Assertion::notUndefined([]))->toBeTrue();
        });

        test('undefined passes with null value', function (): void {
            expect(Assertion::undefined(null))->toBeTrue();
        });

        test('nullable passes with null value for any type', function (): void {
            expect(Assertion::nullable(null, 'string'))->toBeTrue();
            expect(Assertion::nullable(null, 'int'))->toBeTrue();
            expect(Assertion::nullable(null, stdClass::class))->toBeTrue();
        });

        test('nullable passes with matching string type', function (): void {
            expect(Assertion::nullable('test', 'string'))->toBeTrue();
        });

        test('nullable passes with matching int type', function (): void {
            expect(Assertion::nullable(42, 'int'))->toBeTrue();
            expect(Assertion::nullable(42, 'integer'))->toBeTrue();
        });

        test('nullable passes with matching float type', function (): void {
            expect(Assertion::nullable(3.14, 'float'))->toBeTrue();
            expect(Assertion::nullable(3.14, 'double'))->toBeTrue();
        });

        test('nullable passes with matching bool type', function (): void {
            expect(Assertion::nullable(true, 'bool'))->toBeTrue();
            expect(Assertion::nullable(false, 'boolean'))->toBeTrue();
        });

        test('nullable passes with matching array type', function (): void {
            expect(Assertion::nullable([], 'array'))->toBeTrue();
            expect(Assertion::nullable([1, 2], 'array'))->toBeTrue();
        });

        test('nullable passes with matching object type', function (): void {
            expect(Assertion::nullable(
                new stdClass(),
                'object',
            ))->toBeTrue();
        });

        test('nullable passes with matching class instance', function (): void {
            $obj = new stdClass();
            expect(Assertion::nullable($obj, stdClass::class))->toBeTrue();
        });
    });

    describe('Sad Paths', function (): void {
        test('notUndefined throws exception with null', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::ValueNull->value);
            Assertion::notUndefined(null);
        });

        test('undefined throws exception with non-null value', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::ValueNotNull->value);
            Assertion::undefined(0);
        });

        test('undefined throws exception with false', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::ValueNotNull->value);
            Assertion::undefined(false);
        });

        test('undefined throws exception with empty string', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::ValueNotNull->value);
            Assertion::undefined('');
        });
    });
});

describe('Array Comparison Assertions', function (): void {
    describe('Happy Paths', function (): void {
        test('equalCanonicalizing passes with identical arrays', function (): void {
            expect(Assertion::equalCanonicalizing([1, 2, 3], [1, 2, 3]))->toBeTrue();
        });

        test('equalCanonicalizing passes with arrays in different order', function (): void {
            expect(Assertion::equalCanonicalizing([3, 1, 2], [1, 2, 3]))->toBeTrue();
            expect(Assertion::equalCanonicalizing(['c', 'a', 'b'], ['a', 'b', 'c']))->toBeTrue();
        });

        test('matchArray passes with matching key-value pairs', function (): void {
            $array = ['name' => 'John', 'age' => 30, 'city' => 'NYC'];
            expect(Assertion::matchArray($array, ['name' => 'John']))->toBeTrue();
            expect(Assertion::matchArray($array, ['name' => 'John', 'age' => 30]))->toBeTrue();
        });

        test('matchArray passes with exact match', function (): void {
            $array = ['name' => 'John', 'age' => 30];
            expect(Assertion::matchArray($array, ['name' => 'John', 'age' => 30]))->toBeTrue();
        });
    });

    describe('Sad Paths', function (): void {
        test('equalCanonicalizing throws exception with different arrays', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidEq->value);
            Assertion::equalCanonicalizing([1, 2, 3], [1, 2, 4]);
        });

        test('equalCanonicalizing throws exception with different lengths', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidEq->value);
            Assertion::equalCanonicalizing([1, 2], [1, 2, 3]);
        });

        test('matchArray throws exception when key is missing', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidKeyExists->value);
            Assertion::matchArray(['name' => 'John'], ['age' => 30]);
        });

        test('matchArray throws exception when value does not match', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidEq->value);
            Assertion::matchArray(['name' => 'John'], ['name' => 'Jane']);
        });

        test('matchArray throws exception with type mismatch', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidEq->value);
            Assertion::matchArray(['age' => 30], ['age' => '30']);
        });

        test('matchArray throws exception with multiple mismatches', function (): void {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidEq->value);
            Assertion::matchArray(['name' => 'John', 'age' => 30], ['name' => 'John', 'age' => 25]);
        });
    });
});

describe('Object Comparison Assertions', function (): void {
    describe('Happy Paths', function (): void {
        test('matchObject passes with matching properties', function (): void {
            $obj = new stdClass();
            $obj->name = 'John';
            $obj->age = 30;
            $obj->city = 'NYC';

            expect(Assertion::matchObject($obj, ['name' => 'John']))->toBeTrue();
            expect(Assertion::matchObject($obj, ['name' => 'John', 'age' => 30]))->toBeTrue();
        });

        test('matchObject passes with all properties matching', function (): void {
            $obj = new stdClass();
            $obj->name = 'John';
            $obj->age = 30;

            expect(Assertion::matchObject($obj, ['name' => 'John', 'age' => 30]))->toBeTrue();
        });
    });

    describe('Sad Paths', function (): void {
        test('matchObject throws exception when property is missing', function (): void {
            $obj = new stdClass();
            $obj->name = 'John';

            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidProperty->value);
            Assertion::matchObject($obj, ['age' => 30]);
        });

        test('matchObject throws exception when property value does not match', function (): void {
            $obj = new stdClass();
            $obj->name = 'John';

            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidEq->value);
            Assertion::matchObject($obj, ['name' => 'Jane']);
        });

        test('matchObject throws exception with type mismatch', function (): void {
            $obj = new stdClass();
            $obj->age = 30;

            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidEq->value);
            Assertion::matchObject($obj, ['age' => '30']);
        });

        test('matchObject throws exception with multiple property mismatches', function (): void {
            $obj = new stdClass();
            $obj->name = 'John';
            $obj->age = 30;

            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidEq->value);
            Assertion::matchObject($obj, ['name' => 'John', 'age' => 25]);
        });

        test('matchObject throws exception when multiple properties are missing', function (): void {
            $obj = new stdClass();
            $obj->name = 'John';

            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionCode(ValidationError::InvalidProperty->value);
            Assertion::matchObject($obj, ['age' => 30, 'city' => 'NYC']);
        });
    });
});
