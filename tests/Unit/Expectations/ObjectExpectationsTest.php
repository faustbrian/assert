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

            assertExpect($obj)->toHaveProperty('name');
            assertExpect($obj)->toHaveProperty('email');
        });

        test('toHaveProperty() works with class instances', function (): void {
            $user = new class()
            {
                public string $name = 'John';
            };

            assertExpect($user)->toHaveProperty('name');
            assertExpect($user)->toHaveProperty('email');
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

            assertExpect($obj)->toHaveMethod('save');
            assertExpect($obj)->toHaveMethod('delete');
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

            assertExpect($iterator)->toBeInstanceOf(ArrayIterator::class);
            assertExpect($iterator)->toBeInstanceOf(Traversable::class);
        });
    });

    describe('Negation with Objects', function (): void {
        test('not->toHaveProperty() accepts objects without property', function (): void {
            $obj = new stdClass();
            $obj->name = 'John';

            assertExpect($obj)->not->toHaveProperty('email');
        });

        test('not->toHaveMethod() accepts objects without method', function (): void {
            $obj = new class()
            {
                public function save(): void {}
            };

            assertExpect($obj)->not->toHaveMethod('delete');
        });

        test('not->toBeInstanceOf() accepts different classes', function (): void {
            assertExpect(
                new stdClass()
            )->not->toBeInstanceOf(ArrayIterator::class);
        });
    });

    describe('Chaining Object Methods', function (): void {
        test('can chain multiple object assertions', function (): void {
            $obj = new class()
            {
                public string $name = 'John';

                public function save(): void {}
            };

            assertExpect($obj)
                ->toBeObject()
                ->toHaveProperty('name')
                ->toHaveMethod('save');
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

            assertExpect($user)
                ->toBeObject()
                ->not->toBeNull()
                ->toHaveProperty('name')
                ->toHaveMethod('isActive');
        });
    });

    describe('Edge Cases', function (): void {
        test('toHaveProperty() detects private properties', function (): void {
            $obj = new class()
            {
                public string $public = 'visible';
            };

            assertExpect($obj)->toHaveProperty('public');
            assertExpect($obj)->toHaveProperty('private');
        });

        test('toHaveMethod() detects inherited methods', function (): void {
            $obj = new ArrayIterator([]);

            assertExpect($obj)->toHaveMethod('count'); // From Countable
        });
    });
});
