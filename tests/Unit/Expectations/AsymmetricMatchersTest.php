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

use function Cline\Assert\expect as assertExpect;

describe('Asymmetric Matchers', function (): void {
    describe('assertExpect()->any() matcher', function (): void {
        test('matches values of specified type', function (): void {
            expect(fn (): Expectation => assertExpect([
                'id' => 123,
                'name' => 'John',
            ])->toEqual([
                'id' => assertExpect()->any('integer'),
                'name' => assertExpect()->any('string'),
            ]))->not->toThrow(Throwable::class);
        });

        test('rejects values of wrong type', function (): void {
            expect(fn (): Expectation => assertExpect([
                'id' => 'not-an-int',
            ])->toEqual([
                'id' => assertExpect()->any('integer'),
            ]))->toThrow(InvalidArgumentException::class);
        });

        test('matches class instances', function (): void {
            expect(fn (): Expectation => assertExpect([
                'date' => Date::now(),
            ])->toEqual([
                'date' => assertExpect()->any(DateTime::class),
            ]))->not->toThrow(Throwable::class);
        });
    });

    describe('assertExpect()->anything() matcher', function (): void {
        test('matches any non-null value', function (): void {
            expect(fn (): Expectation => assertExpect([
                'field1' => 123,
                'field2' => 'test',
                'field3' => [],
            ])->toEqual([
                'field1' => assertExpect()->anything(),
                'field2' => assertExpect()->anything(),
                'field3' => assertExpect()->anything(),
            ]))->not->toThrow(Throwable::class);
        });

        test('rejects null values', function (): void {
            expect(fn (): Expectation => assertExpect([
                'field' => null,
            ])->toEqual([
                'field' => assertExpect()->anything(),
            ]))->toThrow(InvalidArgumentException::class);
        });
    });

    describe('assertExpect()->stringContaining() matcher', function (): void {
        test('matches strings containing substring', function (): void {
            expect(fn (): Expectation => assertExpect([
                'name' => 'John Doe',
                'email' => 'john@example.com',
            ])->toEqual([
                'name' => assertExpect()->stringContaining('John'),
                'email' => assertExpect()->stringContaining('@example'),
            ]))->not->toThrow(Throwable::class);
        });

        test('rejects strings not containing substring', function (): void {
            expect(fn (): Expectation => assertExpect([
                'name' => 'Jane Smith',
            ])->toEqual([
                'name' => assertExpect()->stringContaining('John'),
            ]))->toThrow(InvalidArgumentException::class);
        });

        test('rejects non-string values', function (): void {
            expect(fn (): Expectation => assertExpect([
                'count' => 123,
            ])->toEqual([
                'count' => assertExpect()->stringContaining('123'),
            ]))->toThrow(InvalidArgumentException::class);
        });
    });

    describe('assertExpect()->arrayContaining() matcher', function (): void {
        test('matches arrays with subset of keys', function (): void {
            expect(fn (): Expectation => assertExpect([
                'user' => [
                    'id' => 1,
                    'name' => 'John',
                    'email' => 'john@example.com',
                    'created_at' => '2025-01-01',
                ],
            ])->toEqual([
                'user' => assertExpect()->arrayContaining([
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
                'user' => assertExpect()->arrayContaining([
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
                'data' => assertExpect()->arrayContaining([
                    'id' => assertExpect()->any('integer'),
                    'name' => assertExpect()->stringContaining('John'),
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
                'id' => assertExpect()->any('integer'),
                'name' => assertExpect()->stringContaining('John'),
                'email' => assertExpect()->stringContaining('@'),
                'data' => assertExpect()->arrayContaining(['key' => 'value']),
                'timestamp' => assertExpect()->anything(),
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
                'user' => assertExpect()->arrayContaining([
                    'profile' => assertExpect()->arrayContaining([
                        'name' => assertExpect()->stringContaining('J'),
                        'settings' => assertExpect()->any('array'),
                    ]),
                ]),
            ]))->not->toThrow(Throwable::class);
        });
    });
});
