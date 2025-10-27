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

describe('Collection Quantifiers', function (): void {
    describe('->all quantifier', function (): void {
        test('passes when all items match', function (): void {
            expect(
                assertExpect([1, 2, 3, 4])->all->toBeInt(),
            )->not->toThrow(Throwable::class);
        });

        test('passes when all strings', function (): void {
            expect(
                assertExpect(['hello', 'world'])->all->toBeString(),
            )->not->toThrow(Throwable::class);
        });

        test('fails when some items do not match', function (): void {
            expect(
                fn () => assertExpect([1, 2, 'three', 4])->all->toBeInt(),
            )->toThrow(InvalidArgumentException::class);
        });

        test('works with empty array', function (): void {
            expect(
                assertExpect([])->all->toBeInt(),
            )->not->toThrow(Throwable::class);
        });

        test('error message shows which items failed', function (): void {
            try {
                assertExpect([1, 2, 'three', 4, 'five'])->all->toBeInt();
            } catch (InvalidArgumentException $e) {
                expect($e->getMessage())->toContain('Expected all items to match');
                expect($e->getMessage())->toContain('2/5 items failed');
            }
        });
    });

    describe('->any quantifier', function (): void {
        test('passes when at least one item matches', function (): void {
            expect(
                assertExpect([1, 'two', 3])->any->toBeString(),
            )->not->toThrow(Throwable::class);
        });

        test('passes when multiple items match', function (): void {
            expect(
                assertExpect(['hello', 'world', 123])->any->toBeString(),
            )->not->toThrow(Throwable::class);
        });

        test('fails when no items match', function (): void {
            expect(
                fn () => assertExpect([1, 2, 3])->any->toBeString(),
            )->toThrow(InvalidArgumentException::class);
        });

        test('error message shows total count', function (): void {
            try {
                assertExpect([1, 2, 3])->any->toBeString();
            } catch (InvalidArgumentException $e) {
                expect($e->getMessage())->toContain('Expected at least one item to match');
                expect($e->getMessage())->toContain('All 3 items failed');
            }
        });
    });

    describe('->none quantifier', function (): void {
        test('passes when no items match', function (): void {
            expect(
                assertExpect([1, 2, 3])->none->toBeString(),
            )->not->toThrow(Throwable::class);
        });

        test('fails when any item matches', function (): void {
            expect(
                fn () => assertExpect([1, 'two', 3])->none->toBeString(),
            )->toThrow(InvalidArgumentException::class);
        });

        test('works with empty array', function (): void {
            expect(
                assertExpect([])->none->toBeString(),
            )->not->toThrow(Throwable::class);
        });

        test('error message shows matched count', function (): void {
            try {
                assertExpect(['hello', 'world', 123])->none->toBeString();
            } catch (InvalidArgumentException $e) {
                expect($e->getMessage())->toContain('Expected no items to match');
                expect($e->getMessage())->toContain('2/3 items matched');
            }
        });
    });

    describe('Complex patterns', function (): void {
        test('all items must be strings', function (): void {
            expect(
                assertExpect(['hello', 'world', 'tests'])->all->toBeString(),
            )->not->toThrow(Throwable::class);
        });

        test('any item is positive integer', function (): void {
            expect(
                assertExpect([-5, 0, 10])->any->toBePositive(),
            )->not->toThrow(Throwable::class);
        });

        test('none are null', function (): void {
            expect(
                assertExpect([1, 'hello', []])->none->toBeNull(),
            )->not->toThrow(Throwable::class);
        });

        test('works with iterators', function (): void {
            $iterator = new ArrayIterator([1, 2, 3]);

            expect(
                assertExpect($iterator)->all->toBeInt(),
            )->not->toThrow(Throwable::class);
        });

        test('throws for non-iterable', function (): void {
            expect(
                fn () => assertExpect(123)->all->toBeInt(),
            )->toThrow(InvalidArgumentException::class);
        });
    });
});
