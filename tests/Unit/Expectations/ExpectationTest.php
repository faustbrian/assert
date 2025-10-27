<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Assert\Exceptions\AssertionFailedException;
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
        assertExpect(fn () => assertExpect(42)->nonExistent)
            ->toThrow(BadMethodCallException::class);
    });
});

describe('Core Expectations', function (): void {
    describe('Happy Paths', function (): void {
        test('toBe() accepts strictly equal values', function (): void {
            expect(fn() => assertExpect(42)->toBe(42))->not->toThrow();
            expect(fn() => assertExpect('hello')->toBe('hello'))->not->toThrow();
            expect(fn() => assertExpect(true)->toBe(true))->not->toThrow();
            expect(fn() => assertExpect(null)->toBe(null))->not->toThrow();
        });

        test('toEqual() is alias for toBe()', function (): void {
            expect(fn() => assertExpect(42)->toEqual(42))->not->toThrow();
            expect(fn() => assertExpect('test')->toEqual('test'))->not->toThrow();
        });

        test('toBeNull() accepts null', function (): void {
            expect(fn() => assertExpect(null)->toBeNull())->not->toThrow();
        });

        test('toBeTrue() accepts boolean true', function (): void {
            expect(fn() => assertExpect(true)->toBeTrue())->not->toThrow();
        });

        test('toBeFalse() accepts boolean false', function (): void {
            expect(fn() => assertExpect(false)->toBeFalse())->not->toThrow();
        });

        test('toBeTruthy() accepts truthy values', function (): void {
            expect(fn() => assertExpect(1)->toBeTruthy())->not->toThrow();
            expect(fn() => assertExpect('yes')->toBeTruthy())->not->toThrow();
            expect(fn() => assertExpect(true)->toBeTruthy())->not->toThrow();
            expect(fn() => assertExpect([1])->toBeTruthy())->not->toThrow();
            expect(fn() => assertExpect((object) [])->toBeTruthy())->not->toThrow();
        });

        test('toBeFalsy() accepts falsy values', function (): void {
            expect(fn() => assertExpect(0)->toBeFalsy())->not->toThrow();
            expect(fn() => assertExpect('')->toBeFalsy())->not->toThrow();
            expect(fn() => assertExpect(false)->toBeFalsy())->not->toThrow();
            expect(fn() => assertExpect(null)->toBeFalsy())->not->toThrow();
            expect(fn() => assertExpect([])->toBeFalsy())->not->toThrow();
        });

        test('toBeEmpty() accepts empty values', function (): void {
            expect(fn() => assertExpect('')->toBeEmpty())->not->toThrow();
            expect(fn() => assertExpect([])->toBeEmpty())->not->toThrow();
            expect(fn() => assertExpect(0)->toBeEmpty())->not->toThrow();
        });
    });

    describe('Sad Paths', function (): void {
        test('toBe() rejects non-equal values', function (): void {
            assertExpect(fn (): Expectation => assertExpect(42)->toBe(43))
                ->toThrow(AssertionFailedException::class);
        });

        test('toBeNull() rejects non-null values', function (): void {
            assertExpect(fn (): Expectation => assertExpect(42)->toBeNull())
                ->toThrow(AssertionFailedException::class);
        });

        test('toBeTrue() rejects non-true values', function (): void {
            assertExpect(fn (): Expectation => assertExpect(false)->toBeTrue())
                ->toThrow(AssertionFailedException::class);
        });

        test('toBeFalse() rejects non-false values', function (): void {
            assertExpect(fn (): Expectation => assertExpect(true)->toBeFalse())
                ->toThrow(AssertionFailedException::class);
        });

        test('toBeTruthy() rejects falsy values', function (): void {
            assertExpect(fn (): Expectation => assertExpect(0)->toBeTruthy())
                ->toThrow(AssertionFailedException::class);
        });

        test('toBeFalsy() rejects truthy values', function (): void {
            assertExpect(fn (): Expectation => assertExpect(1)->toBeFalsy())
                ->toThrow(AssertionFailedException::class);
        });
    });

    describe('Edge Cases', function (): void {
        test('toBe() uses strict equality', function (): void {
            assertExpect(fn (): Expectation => assertExpect('1')->toBe(1))
                ->toThrow(AssertionFailedException::class);
        });

        test('toBeTruthy() uses loose comparison', function (): void {
            expect(fn() => assertExpect('1')->toBeTruthy())->not->toThrow();
            expect(fn() => assertExpect(1)->toBeTruthy())->not->toThrow();
        });

        test('toBeFalsy() uses loose comparison', function (): void {
            expect(fn() => assertExpect('0')->toBeFalsy())->not->toThrow();
            expect(fn() => assertExpect(0)->toBeFalsy())->not->toThrow();
        });
    });
});
