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

    test('not() negates the next assertion - passes when assertion fails', function (): void {
        expect(Assert::that(1)->not()->eq(2))->toBeInstanceOf(AssertionChain::class);
    });

    test('not() negates string assertion', function (): void {
        expect(Assert::that(123)->not()->string())->toBeInstanceOf(AssertionChain::class);
    });

    test('not() negates integer assertion', function (): void {
        expect(Assert::that('foo')->not()->integer())->toBeInstanceOf(AssertionChain::class);
    });

    test('not() negates null assertion', function (): void {
        expect(Assert::that('value')->not()->null())->toBeInstanceOf(AssertionChain::class);
    });

    test('not() negates boolean assertion', function (): void {
        expect(Assert::that('true')->not()->boolean())->toBeInstanceOf(AssertionChain::class);
    });

    test('not() negates same assertion', function (): void {
        expect(Assert::that(1)->not()->same(2))->toBeInstanceOf(AssertionChain::class);
    });

    test('not() negates greaterThan assertion', function (): void {
        expect(Assert::that(5)->not()->greaterThan(10))->toBeInstanceOf(AssertionChain::class);
    });

    test('not() negates lessThan assertion', function (): void {
        expect(Assert::that(10)->not()->lessThan(5))->toBeInstanceOf(AssertionChain::class);
    });

    test('not() negates contains assertion', function (): void {
        expect(Assert::that('hello world')->not()->contains('xyz'))->toBeInstanceOf(AssertionChain::class);
    });

    test('not() negates startsWith assertion', function (): void {
        expect(Assert::that('hello')->not()->startsWith('bye'))->toBeInstanceOf(AssertionChain::class);
    });

    test('not() negates endsWith assertion', function (): void {
        expect(Assert::that('hello')->not()->endsWith('xyz'))->toBeInstanceOf(AssertionChain::class);
    });

    test('not() works with method chaining', function (): void {
        expect(Assert::that(1)->not()->eq(2)->not()->eq(3)->eq(1))->toBeInstanceOf(AssertionChain::class);
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

    test('not() throws when negated assertion passes', function (): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected assertion to fail but it passed');
        Assert::that(1)->not()->eq(1);
    });

    test('not() throws when negated string assertion passes', function (): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected assertion to fail but it passed');
        Assert::that('test')->not()->string();
    });

    test('not() throws when negated integer assertion passes', function (): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected assertion to fail but it passed');
        Assert::that(42)->not()->integer();
    });

    test('not() throws when negated null assertion passes', function (): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected assertion to fail but it passed');
        Assert::that(null)->not()->null();
    });

    test('not() throws when negated greaterThan assertion passes', function (): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected assertion to fail but it passed');
        Assert::that(10)->not()->greaterThan(5);
    });

    test('not() throws when negated contains assertion passes', function (): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected assertion to fail but it passed');
        Assert::that('hello world')->not()->contains('world');
    });
});
