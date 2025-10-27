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

describe('Uncovered Expectation Paths', function (): void {
    describe('__get and property access', function (): void {
        test('__get and property returns same expectation', function (): void {
            $exp = assertExpect(42);
            $andExp = $exp->and;

            expect($andExp)->toBeInstanceOf(Expectation::class);
            expect(fn (): Expectation => $andExp->toBeInt())->not->toThrow(Throwable::class);
        });

        test('__get throws for invalid property', function (): void {
            expect(fn () => assertExpect(42)->invalidProperty)
                ->toThrow(BadMethodCallException::class);
        });
    });

    describe('__call method forwarding', function (): void {
        test('__call forwards to static methods when value is null', function (): void {
            $result = assertExpect(null)->any('string');
            expect($result)->toBeInstanceOf(Cline\Assert\Matchers\AnyMatcher::class);
        });

        test('__call forwards anything matcher when value is null', function (): void {
            $result = assertExpect(null)->anything();
            expect($result)->toBeInstanceOf(Cline\Assert\Matchers\AnythingMatcher::class);
        });

        test('__call forwards stringContaining when value is null', function (): void {
            $result = assertExpect(null)->stringContaining('test');
            expect($result)->toBeInstanceOf(Cline\Assert\Matchers\StringContainingMatcher::class);
        });

        test('__call forwards arrayContaining when value is null', function (): void {
            $result = assertExpect(null)->arrayContaining(['key' => 'value']);
            expect($result)->toBeInstanceOf(Cline\Assert\Matchers\ArrayContainingMatcher::class);
        });

        test('__call throws BadMethodCallException for undefined methods', function (): void {
            expect(fn () => assertExpect(42)->nonExistentMethod())
                ->toThrow(BadMethodCallException::class);
        });

        test('__call throws BadMethodCallException when value is not null and method does not exist', function (): void {
            expect(fn () => assertExpect('test')->nonExistentMethod())
                ->toThrow(BadMethodCallException::class);
        });
    });

    describe('each() proxy property access', function (): void {
        test('each proxy throws BadMethodCallException for invalid property', function (): void {
            $proxy = assertExpect([1, 2, 3])->each;
            expect(fn () => $proxy->invalidProperty)
                ->toThrow(BadMethodCallException::class);
        });
    });

    describe('toStrictEqual', function (): void {
        test('toStrictEqual() passes with identical values', function (): void {
            expect(fn (): Expectation => assertExpect(42)->toStrictEqual(42))
                ->not->toThrow(Throwable::class);
        });

        test('toStrictEqual() passes with identical arrays', function (): void {
            expect(fn (): Expectation => assertExpect([1, 2, 3])->toStrictEqual([1, 2, 3]))
                ->not->toThrow(Throwable::class);
        });

        test('toStrictEqual() passes with identical objects', function (): void {
            $obj = new stdClass();
            expect(fn (): Expectation => assertExpect($obj)->toStrictEqual($obj))
                ->not->toThrow(Throwable::class);
        });

        test('toStrictEqual() fails with different types', function (): void {
            expect(fn (): Expectation => assertExpect('42')->toStrictEqual(42))
                ->toThrow(InvalidArgumentException::class);
        });

        test('toStrictEqual() fails with different values', function (): void {
            expect(fn (): Expectation => assertExpect(42)->toStrictEqual(43))
                ->toThrow(InvalidArgumentException::class);
        });
    });

    describe('toBeDefined, toBeUndefined, toBeNullable', function (): void {
        test('toBeDefined() passes for non-null values', function (): void {
            expect(fn (): Expectation => assertExpect(42)->toBeDefined())
                ->not->toThrow(Throwable::class);
            expect(fn (): Expectation => assertExpect('test')->toBeDefined())
                ->not->toThrow(Throwable::class);
            expect(fn (): Expectation => assertExpect(0)->toBeDefined())
                ->not->toThrow(Throwable::class);
            expect(fn (): Expectation => assertExpect(false)->toBeDefined())
                ->not->toThrow(Throwable::class);
        });

        test('toBeDefined() fails for null value', function (): void {
            expect(fn (): Expectation => assertExpect(null)->toBeDefined())
                ->toThrow(InvalidArgumentException::class);
        });

        test('toBeUndefined() passes for null value', function (): void {
            expect(fn (): Expectation => assertExpect(null)->toBeUndefined())
                ->not->toThrow(Throwable::class);
        });

        test('toBeUndefined() fails for non-null values', function (): void {
            expect(fn (): Expectation => assertExpect(42)->toBeUndefined())
                ->toThrow(InvalidArgumentException::class);
            expect(fn (): Expectation => assertExpect('test')->toBeUndefined())
                ->toThrow(InvalidArgumentException::class);
            expect(fn (): Expectation => assertExpect(0)->toBeUndefined())
                ->toThrow(InvalidArgumentException::class);
        });

        test('toBeNullable() passes for null value', function (): void {
            expect(fn (): Expectation => assertExpect(null)->toBeNullable('string'))
                ->not->toThrow(Throwable::class);
        });

        test('toBeNullable() passes for matching type', function (): void {
            expect(fn (): Expectation => assertExpect('test')->toBeNullable('string'))
                ->not->toThrow(Throwable::class);
            expect(fn (): Expectation => assertExpect(42)->toBeNullable('integer'))
                ->not->toThrow(Throwable::class);
            expect(fn (): Expectation => assertExpect([])->toBeNullable('array'))
                ->not->toThrow(Throwable::class);
            expect(fn (): Expectation => assertExpect(true)->toBeNullable('boolean'))
                ->not->toThrow(Throwable::class);
        });
    });

    describe('Directory assertions', function (): void {
        test('toBeReadableDirectory() passes for readable directory', function (): void {
            $dir = sys_get_temp_dir();
            expect(fn (): Expectation => assertExpect($dir)->toBeReadableDirectory())
                ->not->toThrow(Throwable::class);
        });

        test('toBeReadableDirectory() fails for non-directory', function (): void {
            expect(fn (): Expectation => assertExpect('/nonexistent/directory')->toBeReadableDirectory())
                ->toThrow(InvalidArgumentException::class);
        });

        test('toBeWritableDirectory() passes for writable directory', function (): void {
            $dir = sys_get_temp_dir();
            expect(fn (): Expectation => assertExpect($dir)->toBeWritableDirectory())
                ->not->toThrow(Throwable::class);
        });

        test('toBeWritableDirectory() fails for non-directory', function (): void {
            expect(fn (): Expectation => assertExpect('/nonexistent/directory')->toBeWritableDirectory())
                ->toThrow(InvalidArgumentException::class);
        });
    });

    describe('toBeAlpha negation', function (): void {
        test('not->toBeAlpha() passes for non-alphabetic string', function (): void {
            expect(assertExpect('123')->not->toBeAlpha(...))->not->toThrow(Throwable::class);
            expect(assertExpect('abc123')->not->toBeAlpha(...))->not->toThrow(Throwable::class);
            expect(assertExpect('!@#')->not->toBeAlpha(...))->not->toThrow(Throwable::class);
            expect(assertExpect('')->not->toBeAlpha(...))->not->toThrow(Throwable::class);
        });

        test('not->toBeAlpha() fails for alphabetic string', function (): void {
            expect(fn () => assertExpect('abc')->not->toBeAlpha())
                ->toThrow(InvalidArgumentException::class);
            expect(fn () => assertExpect('ABC')->not->toBeAlpha())
                ->toThrow(InvalidArgumentException::class);
            expect(fn () => assertExpect('aBc')->not->toBeAlpha())
                ->toThrow(InvalidArgumentException::class);
        });
    });

    describe('ray() debugging helper', function (): void {
        test('ray() with label parameter does not throw when ray exists', function (): void {
            if (function_exists('ray')) {
                expect(fn (): Expectation => assertExpect(42)->ray('test label'))
                    ->not->toThrow(Throwable::class);
            } else {
                expect(true)->toBeTrue(); // Skip if ray not available
            }
        });

        test('ray() without label does not throw', function (): void {
            expect(fn (): Expectation => assertExpect(42)->ray())
                ->not->toThrow(Throwable::class);
        });

        test('ray() with label returns expectation for chaining', function (): void {
            $result = assertExpect(42)->ray('test');
            expect($result)->toBeInstanceOf(Expectation::class);
            expect(fn (): Expectation => $result->toBeInt())->not->toThrow(Throwable::class);
        });
    });
});
