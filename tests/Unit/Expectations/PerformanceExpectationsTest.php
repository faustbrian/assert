<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Assert\Exceptions\InvalidArgumentException;

use function Cline\Assert\expect as assertExpect;

describe('Performance Expectations', function (): void {
    describe('->toCompleteWithin()', function (): void {
        test('passes when callable completes quickly', function (): void {
            $fastOperation = fn () => 1 + 1;

            expect(
                assertExpect($fastOperation)->toCompleteWithin(100),
            )->not->toThrow(Throwable::class);
        });

        test('passes with slightly slower operation', function (): void {
            $operation = function (): void {
                usleep(5000); // 5ms
            };

            expect(
                assertExpect($operation)->toCompleteWithin(50),
            )->not->toThrow(Throwable::class);
        });

        test('fails when callable takes too long', function (): void {
            $slowOperation = function (): void {
                usleep(100_000); // 100ms
            };

            expect(
                fn (): mixed => assertExpect($slowOperation)->toCompleteWithin(10),
            )->toThrow(InvalidArgumentException::class);
        });

        test('error message includes timing information', function (): void {
            $slowOperation = function (): void {
                usleep(50_000); // 50ms
            };

            try {
                assertExpect($slowOperation)->toCompleteWithin(10);
            } catch (InvalidArgumentException $exception) {
                expect($exception->getMessage())->toContain('Expected callable to complete within 10ms');
                expect($exception->getMessage())->toContain('but took');
                expect($exception->getMessage())->toMatch('/\d+\.\d+ms/');
            }
        });

        test('throws when value is not callable', function (): void {
            expect(
                fn (): mixed => assertExpect('not a callable')->toCompleteWithin(100),
            )->toThrow(InvalidArgumentException::class);
        });

        test('callable return value is ignored', function (): void {
            $operation = fn () => 'some value';

            expect(
                assertExpect($operation)->toCompleteWithin(100),
            )->not->toThrow(Throwable::class);
        });

        test('works with array sort as realistic example', function (): void {
            $operation = function (): void {
                $array = range(1, 1000);
                shuffle($array);
                sort($array);
            };

            expect(
                assertExpect($operation)->toCompleteWithin(100),
            )->not->toThrow(Throwable::class);
        });

        test('works with string operations', function (): void {
            $operation = function (): void {
                $string = str_repeat('test', 1000);
                strtoupper($string);
            };

            expect(
                assertExpect($operation)->toCompleteWithin(100),
            )->not->toThrow(Throwable::class);
        });

        test('detects performance regression', function (): void {
            $inefficientOperation = function (): void {
                // Simulate inefficient nested loops
                $result = 0;

                for ($i = 0; $i < 100; ++$i) {
                    for ($j = 0; $j < 100; ++$j) {
                        $result += $i * $j;
                    }
                }
            };

            // This should complete reasonably fast
            expect(
                assertExpect($inefficientOperation)->toCompleteWithin(100),
            )->not->toThrow(Throwable::class);
        });
    });
});
