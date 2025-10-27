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

describe('XOR Operator', function (): void {
    test('passes when exactly one group succeeds (first group)', function (): void {
        expect(fn (): Expectation => assertExpect('hello')
            ->xor()
            ->toBeString()
            ->xor()
            ->toBeInt()
            ->xor()
            ->toBeNull()
        )->not->toThrow(Throwable::class);
    });

    test('passes when exactly one group succeeds (second group)', function (): void {
        expect(fn (): Expectation => assertExpect(123)
            ->xor()
            ->toBeString()
            ->xor()
            ->toBeInt()
            ->xor()
            ->toBeNull()
        )->not->toThrow(Throwable::class);
    });

    test('passes when exactly one group succeeds (third group)', function (): void {
        expect(fn (): Expectation => assertExpect(null)
            ->xor()
            ->toBeString()
            ->xor()
            ->toBeInt()
            ->xor()
            ->toBeNull()
        )->not->toThrow(Throwable::class);
    });

    test('fails when no groups succeed', function (): void {
        expect(fn (): Expectation => assertExpect([])
            ->xor()
            ->toBeString()
            ->xor()
            ->toBeInt()
            ->xor()
            ->toBeNull()
        )->toThrow(InvalidArgumentException::class);
    });

    test('fails when multiple groups succeed', function (): void {
        expect(fn (): Expectation => assertExpect('123')
            ->xor()
            ->toBeString()
            ->xor()
            ->toBeNumeric()
            ->xor()
            ->toBeNull()
        )->toThrow(InvalidArgumentException::class);
    });

    test('supports multiple assertions per group', function (): void {
        expect(fn (): Expectation => assertExpect('hello')
            ->xor()
            ->toBeString()
            ->toHaveLength(5)
            ->xor()
            ->toBeInt()
            ->toBePositive()
        )->not->toThrow(Throwable::class);
    });

    test('fails if any assertion in the single successful group fails', function (): void {
        expect(fn (): Expectation => assertExpect('hello')
            ->xor()
            ->toBeString()
            ->toHaveLength(10) // This will fail
            ->xor()
            ->toBeInt()
        )->toThrow(InvalidArgumentException::class);
    });

    test('works with negation', function (): void {
        expect(fn (): Expectation => assertExpect(123)
            ->xor()
            ->not->toBeString()
            ->toBeInt()
            ->xor()
            ->toBeNull()
        )->not->toThrow(Throwable::class);
    });

    test('handles complex XOR chains', function (): void {
        expect(fn (): Expectation => assertExpect('test@example.com')
            ->xor()
            ->toBeInt()
            ->xor()
            ->toBeString()
            ->toContain('@')
            ->xor()
            ->toBeNull()
        )->not->toThrow(Throwable::class);
    });

    test('throws descriptive error when multiple groups pass', function (): void {
        try {
            assertExpect('hello')
                ->xor()
                ->toBeString()
                ->xor()
                ->toHaveLength(5);
        } catch (InvalidArgumentException $e) {
            expect($e->getMessage())->toContain('expected exactly one group to pass');
            expect($e->getMessage())->toContain('but 2 groups passed');
        }
    });

    test('throws descriptive error when no groups pass', function (): void {
        try {
            assertExpect(123)
                ->xor()
                ->toBeString()
                ->xor()
                ->toBeNull();
        } catch (InvalidArgumentException $e) {
            expect($e->getMessage())->toContain('All XOR groups failed');
            expect($e->getMessage())->toContain('expected exactly one to pass');
        }
    });

    test('supports property-style access', function (): void {
        expect(fn (): Expectation => assertExpect('hello')
            ->xor
            ->toBeString()
            ->xor
            ->toBeInt()
        )->not->toThrow(Throwable::class);
    });

    test('property-style works with multiple groups', function (): void {
        expect(fn (): Expectation => assertExpect(null)
            ->xor
            ->toBeString()
            ->xor
            ->toBeInt()
            ->xor
            ->toBeNull()
        )->not->toThrow(Throwable::class);
    });

    test('property-style can be chained with not', function (): void {
        expect(fn (): Expectation => assertExpect(123)
            ->xor
            ->not->toBeString()
            ->toBeInt()
            ->xor
            ->toBeNull()
        )->not->toThrow(Throwable::class);
    });
});
