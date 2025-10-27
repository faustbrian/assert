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

        test('toHaveLength() works for strings', function (): void {
            expect(fn (): Expectation => assertExpect('hello')->toHaveLength(5))->not->toThrow(Throwable::class);
            expect(fn (): Expectation => assertExpect('')->toHaveLength(0))->not->toThrow(Throwable::class);
            expect(fn (): Expectation => assertExpect('test')->toHaveLength(4))->not->toThrow(Throwable::class);
        });

        test('toHaveLength() works for arrays', function (): void {
            expect(fn (): Expectation => assertExpect([1, 2, 3])->toHaveLength(3))->not->toThrow(Throwable::class);
            expect(fn (): Expectation => assertExpect([])->toHaveLength(0))->not->toThrow(Throwable::class);
        });

        test('toHaveLength() rejects incorrect length', function (): void {
            expect(fn (): Expectation => assertExpect('hello')->toHaveLength(3))
                ->toThrow(InvalidArgumentException::class);
            expect(fn (): Expectation => assertExpect([1, 2])->toHaveLength(3))
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

    describe('Aliases', function (): void {
        test('toBeOneOf() accepts value in array', function (): void {
            expect(fn (): Expectation => assertExpect(2)->toBeOneOf([1, 2, 3]))->not->toThrow(Throwable::class);
            expect(fn (): Expectation => assertExpect('active')->toBeOneOf(['pending', 'active', 'completed']))->not->toThrow(Throwable::class);
        });

        test('toBeOneOf() rejects value not in array', function (): void {
            expect(fn (): Expectation => assertExpect(4)->toBeOneOf([1, 2, 3]))
                ->toThrow(InvalidArgumentException::class);
        });

        test('not->toBeOneOf() accepts value not in array', function (): void {
            expect(fn (): Expectation => assertExpect(4)->not->toBeOneOf([1, 2, 3]))
                ->not->toThrow(Throwable::class);
        });
    });

    describe('Batch Operations', function (): void {
        test('toContainAllValues() accepts array with all values', function (): void {
            expect(fn (): Expectation => assertExpect([1, 2, 3, 4, 5])->toContainAllValues([1, 3, 5]))
                ->not->toThrow(Throwable::class);
            expect(fn (): Expectation => assertExpect(['a', 'b', 'c'])->toContainAllValues(['a', 'c']))
                ->not->toThrow(Throwable::class);
        });

        test('toContainAllValues() rejects array missing values', function (): void {
            expect(fn (): Expectation => assertExpect([1, 2, 3])->toContainAllValues([1, 2, 4]))
                ->toThrow(InvalidArgumentException::class);
        });

        test('toContainAllKeys() accepts array with all keys', function (): void {
            expect(fn (): Expectation => assertExpect(['a' => 1, 'b' => 2, 'c' => 3])->toContainAllKeys(['a', 'c']))
                ->not->toThrow(Throwable::class);
        });

        test('toContainAllKeys() rejects array missing keys', function (): void {
            expect(fn (): Expectation => assertExpect(['a' => 1, 'b' => 2])->toContainAllKeys(['a', 'c']))
                ->toThrow(InvalidArgumentException::class);
        });

        test('can chain collection helpers', function (): void {
            expect(fn (): Expectation => assertExpect([1, 2, 3])
                ->toBeArray()
                ->toContainAllValues([1, 3])
                ->toHaveCount(3))
                ->not->toThrow(Throwable::class);
        });

        test('can use batch operations with each modifier', function (): void {
            expect(fn (): Expectation => assertExpect([[1, 2, 3], [2, 3, 4]])->each->toContainAllValues([2, 3]))
                ->not->toThrow(Throwable::class);
        });
    });

    describe('Explicit Aliases', function (): void {
        test('toContainValue() accepts array with value', function (): void {
            expect(fn (): Expectation => assertExpect([1, 2, 3])->toContainValue(2))->not->toThrow(Throwable::class);
        });

        test('toContainKey() accepts array with key', function (): void {
            expect(fn (): Expectation => assertExpect(['a' => 1, 'b' => 2])->toContainKey('a'))->not->toThrow(Throwable::class);
        });
    });

    describe('Key Comparison', function (): void {
        test('toHaveSameKeys() accepts arrays with same keys', function (): void {
            expect(fn (): Expectation => assertExpect(['a' => 1, 'b' => 2])->toHaveSameKeys(['a' => 99, 'b' => 88]))
                ->not->toThrow(Throwable::class);
        });

        test('toHaveSameKeys() rejects arrays with different keys', function (): void {
            expect(fn (): Expectation => assertExpect(['a' => 1])->toHaveSameKeys(['b' => 1]))
                ->toThrow(InvalidArgumentException::class);
        });

        test('toHaveSameKeys() ignores key order', function (): void {
            expect(fn (): Expectation => assertExpect(['b' => 2, 'a' => 1])->toHaveSameKeys(['a' => 99, 'b' => 88]))
                ->not->toThrow(Throwable::class);
        });
    });
});
