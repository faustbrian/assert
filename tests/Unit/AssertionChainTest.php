<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Assert\Assert;
use Cline\Assert\AssertionChain;
use Cline\Assert\InvalidArgumentException;
use Tests\Fixtures\CustomAssertion;

describe('Happy Path', function (): void {
    test('Assert::that() returns assertion chain instance', function (): void {
        expect(Assert::that(10)->notEmpty()->integer())->toBeInstanceOf(AssertionChain::class);
    });

    test('assertion chain shifts arguments by one for comparisons', function (): void {
        expect(Assert::that(10)->eq(10))->toBeInstanceOf(AssertionChain::class);
    });

    test('assertion chain uses default error message when provided', function (): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Not Null and such');
        Assert::that(null, 'Not Null and such')->notEmpty();
    });

    test('nullOr() skips assertions when value is null', function (): void {
        expect(Assert::that(null)->nullOr()->integer()->eq(10))->toBeInstanceOf(AssertionChain::class);
    });

    test('all() validates all array elements', function (): void {
        expect(Assert::that([1, 2, 3])->all()->integer())->toBeInstanceOf(AssertionChain::class);
    });

    test('thatAll() shortcut validates all array elements', function (): void {
        expect(Assert::thatAll([1, 2, 3])->integer())->toBeInstanceOf(AssertionChain::class);
    });

    test('thatNullOr() shortcut skips assertions for null values', function (): void {
        expect(Assert::thatNullOr(null)->integer()->eq(10))->toBeInstanceOf(AssertionChain::class);
    });

    test('satisfy() shortcut accepts custom validation callback', function (): void {
        expect(Assert::that(null)->satisfy(
            fn ($value): bool => null === $value,
        ))->toBeInstanceOf(AssertionChain::class);
    });

    test('custom assertion class is used when set on chain', function (): void {
        $assertionChain = new AssertionChain('foo');
        $assertionChain->setAssertionClassName(CustomAssertion::class);

        CustomAssertion::clearCalls();
        $message = uniqid();
        $assertionChain->string($message);

        expect(CustomAssertion::getCalls())->toBe([['string', 'foo']]);
    });
});

describe('Sad Path', function (): void {
    test('unknown assertion method throws RuntimeException', function (): void {
        $this->expectException('RuntimeException');
        $this->expectExceptionMessage("Assertion 'unknownAssertion' does not exist.");
        Assert::that(null)->unknownAssertion();
    });

    test('setAssertionClassName rejects invalid assertion classes', function ($assertionClassName): void {
        $this->expectException('LogicException');
        $lazyAssertion = new AssertionChain('foo');

        $lazyAssertion->setAssertionClassName($assertionClassName);
    })->with('provideDataToTestThatSetAssertionClassNameWillNotAcceptInvalidAssertionClasses');

    dataset('provideDataToTestThatSetAssertionClassNameWillNotAcceptInvalidAssertionClasses', fn (): array => [
        'null' => [null],
        'string' => ['foo'],
        'array' => [[]],
        'object' => [new stdClass()],
        'other class' => [self::class],
    ]);
});
