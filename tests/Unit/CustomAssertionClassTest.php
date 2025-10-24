<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Assert\LazyAssertionException;
use Tests\Fixtures\CustomAssert;
use Tests\Fixtures\CustomAssertion;
use Tests\Fixtures\CustomException;
use Tests\Fixtures\CustomLazyAssertionException;

beforeEach(function (): void {
    CustomAssertion::clearCalls();
});

describe('Happy Path', function (): void {
    test('custom assertion class uses custom exception', function (): void {
        $this->expectException(CustomException::class);
        CustomAssertion::integer('foo');
    });

    test('custom assertion class is called for assertion chains', function (): void {
        $this->expectException(CustomException::class);
        $string = 's'.uniqid();
        CustomAssert::that($string)->string();
        expect(CustomAssertion::getCalls())->toBe([['string', $string]]);

        CustomAssert::that($string)->integer();
    });

    test('custom lazy assertion uses custom assertion class', function (): void {
        $string = 's'.uniqid();
        CustomAssert::lazy()
            ->that($string, 'foo')->string()
            ->verifyNow();

        expect(CustomAssertion::getCalls())->toBe([['string', $string]]);
    });

    test('custom lazy assertion exception contains only custom assertion exceptions', function (): void {
        try {
            CustomAssert::lazy()
                ->that('foo', 'foo')->integer()
                ->verifyNow();
        } catch (LazyAssertionException $lazyAssertionException) {
            $this->assertContainsOnlyInstancesOf(CustomException::class, $lazyAssertionException->getErrorExceptions());
        }
    });

    test('custom lazy assertion uses tryAll() per chain', function (): void {
        $this->expectException(CustomLazyAssertionException::class);
        CustomAssert::lazy()
            ->that('foo', 'foo')->tryAll()->integer()->isArray()
            ->that(123, 'bar')->tryAll()->string()->isArray()
            ->verifyNow();
    });

    test('custom lazy assertion uses tryAll() globally', function (): void {
        $this->expectException(CustomLazyAssertionException::class);
        CustomAssert::lazy()
            ->tryAll()
            ->that('foo', 'foo')->integer()->isArray()
            ->that(123, 'bar')->string()->isArray()
            ->verifyNow();
    });
});

describe('Sad Path', function (): void {
    test('custom lazy assertion throws custom exception on validation failure', function (): void {
        $this->expectException(CustomLazyAssertionException::class);
        CustomAssert::lazy()
            ->that('foo', 'foo')->integer()
            ->verifyNow();
    });

    test('custom lazy assertion throws custom exception when first assertion passes', function (): void {
        $this->expectException(CustomLazyAssertionException::class);
        CustomAssert::lazy()
            ->that('foo', 'foo')->string()
            ->that('bar', 'bar')->integer()
            ->verifyNow();
    });
});
