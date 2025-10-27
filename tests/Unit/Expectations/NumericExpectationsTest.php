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

describe('Numeric Expectations', function (): void {
    describe('Comparison Methods', function (): void {
        test('toBeGreaterThan() accepts greater values', function (): void {
            assertExpect(10)->toBeGreaterThan(5);
            assertExpect(100)->toBeGreaterThan(99);
            assertExpect(0)->toBeGreaterThan(-1);

            expect(true)->toBeTrue();
        });

        test('toBeGreaterThan() rejects equal or lesser values', function (): void {
            assertExpect(fn (): Expectation => assertExpect(5)->toBeGreaterThan(5))
                ->toThrow(AssertionFailedException::class);
            assertExpect(fn (): Expectation => assertExpect(5)->toBeGreaterThan(10))
                ->toThrow(AssertionFailedException::class);
        });

        test('toBeGreaterThanOrEqual() accepts greater or equal values', function (): void {
            assertExpect(10)->toBeGreaterThanOrEqual(5);
            assertExpect(10)->toBeGreaterThanOrEqual(10);
            assertExpect(0)->toBeGreaterThanOrEqual(0);

            expect(true)->toBeTrue();
        });

        test('toBeGreaterThanOrEqual() rejects lesser values', function (): void {
            assertExpect(fn (): Expectation => assertExpect(5)->toBeGreaterThanOrEqual(10))
                ->toThrow(AssertionFailedException::class);
        });

        test('toBeLessThan() accepts lesser values', function (): void {
            assertExpect(5)->toBeLessThan(10);
            assertExpect(99)->toBeLessThan(100);
            assertExpect(-1)->toBeLessThan(0);

            expect(true)->toBeTrue();
        });

        test('toBeLessThan() rejects equal or greater values', function (): void {
            assertExpect(fn (): Expectation => assertExpect(10)->toBeLessThan(10))
                ->toThrow(AssertionFailedException::class);
            assertExpect(fn (): Expectation => assertExpect(10)->toBeLessThan(5))
                ->toThrow(AssertionFailedException::class);
        });

        test('toBeLessThanOrEqual() accepts lesser or equal values', function (): void {
            assertExpect(5)->toBeLessThanOrEqual(10);
            assertExpect(10)->toBeLessThanOrEqual(10);
            assertExpect(0)->toBeLessThanOrEqual(0);

            expect(true)->toBeTrue();
        });

        test('toBeLessThanOrEqual() rejects greater values', function (): void {
            assertExpect(fn (): Expectation => assertExpect(10)->toBeLessThanOrEqual(5))
                ->toThrow(AssertionFailedException::class);
        });

        test('toBeBetween() accepts values in range (inclusive)', function (): void {
            assertExpect(5)->toBeBetween(1, 10);
            assertExpect(1)->toBeBetween(1, 10);
            assertExpect(10)->toBeBetween(1, 10);
            assertExpect(0)->toBeBetween(-5, 5);

            expect(true)->toBeTrue();
        });

        test('toBeBetween() rejects values outside range', function (): void {
            assertExpect(fn (): Expectation => assertExpect(0)->toBeBetween(1, 10))
                ->toThrow(AssertionFailedException::class);
            assertExpect(fn (): Expectation => assertExpect(11)->toBeBetween(1, 10))
                ->toThrow(AssertionFailedException::class);
        });
    });

    describe('Negation with Comparisons', function (): void {
        test('not->toBeGreaterThan() accepts equal or lesser values', function (): void {
            assertExpect(5)->not->toBeGreaterThan(5);
            assertExpect(5)->not->toBeGreaterThan(10);

            expect(true)->toBeTrue();
        });

        test('not->toBeLessThan() accepts equal or greater values', function (): void {
            assertExpect(10)->not->toBeLessThan(10);
            assertExpect(10)->not->toBeLessThan(5);

            expect(true)->toBeTrue();
        });

        test('not->toBeBetween() accepts values outside range', function (): void {
            assertExpect(0)->not->toBeBetween(1, 10);
            assertExpect(11)->not->toBeBetween(1, 10);

            expect(true)->toBeTrue();
        });
    });

    describe('Chaining Comparisons', function (): void {
        test('can chain multiple comparison assertions', function (): void {
            assertExpect(5)
                ->toBeGreaterThan(0)
                ->toBeLessThan(10)
                ->toBeBetween(1, 10);
            expect(true)->toBeTrue();
        });

        test('can mix comparisons with type checks', function (): void {
            assertExpect(42)
                ->toBeInt()
                ->toBeGreaterThan(0)
                ->toBeLessThan(100);
            expect(true)->toBeTrue();
        });
    });
});
