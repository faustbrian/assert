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
            assertExpect(42)->not->toBe(43);
            assertExpect('hello')->not->toBe('world');
            expect(true)->toBeTrue();
        });

        test('not->toBeNull() accepts non-null values', function (): void {
            assertExpect(42)->not->toBeNull();
            assertExpect('test')->not->toBeNull();
            assertExpect(0)->not->toBeNull();
            assertExpect(false)->not->toBeNull();
            expect(true)->toBeTrue();
        });

        test('not->toBeTrue() accepts non-true values', function (): void {
            assertExpect(false)->not->toBeTrue();
            assertExpect(0)->not->toBeTrue();
            assertExpect('test')->not->toBeTrue();
            expect(true)->toBeTrue();
        });

        test('not->toBeFalse() accepts non-false values', function (): void {
            assertExpect(true)->not->toBeFalse();
            assertExpect(1)->not->toBeFalse();
            assertExpect('test')->not->toBeFalse();
            expect(true)->toBeTrue();
        });

        test('not->toBeTruthy() accepts falsy values', function (): void {
            assertExpect(0)->not->toBeTruthy();
            assertExpect('')->not->toBeTruthy();
            assertExpect(false)->not->toBeTruthy();
            assertExpect(null)->not->toBeTruthy();
            expect(true)->toBeTrue();
        });

        test('not->toBeFalsy() accepts truthy values', function (): void {
            assertExpect(1)->not->toBeFalsy();
            assertExpect('yes')->not->toBeFalsy();
            assertExpect(true)->not->toBeFalsy();
            assertExpect([1])->not->toBeFalsy();
            expect(true)->toBeTrue();
        });

        test('not->toBeEmpty() accepts non-empty values', function (): void {
            assertExpect('hello')->not->toBeEmpty();
            assertExpect([1, 2])->not->toBeEmpty();
            assertExpect(42)->not->toBeEmpty();
            expect(true)->toBeTrue();
        });

        test('not->toBeString() accepts non-string values', function (): void {
            assertExpect(42)->not->toBeString();
            assertExpect([])->not->toBeString();
            assertExpect(true)->not->toBeString();
            expect(true)->toBeTrue();
        });

        test('not->toBeInt() accepts non-integer values', function (): void {
            assertExpect('42')->not->toBeInt();
            assertExpect(3.14)->not->toBeInt();
            assertExpect([])->not->toBeInt();
            expect(true)->toBeTrue();
        });

        test('not->toBeArray() accepts non-array values', function (): void {
            assertExpect('test')->not->toBeArray();
            assertExpect(42)->not->toBeArray();
            assertExpect(null)->not->toBeArray();
            expect(true)->toBeTrue();
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
            expect(true)->toBeTrue();
        });

        test('not property returns new instance', function (): void {
            $expectation = assertExpect(42);
            $negated = $expectation->not;

            expect($negated)->toBeInstanceOf(Expectation::class);
            expect($negated !== $expectation)->toBeTrue();
        });

        test('chaining after negation works', function (): void {
            assertExpect(42)
                ->not->toBeNull()
                ->toBeInt()
                ->not->toBeString();
            expect(true)->toBeTrue();
        });
    });
});
