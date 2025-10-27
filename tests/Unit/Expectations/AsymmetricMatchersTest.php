<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Assert\Exceptions\InvalidArgumentException;
use Cline\Assert\Expectations\Expectation;
use Illuminate\Support\Facades\Date;

use function Cline\Assert\any;
use function Cline\Assert\anything;
use function Cline\Assert\arrayContaining;
use function Cline\Assert\expect as assertExpect;
use function Cline\Assert\stringContaining;

describe('Asymmetric Matchers', function (): void {
    describe('any() matcher', function (): void {
        test('matches values of specified type', function (): void {
            expect(fn (): Expectation => assertExpect([
                'id' => 123,
                'name' => 'John',
            ])->toEqual([
                'id' => any('integer'),
                'name' => any('string'),
            ]))->not->toThrow(Throwable::class);
        });

        test('rejects values of wrong type', function (): void {
            expect(fn (): Expectation => assertExpect([
                'id' => 'not-an-int',
            ])->toEqual([
                'id' => any('integer'),
            ]))->toThrow(InvalidArgumentException::class);
        });

        test('matches class instances', function (): void {
            expect(fn (): Expectation => assertExpect([
                'date' => Date::now(),
            ])->toEqual([
                'date' => any(DateTime::class),
            ]))->not->toThrow(Throwable::class);
        });
    });

    describe('anything() matcher', function (): void {
        test('matches any non-null value', function (): void {
            expect(fn (): Expectation => assertExpect([
                'field1' => 123,
                'field2' => 'test',
                'field3' => [],
            ])->toEqual([
                'field1' => anything(),
                'field2' => anything(),
                'field3' => anything(),
            ]))->not->toThrow(Throwable::class);
        });

        test('rejects null values', function (): void {
            expect(fn (): Expectation => assertExpect([
                'field' => null,
            ])->toEqual([
                'field' => anything(),
            ]))->toThrow(InvalidArgumentException::class);
        });
    });

    describe('stringContaining() matcher', function (): void {
        test('matches strings containing substring', function (): void {
            expect(fn (): Expectation => assertExpect([
                'name' => 'John Doe',
                'email' => 'john@example.com',
            ])->toEqual([
                'name' => stringContaining('John'),
                'email' => stringContaining('@example'),
            ]))->not->toThrow(Throwable::class);
        });

        test('rejects strings not containing substring', function (): void {
            expect(fn (): Expectation => assertExpect([
                'name' => 'Jane Smith',
            ])->toEqual([
                'name' => stringContaining('John'),
            ]))->toThrow(InvalidArgumentException::class);
        });

        test('rejects non-string values', function (): void {
            expect(fn (): Expectation => assertExpect([
                'count' => 123,
            ])->toEqual([
                'count' => stringContaining('123'),
            ]))->toThrow(InvalidArgumentException::class);
        });
    });

    describe('arrayContaining() matcher', function (): void {
        test('matches arrays with subset of keys', function (): void {
            expect(fn (): Expectation => assertExpect([
                'user' => [
                    'id' => 1,
                    'name' => 'John',
                    'email' => 'john@example.com',
                    'created_at' => '2025-01-01',
                ],
            ])->toEqual([
                'user' => arrayContaining([
                    'id' => 1,
                    'name' => 'John',
                ]),
            ]))->not->toThrow(Throwable::class);
        });

        test('rejects arrays missing required keys', function (): void {
            expect(fn (): Expectation => assertExpect([
                'user' => [
                    'id' => 1,
                ],
            ])->toEqual([
                'user' => arrayContaining([
                    'id' => 1,
                    'name' => 'John',
                ]),
            ]))->toThrow(InvalidArgumentException::class);
        });

        test('supports nested matchers', function (): void {
            expect(fn (): Expectation => assertExpect([
                'data' => [
                    'id' => 123,
                    'name' => 'John Doe',
                ],
            ])->toEqual([
                'data' => arrayContaining([
                    'id' => any('integer'),
                    'name' => stringContaining('John'),
                ]),
            ]))->not->toThrow(Throwable::class);
        });
    });

    describe('Complex patterns', function (): void {
        test('combines multiple matcher types', function (): void {
            expect(fn (): Expectation => assertExpect([
                'id' => 42,
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'data' => ['key' => 'value'],
                'timestamp' => 1_234_567_890,
            ])->toEqual([
                'id' => any('integer'),
                'name' => stringContaining('John'),
                'email' => stringContaining('@'),
                'data' => arrayContaining(['key' => 'value']),
                'timestamp' => anything(),
            ]))->not->toThrow(Throwable::class);
        });

        test('handles deeply nested structures', function (): void {
            expect(fn (): Expectation => assertExpect([
                'user' => [
                    'profile' => [
                        'name' => 'John',
                        'settings' => [
                            'theme' => 'dark',
                        ],
                    ],
                ],
            ])->toEqual([
                'user' => arrayContaining([
                    'profile' => arrayContaining([
                        'name' => stringContaining('J'),
                        'settings' => any('array'),
                    ]),
                ]),
            ]))->not->toThrow(Throwable::class);
        });
    });
});
