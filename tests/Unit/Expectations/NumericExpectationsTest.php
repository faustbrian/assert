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

describe('Numeric Expectations', function (): void {
    describe('Comparison Methods', function (): void {
        test('toBeGreaterThan() accepts greater values', function (): void {
            expect(fn() => assertExpect(10)->toBeGreaterThan(5))->not->toThrow(\Throwable::class);
            expect(fn() => assertExpect(100)->toBeGreaterThan(99))->not->toThrow(\Throwable::class);
            expect(fn() => assertExpect(0)->toBeGreaterThan(-1))->not->toThrow(\Throwable::class);
        });

        test('toBeGreaterThan() rejects equal or lesser values', function (): void {
            expect(fn () => assertExpect(5)->toBeGreaterThan(5))
                ->toThrow(InvalidArgumentException::class);
            expect(fn () => assertExpect(5)->toBeGreaterThan(10))
                ->toThrow(InvalidArgumentException::class);
        });

        test('toBeGreaterThanOrEqual() accepts greater or equal values', function (): void {
            expect(fn() => assertExpect(10)->toBeGreaterThanOrEqual(5))->not->toThrow(\Throwable::class);
            expect(fn() => assertExpect(10)->toBeGreaterThanOrEqual(10))->not->toThrow(\Throwable::class);
            expect(fn() => assertExpect(0)->toBeGreaterThanOrEqual(0))->not->toThrow(\Throwable::class);
        });

        test('toBeGreaterThanOrEqual() rejects lesser values', function (): void {
            expect(fn () => assertExpect(5)->toBeGreaterThanOrEqual(10))
                ->toThrow(InvalidArgumentException::class);
        });

        test('toBeLessThan() accepts lesser values', function (): void {
            expect(fn() => assertExpect(5)->toBeLessThan(10))->not->toThrow(\Throwable::class);
            expect(fn() => assertExpect(99)->toBeLessThan(100))->not->toThrow(\Throwable::class);
            expect(fn() => assertExpect(-1)->toBeLessThan(0))->not->toThrow(\Throwable::class);
        });

        test('toBeLessThan() rejects equal or greater values', function (): void {
            expect(fn () => assertExpect(10)->toBeLessThan(10))
                ->toThrow(InvalidArgumentException::class);
            expect(fn () => assertExpect(10)->toBeLessThan(5))
                ->toThrow(InvalidArgumentException::class);
        });

        test('toBeLessThanOrEqual() accepts lesser or equal values', function (): void {
            expect(fn() => assertExpect(5)->toBeLessThanOrEqual(10))->not->toThrow(\Throwable::class);
            expect(fn() => assertExpect(10)->toBeLessThanOrEqual(10))->not->toThrow(\Throwable::class);
            expect(fn() => assertExpect(0)->toBeLessThanOrEqual(0))->not->toThrow(\Throwable::class);
        });

        test('toBeLessThanOrEqual() rejects greater values', function (): void {
            expect(fn () => assertExpect(10)->toBeLessThanOrEqual(5))
                ->toThrow(InvalidArgumentException::class);
        });

        test('toBeBetween() accepts values in range (inclusive)', function (): void {
            expect(fn() => assertExpect(5)->toBeBetween(1, 10))->not->toThrow(\Throwable::class);
            expect(fn() => assertExpect(1)->toBeBetween(1, 10))->not->toThrow(\Throwable::class);
            expect(fn() => assertExpect(10)->toBeBetween(1, 10))->not->toThrow(\Throwable::class);
            expect(fn() => assertExpect(0)->toBeBetween(-5, 5))->not->toThrow(\Throwable::class);
        });

        test('toBeBetween() rejects values outside range', function (): void {
            expect(fn () => assertExpect(0)->toBeBetween(1, 10))
                ->toThrow(InvalidArgumentException::class);
            expect(fn () => assertExpect(11)->toBeBetween(1, 10))
                ->toThrow(InvalidArgumentException::class);
        });
    });

    describe('Negation with Comparisons', function (): void {
        test('not->toBeGreaterThan() accepts equal or lesser values', function (): void {
            expect(fn() => assertExpect(5)->not->toBeGreaterThan(5))->not->toThrow(\Throwable::class);
            expect(fn() => assertExpect(5)->not->toBeGreaterThan(10))->not->toThrow(\Throwable::class);
        });

        test('not->toBeLessThan() accepts equal or greater values', function (): void {
            expect(fn() => assertExpect(10)->not->toBeLessThan(10))->not->toThrow(\Throwable::class);
            expect(fn() => assertExpect(10)->not->toBeLessThan(5))->not->toThrow(\Throwable::class);
        });

        test('not->toBeBetween() accepts values outside range', function (): void {
            expect(fn() => assertExpect(0)->not->toBeBetween(1, 10))->not->toThrow(\Throwable::class);
            expect(fn() => assertExpect(11)->not->toBeBetween(1, 10))->not->toThrow(\Throwable::class);
        });
    });

    describe('Chaining Comparisons', function (): void {
        test('can chain multiple comparison assertions', function (): void {
            expect(fn() => assertExpect(5)
                ->toBeGreaterThan(0)
                ->toBeLessThan(10)
                ->toBeBetween(1, 10))->not->toThrow(\Throwable::class);
        });

        test('can mix comparisons with type checks', function (): void {
            expect(fn() => assertExpect(42)
                ->toBeInt()
                ->toBeGreaterThan(0)
                ->toBeLessThan(100))->not->toThrow(\Throwable::class);
        });
    });
});
