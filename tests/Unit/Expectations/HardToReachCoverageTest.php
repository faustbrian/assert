<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Assert\Assertions\AbstractAssertion;
use Cline\Assert\Assertions\Assertion;
use Cline\Assert\Exceptions\AssertionFailedException;
use Cline\Assert\Exceptions\InvalidArgumentException;
use Cline\Assert\Expectations\Expectation;
use Cline\Assert\Matchers\AnythingMatcher;

use function Cline\Assert\expect as assertExpect;

describe('Hard-to-Reach Coverage Tests', function (): void {
    describe('Phase 1: Easy Wins', function (): void {
        describe('SchemaValidator type edge cases', function (): void {
            test('validates number type with numeric string', function (): void {
                expect(fn (): Expectation => assertExpect('42')->toMatchSchema(['type' => 'number']))
                    ->not->toThrow(Throwable::class);
            });

            test('validates object type with array', function (): void {
                expect(fn (): Expectation => assertExpect(['key' => 'value'])->toMatchSchema(['type' => 'object']))
                    ->not->toThrow(Throwable::class);
            });

            test('validates null type', function (): void {
                expect(fn (): Expectation => assertExpect(null)->toMatchSchema(['type' => 'null']))
                    ->not->toThrow(Throwable::class);
            });

            test('validates boolean type', function (): void {
                expect(fn (): Expectation => assertExpect(true)->toMatchSchema(['type' => 'boolean']))
                    ->not->toThrow(Throwable::class);
            });
        });

        describe('AbstractAssertion nullable type checks', function (): void {
            test('nullable() with callable', function (): void {
                expect(Assertion::nullable(fn () => true, 'callable'))->toBeTrue();
                expect(Assertion::nullable(null, 'callable'))->toBeTrue();
            });

            test('nullable() with iterable', function (): void {
                expect(Assertion::nullable([1, 2, 3], 'iterable'))->toBeTrue();
                expect(Assertion::nullable(null, 'iterable'))->toBeTrue();
            });

            test('nullable() with resource', function (): void {
                $resource = fopen('php://memory', 'rb');
                expect(Assertion::nullable($resource, 'resource'))->toBeTrue();
                fclose($resource);
                expect(Assertion::nullable(null, 'resource'))->toBeTrue();
            });

            test('nullable() with numeric', function (): void {
                expect(Assertion::nullable(42, 'numeric'))->toBeTrue();
                expect(Assertion::nullable('42', 'numeric'))->toBeTrue();
                expect(Assertion::nullable(null, 'numeric'))->toBeTrue();
            });

            test('nullable() with scalar', function (): void {
                expect(Assertion::nullable(42, 'scalar'))->toBeTrue();
                expect(Assertion::nullable('test', 'scalar'))->toBeTrue();
                expect(Assertion::nullable(true, 'scalar'))->toBeTrue();
                expect(Assertion::nullable(null, 'scalar'))->toBeTrue();
            });
        });

        describe('AbstractAssertion key case validation errors', function (): void {
            test('snakeCaseKeys throws on non-snake-case key', function (): void {
                expect(fn () => Assertion::snakeCaseKeys(['camelCase' => 1]))
                    ->toThrow(InvalidArgumentException::class);
            });

            test('camelCaseKeys throws on non-camel-case key', function (): void {
                expect(fn () => Assertion::camelCaseKeys(['snake_case' => 1]))
                    ->toThrow(InvalidArgumentException::class);
            });

            test('kebabCaseKeys throws on non-kebab-case key', function (): void {
                expect(fn () => Assertion::kebabCaseKeys(['camelCase' => 1]))
                    ->toThrow(InvalidArgumentException::class);
            });

            test('studlyCaseKeys throws on non-studly-case key', function (): void {
                expect(fn () => Assertion::studlyCaseKeys(['snake_case' => 1]))
                    ->toThrow(InvalidArgumentException::class);
            });
        });
    });

    describe('Phase 2: Medium Difficulty', function (): void {
        describe('Expectation asymmetric matching edge cases', function (): void {
            test('asymmetric matching fails when key missing', function (): void {
                expect(fn () => assertExpect(['a' => 1])->toEqual([
                    'a' => 1,
                    'b' => assertExpect()->anything(),
                ]))->toThrow(InvalidArgumentException::class);
            });

            test('asymmetric matching uses strict equality fallback', function (): void {
                $result = assertExpect(['a' => 1])->toEqual(['a' => 1]);
                expect($result)->toBeInstanceOf(Expectation::class);
            });
        });

        describe('Expectation negation edge cases', function (): void {
            test('not->invoke throws when assertion passes', function (): void {
                expect(fn () => assertExpect(42)->not->toBeGreaterThan(10))
                    ->toThrow(InvalidArgumentException::class, 'Expected assertion greaterThan to fail but it passed');
            });
        });
    });

    describe('Phase 3: Hard Cases', function (): void {
        describe('Expectation __call static forwarding', function (): void {
            test('__call forwards to static method when value is null', function (): void {
                // Call a static method on a null expectation via __call
                // 'assertSoft' is a static method that exists
                $expectation = assertExpect(null);
                $expectation->assertSoft();
                // clearSoftErrors to clean up
                $expectation->clearSoftErrors();
                expect(true)->toBeTrue(); // Test passes if no exception
            });
        });

        describe('Expectation orGroups edge cases', function (): void {
            test('or() initializes empty groups', function (): void {
                $expectation = assertExpect(42);
                $expectation->or();
                // Evaluate by calling a final assertion
                expect($expectation->toBeInt())->toBeInstanceOf(Expectation::class);
            });

            test('not->assertion in OR mode throws when passes', function (): void {
                expect(fn () => assertExpect(50)
                    ->or()
                    ->not->toBeGreaterThan(10)
                    ->endOr())
                    ->toThrow(InvalidArgumentException::class, 'Expected assertion greaterThan to fail but it passed');
            });
        });
    });
});
