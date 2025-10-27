<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Assert\Exceptions\InvalidArgumentException;
use Cline\Assert\Expectations\Expectation;

use function Cline\Assert\expect as assertExpect;

describe('Advanced Expectations', function (): void {
    describe('Equality Helpers', function (): void {
        test('toEqualCanonicalizing() ignores array order', function (): void {
            expect(fn (): Expectation => assertExpect([3, 2, 1])->toEqualCanonicalizing([1, 2, 3]))
                ->not->toThrow(Throwable::class);
            expect(fn (): Expectation => assertExpect(['b', 'a', 'c'])->toEqualCanonicalizing(['a', 'b', 'c']))
                ->not->toThrow(Throwable::class);
        });

        test('toEqualWithDelta() accepts values within delta', function (): void {
            expect(fn (): Expectation => assertExpect(3.141_59)->toEqualWithDelta(3.14, 0.01))
                ->not->toThrow(Throwable::class);
            expect(fn (): Expectation => assertExpect(100)->toEqualWithDelta(99, 1.0))
                ->not->toThrow(Throwable::class);
        });

        test('toEqualWithDelta() rejects values outside delta', function (): void {
            expect(fn (): Expectation => assertExpect(3.5)->toEqualWithDelta(3.0, 0.1))
                ->toThrow(InvalidArgumentException::class);
        });
    });

    describe('Numeric Edge Cases', function (): void {
        test('toBeInfinite() accepts INF', function (): void {
            expect(fn (): Expectation => assertExpect(\INF)->toBeInfinite())->not->toThrow(Throwable::class);
            expect(fn (): Expectation => assertExpect(-\INF)->toBeInfinite())->not->toThrow(Throwable::class);
        });

        test('toBeInfinite() rejects finite numbers', function (): void {
            expect(fn (): Expectation => assertExpect(42)->toBeInfinite())
                ->toThrow(InvalidArgumentException::class);
        });

        test('toBeNan() accepts NAN', function (): void {
            expect(fn (): Expectation => assertExpect(\NAN)->toBeNan())->not->toThrow(Throwable::class);
        });

        test('toBeNan() rejects non-NaN values', function (): void {
            expect(fn (): Expectation => assertExpect(42)->toBeNan())
                ->toThrow(InvalidArgumentException::class);
        });

        test('toBeDigits() accepts digit strings', function (): void {
            expect(fn (): Expectation => assertExpect('123')->toBeDigits())->not->toThrow(Throwable::class);
        });

        test('toBeDigits() rejects non-digit strings', function (): void {
            expect(fn (): Expectation => assertExpect('12.3')->toBeDigits())
                ->toThrow(InvalidArgumentException::class);
        });
    });

    describe('Array Key Case Expectations', function (): void {
        test('toHaveSnakeCaseKeys() accepts snake_case keys', function (): void {
            expect(fn (): Expectation => assertExpect(['first_name' => 'John', 'last_name' => 'Doe'])
                ->toHaveSnakeCaseKeys())->not->toThrow(Throwable::class);
        });

        test('toHaveKebabCaseKeys() accepts kebab-case keys', function (): void {
            expect(fn (): Expectation => assertExpect(['first-name' => 'John', 'last-name' => 'Doe'])
                ->toHaveKebabCaseKeys())->not->toThrow(Throwable::class);
        });

        test('toHaveCamelCaseKeys() accepts camelCase keys', function (): void {
            expect(fn (): Expectation => assertExpect(['firstName' => 'John', 'lastName' => 'Doe'])
                ->toHaveCamelCaseKeys())->not->toThrow(Throwable::class);
        });

        test('toHaveStudlyCaseKeys() accepts StudlyCase keys', function (): void {
            expect(fn (): Expectation => assertExpect(['FirstName' => 'John', 'LastName' => 'Doe'])
                ->toHaveStudlyCaseKeys())->not->toThrow(Throwable::class);
        });
    });

    describe('File System Expectations', function (): void {
        test('toBeFile() accepts file paths', function (): void {
            $file = tempnam(sys_get_temp_dir(), 'test');
            expect(fn (): Expectation => assertExpect($file)->toBeFile())->not->toThrow(Throwable::class);
            unlink($file);
        });

        test('toBeReadableFile() accepts readable files', function (): void {
            $file = tempnam(sys_get_temp_dir(), 'test');
            expect(fn (): Expectation => assertExpect($file)->toBeReadableFile())->not->toThrow(Throwable::class);
            unlink($file);
        });

        test('toBeWritableFile() accepts writable files', function (): void {
            $file = tempnam(sys_get_temp_dir(), 'test');
            expect(fn (): Expectation => assertExpect($file)->toBeWritableFile())->not->toThrow(Throwable::class);
            unlink($file);
        });
    });

    describe('Advanced Collection Methods', function (): void {
        test('toHaveSameSize() accepts same-sized collections', function (): void {
            expect(fn (): Expectation => assertExpect([1, 2, 3])->toHaveSameSize([4, 5, 6]))
                ->not->toThrow(Throwable::class);
        });

        test('toHaveSameSize() rejects different-sized collections', function (): void {
            expect(fn (): Expectation => assertExpect([1, 2])->toHaveSameSize([1, 2, 3]))
                ->toThrow(InvalidArgumentException::class);
        });

        test('toContainOnlyInstancesOf() accepts array of same class', function (): void {
            $arr = [new stdClass(), new stdClass()];
            expect(fn (): Expectation => assertExpect($arr)->toContainOnlyInstancesOf(stdClass::class))
                ->not->toThrow(Throwable::class);
        });

        test('toContainOnlyInstancesOf() rejects mixed types', function (): void {
            $arr = [new stdClass(), 'string'];
            expect(fn (): Expectation => assertExpect($arr)->toContainOnlyInstancesOf(stdClass::class))
                ->toThrow(InvalidArgumentException::class);
        });
    });
});
