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

describe('String Expectations', function (): void {
    describe('String Pattern Methods', function (): void {
        test('toStartWith() accepts strings with matching prefix', function (): void {
            expect(fn() => assertExpect('hello world')->toStartWith('hello'))->not->toThrow(\Throwable::class);
            expect(fn() => assertExpect('test')->toStartWith('te'))->not->toThrow(\Throwable::class);
            expect(fn() => assertExpect('PHP')->toStartWith('P'))->not->toThrow(\Throwable::class);
        });

        test('toStartWith() rejects strings without matching prefix', function (): void {
            expect(fn () => assertExpect('hello world')->toStartWith('world'))
                ->toThrow(InvalidArgumentException::class);
        });

        test('toEndWith() accepts strings with matching suffix', function (): void {
            expect(fn() => assertExpect('hello world')->toEndWith('world'))->not->toThrow(\Throwable::class);
            expect(fn() => assertExpect('test')->toEndWith('st'))->not->toThrow(\Throwable::class);
            expect(fn() => assertExpect('PHP')->toEndWith('P'))->not->toThrow(\Throwable::class);
        });

        test('toEndWith() rejects strings without matching suffix', function (): void {
            expect(fn () => assertExpect('hello world')->toEndWith('hello'))
                ->toThrow(InvalidArgumentException::class);
        });

        test('toMatch() accepts strings matching regex', function (): void {
            expect(fn() => assertExpect('test@example.com')->toMatch('/^.+@.+\..+$/'))->not->toThrow(\Throwable::class);
            expect(fn() => assertExpect('hello123')->toMatch('/^[a-z]+\d+$/'))->not->toThrow(\Throwable::class);
            expect(fn() => assertExpect('PHP')->toMatch('/^[A-Z]+$/'))->not->toThrow(\Throwable::class);
        });

        test('toMatch() rejects strings not matching regex', function (): void {
            expect(fn () => assertExpect('invalid-email')->toMatch('/^.+@.+\..+$/'))
                ->toThrow(InvalidArgumentException::class);
        });

        test('toHaveLength() accepts strings with exact length', function (): void {
            expect(fn() => assertExpect('hello')->toHaveLength(5))->not->toThrow(\Throwable::class);
            expect(fn() => assertExpect('')->toHaveLength(0))->not->toThrow(\Throwable::class);
            expect(fn() => assertExpect('PHP')->toHaveLength(3))->not->toThrow(\Throwable::class);
        });

        test('toHaveLength() rejects strings with different length', function (): void {
            expect(fn () => assertExpect('hello')->toHaveLength(4))
                ->toThrow(InvalidArgumentException::class);
        });

        test('toContain() accepts strings containing substring', function (): void {
            expect(fn() => assertExpect('hello world')->toContain('world'))->not->toThrow(\Throwable::class);
            expect(fn() => assertExpect('hello world')->toContain('hello'))->not->toThrow(\Throwable::class);
            expect(fn() => assertExpect('hello world')->toContain('o w'))->not->toThrow(\Throwable::class);
        });

        test('toContain() rejects strings not containing substring', function (): void {
            expect(fn () => assertExpect('hello world')->toContain('foo'))
                ->toThrow(InvalidArgumentException::class);
        });
    });

    describe('Negation with String Methods', function (): void {
        test('not->toStartWith() accepts strings without prefix', function (): void {
            expect(fn() => assertExpect('hello world')->not->toStartWith('world'))->not->toThrow(\Throwable::class);
            expect(fn() => assertExpect('test')->not->toStartWith('foo'))->not->toThrow(\Throwable::class);
        });

        test('not->toEndWith() accepts strings without suffix', function (): void {
            expect(fn() => assertExpect('hello world')->not->toEndWith('hello'))->not->toThrow(\Throwable::class);
            expect(fn() => assertExpect('test')->not->toEndWith('foo'))->not->toThrow(\Throwable::class);
        });

        test('not->toMatch() accepts strings not matching regex', function (): void {
            expect(fn() => assertExpect('invalid-email')->not->toMatch('/^.+@.+\..+$/'))->not->toThrow(\Throwable::class);
        });

        test('not->toContain() accepts strings without substring', function (): void {
            expect(fn() => assertExpect('hello world')->not->toContain('foo'))->not->toThrow(\Throwable::class);
        });
    });

    describe('Chaining String Methods', function (): void {
        test('can chain multiple string assertions', function (): void {
            expect(fn() => assertExpect('hello world')
                ->toBeString()
                ->toStartWith('hello')
                ->toEndWith('world')
                ->toContain('o w')
                ->toHaveLength(11))->not->toThrow(\Throwable::class);
        });

        test('can mix string and pattern checks', function (): void {
            expect(fn() => assertExpect('test@example.com')
                ->toBeString()
                ->toContain('@')
                ->toMatch('/^.+@.+\..+$/'))->not->toThrow(\Throwable::class);
        });
    });
});
