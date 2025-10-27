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

describe('Object Expectations', function (): void {
    describe('Object Structure Methods', function (): void {
        test('toHaveProperty() accepts objects with existing property', function (): void {
            $obj = new stdClass();
            $obj->name = 'John';
            $obj->email = 'john@example.com';

            expect(fn() => assertExpect($obj)->toHaveProperty('name'))->not->toThrow();
            expect(fn() => assertExpect($obj)->toHaveProperty('email'))->not->toThrow();
        });

        test('toHaveProperty() works with class instances', function (): void {
            $user = new class()
            {
                public string $name = 'John';

                private string $email = 'john@example.com';
            };

            expect(fn() => assertExpect($user)->toHaveProperty('name'))->not->toThrow();
            expect(fn() => assertExpect($user)->toHaveProperty('email'))->not->toThrow();
        });

        test('toHaveProperty() rejects objects without property', function (): void {
            $obj = new stdClass();
            $obj->name = 'John';

            assertExpect(fn (): Expectation => assertExpect($obj)->toHaveProperty('email'))
                ->toThrow(AssertionFailedException::class);
        });

        test('toHaveMethod() accepts objects with existing method', function (): void {
            $obj = new class()
            {
                public function save(): void {}

                public function delete(): void {}
            };

            expect(fn() => assertExpect($obj)->toHaveMethod('save'))->not->toThrow();
            expect(fn() => assertExpect($obj)->toHaveMethod('delete'))->not->toThrow();
        });

        test('toHaveMethod() rejects objects without method', function (): void {
            $obj = new class()
            {
                public function save(): void {}
            };

            assertExpect(fn (): Expectation => assertExpect($obj)->toHaveMethod('delete'))
                ->toThrow(AssertionFailedException::class);
        });

        test('toBeInstanceOf() checks class inheritance', function (): void {
            $iterator = new ArrayIterator([]);

            expect(fn() => assertExpect($iterator)->toBeInstanceOf(ArrayIterator::class))->not->toThrow();
            expect(fn() => assertExpect($iterator)->toBeInstanceOf(Traversable::class))->not->toThrow();
        });
    });

    describe('Negation with Objects', function (): void {
        test('not->toHaveProperty() accepts objects without property', function (): void {
            $obj = new stdClass();
            $obj->name = 'John';

            expect(fn() => assertExpect($obj)->not->toHaveProperty('email'))->not->toThrow();
        });

        test('not->toHaveMethod() accepts objects without method', function (): void {
            $obj = new class()
            {
                public function save(): void {}
            };

            expect(fn() => assertExpect($obj)->not->toHaveMethod('delete'))->not->toThrow();
        });

        test('not->toBeInstanceOf() accepts different classes', function (): void {
            expect(fn() => assertExpect(
                new stdClass(),
            )->not->toBeInstanceOf(ArrayIterator::class))->not->toThrow();
        });
    });

    describe('Chaining Object Methods', function (): void {
        test('can chain multiple object assertions', function (): void {
            $obj = new class()
            {
                public string $name = 'John';

                public function save(): void {}
            };

            expect(fn() => assertExpect($obj)
                ->toBeObject()
                ->toHaveProperty('name')
                ->toHaveMethod('save'))->not->toThrow();
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

            expect(fn() => assertExpect($user)
                ->toBeObject()
                ->not->toBeNull()
                ->toHaveProperty('name')
                ->toHaveMethod('isActive'))->not->toThrow();
        });
    });

    describe('Edge Cases', function (): void {
        test('toHaveProperty() detects private properties', function (): void {
            $obj = new class()
            {
                public string $public = 'visible';

                private string $private = 'hidden';
            };

            expect(fn() => assertExpect($obj)->toHaveProperty('public'))->not->toThrow();
            expect(fn() => assertExpect($obj)->toHaveProperty('private'))->not->toThrow();
        });

        test('toHaveMethod() detects inherited methods', function (): void {
            $obj = new ArrayIterator([]);

            expect(fn() => assertExpect($obj)->toHaveMethod('count'))->not->toThrow();
        });
    });
});
