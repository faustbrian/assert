<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Assert\Assert;
use Cline\Assert\InvalidArgumentException;
use Cline\Assert\LazyAssertion;
use Cline\Assert\LazyAssertionException;
use Tests\Fixtures\CustomLazyAssertionException;

describe('Happy Path', function (): void {
    test('verifyNow() returns true when all assertions pass', function (): void {
        expect(Assert::lazy()
            ->that(2, 'Two')->eq(2)
            ->verifyNow())->toBeTrue();
    });

    test('lazy assertion exception can return all error messages', function (): void {
        try {
            Assert::lazy()
                ->that(10, 'foo')->string()
                ->that(null, 'bar')->notEmpty()
                ->that('string', 'baz')->isArray()
                ->verifyNow();
        } catch (LazyAssertionException $lazyAssertionException) {
            self::assertEquals(
                [
                    'Expected a string. Got: 10',
                    'Expected a non-empty value. Got: <NULL>',
                    'Expected an array. Got: string',
                ],
                array_map(
                    fn (Exception $lazyAssertionException): string => $lazyAssertionException->getMessage(),
                    $lazyAssertionException->getErrorExceptions(),
                ),
            );
        }
    });

    test('tryAll() reports all assertion failures in chain', function (): void {
        try {
            Assert::lazy()
                ->that(9.9, 'foo')->tryAll()->integer('must be int')->between(10, 20, 'must be between')
                ->verifyNow();
        } catch (LazyAssertionException $lazyAssertionException) {
            expect(array_map(
                fn (Exception $lazyAssertionException): string => $lazyAssertionException->getMessage(),
                $lazyAssertionException->getErrorExceptions(),
            ))->toEqual([
                'must be int',
                'must be between',
            ]);
        }
    });

    test('tryAll() on lazy reports all failures across chains', function (): void {
        $this->expectException(LazyAssertionException::class);
        $this->expectExceptionMessage('The following 4 assertions failed:');
        Assert::lazy()->tryAll()
            ->that(10, 'foo')->float()->greaterThan(100)
            ->that(null, 'foo')->notEmpty()->string()
            ->verifyNow();
    });

    test('custom exception class can be set on lazy assertion', function (): void {
        $this->expectException(CustomLazyAssertionException::class);
        $this->expectExceptionMessage('The following 1 assertions failed:');
        $lazyAssertion = new LazyAssertion();
        $lazyAssertion->setExceptionClass(CustomLazyAssertionException::class);

        var_dump(
            $lazyAssertion
                ->that('foo', 'property')->integer()
                ->verifyNow(),
        );
    });

    test('lazy assertion exception extends InvalidArgumentException', function (): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The following 4 assertions failed:');
        Assert::lazy()->tryAll()
            ->that(10, 'foo')->float()->greaterThan(100)
            ->that(null, 'foo')->notEmpty()->string()
            ->verifyNow();
    });
});

describe('Sad Path', function (): void {
    test('lazy assertion collects all assertion failures', function (): void {
        $this->expectException(LazyAssertionException::class);
        $this->expectExceptionMessage('The following 3 assertions failed:');
        Assert::lazy()
            ->that(10, 'foo')->string()
            ->that(null, 'bar')->notEmpty()
            ->that('string', 'baz')->isArray()
            ->verifyNow();
    });

    test('lazy assertion skips remaining assertions in chain after failure', function (): void {
        $this->expectException(LazyAssertionException::class);
        $this->expectExceptionMessage('The following 1 assertions failed:');
        Assert::lazy()
            ->that(null, 'foo')->notEmpty()->string()
            ->verifyNow();
    });

    test('tryAll() followed by that() still skips after first failure in new chain', function (): void {
        $this->expectException(LazyAssertionException::class);
        $this->expectExceptionMessage('The following 1 assertions failed:');
        Assert::lazy()
            ->that(10, 'foo')->tryAll()->integer()
            ->that(null, 'foo')->notEmpty()->string()
            ->verifyNow();
    });

    test('tryAll() with multiple chains reports all failures', function (): void {
        $this->expectException(LazyAssertionException::class);
        $this->expectExceptionMessage('The following 4 assertions failed:');
        Assert::lazy()
            ->that(10, 'foo')->tryAll()->float()->greaterThan(100)
            ->that(null, 'foo')->tryAll()->notEmpty()->string()
            ->verifyNow();
    });

    test('setExceptionClass rejects invalid exception class', function (): void {
        $this->expectException('LogicException');
        $this->expectExceptionMessage('stdClass is not (a subclass of) Cline\Assert\LazyAssertionException');
        $lazyAssertion = new LazyAssertion();
        $lazyAssertion->setExceptionClass(stdClass::class);
    });

    test('setAssertClass rejects invalid assertion class', function (): void {
        $this->expectException('LogicException');
        $this->expectExceptionMessage('stdClass is not (a subclass of) Cline\Assert\Assert');
        $lazyAssertion = new LazyAssertion();
        $lazyAssertion->setAssertClass(stdClass::class);
    });
});
