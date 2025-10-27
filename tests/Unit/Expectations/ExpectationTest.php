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

describe('Expectation Foundation', function (): void {
    test('can be instantiated with a value', function (): void {
        $expectation = new Expectation(42);

        expect($expectation)->toBeInstanceOf(Expectation::class);
    });

    test('global expect() function returns Expectation instance', function (): void {
        $result = assertExpect('test');

        expect($result)->toBeInstanceOf(Expectation::class);
    });

    test('throws exception for non-existent property', function (): void {
        expect(fn () => assertExpect(42)->nonExistent)
            ->toThrow(BadMethodCallException::class);
    });
});

describe('Core Expectations', function (): void {
    describe('Happy Paths', function (): void {
        test('toBe() accepts strictly equal values', function (): void {
            expect(fn (): Expectation => assertExpect(42)->toBe(42))->not->toThrow(Throwable::class);
            expect(fn (): Expectation => assertExpect('hello')->toBe('hello'))->not->toThrow(Throwable::class);
            expect(fn (): Expectation => assertExpect(true)->toBe(true))->not->toThrow(Throwable::class);
            expect(fn (): Expectation => assertExpect(null)->toBe(null))->not->toThrow(Throwable::class);
        });

        test('toEqual() is alias for toBe()', function (): void {
            expect(fn (): Expectation => assertExpect(42)->toEqual(42))->not->toThrow(Throwable::class);
            expect(fn (): Expectation => assertExpect('test')->toEqual('test'))->not->toThrow(Throwable::class);
        });

        test('toBeNull() accepts null', function (): void {
            expect(fn (): Expectation => assertExpect(null)->toBeNull())->not->toThrow(Throwable::class);
        });

        test('toBeTrue() accepts boolean true', function (): void {
            expect(fn (): Expectation => assertExpect(true)->toBeTrue())->not->toThrow(Throwable::class);
        });

        test('toBeFalse() accepts boolean false', function (): void {
            expect(fn (): Expectation => assertExpect(false)->toBeFalse())->not->toThrow(Throwable::class);
        });

        test('toBeTruthy() accepts truthy values', function (): void {
            expect(fn (): Expectation => assertExpect(1)->toBeTruthy())->not->toThrow(Throwable::class);
            expect(fn (): Expectation => assertExpect('yes')->toBeTruthy())->not->toThrow(Throwable::class);
            expect(fn (): Expectation => assertExpect(true)->toBeTruthy())->not->toThrow(Throwable::class);
            expect(fn (): Expectation => assertExpect([1])->toBeTruthy())->not->toThrow(Throwable::class);
            expect(fn (): Expectation => assertExpect((object) [])->toBeTruthy())->not->toThrow(Throwable::class);
        });

        test('toBeFalsy() accepts falsy values', function (): void {
            expect(fn (): Expectation => assertExpect(0)->toBeFalsy())->not->toThrow(Throwable::class);
            expect(fn (): Expectation => assertExpect('')->toBeFalsy())->not->toThrow(Throwable::class);
            expect(fn (): Expectation => assertExpect(false)->toBeFalsy())->not->toThrow(Throwable::class);
            expect(fn (): Expectation => assertExpect(null)->toBeFalsy())->not->toThrow(Throwable::class);
            expect(fn (): Expectation => assertExpect([])->toBeFalsy())->not->toThrow(Throwable::class);
        });

        test('toBeEmpty() accepts empty values', function (): void {
            expect(fn (): Expectation => assertExpect('')->toBeEmpty())->not->toThrow(Throwable::class);
            expect(fn (): Expectation => assertExpect([])->toBeEmpty())->not->toThrow(Throwable::class);
            expect(fn (): Expectation => assertExpect(0)->toBeEmpty())->not->toThrow(Throwable::class);
        });
    });

    describe('Sad Paths', function (): void {
        test('toBe() rejects non-equal values', function (): void {
            expect(fn (): Expectation => assertExpect(42)->toBe(43))
                ->toThrow(InvalidArgumentException::class);
        });

        test('toBeNull() rejects non-null values', function (): void {
            expect(fn (): Expectation => assertExpect(42)->toBeNull())
                ->toThrow(InvalidArgumentException::class);
        });

        test('toBeTrue() rejects non-true values', function (): void {
            expect(fn (): Expectation => assertExpect(false)->toBeTrue())
                ->toThrow(InvalidArgumentException::class);
        });

        test('toBeFalse() rejects non-false values', function (): void {
            expect(fn (): Expectation => assertExpect(true)->toBeFalse())
                ->toThrow(InvalidArgumentException::class);
        });

        test('toBeTruthy() rejects falsy values', function (): void {
            expect(fn (): Expectation => assertExpect(0)->toBeTruthy())
                ->toThrow(InvalidArgumentException::class);
        });

        test('toBeFalsy() rejects truthy values', function (): void {
            expect(fn (): Expectation => assertExpect(1)->toBeFalsy())
                ->toThrow(InvalidArgumentException::class);
        });
    });

    describe('Edge Cases', function (): void {
        test('toBe() uses strict equality', function (): void {
            expect(fn (): Expectation => assertExpect('1')->toBe(1))
                ->toThrow(InvalidArgumentException::class);
        });

        test('toBeTruthy() uses loose comparison', function (): void {
            expect(fn (): Expectation => assertExpect('1')->toBeTruthy())->not->toThrow(Throwable::class);
            expect(fn (): Expectation => assertExpect(1)->toBeTruthy())->not->toThrow(Throwable::class);
        });

        test('toBeFalsy() uses loose comparison', function (): void {
            expect(fn (): Expectation => assertExpect('0')->toBeFalsy())->not->toThrow(Throwable::class);
            expect(fn (): Expectation => assertExpect(0)->toBeFalsy())->not->toThrow(Throwable::class);
        });
    });
});
