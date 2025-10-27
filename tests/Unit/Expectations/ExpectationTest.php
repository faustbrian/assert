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
            assertExpect(42)->toBe(42);
            assertExpect('hello')->toBe('hello');
            assertExpect(true)->toBe(true);
            assertExpect(null)->toBe(null);
            expect(true)->toBeTrue();
        });

        test('toEqual() is alias for toBe()', function (): void {
            assertExpect(42)->toEqual(42);
            assertExpect('test')->toEqual('test');
            expect(true)->toBeTrue();
        });

        test('toBeNull() accepts null', function (): void {
            assertExpect(null)->toBeNull();
            expect(true)->toBeTrue();
        });

        test('toBeTrue() accepts boolean true', function (): void {
            assertExpect(true)->toBeTrue();
            expect(true)->toBeTrue();
        });

        test('toBeFalse() accepts boolean false', function (): void {
            assertExpect(false)->toBeFalse();
            expect(true)->toBeTrue();
        });

        test('toBeTruthy() accepts truthy values', function (): void {
            assertExpect(1)->toBeTruthy();
            assertExpect('yes')->toBeTruthy();
            assertExpect(true)->toBeTruthy();
            assertExpect([1])->toBeTruthy();
            assertExpect((object) [])->toBeTruthy();
            expect(true)->toBeTrue();
        });

        test('toBeFalsy() accepts falsy values', function (): void {
            assertExpect(0)->toBeFalsy();
            assertExpect('')->toBeFalsy();
            assertExpect(false)->toBeFalsy();
            assertExpect(null)->toBeFalsy();
            assertExpect([])->toBeFalsy();
            expect(true)->toBeTrue();
        });

        test('toBeEmpty() accepts empty values', function (): void {
            assertExpect('')->toBeEmpty();
            assertExpect([])->toBeEmpty();
            assertExpect(0)->toBeEmpty();
            expect(true)->toBeTrue();
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
            assertExpect('1')->toBeTruthy();
            assertExpect(1)->toBeTruthy();
            expect(true)->toBeTrue();
        });

        test('toBeFalsy() uses loose comparison', function (): void {
            assertExpect('0')->toBeFalsy();
            assertExpect(0)->toBeFalsy();
            expect(true)->toBeTrue();
        });
    });
});
