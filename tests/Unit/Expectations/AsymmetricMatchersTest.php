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

        test('matches float type', function (): void {
            expect(fn (): Expectation => assertExpect([
                'price' => 19.99,
            ])->toEqual([
                'price' => assertExpect()->any('float'),
            ]))->not->toThrow(Throwable::class);
        });

        test('matches double type alias', function (): void {
            expect(fn (): Expectation => assertExpect([
                'value' => 3.141_59,
            ])->toEqual([
                'value' => assertExpect()->any('double'),
            ]))->not->toThrow(Throwable::class);
        });

        test('matches boolean type', function (): void {
            expect(fn (): Expectation => assertExpect([
                'active' => true,
            ])->toEqual([
                'active' => assertExpect()->any('boolean'),
            ]))->not->toThrow(Throwable::class);
        });

        test('matches bool type alias', function (): void {
            expect(fn (): Expectation => assertExpect([
                'enabled' => false,
            ])->toEqual([
                'enabled' => assertExpect()->any('bool'),
            ]))->not->toThrow(Throwable::class);
        });

        test('matches array type', function (): void {
            expect(fn (): Expectation => assertExpect([
                'items' => [1, 2, 3],
            ])->toEqual([
                'items' => assertExpect()->any('array'),
            ]))->not->toThrow(Throwable::class);
        });

        test('matches object type', function (): void {
            expect(fn (): Expectation => assertExpect([
                'data' => new stdClass(),
            ])->toEqual([
                'data' => assertExpect()->any('object'),
            ]))->not->toThrow(Throwable::class);
        });

        test('matches resource type', function (): void {
            $resource = fopen('php://memory', 'rb');
            expect(fn (): Expectation => assertExpect([
                'handle' => $resource,
            ])->toEqual([
                'handle' => assertExpect()->any('resource'),
            ]))->not->toThrow(Throwable::class);
            fclose($resource);
        });

        test('matches null type', function (): void {
            expect(fn (): Expectation => assertExpect([
                'value' => null,
            ])->toEqual([
                'value' => assertExpect()->any('null'),
            ]))->not->toThrow(Throwable::class);
        });

        test('matches numeric type with integer', function (): void {
            expect(fn (): Expectation => assertExpect([
                'count' => 42,
            ])->toEqual([
                'count' => assertExpect()->any('numeric'),
            ]))->not->toThrow(Throwable::class);
        });

        test('matches numeric type with float', function (): void {
            expect(fn (): Expectation => assertExpect([
                'amount' => 42.5,
            ])->toEqual([
                'amount' => assertExpect()->any('numeric'),
            ]))->not->toThrow(Throwable::class);
        });

        test('matches numeric type with numeric string', function (): void {
            expect(fn (): Expectation => assertExpect([
                'number' => '123',
            ])->toEqual([
                'number' => assertExpect()->any('numeric'),
            ]))->not->toThrow(Throwable::class);
        });

        test('matches scalar type with string', function (): void {
            expect(fn (): Expectation => assertExpect([
                'text' => 'hello',
            ])->toEqual([
                'text' => assertExpect()->any('scalar'),
            ]))->not->toThrow(Throwable::class);
        });

        test('matches scalar type with integer', function (): void {
            expect(fn (): Expectation => assertExpect([
                'number' => 100,
            ])->toEqual([
                'number' => assertExpect()->any('scalar'),
            ]))->not->toThrow(Throwable::class);
        });

        test('matches scalar type with float', function (): void {
            expect(fn (): Expectation => assertExpect([
                'decimal' => 99.9,
            ])->toEqual([
                'decimal' => assertExpect()->any('scalar'),
            ]))->not->toThrow(Throwable::class);
        });

        test('matches scalar type with boolean', function (): void {
            expect(fn (): Expectation => assertExpect([
                'flag' => true,
            ])->toEqual([
                'flag' => assertExpect()->any('scalar'),
            ]))->not->toThrow(Throwable::class);
        });

        test('matches callable type with closure', function (): void {
            expect(fn (): Expectation => assertExpect([
                'callback' => fn () => 'test',
            ])->toEqual([
                'callback' => assertExpect()->any('callable'),
            ]))->not->toThrow(Throwable::class);
        });

        test('matches callable type with function name', function (): void {
            expect(fn (): Expectation => assertExpect([
                'handler' => 'strlen',
            ])->toEqual([
                'handler' => assertExpect()->any('callable'),
            ]))->not->toThrow(Throwable::class);
        });

        test('matches callable type with invokable object', function (): void {
            $invokable = new class()
            {
                public function __invoke(): string
                {
                    return 'invoked';
                }
            };

            expect(fn (): Expectation => assertExpect([
                'processor' => $invokable,
            ])->toEqual([
                'processor' => assertExpect()->any('callable'),
            ]))->not->toThrow(Throwable::class);
        });

        test('toString returns formatted string', function (): void {
            $matcher = assertExpect()->any('string');
            $reflection = new ReflectionClass($matcher);
            $method = $reflection->getMethod('toString');
            $result = $method->invoke($matcher);

            expect($result)->toBe('any(string)');
        });

        test('toString returns formatted string for custom class', function (): void {
            $matcher = assertExpect()->any(DateTime::class);
            $reflection = new ReflectionClass($matcher);
            $method = $reflection->getMethod('toString');
            $result = $method->invoke($matcher);

            expect($result)->toBe('any(DateTime)');
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

        test('rejects non-array values', function (): void {
            expect(fn (): Expectation => assertExpect([
                'data' => 'not-an-array',
            ])->toEqual([
                'data' => assertExpect()->arrayContaining(['key' => 'value']),
            ]))->toThrow(InvalidArgumentException::class);
        });

        test('rejects arrays with mismatched values', function (): void {
            expect(fn (): Expectation => assertExpect([
                'user' => [
                    'id' => 1,
                    'name' => 'Jane',
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

        test('rejects when nested matcher fails', function (): void {
            expect(fn (): Expectation => assertExpect([
                'data' => [
                    'id' => 123,
                    'name' => 'Jane Smith',
                ],
            ])->toEqual([
                'data' => assertExpect()->arrayContaining([
                    'id' => assertExpect()->any('integer'),
                    'name' => assertExpect()->stringContaining('John'),
                ]),
            ]))->toThrow(InvalidArgumentException::class);
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
