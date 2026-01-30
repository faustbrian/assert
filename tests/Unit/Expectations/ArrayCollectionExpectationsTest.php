<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Assert\Exceptions\InvalidArgumentException;

use function Cline\Assert\expect as assertExpect;

describe('Array/Collection Expectations', function (): void {
    describe('->toBeSubsetOf()', function (): void {
        test('passes when array is subset of superset', function (): void {
            expect(
                assertExpect([1, 2])->toBeSubsetOf([1, 2, 3, 4, 5]),
            )->not->toThrow(Throwable::class);
        });

        test('passes when array equals superset', function (): void {
            expect(
                assertExpect([1, 2, 3])->toBeSubsetOf([1, 2, 3]),
            )->not->toThrow(Throwable::class);
        });

        test('passes with empty array', function (): void {
            expect(
                assertExpect([])->toBeSubsetOf([1, 2, 3]),
            )->not->toThrow(Throwable::class);
        });

        test('fails when array has elements not in superset', function (): void {
            expect(
                fn (): mixed => assertExpect([1, 2, 6])->toBeSubsetOf([1, 2, 3, 4, 5]),
            )->toThrow(InvalidArgumentException::class);
        });

        test('works with associative arrays', function (): void {
            expect(
                assertExpect(['a' => 1, 'b' => 2])->toBeSubsetOf(['a' => 1, 'b' => 2, 'c' => 3]),
            )->not->toThrow(Throwable::class);
        });
    });

    describe('->toHaveUniqueValues()', function (): void {
        test('passes when array has unique values', function (): void {
            expect(
                assertExpect([1, 2, 3, 4, 5])->toHaveUniqueValues(),
            )->not->toThrow(Throwable::class);
        });

        test('passes with empty array', function (): void {
            expect(
                assertExpect([])->toHaveUniqueValues(),
            )->not->toThrow(Throwable::class);
        });

        test('passes with single element', function (): void {
            expect(
                assertExpect([1])->toHaveUniqueValues(),
            )->not->toThrow(Throwable::class);
        });

        test('fails when array has duplicate values', function (): void {
            expect(
                fn (): mixed => assertExpect([1, 2, 3, 2, 4])->toHaveUniqueValues(),
            )->toThrow(InvalidArgumentException::class);
        });

        test('works with string values', function (): void {
            expect(
                assertExpect(['apple', 'banana', 'cherry'])->toHaveUniqueValues(),
            )->not->toThrow(Throwable::class);

            expect(
                fn (): mixed => assertExpect(['apple', 'banana', 'apple'])->toHaveUniqueValues(),
            )->toThrow(InvalidArgumentException::class);
        });
    });

    describe('->toBeSorted()', function (): void {
        test('passes when array is sorted ascending', function (): void {
            expect(
                assertExpect([1, 2, 3, 4, 5])->toBeSorted(),
            )->not->toThrow(Throwable::class);
        });

        test('passes with empty array', function (): void {
            expect(
                assertExpect([])->toBeSorted(),
            )->not->toThrow(Throwable::class);
        });

        test('passes with single element', function (): void {
            expect(
                assertExpect([1])->toBeSorted(),
            )->not->toThrow(Throwable::class);
        });

        test('passes with equal consecutive elements', function (): void {
            expect(
                assertExpect([1, 2, 2, 3, 4])->toBeSorted(),
            )->not->toThrow(Throwable::class);
        });

        test('fails when array is not sorted', function (): void {
            expect(
                fn (): mixed => assertExpect([1, 3, 2, 4, 5])->toBeSorted(),
            )->toThrow(InvalidArgumentException::class);
        });

        test('fails when array is descending', function (): void {
            expect(
                fn (): mixed => assertExpect([5, 4, 3, 2, 1])->toBeSorted(),
            )->toThrow(InvalidArgumentException::class);
        });

        test('works with string arrays', function (): void {
            expect(
                assertExpect(['apple', 'banana', 'cherry'])->toBeSorted(),
            )->not->toThrow(Throwable::class);

            expect(
                fn (): mixed => assertExpect(['cherry', 'banana', 'apple'])->toBeSorted(),
            )->toThrow(InvalidArgumentException::class);
        });
    });

    describe('->toBeSortedDesc()', function (): void {
        test('passes when array is sorted descending', function (): void {
            expect(
                assertExpect([5, 4, 3, 2, 1])->toBeSortedDesc(),
            )->not->toThrow(Throwable::class);
        });

        test('passes with empty array', function (): void {
            expect(
                assertExpect([])->toBeSortedDesc(),
            )->not->toThrow(Throwable::class);
        });

        test('passes with single element', function (): void {
            expect(
                assertExpect([1])->toBeSortedDesc(),
            )->not->toThrow(Throwable::class);
        });

        test('passes with equal consecutive elements', function (): void {
            expect(
                assertExpect([5, 4, 4, 3, 2])->toBeSortedDesc(),
            )->not->toThrow(Throwable::class);
        });

        test('fails when array is not sorted descending', function (): void {
            expect(
                fn (): mixed => assertExpect([5, 3, 4, 2, 1])->toBeSortedDesc(),
            )->toThrow(InvalidArgumentException::class);
        });

        test('fails when array is ascending', function (): void {
            expect(
                fn (): mixed => assertExpect([1, 2, 3, 4, 5])->toBeSortedDesc(),
            )->toThrow(InvalidArgumentException::class);
        });

        test('works with string arrays', function (): void {
            expect(
                assertExpect(['cherry', 'banana', 'apple'])->toBeSortedDesc(),
            )->not->toThrow(Throwable::class);

            expect(
                fn (): mixed => assertExpect(['apple', 'banana', 'cherry'])->toBeSortedDesc(),
            )->toThrow(InvalidArgumentException::class);
        });
    });
});
