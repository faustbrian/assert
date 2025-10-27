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

describe('Type Expectations', function (): void {
    describe('Happy Paths', function (): void {
        test('toBeString() accepts string values', function (): void {
            assertExpect('hello')->toBeString();
            assertExpect('')->toBeString();
            assertExpect('123')->toBeString();

            expect(true)->toBeTrue();
        });

        test('toBeInt() accepts integer values', function (): void {
            assertExpect(42)->toBeInt();
            assertExpect(0)->toBeInt();
            assertExpect(-1)->toBeInt();
            assertExpect(\PHP_INT_MAX)->toBeInt();

            expect(true)->toBeTrue();
        });

        test('toBeFloat() accepts float values', function (): void {
            assertExpect(3.14)->toBeFloat();
            assertExpect(0.0)->toBeFloat();
            assertExpect(-2.5)->toBeFloat();

            expect(true)->toBeTrue();
        });

        test('toBeBool() accepts boolean values', function (): void {
            assertExpect(true)->toBeBool();
            assertExpect(false)->toBeBool();

            expect(true)->toBeTrue();
        });

        test('toBeArray() accepts array values', function (): void {
            assertExpect([])->toBeArray();
            assertExpect([1, 2, 3])->toBeArray();
            assertExpect(['key' => 'value'])->toBeArray();

            expect(true)->toBeTrue();
        });

        test('toBeObject() accepts object values', function (): void {
            assertExpect(
                new stdClass(),
            )->toBeObject();
            assertExpect((object) ['a' => 1])->toBeObject();

            expect(true)->toBeTrue();
        });

        test('toBeCallable() accepts callable values', function (): void {
            assertExpect(fn (): true => true)->toBeCallable();
            assertExpect('strlen')->toBeCallable();

            $obj = new class()
            {
                public function test(): void {}
            };
            assertExpect($obj->test(...))->toBeCallable();
            expect(true)->toBeTrue();
        });

        test('toBeIterable() accepts iterable values', function (): void {
            assertExpect([])->toBeIterable();
            assertExpect(
                new ArrayIterator([]),
            )->toBeIterable();
            expect(true)->toBeTrue();
        });

        test('toBeCountable() accepts countable values', function (): void {
            assertExpect([])->toBeCountable();
            assertExpect(
                new ArrayObject([1, 2]),
            )->toBeCountable();
            expect(true)->toBeTrue();
        });

        test('toBeNumeric() accepts numeric values', function (): void {
            assertExpect(42)->toBeNumeric();
            assertExpect('42')->toBeNumeric();
            assertExpect(3.14)->toBeNumeric();

            expect(true)->toBeTrue();
        });

        test('toBeScalar() accepts scalar values', function (): void {
            assertExpect(42)->toBeScalar();
            assertExpect('test')->toBeScalar();
            assertExpect(3.14)->toBeScalar();
            assertExpect(true)->toBeScalar();

            expect(true)->toBeTrue();
        });

        test('toBeResource() accepts resource values', function (): void {
            $handle = fopen('php://memory', 'rb');
            assertExpect($handle)->toBeResource();
            fclose($handle);
            expect(true)->toBeTrue();
        });
    });

    describe('Sad Paths', function (): void {
        test('toBeString() rejects non-string values', function (): void {
            assertExpect(fn (): Expectation => assertExpect(42)->toBeString())
                ->toThrow(AssertionFailedException::class);
        });

        test('toBeInt() rejects non-integer values', function (): void {
            assertExpect(fn (): Expectation => assertExpect(3.14)->toBeInt())
                ->toThrow(AssertionFailedException::class);
            assertExpect(fn (): Expectation => assertExpect('42')->toBeInt())
                ->toThrow(AssertionFailedException::class);
        });

        test('toBeFloat() rejects non-float values', function (): void {
            assertExpect(fn (): Expectation => assertExpect(42)->toBeFloat())
                ->toThrow(AssertionFailedException::class);
        });

        test('toBeBool() rejects non-boolean values', function (): void {
            assertExpect(fn (): Expectation => assertExpect(1)->toBeBool())
                ->toThrow(AssertionFailedException::class);
            assertExpect(fn (): Expectation => assertExpect('true')->toBeBool())
                ->toThrow(AssertionFailedException::class);
        });

        test('toBeArray() rejects non-array values', function (): void {
            assertExpect(fn (): Expectation => assertExpect('test')->toBeArray())
                ->toThrow(AssertionFailedException::class);
            assertExpect(fn (): Expectation => assertExpect(
                new ArrayObject([]),
            )->toBeArray())
                ->toThrow(AssertionFailedException::class);
        });

        test('toBeObject() rejects non-object values', function (): void {
            assertExpect(fn (): Expectation => assertExpect([])->toBeObject())
                ->toThrow(AssertionFailedException::class);
        });

        test('toBeCallable() rejects non-callable values', function (): void {
            assertExpect(fn (): Expectation => assertExpect(42)->toBeCallable())
                ->toThrow(AssertionFailedException::class);
            assertExpect(fn (): Expectation => assertExpect('nonExistentFunction')->toBeCallable())
                ->toThrow(AssertionFailedException::class);
        });

        test('toBeNumeric() rejects non-numeric values', function (): void {
            assertExpect(fn (): Expectation => assertExpect('abc')->toBeNumeric())
                ->toThrow(AssertionFailedException::class);
        });

        test('toBeScalar() rejects non-scalar values', function (): void {
            assertExpect(fn (): Expectation => assertExpect([])->toBeScalar())
                ->toThrow(AssertionFailedException::class);
            assertExpect(fn (): Expectation => assertExpect(
                new stdClass(),
            )->toBeScalar())
                ->toThrow(AssertionFailedException::class);
        });
    });

    describe('Edge Cases', function (): void {
        test('toBeInt() strict check', function (): void {
            assertExpect(fn (): Expectation => assertExpect('42')->toBeInt())
                ->toThrow(AssertionFailedException::class);
        });

        test('toBeFloat() strict check', function (): void {
            assertExpect(fn (): Expectation => assertExpect(42)->toBeFloat())
                ->toThrow(AssertionFailedException::class);
        });

        test('toBeBool() strict check', function (): void {
            assertExpect(fn (): Expectation => assertExpect(0)->toBeBool())
                ->toThrow(AssertionFailedException::class);
            assertExpect(fn (): Expectation => assertExpect(1)->toBeBool())
                ->toThrow(AssertionFailedException::class);
        });

        test('type expectations can be chained', function (): void {
            assertExpect('test')
                ->toBeString()
                ->not->toBeInt()
                ->not->toBeArray();
            expect(true)->toBeTrue();
        });

        test('toBeNumeric() accepts string numbers', function (): void {
            assertExpect('42')->toBeNumeric();
            assertExpect('3.14')->toBeNumeric();

            expect(true)->toBeTrue();
        });
    });

    describe('Negation with Types', function (): void {
        test('not->toBeString() accepts non-strings', function (): void {
            assertExpect(42)->not->toBeString();
            assertExpect([])->not->toBeString();

            expect(true)->toBeTrue();
        });

        test('not->toBeInt() accepts non-integers', function (): void {
            assertExpect('test')->not->toBeInt();
            assertExpect(3.14)->not->toBeInt();

            expect(true)->toBeTrue();
        });

        test('not->toBeFloat() accepts non-floats', function (): void {
            assertExpect(42)->not->toBeFloat();
            assertExpect('3.14')->not->toBeFloat();

            expect(true)->toBeTrue();
        });

        test('not->toBeArray() accepts non-arrays', function (): void {
            assertExpect('test')->not->toBeArray();
            assertExpect(42)->not->toBeArray();

            expect(true)->toBeTrue();
        });
    });
});
