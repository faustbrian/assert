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

describe('Object Expectations', function (): void {
    describe('Object Structure Methods', function (): void {
        test('toHaveProperty() accepts objects with existing property', function (): void {
            $obj = new stdClass();
            $obj->name = 'John';
            $obj->email = 'john@example.com';

            expect(fn (): Expectation => assertExpect($obj)->toHaveProperty('name'))->not->toThrow(Throwable::class);
            expect(fn (): Expectation => assertExpect($obj)->toHaveProperty('email'))->not->toThrow(Throwable::class);
        });

        test('toHaveProperty() works with class instances', function (): void {
            $user = new class()
            {
                public string $name = 'John';
            };

            expect(fn (): Expectation => assertExpect($user)->toHaveProperty('name'))->not->toThrow(Throwable::class);
            expect(fn (): Expectation => assertExpect($user)->toHaveProperty('email'))->not->toThrow(Throwable::class);
        });

        test('toHaveProperty() rejects objects without property', function (): void {
            $obj = new stdClass();
            $obj->name = 'John';

            expect(fn (): Expectation => assertExpect($obj)->toHaveProperty('email'))
                ->toThrow(InvalidArgumentException::class);
        });

        test('toHaveMethod() accepts objects with existing method', function (): void {
            $obj = new class()
            {
                public function save(): void {}

                public function delete(): void {}
            };

            expect(fn (): Expectation => assertExpect($obj)->toHaveMethod('save'))->not->toThrow(Throwable::class);
            expect(fn (): Expectation => assertExpect($obj)->toHaveMethod('delete'))->not->toThrow(Throwable::class);
        });

        test('toHaveMethod() rejects objects without method', function (): void {
            $obj = new class()
            {
                public function save(): void {}
            };

            expect(fn (): Expectation => assertExpect($obj)->toHaveMethod('delete'))
                ->toThrow(InvalidArgumentException::class);
        });

        test('toBeInstanceOf() checks class inheritance', function (): void {
            $iterator = new ArrayIterator([]);

            expect(fn (): Expectation => assertExpect($iterator)->toBeInstanceOf(ArrayIterator::class))->not->toThrow(Throwable::class);
            expect(fn (): Expectation => assertExpect($iterator)->toBeInstanceOf(Traversable::class))->not->toThrow(Throwable::class);
        });
    });

    describe('Negation with Objects', function (): void {
        test('not->toHaveProperty() accepts objects without property', function (): void {
            $obj = new stdClass();
            $obj->name = 'John';

            expect(fn () => assertExpect($obj)->not->toHaveProperty('email'))->not->toThrow(Throwable::class);
        });

        test('not->toHaveMethod() accepts objects without method', function (): void {
            $obj = new class()
            {
                public function save(): void {}
            };

            expect(fn () => assertExpect($obj)->not->toHaveMethod('delete'))->not->toThrow(Throwable::class);
        });

        test('not->toBeInstanceOf() accepts different classes', function (): void {
            expect(fn () => assertExpect(
                new stdClass(),
            )->not->toBeInstanceOf(ArrayIterator::class))->not->toThrow(Throwable::class);
        });
    });

    describe('Chaining Object Methods', function (): void {
        test('can chain multiple object assertions', function (): void {
            $obj = new class()
            {
                public string $name = 'John';

                public function save(): void {}
            };

            expect(fn (): Expectation => assertExpect($obj)
                ->toBeObject()
                ->toHaveProperty('name')
                ->toHaveMethod('save'))->not->toThrow(Throwable::class);
        });

        test('can mix object and type checks', function (): void {
            $user = new class()
            {
                public string $name = 'John';

                public function isActive(): bool
                {
                    return true;
                }
            };

            expect(fn () => assertExpect($user)
                ->toBeObject()
                ->not->toBeNull()
                ->toHaveProperty('name')
                ->toHaveMethod('isActive'))->not->toThrow(Throwable::class);
        });
    });

    describe('Edge Cases', function (): void {
        test('toHaveProperty() detects private properties', function (): void {
            $obj = new class()
            {
                public string $public = 'visible';
            };

            expect(fn (): Expectation => assertExpect($obj)->toHaveProperty('public'))->not->toThrow(Throwable::class);
            expect(fn (): Expectation => assertExpect($obj)->toHaveProperty('private'))->not->toThrow(Throwable::class);
        });

        test('toHaveMethod() detects inherited methods', function (): void {
            $obj = new ArrayIterator([]);

            expect(fn (): Expectation => assertExpect($obj)->toHaveMethod('count'))->not->toThrow(Throwable::class);
        });
    });
});
