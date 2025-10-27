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

describe('Modifier Methods', function (): void {
    describe('->sequence()', function (): void {
        test('applies different expectations to each element in order', function (): void {
            expect(fn (): Expectation => assertExpect([1, 'test', 3.14])->sequence(
                fn ($e) => $e->toBeInt(),
                fn ($e) => $e->toBeString(),
                fn ($e) => $e->toBeFloat()
            ))->not->toThrow(Throwable::class);
        });

        test('rejects when not enough items in sequence', function (): void {
            expect(fn (): Expectation => assertExpect([1, 2])->sequence(
                fn ($e) => $e->toBeInt(),
                fn ($e) => $e->toBeInt(),
                fn ($e) => $e->toBeInt()
            ))->toThrow(InvalidArgumentException::class);
        });

        test('rejects when expectation fails for element', function (): void {
            expect(fn (): Expectation => assertExpect([1, 2, 3])->sequence(
                fn ($e) => $e->toBeInt(),
                fn ($e) => $e->toBeString(),
                fn ($e) => $e->toBeInt()
            ))->toThrow(InvalidArgumentException::class);
        });
    });

    describe('->json()', function (): void {
        test('parses JSON and returns new expectation', function (): void {
            expect(fn (): Expectation => assertExpect('{"name":"John","age":30}')
                ->json()
                ->toHaveKey('name'))
                ->not->toThrow(Throwable::class);
        });

        test('allows chaining expectations on parsed JSON', function (): void {
            expect(fn (): Expectation => assertExpect('{"count":5}')
                ->json()
                ->toHaveKey('count'))
                ->not->toThrow(Throwable::class);
        });

        test('rejects invalid JSON', function (): void {
            expect(fn (): Expectation => assertExpect('invalid json')->json())
                ->toThrow(InvalidArgumentException::class);
        });
    });

    describe('->match()', function (): void {
        test('matches value with exact match', function (): void {
            expect(fn (): Expectation => assertExpect('active')->match(
                ['pending', fn ($e) => $e->toBeString()],
                ['active', fn ($e) => $e->toBeString()]
            ))->not->toThrow(Throwable::class);
        });

        test('matches value with callable matcher', function (): void {
            expect(fn (): Expectation => assertExpect(42)->match(
                [fn ($v) => $v < 10, fn ($e) => $e->toBeInt()],
                [fn ($v) => $v > 10, fn ($e) => $e->toBeInt()]
            ))->not->toThrow(Throwable::class);
        });

        test('rejects when no pattern matches', function (): void {
            expect(fn (): Expectation => assertExpect('unknown')->match(
                ['pending', fn ($e) => $e->toBeString()],
                ['active', fn ($e) => $e->toBeString()]
            ))->toThrow(InvalidArgumentException::class);
        });
    });

    describe('->ray()', function (): void {
        test('returns self when ray not available', function (): void {
            $result = assertExpect(42)->ray();
            expect($result)->toBeInstanceOf(Expectation::class);
        });

        test('returns self with label when ray not available', function (): void {
            $result = assertExpect(42)->ray('test label');
            expect($result)->toBeInstanceOf(Expectation::class);
        });
    });
});
