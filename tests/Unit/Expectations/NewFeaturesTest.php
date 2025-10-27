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

describe('New Expectation Features', function (): void {
    describe('Aliases', function (): void {
        test('toBeOneOf() accepts value in array', function (): void {
            expect(fn (): Expectation => assertExpect(2)->toBeOneOf([1, 2, 3]))->not->toThrow(Throwable::class);
            expect(fn (): Expectation => assertExpect('active')->toBeOneOf(['pending', 'active', 'completed']))->not->toThrow(Throwable::class);
        });

        test('toBeOneOf() rejects value not in array', function (): void {
            expect(fn (): Expectation => assertExpect(4)->toBeOneOf([1, 2, 3]))
                ->toThrow(InvalidArgumentException::class);
        });

        test('toBeCloseTo() accepts values within delta', function (): void {
            expect(fn (): Expectation => assertExpect(3.141_59)->toBeCloseTo(3.14, 0.01))->not->toThrow(Throwable::class);
            expect(fn (): Expectation => assertExpect(100)->toBeCloseTo(99, 1.0))->not->toThrow(Throwable::class);
        });

        test('toBeCloseTo() rejects values outside delta', function (): void {
            expect(fn (): Expectation => assertExpect(3.5)->toBeCloseTo(3.0, 0.1))
                ->toThrow(InvalidArgumentException::class);
        });

        test('toBeCloseTo() uses default delta', function (): void {
            expect(fn (): Expectation => assertExpect(3.005)->toBeCloseTo(3.0))->not->toThrow(Throwable::class);
        });
    });

    describe('Custom Validation', function (): void {
        test('toSatisfy() accepts value matching callback', function (): void {
            expect(fn (): Expectation => assertExpect(25)->toSatisfy(fn ($v): bool => $v > 18))->not->toThrow(Throwable::class);
            expect(fn (): Expectation => assertExpect('test')->toSatisfy(fn ($v): bool => mb_strlen($v) === 4))->not->toThrow(Throwable::class);
        });

        test('toSatisfy() rejects value not matching callback', function (): void {
            expect(fn (): Expectation => assertExpect(10)->toSatisfy(fn ($v): bool => $v > 18))
                ->toThrow(InvalidArgumentException::class);
        });

        test('toSatisfy() works with complex conditions', function (): void {
            $user = (object) ['age' => 25, 'verified' => true];
            expect(fn (): Expectation => assertExpect($user)->toSatisfy(
                fn ($u): bool => $u->age > 18 && $u->verified === true,
            ))->not->toThrow(Throwable::class);
        });
    });

    describe('Numeric Helpers', function (): void {
        test('toBePositive() accepts positive numbers', function (): void {
            expect(fn (): Expectation => assertExpect(1)->toBePositive())->not->toThrow(Throwable::class);
            expect(fn (): Expectation => assertExpect(42)->toBePositive())->not->toThrow(Throwable::class);
            expect(fn (): Expectation => assertExpect(0.1)->toBePositive())->not->toThrow(Throwable::class);
        });

        test('toBePositive() rejects zero and negative', function (): void {
            expect(fn (): Expectation => assertExpect(0)->toBePositive())
                ->toThrow(InvalidArgumentException::class);
            expect(fn (): Expectation => assertExpect(-5)->toBePositive())
                ->toThrow(InvalidArgumentException::class);
        });

        test('toBeNegative() accepts negative numbers', function (): void {
            expect(fn (): Expectation => assertExpect(-1)->toBeNegative())->not->toThrow(Throwable::class);
            expect(fn (): Expectation => assertExpect(-42)->toBeNegative())->not->toThrow(Throwable::class);
            expect(fn (): Expectation => assertExpect(-0.1)->toBeNegative())->not->toThrow(Throwable::class);
        });

        test('toBeNegative() rejects zero and positive', function (): void {
            expect(fn (): Expectation => assertExpect(0)->toBeNegative())
                ->toThrow(InvalidArgumentException::class);
            expect(fn (): Expectation => assertExpect(5)->toBeNegative())
                ->toThrow(InvalidArgumentException::class);
        });

        test('toBeEven() accepts even numbers', function (): void {
            expect(fn (): Expectation => assertExpect(2)->toBeEven())->not->toThrow(Throwable::class);
            expect(fn (): Expectation => assertExpect(0)->toBeEven())->not->toThrow(Throwable::class);
            expect(fn (): Expectation => assertExpect(-4)->toBeEven())->not->toThrow(Throwable::class);
        });

        test('toBeEven() rejects odd numbers', function (): void {
            expect(fn (): Expectation => assertExpect(1)->toBeEven())
                ->toThrow(InvalidArgumentException::class);
            expect(fn (): Expectation => assertExpect(3)->toBeEven())
                ->toThrow(InvalidArgumentException::class);
        });

        test('toBeOdd() accepts odd numbers', function (): void {
            expect(fn (): Expectation => assertExpect(1)->toBeOdd())->not->toThrow(Throwable::class);
            expect(fn (): Expectation => assertExpect(3)->toBeOdd())->not->toThrow(Throwable::class);
            expect(fn (): Expectation => assertExpect(-5)->toBeOdd())->not->toThrow(Throwable::class);
        });

        test('toBeOdd() rejects even numbers', function (): void {
            expect(fn (): Expectation => assertExpect(2)->toBeOdd())
                ->toThrow(InvalidArgumentException::class);
            expect(fn (): Expectation => assertExpect(0)->toBeOdd())
                ->toThrow(InvalidArgumentException::class);
        });
    });

    describe('Collection Helpers', function (): void {
        test('toContainAllValues() accepts array with all values', function (): void {
            expect(fn (): Expectation => assertExpect([1, 2, 3, 4, 5])->toContainAllValues([1, 3, 5]))
                ->not->toThrow(Throwable::class);
            expect(fn (): Expectation => assertExpect(['a', 'b', 'c'])->toContainAllValues(['a', 'c']))
                ->not->toThrow(Throwable::class);
        });

        test('toContainAllValues() rejects array missing values', function (): void {
            expect(fn (): Expectation => assertExpect([1, 2, 3])->toContainAllValues([1, 2, 4]))
                ->toThrow(InvalidArgumentException::class);
        });

        test('toContainAllKeys() accepts array with all keys', function (): void {
            expect(fn (): Expectation => assertExpect(['a' => 1, 'b' => 2, 'c' => 3])->toContainAllKeys(['a', 'c']))
                ->not->toThrow(Throwable::class);
        });

        test('toContainAllKeys() rejects array missing keys', function (): void {
            expect(fn (): Expectation => assertExpect(['a' => 1, 'b' => 2])->toContainAllKeys(['a', 'c']))
                ->toThrow(InvalidArgumentException::class);
        });
    });

    describe('Negation Support', function (): void {
        test('not->toBeOneOf() accepts value not in array', function (): void {
            expect(fn (): Expectation => assertExpect(4)->not->toBeOneOf([1, 2, 3]))
                ->not->toThrow(Throwable::class);
        });

        test('not->toBePositive() accepts zero and negative', function (): void {
            expect(assertExpect(0)->not->toBePositive(...))
                ->not->toThrow(Throwable::class);
            expect(assertExpect(-5)->not->toBePositive(...))
                ->not->toThrow(Throwable::class);
        });

        test('not->toBeEven() accepts odd numbers', function (): void {
            expect(assertExpect(1)->not->toBeEven(...))
                ->not->toThrow(Throwable::class);
        });

        test('not->toSatisfy() accepts value not matching callback', function (): void {
            expect(fn (): Expectation => assertExpect(10)->not->toSatisfy(fn ($v): bool => $v > 18))
                ->not->toThrow(Throwable::class);
        });
    });

    describe('Chaining with New Features', function (): void {
        test('can chain numeric helpers', function (): void {
            expect(fn (): Expectation => assertExpect(4)
                ->toBeInt()
                ->toBePositive()
                ->toBeEven())
                ->not->toThrow(Throwable::class);
        });

        test('can chain collection helpers', function (): void {
            expect(fn (): Expectation => assertExpect([1, 2, 3])
                ->toBeArray()
                ->toContainAllValues([1, 3])
                ->toHaveCount(3))
                ->not->toThrow(Throwable::class);
        });

        test('can use new features with each modifier', function (): void {
            expect(assertExpect([2, 4, 6])->each->toBeEven(...))
                ->not->toThrow(Throwable::class);
            expect(assertExpect([1, 5, 10])->each->toBePositive(...))
                ->not->toThrow(Throwable::class);
        });
    });
});
