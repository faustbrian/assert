<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Assert\Exceptions\InvalidArgumentException;

use function Cline\Assert\expect as assertExpect;

describe('Threshold Matching', function (): void {
    describe('->exactly() threshold', function (): void {
        test('passes when exactly n groups pass', function (): void {
            expect(
                assertExpect('123')
                    ->exactly(2)
                    ->or->toBeString()
                    ->or->toBeNumeric()
                    ->or->toBeArray(),
            )->not->toThrow(Throwable::class);
        });

        test('fails when fewer groups pass', function (): void {
            expect(
                assertExpect(123)
                    ->exactly(2)
                    ->or->toBeString()
                    ->or->toBeInt(...),
            )->toThrow(InvalidArgumentException::class);
        });

        test('fails when more groups pass', function (): void {
            expect(
                assertExpect('123')
                    ->exactly(1)
                    ->or->toBeString()
                    ->or->toBeNumeric(...),
            )->toThrow(InvalidArgumentException::class);
        });
    });

    describe('->atLeast() threshold', function (): void {
        test('passes when at least n groups pass', function (): void {
            expect(
                assertExpect('123')
                    ->atLeast(2)
                    ->or->toBeString()
                    ->or->toBeNumeric()
                    ->or->toBeArray(),
            )->not->toThrow(Throwable::class);
        });

        test('passes when exactly n groups pass', function (): void {
            expect(
                assertExpect(123)
                    ->atLeast(1)
                    ->or->toBeString()
                    ->or->toBeInt(),
            )->not->toThrow(Throwable::class);
        });

        test('fails when fewer groups pass', function (): void {
            expect(
                assertExpect(123)
                    ->atLeast(2)
                    ->or->toBeString()
                    ->or->toBeInt(...),
            )->toThrow(InvalidArgumentException::class);
        });
    });

    describe('->atMost() threshold', function (): void {
        test('passes when at most n groups pass', function (): void {
            expect(
                assertExpect(123)
                    ->atMost(1)
                    ->or->toBeString()
                    ->or->toBeInt(),
            )->not->toThrow(Throwable::class);
        });

        test('passes when fewer groups pass', function (): void {
            expect(
                assertExpect([])
                    ->atMost(2)
                    ->or->toBeString()
                    ->or->toBeArray(),
            )->not->toThrow(Throwable::class);
        });

        test('fails when more groups pass', function (): void {
            expect(
                assertExpect('123')
                    ->atMost(1)
                    ->or->toBeString()
                    ->or->toBeNumeric(...),
            )->toThrow(InvalidArgumentException::class);
        });
    });

    describe('Complex patterns', function (): void {
        test('password validation with atLeast', function (): void {
            $password = 'Secret123!';

            expect(
                assertExpect($password)
                    ->atLeast(3)
                    ->or->toMatch('/[A-Z]/')
                    ->or->toMatch('/[a-z]/')
                    ->or->toMatch('/\d/')
                    ->or->toMatch('/[^a-zA-Z0-9]/'),
            )->not->toThrow(Throwable::class);
        });

        test('config validation with exactly', function (): void {
            $config = ['cache' => true, 'queue' => true];

            expect(
                assertExpect($config)
                    ->exactly(2)
                    ->or->toHaveKey('cache')
                    ->or->toHaveKey('queue')
                    ->or->toHaveKey('database')
                    ->or->toHaveKey('storage'),
            )->not->toThrow(Throwable::class);
        });
    });
});
