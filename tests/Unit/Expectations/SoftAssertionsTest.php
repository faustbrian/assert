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

describe('Soft Assertions', function (): void {
    beforeEach(function (): void {
        Expectation::clearSoftErrors();
    });

    test('soft assertions collect errors without throwing immediately', function (): void {
        $user = (object) ['name' => null, 'email' => null];

        // These should not throw immediately
        assertExpect($user)->soft->toHaveProperty('name');
        assertExpect($user)->soft->toHaveProperty('email');
        assertExpect($user)->soft->toHaveProperty('phone');

        // Now assert all soft assertions
        expect(Expectation::assertSoft(...))
            ->toThrow(InvalidArgumentException::class);
    });

    test('soft assertions pass when all succeed', function (): void {
        $user = (object) ['name' => 'John', 'email' => 'john@example.com'];

        assertExpect($user)->soft->toHaveProperty('name');
        assertExpect($user)->soft->toHaveProperty('email');

        // Should not throw
        expect(Expectation::assertSoft(...))->not->toThrow(Throwable::class);
    });

    test('soft assertions collect multiple failures', function (): void {
        assertExpect(10)->soft->toBeGreaterThan(100);
        assertExpect('hello')->soft->toBeInt();
        assertExpect([])->soft->toHaveCount(5);

        expect(Expectation::assertSoft(...))
            ->toThrow(InvalidArgumentException::class);
    });

    test('soft assertions can be cleared', function (): void {
        assertExpect(10)->soft->toBeGreaterThan(100);

        Expectation::clearSoftErrors();

        // Should not throw after clearing
        expect(Expectation::assertSoft(...))->not->toThrow(Throwable::class);
    });

    test('soft assertions work with type checks', function (): void {
        assertExpect(123)->soft->toBeString();
        assertExpect('hello')->soft->toBeInt();
        assertExpect([])->soft->toBeObject();

        expect(Expectation::assertSoft(...))
            ->toThrow(InvalidArgumentException::class);
    });

    test('soft assertions work with numeric comparisons', function (): void {
        assertExpect(5)->soft->toBeGreaterThan(10);
        assertExpect(100)->soft->toBeLessThan(50);

        expect(Expectation::assertSoft(...))
            ->toThrow(InvalidArgumentException::class);
    });

    test('soft assertions work with string checks', function (): void {
        assertExpect('hello')->soft->toStartWith('goodbye');
        assertExpect('world')->soft->toEndWith('hello');

        expect(Expectation::assertSoft(...))
            ->toThrow(InvalidArgumentException::class);
    });

    test('soft assertions can mix with regular assertions', function (): void {
        assertExpect(10)->soft->toBeGreaterThan(100);  // Collected
        assertExpect(20)->toBeInt();  // Throws immediately if fails

        expect(Expectation::assertSoft(...))
            ->toThrow(InvalidArgumentException::class);
    });

    test('error message includes all failures', function (): void {
        assertExpect(10)->soft->toBeGreaterThan(100);
        assertExpect('hello')->soft->toBeInt();

        try {
            Expectation::assertSoft();

            throw new Exception('Should have thrown');
        } catch (InvalidArgumentException $invalidArgumentException) {
            expect($invalidArgumentException->getMessage())->toContain('Soft assertions failed');
            expect($invalidArgumentException->getMessage())->toContain('greater than 100');
            expect($invalidArgumentException->getMessage())->toContain('Expected an integer');
        }
    });
});
