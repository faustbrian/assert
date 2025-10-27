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
            expect(fn() => assertExpect('hello world')->toStartWith('hello'))->not->toThrow();
            expect(fn() => assertExpect('test')->toStartWith('te'))->not->toThrow();
            expect(fn() => assertExpect('PHP')->toStartWith('P'))->not->toThrow();
        });

        test('toStartWith() rejects strings without matching prefix', function (): void {
            expect(fn () => assertExpect('hello world')->toStartWith('world'))
                ->toThrow(AssertionFailedException::class);
        });

        test('toEndWith() accepts strings with matching suffix', function (): void {
            expect(fn() => assertExpect('hello world')->toEndWith('world'))->not->toThrow();
            expect(fn() => assertExpect('test')->toEndWith('st'))->not->toThrow();
            expect(fn() => assertExpect('PHP')->toEndWith('P'))->not->toThrow();
        });

        test('toEndWith() rejects strings without matching suffix', function (): void {
            expect(fn () => assertExpect('hello world')->toEndWith('hello'))
                ->toThrow(AssertionFailedException::class);
        });

        test('toMatch() accepts strings matching regex', function (): void {
            expect(fn() => assertExpect('test@example.com')->toMatch('/^.+@.+\..+$/'))->not->toThrow();
            expect(fn() => assertExpect('hello123')->toMatch('/^[a-z]+\d+$/'))->not->toThrow();
            expect(fn() => assertExpect('PHP')->toMatch('/^[A-Z]+$/'))->not->toThrow();
        });

        test('toMatch() rejects strings not matching regex', function (): void {
            expect(fn () => assertExpect('invalid-email')->toMatch('/^.+@.+\..+$/'))
                ->toThrow(AssertionFailedException::class);
        });

        test('toHaveLength() accepts strings with exact length', function (): void {
            expect(fn() => assertExpect('hello')->toHaveLength(5))->not->toThrow();
            expect(fn() => assertExpect('')->toHaveLength(0))->not->toThrow();
            expect(fn() => assertExpect('PHP')->toHaveLength(3))->not->toThrow();
        });

        test('toHaveLength() rejects strings with different length', function (): void {
            expect(fn () => assertExpect('hello')->toHaveLength(4))
                ->toThrow(AssertionFailedException::class);
        });

        test('toContain() accepts strings containing substring', function (): void {
            expect(fn() => assertExpect('hello world')->toContain('world'))->not->toThrow();
            expect(fn() => assertExpect('hello world')->toContain('hello'))->not->toThrow();
            expect(fn() => assertExpect('hello world')->toContain('o w'))->not->toThrow();
        });

        test('toContain() rejects strings not containing substring', function (): void {
            expect(fn () => assertExpect('hello world')->toContain('foo'))
                ->toThrow(AssertionFailedException::class);
        });
    });

    describe('Negation with String Methods', function (): void {
        test('not->toStartWith() accepts strings without prefix', function (): void {
            expect(fn() => assertExpect('hello world')->not->toStartWith('world'))->not->toThrow();
            expect(fn() => assertExpect('test')->not->toStartWith('foo'))->not->toThrow();
        });

        test('not->toEndWith() accepts strings without suffix', function (): void {
            expect(fn() => assertExpect('hello world')->not->toEndWith('hello'))->not->toThrow();
            expect(fn() => assertExpect('test')->not->toEndWith('foo'))->not->toThrow();
        });

        test('not->toMatch() accepts strings not matching regex', function (): void {
            expect(fn() => assertExpect('invalid-email')->not->toMatch('/^.+@.+\..+$/'))->not->toThrow();
        });

        test('not->toContain() accepts strings without substring', function (): void {
            expect(fn() => assertExpect('hello world')->not->toContain('foo'))->not->toThrow();
        });
    });

    describe('Chaining String Methods', function (): void {
        test('can chain multiple string assertions', function (): void {
            expect(fn() => assertExpect('hello world')
                ->toBeString()
                ->toStartWith('hello')
                ->toEndWith('world')
                ->toContain('o w')
                ->toHaveLength(11))->not->toThrow();
        });

        test('can mix string and pattern checks', function (): void {
            expect(fn() => assertExpect('test@example.com')
                ->toBeString()
                ->toContain('@')
                ->toMatch('/^.+@.+\..+$/'))->not->toThrow();
        });
    });
});
