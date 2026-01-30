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

describe('Additional Coverage for Expectation', function (): void {
    describe('toHaveProperty negation', function (): void {
        test('not->toHaveProperty() passes when property does not exist', function (): void {
            $obj = new stdClass();
            $obj->name = 'test';

            expect(assertExpect($obj)->not->toHaveProperty('nonExistent'))->not->toThrow(Throwable::class);
        });

        test('not->toHaveProperty() fails when property exists', function (): void {
            $obj = new stdClass();
            $obj->name = 'test';

            expect(fn () => assertExpect($obj)->not->toHaveProperty('name'))
                ->toThrow(InvalidArgumentException::class);
        });
    });

    describe('toHaveMethod negation', function (): void {
        test('not->toHaveMethod() passes when method does not exist', function (): void {
            $obj = new stdClass();

            expect(assertExpect($obj)->not->toHaveMethod('nonExistentMethod'))->not->toThrow(Throwable::class);
        });

        test('not->toHaveMethod() fails when method exists', function (): void {
            $obj = new class()
            {
                public function testMethod(): void {}
            };

            expect(fn () => assertExpect($obj)->not->toHaveMethod('testMethod'))
                ->toThrow(InvalidArgumentException::class);
        });
    });

    describe('toMatchConstraint', function (): void {
        test('toMatchConstraint() passes when constraint evaluates successfully', function (): void {
            $constraint = new class()
            {
                public function evaluate($value): void
                {
                    throw_if($value !== 42, Exception::class, 'Value must be 42');
                }
            };

            expect(fn (): Expectation => assertExpect(42)->toMatchConstraint($constraint))
                ->not->toThrow(Throwable::class);
        });

        test('toMatchConstraint() fails when constraint evaluation throws', function (): void {
            $constraint = new class()
            {
                public function evaluate($value): never
                {
                    throw new Exception('Always fails');
                }
            };

            expect(fn (): Expectation => assertExpect(42)->toMatchConstraint($constraint))
                ->toThrow(InvalidArgumentException::class, 'Constraint evaluation failed');
        });

        test('toMatchConstraint() with negation passes when constraint fails', function (): void {
            $constraint = new class()
            {
                public function evaluate($value): never
                {
                    throw new Exception('Fails');
                }
            };

            expect(assertExpect(42)->not->toMatchConstraint($constraint))->not->toThrow(Throwable::class);
        });

        test('toMatchConstraint() with negation fails when constraint passes', function (): void {
            $constraint = new class()
            {
                public function evaluate($value): void
                {
                    // Passes silently
                }
            };

            expect(fn () => assertExpect(42)->not->toMatchConstraint($constraint))
                ->toThrow(InvalidArgumentException::class);
        });

        test('toMatchConstraint() throws when constraint has no evaluate method', function (): void {
            $constraint = new stdClass();

            expect(fn (): Expectation => assertExpect(42)->toMatchConstraint($constraint))
                ->toThrow(InvalidArgumentException::class);
        });
    });

    describe('toThrowError alias', function (): void {
        test('toThrowError() is alias for toThrow()', function (): void {
            expect(fn (): Expectation => assertExpect(fn (): never => throw new Exception('test'))->toThrowError())
                ->not->toThrow(Throwable::class);
        });

        test('toThrowError() with exception class', function (): void {
            expect(fn (): Expectation => assertExpect(fn (): never => throw new InvalidArgumentException('test'))->toThrowError(InvalidArgumentException::class))
                ->not->toThrow(Throwable::class);
        });

        test('toThrowError() with message', function (): void {
            expect(fn (): Expectation => assertExpect(fn (): never => throw new Exception('specific message'))->toThrowError(null, 'specific'))
                ->not->toThrow(Throwable::class);
        });
    });

    describe('matchesAsymmetric edge cases', function (): void {
        test('matchesAsymmetric returns false when array key missing', function (): void {
            $actual = ['a' => 1];
            $expected = ['a' => 1, 'b' => 2];

            expect(fn (): Expectation => assertExpect($actual)->toEqual($expected))
                ->toThrow(InvalidArgumentException::class);
        });
    });

    describe('evaluateOrGroups edge cases', function (): void {
        test('evaluateOrGroups with default case in threshold mode', function (): void {
            // This tests the default case in the match statement at line 1643
            $exp = assertExpect(42);

            // Using reflection to set invalid threshold mode to reach default case
            $reflection = new ReflectionClass($exp);

            $thresholdModeProp = $reflection->getProperty('thresholdMode');
            $thresholdModeProp->setValue($exp, 'invalidMode');

            $thresholdCountProp = $reflection->getProperty('thresholdCount');
            $thresholdCountProp->setValue($exp, 1);

            $orModeProp = $reflection->getProperty('orMode');
            $orModeProp->setValue($exp, true);

            $orGroupsProp = $reflection->getProperty('orGroups');
            $orGroupsProp->setValue($exp, [['success' => true, 'errors' => []]]);

            // Force evaluation
            $evaluateMethod = $reflection->getMethod('evaluateOrGroups');

            expect(fn (): mixed => $evaluateMethod->invoke($exp))
                ->toThrow(InvalidArgumentException::class);
        });
    });
});
