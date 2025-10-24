<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Assert\AssertionChain;
use Cline\Assert\InvalidArgumentException;

use function Cline\Assert\that;
use function Cline\Assert\thatAll;
use function Cline\Assert\thatNullOr;

describe('Happy Path', function (): void {
    test('that() returns assertion chain instance', function (): void {
        expect(that(10)->notEmpty()->integer())->toBeInstanceOf(AssertionChain::class);
    });

    test('assertion chain shifts arguments by one for comparisons', function (): void {
        expect(that(10)->eq(10))->toBeInstanceOf(AssertionChain::class);
    });

    test('assertion chain uses default error message when provided', function (): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Not Null and such');
        that(null, 'Not Null and such')->notEmpty();
    });

    test('nullOr() skips assertions when value is null', function (): void {
        expect(that(null)->nullOr()->integer()->eq(10))->toBeInstanceOf(AssertionChain::class);
    });

    test('all() validates all array elements', function (): void {
        expect(that([1, 2, 3])->all()->integer())->toBeInstanceOf(AssertionChain::class);
    });

    test('thatAll() shortcut validates all array elements', function (): void {
        expect(thatAll([1, 2, 3])->integer())->toBeInstanceOf(AssertionChain::class);
    });

    test('thatNullOr() shortcut skips assertions for null values', function (): void {
        expect(thatNullOr(null)->integer()->eq(10))->toBeInstanceOf(AssertionChain::class);
    });

    test('satisfy() shortcut accepts custom validation callback', function (): void {
        expect(that(null)->satisfy(
            fn ($value): bool => null === $value,
        ))->toBeInstanceOf(AssertionChain::class);
    });
});

describe('Sad Path', function (): void {
    test('unknown assertion method throws RuntimeException', function (): void {
        $this->expectException('RuntimeException');
        $this->expectExceptionMessage("Assertion 'unknownAssertion' does not exist.");
        that(null)->unknownAssertion();
    });
});
