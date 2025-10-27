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

describe('Schema Validation', function (): void {
    test('validates simple type schemas', function (): void {
        expect(fn (): Expectation => assertExpect('hello')->toMatchSchema(['type' => 'string']))
            ->not->toThrow(Throwable::class);

        expect(fn (): Expectation => assertExpect(123)->toMatchSchema(['type' => 'integer']))
            ->not->toThrow(Throwable::class);

        expect(fn (): Expectation => assertExpect([])->toMatchSchema(['type' => 'array']))
            ->not->toThrow(Throwable::class);
    });

    test('rejects wrong types', function (): void {
        expect(fn (): Expectation => assertExpect(123)->toMatchSchema(['type' => 'string']))
            ->toThrow(InvalidArgumentException::class);
    });

    test('validates object schemas', function (): void {
        $user = [
            'name' => 'John',
            'age' => 30,
        ];

        expect(fn (): Expectation => assertExpect($user)->toMatchSchema([
            'type' => 'object',
            'properties' => [
                'name' => ['type' => 'string'],
                'age' => ['type' => 'integer'],
            ],
        ]))->not->toThrow(Throwable::class);
    });

    test('validates required properties', function (): void {
        $user = ['name' => 'John'];

        expect(fn (): Expectation => assertExpect($user)->toMatchSchema([
            'type' => 'object',
            'properties' => [
                'name' => ['type' => 'string'],
                'age' => ['type' => 'integer'],
            ],
            'required' => ['name', 'age'],
        ]))->toThrow(InvalidArgumentException::class);
    });

    test('validates numeric constraints', function (): void {
        expect(fn (): Expectation => assertExpect(5)->toMatchSchema([
            'type' => 'integer',
            'minimum' => 0,
            'maximum' => 10,
        ]))->not->toThrow(Throwable::class);

        expect(fn (): Expectation => assertExpect(-5)->toMatchSchema([
            'type' => 'integer',
            'minimum' => 0,
        ]))->toThrow(InvalidArgumentException::class);
    });

    test('validates string constraints', function (): void {
        expect(fn (): Expectation => assertExpect('hello')->toMatchSchema([
            'type' => 'string',
            'minLength' => 3,
            'maxLength' => 10,
        ]))->not->toThrow(Throwable::class);

        expect(fn (): Expectation => assertExpect('hi')->toMatchSchema([
            'type' => 'string',
            'minLength' => 5,
        ]))->toThrow(InvalidArgumentException::class);
    });

    test('validates string patterns', function (): void {
        expect(fn (): Expectation => assertExpect('test@example.com')->toMatchSchema([
            'type' => 'string',
            'pattern' => '/^.+@.+\..+$/',
        ]))->not->toThrow(Throwable::class);

        expect(fn (): Expectation => assertExpect('not-an-email')->toMatchSchema([
            'type' => 'string',
            'pattern' => '/^.+@.+\..+$/',
        ]))->toThrow(InvalidArgumentException::class);
    });

    test('validates enum values', function (): void {
        expect(fn (): Expectation => assertExpect('active')->toMatchSchema([
            'type' => 'string',
            'enum' => ['pending', 'active', 'completed'],
        ]))->not->toThrow(Throwable::class);

        expect(fn (): Expectation => assertExpect('invalid')->toMatchSchema([
            'type' => 'string',
            'enum' => ['pending', 'active', 'completed'],
        ]))->toThrow(InvalidArgumentException::class);
    });

    test('validates array items', function (): void {
        expect(fn (): Expectation => assertExpect([1, 2, 3])->toMatchSchema([
            'type' => 'array',
            'items' => ['type' => 'integer'],
        ]))->not->toThrow(Throwable::class);

        expect(fn (): Expectation => assertExpect([1, 'two', 3])->toMatchSchema([
            'type' => 'array',
            'items' => ['type' => 'integer'],
        ]))->toThrow(InvalidArgumentException::class);
    });

    test('validates nested objects', function (): void {
        $data = [
            'user' => [
                'name' => 'John',
                'profile' => [
                    'age' => 30,
                ],
            ],
        ];

        expect(fn (): Expectation => assertExpect($data)->toMatchSchema([
            'type' => 'object',
            'properties' => [
                'user' => [
                    'type' => 'object',
                    'properties' => [
                        'name' => ['type' => 'string'],
                        'profile' => [
                            'type' => 'object',
                            'properties' => [
                                'age' => ['type' => 'integer'],
                            ],
                        ],
                    ],
                ],
            ],
        ]))->not->toThrow(Throwable::class);
    });

    test('error message includes all validation failures', function (): void {
        $user = [
            'name' => 123,  // Wrong type
            'age' => -5,    // Below minimum
        ];

        try {
            assertExpect($user)->toMatchSchema([
                'type' => 'object',
                'properties' => [
                    'name' => ['type' => 'string'],
                    'age' => ['type' => 'integer', 'minimum' => 0],
                ],
            ]);

            throw new Exception('Should have thrown');
        } catch (InvalidArgumentException $invalidArgumentException) {
            expect($invalidArgumentException->getMessage())->toContain('Schema validation failed');
            expect($invalidArgumentException->getMessage())->toContain('name');
            expect($invalidArgumentException->getMessage())->toContain('age');
        }
    });

    test('validates numeric value exactly at maximum boundary', function (): void {
        expect(fn (): Expectation => assertExpect(10)->toMatchSchema([
            'type' => 'integer',
            'maximum' => 10,
        ]))->not->toThrow(Throwable::class);
    });

    test('validates numeric value exceeding maximum', function (): void {
        expect(fn (): Expectation => assertExpect(15)->toMatchSchema([
            'type' => 'integer',
            'maximum' => 10,
        ]))->toThrow(InvalidArgumentException::class);
    });

    test('validates string exactly at maxLength boundary', function (): void {
        expect(fn (): Expectation => assertExpect('hello')->toMatchSchema([
            'type' => 'string',
            'maxLength' => 5,
        ]))->not->toThrow(Throwable::class);
    });

    test('validates string exceeding maxLength', function (): void {
        expect(fn (): Expectation => assertExpect('hello world')->toMatchSchema([
            'type' => 'string',
            'maxLength' => 5,
        ]))->toThrow(InvalidArgumentException::class);
    });

    test('validates unknown type in schema', function (): void {
        expect(fn (): Expectation => assertExpect('anything')->toMatchSchema([
            'type' => 'custom-type',
        ]))->not->toThrow(Throwable::class);
    });

    test('formats array item validation errors with index', function (): void {
        try {
            assertExpect([1, 'invalid', 3])->toMatchSchema([
                'type' => 'array',
                'items' => ['type' => 'integer'],
            ]);

            throw new Exception('Should have thrown');
        } catch (InvalidArgumentException $invalidArgumentException) {
            expect($invalidArgumentException->getMessage())->toContain('[1]');
        }
    });
});
