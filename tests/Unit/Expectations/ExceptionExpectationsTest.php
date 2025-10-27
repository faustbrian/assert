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

describe('Exception Expectations', function (): void {
    describe('toThrow()', function (): void {
        test('accepts callable that throws any exception', function (): void {
            expect(fn (): Expectation => assertExpect(function (): void {
                throw new Exception('test');
            })->toThrow())->not->toThrow(Throwable::class);
        });

        test('accepts callable that throws specific exception class', function (): void {
            expect(fn (): Expectation => assertExpect(function (): void {
                throw new InvalidArgumentException('test');
            })->toThrow(InvalidArgumentException::class))->not->toThrow(Throwable::class);
        });

        test('accepts callable that throws exception with message containing text', function (): void {
            expect(fn (): Expectation => assertExpect(function (): void {
                throw new Exception('Custom error message');
            })->toThrow(Exception::class, 'Custom error'))->not->toThrow(Throwable::class);
        });

        test('rejects callable that does not throw', function (): void {
            expect(fn (): Expectation => assertExpect(fn (): int => 42)->toThrow())
                ->toThrow(InvalidArgumentException::class);
        });

        test('rejects callable that throws wrong exception type', function (): void {
            expect(fn (): Expectation => assertExpect(function (): void {
                throw new Exception('test');
            })->toThrow(InvalidArgumentException::class))
                ->toThrow(InvalidArgumentException::class);
        });

        test('rejects callable that throws exception with wrong message', function (): void {
            expect(fn (): Expectation => assertExpect(function (): void {
                throw new Exception('actual message');
            })->toThrow(Exception::class, 'expected message'))
                ->toThrow(InvalidArgumentException::class);
        });

        test('requires callable value', function (): void {
            expect(fn (): Expectation => assertExpect(42)->toThrow())
                ->toThrow(InvalidArgumentException::class);
        });
    });

    describe('Negation with toThrow()', function (): void {
        test('not->toThrow() accepts callable that does not throw', function (): void {
            expect(assertExpect(fn (): int => 42)->not->toThrow(...))
                ->not->toThrow(Throwable::class);
        });

        test('not->toThrow() rejects callable that throws', function (): void {
            expect(assertExpect(function (): void {
                throw new Exception('test');
            })->not->toThrow(...))
                ->toThrow(InvalidArgumentException::class);
        });
    });
});
