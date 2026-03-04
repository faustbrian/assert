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

describe('High Priority Expectations', function (): void {
    describe('toBeAlpha()', function (): void {
        test('accepts alphabetic strings', function (): void {
            expect(fn (): Expectation => assertExpect('abc')->toBeAlpha())->not->toThrow(Throwable::class);
            expect(fn (): Expectation => assertExpect('ABC')->toBeAlpha())->not->toThrow(Throwable::class);
            expect(fn (): Expectation => assertExpect('AbCdEf')->toBeAlpha())->not->toThrow(Throwable::class);
        });

        test('rejects non-alphabetic strings', function (): void {
            expect(fn (): Expectation => assertExpect('abc123')->toBeAlpha())
                ->toThrow(InvalidArgumentException::class);
            expect(fn (): Expectation => assertExpect('hello world')->toBeAlpha())
                ->toThrow(InvalidArgumentException::class);
        });
    });

    describe('toBeAlphaNumeric()', function (): void {
        test('accepts alphanumeric strings', function (): void {
            expect(fn (): Expectation => assertExpect('abc123')->toBeAlphaNumeric())->not->toThrow(Throwable::class);
            expect(fn (): Expectation => assertExpect('test123')->toBeAlphaNumeric())->not->toThrow(Throwable::class);
        });

        test('rejects non-alphanumeric strings', function (): void {
            expect(fn (): Expectation => assertExpect('hello world')->toBeAlphaNumeric())
                ->toThrow(InvalidArgumentException::class);
        });
    });

    describe('toBeIn()', function (): void {
        test('accepts value in array', function (): void {
            expect(fn (): Expectation => assertExpect(2)->toBeIn([1, 2, 3]))->not->toThrow(Throwable::class);
            expect(fn (): Expectation => assertExpect('b')->toBeIn(['a', 'b', 'c']))->not->toThrow(Throwable::class);
        });

        test('rejects value not in array', function (): void {
            expect(fn (): Expectation => assertExpect(4)->toBeIn([1, 2, 3]))
                ->toThrow(InvalidArgumentException::class);
        });
    });

    describe('toHaveKeys()', function (): void {
        test('accepts array with all keys', function (): void {
            expect(fn (): Expectation => assertExpect(['name' => 'John', 'email' => 'j@ex.com'])
                ->toHaveKeys(['name', 'email']))->not->toThrow(Throwable::class);
        });

        test('rejects array missing keys', function (): void {
            expect(fn (): Expectation => assertExpect(['name' => 'John'])
                ->toHaveKeys(['name', 'email']))
                ->toThrow(InvalidArgumentException::class);
        });
    });

    describe('toHaveProperties()', function (): void {
        test('accepts object with all properties', function (): void {
            $obj = (object) ['name' => 'John', 'email' => 'j@ex.com'];
            expect(fn (): Expectation => assertExpect($obj)->toHaveProperties(['name', 'email']))
                ->not->toThrow(Throwable::class);
        });

        test('rejects object missing properties', function (): void {
            $obj = (object) ['name' => 'John'];
            expect(fn (): Expectation => assertExpect($obj)->toHaveProperties(['name', 'email']))
                ->toThrow(InvalidArgumentException::class);
        });
    });

    describe('toContainEqual()', function (): void {
        test('accepts array containing equal value', function (): void {
            expect(fn (): Expectation => assertExpect([['a' => 1], ['b' => 2]])->toContainEqual(['a' => 1]))
                ->not->toThrow(Throwable::class);
        });

        test('rejects array not containing equal value', function (): void {
            expect(fn (): Expectation => assertExpect([['a' => 1], ['b' => 2]])->toContainEqual(['c' => 3]))
                ->toThrow(InvalidArgumentException::class);
        });
    });

    describe('toMatchArray()', function (): void {
        test('accepts array subset match', function (): void {
            expect(fn (): Expectation => assertExpect(['a' => 1, 'b' => 2, 'c' => 3])
                ->toMatchArray(['a' => 1, 'c' => 3]))
                ->not->toThrow(Throwable::class);
        });

        test('rejects non-matching array', function (): void {
            expect(fn (): Expectation => assertExpect(['a' => 1, 'b' => 2])
                ->toMatchArray(['a' => 1, 'c' => 3]))
                ->toThrow(InvalidArgumentException::class);
        });
    });

    describe('toMatchObject()', function (): void {
        test('accepts object with matching properties', function (): void {
            $obj = (object) ['name' => 'John', 'age' => 30, 'city' => 'NYC'];
            expect(fn (): Expectation => assertExpect($obj)->toMatchObject(['name' => 'John', 'age' => 30]))
                ->not->toThrow(Throwable::class);
        });

        test('rejects object with non-matching properties', function (): void {
            $obj = (object) ['name' => 'John', 'age' => 30];
            expect(fn (): Expectation => assertExpect($obj)->toMatchObject(['name' => 'Jane']))
                ->toThrow(InvalidArgumentException::class);
        });
    });
});
