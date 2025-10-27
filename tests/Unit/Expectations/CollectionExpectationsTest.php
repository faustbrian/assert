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

describe('Collection Expectations', function (): void {
    describe('Array/Collection Methods', function (): void {
        test('toHaveCount() accepts arrays with exact count', function (): void {
            assertExpect([1, 2, 3])->toHaveCount(3);
            assertExpect([])->toHaveCount(0);
            assertExpect(['a' => 1, 'b' => 2])->toHaveCount(2);
            expect(true)->toBeTrue();
        });

        test('toHaveCount() rejects arrays with different count', function (): void {
            assertExpect(fn (): Expectation => assertExpect([1, 2, 3])->toHaveCount(2))
                ->toThrow(AssertionFailedException::class);
        });

        test('toHaveKey() accepts arrays with existing key', function (): void {
            assertExpect(['name' => 'John'])->toHaveKey('name');
            assertExpect([1, 2, 3])->toHaveKey(0);
            assertExpect(['a' => 1, 'b' => 2])->toHaveKey('b');
            expect(true)->toBeTrue();
        });

        test('toHaveKey() rejects arrays without key', function (): void {
            assertExpect(fn (): Expectation => assertExpect(['name' => 'John'])->toHaveKey('email'))
                ->toThrow(AssertionFailedException::class);
        });

        test('toContain() accepts arrays containing value', function (): void {
            assertExpect([1, 2, 3])->toContain(2);
            assertExpect(['a', 'b', 'c'])->toContain('b');
            assertExpect([true, false])->toContain(true);
            expect(true)->toBeTrue();
        });

        test('toContain() rejects arrays not containing value', function (): void {
            assertExpect(fn (): Expectation => assertExpect([1, 2, 3])->toContain(4))
                ->toThrow(AssertionFailedException::class);
        });

        test('toHaveCount() works with countable objects', function (): void {
            assertExpect(
                new ArrayObject([1, 2, 3])
            )->toHaveCount(3);
            expect(true)->toBeTrue();
        });
    });

    describe('Negation with Collections', function (): void {
        test('not->toHaveCount() accepts arrays with different count', function (): void {
            assertExpect([1, 2, 3])->not->toHaveCount(2);
            assertExpect([])->not->toHaveCount(1);
            expect(true)->toBeTrue();
        });

        test('not->toHaveKey() accepts arrays without key', function (): void {
            assertExpect(['name' => 'John'])->not->toHaveKey('email');
            expect(true)->toBeTrue();
        });

        test('not->toContain() accepts arrays without value', function (): void {
            assertExpect([1, 2, 3])->not->toContain(4);
            expect(true)->toBeTrue();
        });
    });

    describe('Chaining Collection Methods', function (): void {
        test('can chain multiple collection assertions', function (): void {
            assertExpect(['name' => 'John', 'email' => 'john@example.com'])
                ->toBeArray()
                ->toHaveCount(2)
                ->toHaveKey('name')
                ->toHaveKey('email');
            expect(true)->toBeTrue();
        });

        test('can mix collection and value checks', function (): void {
            assertExpect([1, 2, 3])
                ->toBeArray()
                ->toHaveCount(3)
                ->toContain(2)
                ->not->toContain(4);
            expect(true)->toBeTrue();
        });
    });

    describe('Edge Cases', function (): void {
        test('toHaveLength() works for arrays (alias for toHaveCount)', function (): void {
            assertExpect([1, 2, 3])->toHaveLength(3);
            expect(true)->toBeTrue();
        });

        test('toContain() requires string or array', function (): void {
            assertExpect(fn (): Expectation => assertExpect(42)->toContain('foo'))
                ->toThrow(AssertionFailedException::class);
        });
    });
});
