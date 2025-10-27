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

describe('Collection Expectations', function (): void {
    describe('Array/Collection Methods', function (): void {
        test('toHaveCount() accepts arrays with exact count', function (): void {
            expect(fn (): Expectation => assertExpect([1, 2, 3])->toHaveCount(3))->not->toThrow(Throwable::class);
            expect(fn (): Expectation => assertExpect([])->toHaveCount(0))->not->toThrow(Throwable::class);
            expect(fn (): Expectation => assertExpect(['a' => 1, 'b' => 2])->toHaveCount(2))->not->toThrow(Throwable::class);
        });

        test('toHaveCount() rejects arrays with different count', function (): void {
            expect(fn (): Expectation => assertExpect([1, 2, 3])->toHaveCount(2))
                ->toThrow(InvalidArgumentException::class);
        });

        test('toHaveKey() accepts arrays with existing key', function (): void {
            expect(fn (): Expectation => assertExpect(['name' => 'John'])->toHaveKey('name'))->not->toThrow(Throwable::class);
            expect(fn (): Expectation => assertExpect([1, 2, 3])->toHaveKey(0))->not->toThrow(Throwable::class);
            expect(fn (): Expectation => assertExpect(['a' => 1, 'b' => 2])->toHaveKey('b'))->not->toThrow(Throwable::class);
        });

        test('toHaveKey() rejects arrays without key', function (): void {
            expect(fn (): Expectation => assertExpect(['name' => 'John'])->toHaveKey('email'))
                ->toThrow(InvalidArgumentException::class);
        });

        test('toContain() accepts arrays containing value', function (): void {
            expect(fn (): Expectation => assertExpect([1, 2, 3])->toContain(2))->not->toThrow(Throwable::class);
            expect(fn (): Expectation => assertExpect(['a', 'b', 'c'])->toContain('b'))->not->toThrow(Throwable::class);
            expect(fn (): Expectation => assertExpect([true, false])->toContain(true))->not->toThrow(Throwable::class);
        });

        test('toContain() rejects arrays not containing value', function (): void {
            expect(fn (): Expectation => assertExpect([1, 2, 3])->toContain(4))
                ->toThrow(InvalidArgumentException::class);
        });

        test('toHaveCount() works with countable objects', function (): void {
            expect(fn (): Expectation => assertExpect(
                new ArrayObject([1, 2, 3]),
            )->toHaveCount(3))->not->toThrow(Throwable::class);
        });
    });

    describe('Negation with Collections', function (): void {
        test('not->toHaveCount() accepts arrays with different count', function (): void {
            expect(fn () => assertExpect([1, 2, 3])->not->toHaveCount(2))->not->toThrow(Throwable::class);
            expect(fn () => assertExpect([])->not->toHaveCount(1))->not->toThrow(Throwable::class);
        });

        test('not->toHaveKey() accepts arrays without key', function (): void {
            expect(fn () => assertExpect(['name' => 'John'])->not->toHaveKey('email'))->not->toThrow(Throwable::class);
        });

        test('not->toContain() accepts arrays without value', function (): void {
            expect(fn () => assertExpect([1, 2, 3])->not->toContain(4))->not->toThrow(Throwable::class);
        });
    });

    describe('Chaining Collection Methods', function (): void {
        test('can chain multiple collection assertions', function (): void {
            expect(fn (): Expectation => assertExpect(['name' => 'John', 'email' => 'john@example.com'])
                ->toBeArray()
                ->toHaveCount(2)
                ->toHaveKey('name')
                ->toHaveKey('email'))->not->toThrow(Throwable::class);
        });

        test('can mix collection and value checks', function (): void {
            expect(fn () => assertExpect([1, 2, 3])
                ->toBeArray()
                ->toHaveCount(3)
                ->toContain(2)
                ->not->toContain(4))->not->toThrow(Throwable::class);
        });
    });

    describe('Edge Cases', function (): void {
        test('toHaveLength() works for arrays (alias for toHaveCount)', function (): void {
            expect(fn (): Expectation => assertExpect([1, 2, 3])->toHaveLength(3))->not->toThrow(Throwable::class);
        });

        test('toContain() requires string or array', function (): void {
            expect(fn (): Expectation => assertExpect(42)->toContain('foo'))
                ->toThrow(InvalidArgumentException::class);
        });
    });
});
