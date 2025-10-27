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

describe('OR Operator', function (): void {
    test('passes when first group succeeds', function (): void {
        expect(fn (): Expectation => assertExpect('hello')
            ->or()
            ->toBeString()
        )->not->toThrow(Throwable::class);
    });

    test('passes when second group succeeds', function (): void {
        expect(fn (): Expectation => assertExpect(123)
            ->or()
            ->toBeString()
            ->or()
            ->toBeInt()
        )->not->toThrow(Throwable::class);
    });

    test('passes when third group succeeds', function (): void {
        expect(fn (): Expectation => assertExpect(null)
            ->or()
            ->toBeString()
            ->or()
            ->toBeInt()
            ->or()
            ->toBeNull()
        )->not->toThrow(Throwable::class);
    });

    test('fails when all groups fail', function (): void {
        expect(fn (): Expectation => assertExpect([])
            ->or()
            ->toBeString()
            ->or()
            ->toBeInt()
            ->or()
            ->toBeNull()
        )->toThrow(InvalidArgumentException::class);
    });

    test('supports multiple assertions per group', function (): void {
        expect(fn (): Expectation => assertExpect('hello')
            ->or()
            ->toBeString()
            ->toHaveLength(5)
        )->not->toThrow(Throwable::class);
    });

    test('fails if any assertion in successful group fails', function (): void {
        expect(fn (): Expectation => assertExpect('hello')
            ->or()
            ->toBeString()
            ->toHaveLength(10) // This will fail
        )->toThrow(InvalidArgumentException::class);
    });

    test('works with negation', function (): void {
        expect(fn (): Expectation => assertExpect(123)
            ->or()
            ->not->toBeString()
            ->toBeInt()
        )->not->toThrow(Throwable::class);
    });

    test('handles complex OR chains', function (): void {
        expect(fn (): Expectation => assertExpect('test@example.com')
            ->or()
            ->toBeInt()
            ->or()
            ->toBeString()
            ->toContain('@')
        )->not->toThrow(Throwable::class);
    });
});
