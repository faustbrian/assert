<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Assert\Exceptions\AssertionFailedException;
use Cline\Assert\Expectations\Expectation;

use function Cline\Assert\expect as assertExpect;

describe('Negation Modifier (->not)', function (): void {
    describe('Happy Paths', function (): void {
        test('not->toBe() rejects equal values', function (): void {
            expect(fn() => assertExpect(42)->not->toBe(43))->not->toThrow();
            expect(fn() => assertExpect('hello')->not->toBe('world'))->not->toThrow();
        });

        test('not->toBeNull() accepts non-null values', function (): void {
            expect(fn() => assertExpect(42)->not->toBeNull())->not->toThrow();
            expect(fn() => assertExpect('test')->not->toBeNull())->not->toThrow();
            expect(fn() => assertExpect(0)->not->toBeNull())->not->toThrow();
            expect(fn() => assertExpect(false)->not->toBeNull())->not->toThrow();
        });

        test('not->toBeTrue() accepts non-true values', function (): void {
            expect(fn() => assertExpect(false)->not->toBeTrue())->not->toThrow();
            expect(fn() => assertExpect(0)->not->toBeTrue())->not->toThrow();
            expect(fn() => assertExpect('test')->not->toBeTrue())->not->toThrow();
        });

        test('not->toBeFalse() accepts non-false values', function (): void {
            expect(fn() => assertExpect(true)->not->toBeFalse())->not->toThrow();
            expect(fn() => assertExpect(1)->not->toBeFalse())->not->toThrow();
            expect(fn() => assertExpect('test')->not->toBeFalse())->not->toThrow();
        });

        test('not->toBeTruthy() accepts falsy values', function (): void {
            expect(fn() => assertExpect(0)->not->toBeTruthy())->not->toThrow();
            expect(fn() => assertExpect('')->not->toBeTruthy())->not->toThrow();
            expect(fn() => assertExpect(false)->not->toBeTruthy())->not->toThrow();
            expect(fn() => assertExpect(null)->not->toBeTruthy())->not->toThrow();
        });

        test('not->toBeFalsy() accepts truthy values', function (): void {
            expect(fn() => assertExpect(1)->not->toBeFalsy())->not->toThrow();
            expect(fn() => assertExpect('yes')->not->toBeFalsy())->not->toThrow();
            expect(fn() => assertExpect(true)->not->toBeFalsy())->not->toThrow();
            expect(fn() => assertExpect([1])->not->toBeFalsy())->not->toThrow();
        });

        test('not->toBeEmpty() accepts non-empty values', function (): void {
            expect(fn() => assertExpect('hello')->not->toBeEmpty())->not->toThrow();
            expect(fn() => assertExpect([1, 2])->not->toBeEmpty())->not->toThrow();
            expect(fn() => assertExpect(42)->not->toBeEmpty())->not->toThrow();
        });

        test('not->toBeString() accepts non-string values', function (): void {
            expect(fn() => assertExpect(42)->not->toBeString())->not->toThrow();
            expect(fn() => assertExpect([])->not->toBeString())->not->toThrow();
            expect(fn() => assertExpect(true)->not->toBeString())->not->toThrow();
        });

        test('not->toBeInt() accepts non-integer values', function (): void {
            expect(fn() => assertExpect('42')->not->toBeInt())->not->toThrow();
            expect(fn() => assertExpect(3.14)->not->toBeInt())->not->toThrow();
            expect(fn() => assertExpect([])->not->toBeInt())->not->toThrow();
        });

        test('not->toBeArray() accepts non-array values', function (): void {
            expect(fn() => assertExpect('test')->not->toBeArray())->not->toThrow();
            expect(fn() => assertExpect(42)->not->toBeArray())->not->toThrow();
            expect(fn() => assertExpect(null)->not->toBeArray())->not->toThrow();
        });
    });

    describe('Sad Paths', function (): void {
        test('not->toBe() fails when values are equal', function (): void {
            assertExpect(fn () => assertExpect(42)->not->toBe(42))
                ->toThrow(AssertionFailedException::class);
        });

        test('not->toBeNull() fails when value is null', function (): void {
            assertExpect(assertExpect(null)->not->toBeNull(...))
                ->toThrow(AssertionFailedException::class);
        });

        test('not->toBeTrue() fails when value is true', function (): void {
            assertExpect(assertExpect(true)->not->toBeTrue(...))
                ->toThrow(AssertionFailedException::class);
        });

        test('not->toBeFalse() fails when value is false', function (): void {
            assertExpect(assertExpect(false)->not->toBeFalse(...))
                ->toThrow(AssertionFailedException::class);
        });

        test('not->toBeTruthy() fails when value is truthy', function (): void {
            assertExpect(assertExpect(1)->not->toBeTruthy(...))
                ->toThrow(AssertionFailedException::class);
        });

        test('not->toBeFalsy() fails when value is falsy', function (): void {
            assertExpect(assertExpect(0)->not->toBeFalsy(...))
                ->toThrow(AssertionFailedException::class);
        });

        test('not->toBeString() fails when value is string', function (): void {
            assertExpect(assertExpect('test')->not->toBeString(...))
                ->toThrow(AssertionFailedException::class);
        });
    });

    describe('Edge Cases', function (): void {
        test('multiple not calls create separate clones', function (): void {
            $base = assertExpect(42);

            expect($base->not->toBe(43))->toBeInstanceOf(Expectation::class);
            expect($base->toBe(42))->toBeInstanceOf(Expectation::class);
        });

        test('not property returns new instance', function (): void {
            $expectation = assertExpect(42);
            $negated = $expectation->not;

            expect($negated)->toBeInstanceOf(Expectation::class);
            expect($negated !== $expectation)->toBeTrue();
        });

        test('chaining after negation works', function (): void {
            expect(fn() => assertExpect(42)
                ->not->toBeNull()
                ->toBeInt()
                ->not->toBeString())->not->toThrow();
        });
    });
});
