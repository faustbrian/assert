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

describe('String Expectations', function (): void {
    describe('String Pattern Methods', function (): void {
        test('toStartWith() accepts strings with matching prefix', function (): void {
            assertExpect('hello world')->toStartWith('hello');
            assertExpect('test')->toStartWith('te');
            assertExpect('PHP')->toStartWith('P');
            expect(true)->toBeTrue();
        });

        test('toStartWith() rejects strings without matching prefix', function (): void {
            assertExpect(fn (): Expectation => assertExpect('hello world')->toStartWith('world'))
                ->toThrow(AssertionFailedException::class);
        });

        test('toEndWith() accepts strings with matching suffix', function (): void {
            assertExpect('hello world')->toEndWith('world');
            assertExpect('test')->toEndWith('st');
            assertExpect('PHP')->toEndWith('P');
            expect(true)->toBeTrue();
        });

        test('toEndWith() rejects strings without matching suffix', function (): void {
            assertExpect(fn (): Expectation => assertExpect('hello world')->toEndWith('hello'))
                ->toThrow(AssertionFailedException::class);
        });

        test('toMatch() accepts strings matching regex', function (): void {
            assertExpect('test@example.com')->toMatch('/^.+@.+\..+$/');
            assertExpect('hello123')->toMatch('/^[a-z]+\d+$/');
            assertExpect('PHP')->toMatch('/^[A-Z]+$/');
            expect(true)->toBeTrue();
        });

        test('toMatch() rejects strings not matching regex', function (): void {
            assertExpect(fn (): Expectation => assertExpect('invalid-email')->toMatch('/^.+@.+\..+$/'))
                ->toThrow(AssertionFailedException::class);
        });

        test('toHaveLength() accepts strings with exact length', function (): void {
            assertExpect('hello')->toHaveLength(5);
            assertExpect('')->toHaveLength(0);
            assertExpect('PHP')->toHaveLength(3);
            expect(true)->toBeTrue();
        });

        test('toHaveLength() rejects strings with different length', function (): void {
            assertExpect(fn (): Expectation => assertExpect('hello')->toHaveLength(4))
                ->toThrow(AssertionFailedException::class);
        });

        test('toContain() accepts strings containing substring', function (): void {
            assertExpect('hello world')->toContain('world');
            assertExpect('hello world')->toContain('hello');
            assertExpect('hello world')->toContain('o w');
            expect(true)->toBeTrue();
        });

        test('toContain() rejects strings not containing substring', function (): void {
            assertExpect(fn (): Expectation => assertExpect('hello world')->toContain('foo'))
                ->toThrow(AssertionFailedException::class);
        });
    });

    describe('Negation with String Methods', function (): void {
        test('not->toStartWith() accepts strings without prefix', function (): void {
            assertExpect('hello world')->not->toStartWith('world');
            assertExpect('test')->not->toStartWith('foo');
            expect(true)->toBeTrue();
        });

        test('not->toEndWith() accepts strings without suffix', function (): void {
            assertExpect('hello world')->not->toEndWith('hello');
            assertExpect('test')->not->toEndWith('foo');
            expect(true)->toBeTrue();
        });

        test('not->toMatch() accepts strings not matching regex', function (): void {
            assertExpect('invalid-email')->not->toMatch('/^.+@.+\..+$/');
            expect(true)->toBeTrue();
        });

        test('not->toContain() accepts strings without substring', function (): void {
            assertExpect('hello world')->not->toContain('foo');
            expect(true)->toBeTrue();
        });
    });

    describe('Chaining String Methods', function (): void {
        test('can chain multiple string assertions', function (): void {
            assertExpect('hello world')
                ->toBeString()
                ->toStartWith('hello')
                ->toEndWith('world')
                ->toContain('o w')
                ->toHaveLength(11);
            expect(true)->toBeTrue();
        });

        test('can mix string and pattern checks', function (): void {
            assertExpect('test@example.com')
                ->toBeString()
                ->toContain('@')
                ->toMatch('/^.+@.+\..+$/');
            expect(true)->toBeTrue();
        });
    });
});
