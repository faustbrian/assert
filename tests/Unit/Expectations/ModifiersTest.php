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

describe('Expectation Modifiers', function (): void {
    describe('each() Modifier', function (): void {
        test('each() with callback applies expectation to all items', function (): void {
            expect(fn (): mixed => assertExpect([1, 2, 3])->each(fn ($item) => $item->toBeInt()))->not->toThrow(Throwable::class);
        });

        test('each() as property applies next assertion to all items', function (): void {
            expect(assertExpect([1, 2, 3])->each->toBeInt(...))->not->toThrow(Throwable::class);
            expect(assertExpect(['a', 'b', 'c'])->each->toBeString(...))->not->toThrow(Throwable::class);
        });

        test('each() can use multiple chained assertions', function (): void {
            expect(fn (): mixed => assertExpect([1, 2, 3])->each(
                fn ($item) => $item->toBeInt()->toBeGreaterThan(0),
            ))->not->toThrow(Throwable::class);
        });

        test('each() receives key as second parameter', function (): void {
            $keys = [];
            assertExpect(['a' => 1, 'b' => 2])->each(function ($item, $key) use (&$keys): void {
                $keys[] = $key;
                $item->toBeInt();
            });

            expect($keys)->toBe(['a', 'b']);
        });

        test('each() fails if any item fails assertion', function (): void {
            expect(fn () => (assertExpect([1, 'two', 3])->each->toBeInt(...))())
                ->toThrow(InvalidArgumentException::class);
        });

        test('each() requires traversable value', function (): void {
            expect(fn () => assertExpect(42)->each->toBeInt(...))
                ->toThrow(InvalidArgumentException::class);
        });
    });

    describe('and() Modifier', function (): void {
        test('and() without argument continues on same value', function (): void {
            expect(fn (): Expectation => assertExpect(42)
                ->toBeInt()
                ->and()
                ->toBeGreaterThan(0))->not->toThrow(Throwable::class);
        });

        test('and() with argument creates new expectation', function (): void {
            expect(fn (): Expectation => assertExpect(42)
                ->toBeInt()
                ->and('hello')
                ->toBeString())->not->toThrow(Throwable::class);
        });

        test('and() chains multiple different values', function (): void {
            expect(fn (): Expectation => assertExpect(42)
                ->toBeInt()
                ->and('test')
                ->toBeString()
                ->and([1, 2])
                ->toBeArray())->not->toThrow(Throwable::class);
        });

        test('and() preserves original expectation', function (): void {
            $first = assertExpect(42)->toBeInt();
            $second = $first->and('hello');

            assertExpect($first->toBeString(...))
                ->toThrow(InvalidArgumentException::class);
        });
    });

    describe('when() Modifier', function (): void {
        test('when() executes callback when condition is true', function (): void {
            $executed = false;

            assertExpect(42)->when(true, function ($exp) use (&$executed): void {
                $executed = true;
                $exp->toBeInt();
            });

            expect($executed)->toBeTrue();
        });

        test('when() skips callback when condition is false', function (): void {
            $executed = false;

            assertExpect(42)->when(false, function ($exp) use (&$executed): void {
                $executed = true;
                $exp->toBeString(); // Would fail if executed
            });

            expect($executed)->toBeFalse();
        });

        test('when() accepts callable condition', function (): void {
            expect(fn (): Expectation => assertExpect(42)->when(
                fn ($v): bool => $v > 0,
                fn ($exp) => $exp->toBeGreaterThan(0),
            ))->not->toThrow(Throwable::class);
        });

        test('when() chains with other expectations', function (): void {
            expect(fn (): Expectation => assertExpect(42)
                ->toBeInt()
                ->when(true, fn ($exp) => $exp->toBeGreaterThan(0))
                ->toBeLessThan(100))->not->toThrow(Throwable::class);
        });
    });

    describe('unless() Modifier', function (): void {
        test('unless() executes callback when condition is false', function (): void {
            $executed = false;

            assertExpect(42)->unless(false, function ($exp) use (&$executed): void {
                $executed = true;
                $exp->toBeInt();
            });

            expect($executed)->toBeTrue();
        });

        test('unless() skips callback when condition is true', function (): void {
            $executed = false;

            assertExpect(42)->unless(true, function ($exp) use (&$executed): void {
                $executed = true;
                $exp->toBeString(); // Would fail if executed
            });

            expect($executed)->toBeFalse();
        });

        test('unless() accepts callable condition', function (): void {
            expect(fn (): Expectation => assertExpect(null)->unless(
                fn ($v): bool => $v !== null,
                fn ($exp) => $exp->toBeNull(),
            ))->not->toThrow(Throwable::class);
        });

        test('unless() is inverse of when()', function (): void {
            $whenExecuted = false;
            $unlessExecuted = false;

            assertExpect(42)
                ->when(true, function () use (&$whenExecuted): void {
                    $whenExecuted = true;
                })
                ->unless(true, function () use (&$unlessExecuted): void {
                    $unlessExecuted = true;
                });

            expect($whenExecuted)->toBeTrue();
            expect($unlessExecuted)->toBeFalse();
        });
    });

    describe('Combining Modifiers', function (): void {
        test('can combine each() with negation', function (): void {
            expect(assertExpect([1, 2, 3])->each->not->toBeString(...))->not->toThrow(Throwable::class);
        });

        test('can combine when() with and()', function (): void {
            expect(fn (): Expectation => assertExpect(42)
                ->when(true, fn ($exp) => $exp->toBeInt())
                ->and('test')
                ->toBeString())->not->toThrow(Throwable::class);
        });

        test('can chain multiple conditional modifiers', function (): void {
            $count = 0;

            assertExpect(42)
                ->when(true, function () use (&$count): void {
                    ++$count;
                })
                ->unless(false, function () use (&$count): void {
                    ++$count;
                })
                ->when(fn ($v): bool => $v > 0, function () use (&$count): void {
                    ++$count;
                });

            expect($count)->toBe(3);
        });

        test('each() with when() creates complex conditions', function (): void {
            expect(fn (): mixed => assertExpect([1, 2, 3, 4, 5])->each(function ($item): void {
                $item
                    ->toBeInt()
                    ->when(fn ($v): bool => $v > 3, fn ($exp) => $exp->toBeGreaterThan(3));
            }))->not->toThrow(Throwable::class);
        });
    });
});
