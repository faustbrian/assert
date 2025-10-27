<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Assert\Expectations\Expectation;

use function Cline\Assert\expect as assertExpect;

describe('Utility Modifiers', function (): void {
    describe('->tap() modifier', function (): void {
        test('inspects value without breaking chain', function (): void {
            $inspected = null;

            expect(
                assertExpect('hello')
                    ->tap(function ($value) use (&$inspected): void {
                        $inspected = $value;
                    })
                    ->toBeString(),
            )->not->toThrow(Throwable::class);

            expect($inspected)->toBe('hello');
        });

        test('value is passed to callback', function (): void {
            $inspected = null;
            $type = null;

            assertExpect([1, 2, 3])
                ->tap(function ($value) use (&$inspected, &$type): void {
                    $inspected = $value;
                    $type = gettype($value);
                })
                ->toBeArray();

            expect($inspected)->toBe([1, 2, 3]);
            expect($type)->toBe('array');
        });
    });

    describe('->dump() modifier', function (): void {
        test('continues chain after dumping', function (): void {
            expect(
                assertExpect(123)
                    ->dump()
                    ->toBeInt(),
            )->not->toThrow(Throwable::class);
        });
    });

    describe('->pipe() modifier', function (): void {
        test('transforms value in chain', function (): void {
            expect(
                assertExpect([1, 2, 3])
                    ->pipe(fn ($arr) => count($arr))
                    ->toBe(3),
            )->not->toThrow(Throwable::class);
        });

        test('can chain multiple transformations', function (): void {
            expect(
                assertExpect('hello')
                    ->pipe(fn ($str) => strtoupper($str))
                    ->pipe(fn ($str) => str_split($str))
                    ->toBeArray()
                    ->toHaveCount(5),
            )->not->toThrow(Throwable::class);
        });

        test('works with Laravel collections', function (): void {
            expect(
                assertExpect([
                    ['name' => 'John', 'age' => 30],
                    ['name' => 'Jane', 'age' => 25],
                ])
                    ->pipe(fn ($users) => collect($users)->pluck('name'))
                    ->pipe(fn ($names) => $names->toArray())
                    ->toBe(['John', 'Jane']),
            )->not->toThrow(Throwable::class);
        });
    });

    describe('->unless() modifier', function (): void {
        test('applies callback when condition is false', function (): void {
            $called = false;

            expect(
                assertExpect('guest')
                    ->unless(
                        fn ($value) => $value === 'admin',
                        function (Expectation $exp) use (&$called): void {
                            $called = true;
                            $exp->toBeString();
                        },
                    ),
            )->not->toThrow(Throwable::class);

            expect($called)->toBeTrue();
        });

        test('skips callback when condition is true', function (): void {
            $called = false;

            expect(
                assertExpect('admin')
                    ->unless(
                        fn ($value) => $value === 'admin',
                        function (Expectation $exp) use (&$called): void {
                            $called = true;
                        },
                    ),
            )->not->toThrow(Throwable::class);

            expect($called)->toBeFalse();
        });

        test('works with boolean conditions', function (): void {
            $isGuest = true;

            expect(
                assertExpect('user')
                    ->unless($isGuest, fn (Expectation $exp) => $exp->toBe('admin')),
            )->not->toThrow(Throwable::class);
        });
    });

    describe('Combined patterns', function (): void {
        test('tap with pipe', function (): void {
            expect(
                assertExpect([1, 2, 3])
                    ->tap(fn ($v) => expect($v)->toBeArray())
                    ->pipe(fn ($arr) => array_sum($arr))
                    ->toBe(6),
            )->not->toThrow(Throwable::class);
        });

        test('unless with pipe', function (): void {
            expect(
                assertExpect('hello')
                    ->unless(
                        fn ($str) => str_starts_with($str, 'admin'),
                        fn (Expectation $exp) => $exp->pipe(fn ($s) => strtoupper($s))->toBe('HELLO'),
                    ),
            )->not->toThrow(Throwable::class);
        });
    });
});
