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

describe('Type Expectations', function (): void {
    describe('Happy Paths', function (): void {
        test('toBeString() accepts string values', function (): void {
            expect(fn() => assertExpect('hello')->toBeString())->not->toThrow(\Throwable::class);
            expect(fn() => assertExpect('')->toBeString())->not->toThrow(\Throwable::class);
            expect(fn() => assertExpect('123')->toBeString())->not->toThrow(\Throwable::class);
        });

        test('toBeInt() accepts integer values', function (): void {
            expect(fn() => assertExpect(42)->toBeInt())->not->toThrow(\Throwable::class);
            expect(fn() => assertExpect(0)->toBeInt())->not->toThrow(\Throwable::class);
            expect(fn() => assertExpect(-1)->toBeInt())->not->toThrow(\Throwable::class);
            expect(fn() => assertExpect(\PHP_INT_MAX)->toBeInt())->not->toThrow(\Throwable::class);
        });

        test('toBeFloat() accepts float values', function (): void {
            expect(fn() => assertExpect(3.14)->toBeFloat())->not->toThrow(\Throwable::class);
            expect(fn() => assertExpect(0.0)->toBeFloat())->not->toThrow(\Throwable::class);
            expect(fn() => assertExpect(-2.5)->toBeFloat())->not->toThrow(\Throwable::class);
        });

        test('toBeBool() accepts boolean values', function (): void {
            expect(fn() => assertExpect(true)->toBeBool())->not->toThrow(\Throwable::class);
            expect(fn() => assertExpect(false)->toBeBool())->not->toThrow(\Throwable::class);
        });

        test('toBeArray() accepts array values', function (): void {
            expect(fn() => assertExpect([])->toBeArray())->not->toThrow(\Throwable::class);
            expect(fn() => assertExpect([1, 2, 3])->toBeArray())->not->toThrow(\Throwable::class);
            expect(fn() => assertExpect(['key' => 'value'])->toBeArray())->not->toThrow(\Throwable::class);
        });

        test('toBeObject() accepts object values', function (): void {
            expect(fn() => assertExpect(
                new stdClass(),
            )->toBeObject())->not->toThrow(\Throwable::class);
            expect(fn() => assertExpect((object) ['a' => 1])->toBeObject())->not->toThrow(\Throwable::class);
        });

        test('toBeCallable() accepts callable values', function (): void {
            expect(fn() => assertExpect(fn (): true => true)->toBeCallable())->not->toThrow(\Throwable::class);
            expect(fn() => assertExpect('strlen')->toBeCallable())->not->toThrow(\Throwable::class);

            $obj = new class()
            {
                public function test(): void {}
            };
            expect(fn() => assertExpect($obj->test(...))->toBeCallable())->not->toThrow(\Throwable::class);
        });

        test('toBeIterable() accepts iterable values', function (): void {
            expect(fn() => assertExpect([])->toBeIterable())->not->toThrow(\Throwable::class);
            expect(fn() => assertExpect(
                new ArrayIterator([]),
            )->toBeIterable())->not->toThrow(\Throwable::class);
        });

        test('toBeCountable() accepts countable values', function (): void {
            expect(fn() => assertExpect([])->toBeCountable())->not->toThrow(\Throwable::class);
            expect(fn() => assertExpect(
                new ArrayObject([1, 2]),
            )->toBeCountable())->not->toThrow(\Throwable::class);
        });

        test('toBeNumeric() accepts numeric values', function (): void {
            expect(fn() => assertExpect(42)->toBeNumeric())->not->toThrow(\Throwable::class);
            expect(fn() => assertExpect('42')->toBeNumeric())->not->toThrow(\Throwable::class);
            expect(fn() => assertExpect(3.14)->toBeNumeric())->not->toThrow(\Throwable::class);
        });

        test('toBeScalar() accepts scalar values', function (): void {
            expect(fn() => assertExpect(42)->toBeScalar())->not->toThrow(\Throwable::class);
            expect(fn() => assertExpect('test')->toBeScalar())->not->toThrow(\Throwable::class);
            expect(fn() => assertExpect(3.14)->toBeScalar())->not->toThrow(\Throwable::class);
            expect(fn() => assertExpect(true)->toBeScalar())->not->toThrow(\Throwable::class);
        });

        test('toBeResource() accepts resource values', function (): void {
            $handle = fopen('php://memory', 'rb');
            expect(fn() => assertExpect($handle)->toBeResource())->not->toThrow(\Throwable::class);
            fclose($handle);
        });
    });

    describe('Sad Paths', function (): void {
        test('toBeString() rejects non-string values', function (): void {
            expect(fn () => assertExpect(42)->toBeString())
                ->toThrow(InvalidArgumentException::class);
        });

        test('toBeInt() rejects non-integer values', function (): void {
            expect(fn () => assertExpect(3.14)->toBeInt())
                ->toThrow(InvalidArgumentException::class);
            expect(fn () => assertExpect('42')->toBeInt())
                ->toThrow(InvalidArgumentException::class);
        });

        test('toBeFloat() rejects non-float values', function (): void {
            expect(fn () => assertExpect(42)->toBeFloat())
                ->toThrow(InvalidArgumentException::class);
        });

        test('toBeBool() rejects non-boolean values', function (): void {
            expect(fn () => assertExpect(1)->toBeBool())
                ->toThrow(InvalidArgumentException::class);
            expect(fn () => assertExpect('true')->toBeBool())
                ->toThrow(InvalidArgumentException::class);
        });

        test('toBeArray() rejects non-array values', function (): void {
            expect(fn () => assertExpect('test')->toBeArray())
                ->toThrow(InvalidArgumentException::class);
            expect(fn () => assertExpect(
                new ArrayObject([]),
            )->toBeArray())
                ->toThrow(InvalidArgumentException::class);
        });

        test('toBeObject() rejects non-object values', function (): void {
            expect(fn () => assertExpect([])->toBeObject())
                ->toThrow(InvalidArgumentException::class);
        });

        test('toBeCallable() rejects non-callable values', function (): void {
            expect(fn () => assertExpect(42)->toBeCallable())
                ->toThrow(InvalidArgumentException::class);
            expect(fn () => assertExpect('nonExistentFunction')->toBeCallable())
                ->toThrow(InvalidArgumentException::class);
        });

        test('toBeNumeric() rejects non-numeric values', function (): void {
            expect(fn () => assertExpect('abc')->toBeNumeric())
                ->toThrow(InvalidArgumentException::class);
        });

        test('toBeScalar() rejects non-scalar values', function (): void {
            expect(fn () => assertExpect([])->toBeScalar())
                ->toThrow(InvalidArgumentException::class);
            expect(fn () => assertExpect(
                new stdClass(),
            )->toBeScalar())
                ->toThrow(InvalidArgumentException::class);
        });
    });

    describe('Edge Cases', function (): void {
        test('toBeInt() strict check', function (): void {
            expect(fn () => assertExpect('42')->toBeInt())
                ->toThrow(InvalidArgumentException::class);
        });

        test('toBeFloat() strict check', function (): void {
            expect(fn () => assertExpect(42)->toBeFloat())
                ->toThrow(InvalidArgumentException::class);
        });

        test('toBeBool() strict check', function (): void {
            expect(fn () => assertExpect(0)->toBeBool())
                ->toThrow(InvalidArgumentException::class);
            expect(fn () => assertExpect(1)->toBeBool())
                ->toThrow(InvalidArgumentException::class);
        });

        test('type expectations can be chained', function (): void {
            expect(fn() => assertExpect('test')
                ->toBeString()
                ->not->toBeInt()
                ->not->toBeArray())->not->toThrow(\Throwable::class);
        });

        test('toBeNumeric() accepts string numbers', function (): void {
            expect(fn() => assertExpect('42')->toBeNumeric())->not->toThrow(\Throwable::class);
            expect(fn() => assertExpect('3.14')->toBeNumeric())->not->toThrow(\Throwable::class);
        });
    });

    describe('Negation with Types', function (): void {
        test('not->toBeString() accepts non-strings', function (): void {
            expect(fn() => assertExpect(42)->not->toBeString())->not->toThrow(\Throwable::class);
            expect(fn() => assertExpect([])->not->toBeString())->not->toThrow(\Throwable::class);
        });

        test('not->toBeInt() accepts non-integers', function (): void {
            expect(fn() => assertExpect('test')->not->toBeInt())->not->toThrow(\Throwable::class);
            expect(fn() => assertExpect(3.14)->not->toBeInt())->not->toThrow(\Throwable::class);
        });

        test('not->toBeFloat() accepts non-floats', function (): void {
            expect(fn() => assertExpect(42)->not->toBeFloat())->not->toThrow(\Throwable::class);
            expect(fn() => assertExpect('3.14')->not->toBeFloat())->not->toThrow(\Throwable::class);
        });

        test('not->toBeArray() accepts non-arrays', function (): void {
            expect(fn() => assertExpect('test')->not->toBeArray())->not->toThrow(\Throwable::class);
            expect(fn() => assertExpect(42)->not->toBeArray())->not->toThrow(\Throwable::class);
        });
    });
});
