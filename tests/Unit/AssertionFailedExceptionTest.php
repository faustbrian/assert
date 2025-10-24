<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Assert\AssertionFailedException;
use Cline\Assert\LazyAssertionException;
use Tests\Fixtures\CustomException;
use Tests\Fixtures\CustomLazyAssertionException;

describe('Happy Path', function (): void {
    test('exception classes implement Throwable interface', function (string $exceptionClass): void {
        self::assertTrue(
            new ReflectionClass($exceptionClass)
                ->implementsInterface(Throwable::class),
        );
    })->with('provideExceptionClasses');

    dataset('provideExceptionClasses', fn (): array => [
        'AssertionFailedException' => [AssertionFailedException::class],
        'LazyAssertionException' => [LazyAssertionException::class],
        'CustomException' => [CustomException::class],
        'CustomLazyAssertionException' => [CustomLazyAssertionException::class],
    ]);
});
