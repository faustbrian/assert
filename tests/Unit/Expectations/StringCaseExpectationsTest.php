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

describe('String Case Expectations', function (): void {
    describe('toBeSnakeCase()', function (): void {
        test('accepts snake_case strings', function (): void {
            expect(fn (): Expectation => assertExpect('hello_world')->toBeSnakeCase())->not->toThrow(Throwable::class);
            expect(fn (): Expectation => assertExpect('foo_bar_baz')->toBeSnakeCase())->not->toThrow(Throwable::class);
            expect(fn (): Expectation => assertExpect('test')->toBeSnakeCase())->not->toThrow(Throwable::class);
        });

        test('rejects non-snake_case strings', function (): void {
            expect(fn (): Expectation => assertExpect('helloWorld')->toBeSnakeCase())
                ->toThrow(InvalidArgumentException::class);
            expect(fn (): Expectation => assertExpect('hello-world')->toBeSnakeCase())
                ->toThrow(InvalidArgumentException::class);
        });
    });

    describe('toBeKebabCase()', function (): void {
        test('accepts kebab-case strings', function (): void {
            expect(fn (): Expectation => assertExpect('hello-world')->toBeKebabCase())->not->toThrow(Throwable::class);
            expect(fn (): Expectation => assertExpect('foo-bar-baz')->toBeKebabCase())->not->toThrow(Throwable::class);
            expect(fn (): Expectation => assertExpect('test')->toBeKebabCase())->not->toThrow(Throwable::class);
        });

        test('rejects non-kebab-case strings', function (): void {
            expect(fn (): Expectation => assertExpect('helloWorld')->toBeKebabCase())
                ->toThrow(InvalidArgumentException::class);
            expect(fn (): Expectation => assertExpect('hello_world')->toBeKebabCase())
                ->toThrow(InvalidArgumentException::class);
        });
    });

    describe('toBeCamelCase()', function (): void {
        test('accepts camelCase strings', function (): void {
            expect(fn (): Expectation => assertExpect('helloWorld')->toBeCamelCase())->not->toThrow(Throwable::class);
            expect(fn (): Expectation => assertExpect('fooBarBaz')->toBeCamelCase())->not->toThrow(Throwable::class);
            expect(fn (): Expectation => assertExpect('test')->toBeCamelCase())->not->toThrow(Throwable::class);
        });

        test('rejects non-camelCase strings', function (): void {
            expect(fn (): Expectation => assertExpect('HelloWorld')->toBeCamelCase())
                ->toThrow(InvalidArgumentException::class);
            expect(fn (): Expectation => assertExpect('hello_world')->toBeCamelCase())
                ->toThrow(InvalidArgumentException::class);
        });
    });

    describe('toBeStudlyCase()', function (): void {
        test('accepts StudlyCase strings', function (): void {
            expect(fn (): Expectation => assertExpect('HelloWorld')->toBeStudlyCase())->not->toThrow(Throwable::class);
            expect(fn (): Expectation => assertExpect('FooBarBaz')->toBeStudlyCase())->not->toThrow(Throwable::class);
        });

        test('rejects non-StudlyCase strings', function (): void {
            expect(fn (): Expectation => assertExpect('helloWorld')->toBeStudlyCase())
                ->toThrow(InvalidArgumentException::class);
            expect(fn (): Expectation => assertExpect('hello_world')->toBeStudlyCase())
                ->toThrow(InvalidArgumentException::class);
        });
    });

    describe('toBeUppercase()', function (): void {
        test('accepts uppercase strings', function (): void {
            expect(fn (): Expectation => assertExpect('HELLO')->toBeUppercase())->not->toThrow(Throwable::class);
            expect(fn (): Expectation => assertExpect('ABC123')->toBeUppercase())->not->toThrow(Throwable::class);
        });

        test('rejects non-uppercase strings', function (): void {
            expect(fn (): Expectation => assertExpect('Hello')->toBeUppercase())
                ->toThrow(InvalidArgumentException::class);
        });
    });

    describe('toBeLowercase()', function (): void {
        test('accepts lowercase strings', function (): void {
            expect(fn (): Expectation => assertExpect('hello')->toBeLowercase())->not->toThrow(Throwable::class);
            expect(fn (): Expectation => assertExpect('abc123')->toBeLowercase())->not->toThrow(Throwable::class);
        });

        test('rejects non-lowercase strings', function (): void {
            expect(fn (): Expectation => assertExpect('Hello')->toBeLowercase())
                ->toThrow(InvalidArgumentException::class);
        });
    });
});
