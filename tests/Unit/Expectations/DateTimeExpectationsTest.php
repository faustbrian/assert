<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Assert\Exceptions\InvalidArgumentException;

use function Cline\Assert\expect as assertExpect;

describe('Date/Time Expectations', function (): void {
    describe('->toBeBefore()', function (): void {
        test('passes when date is before target', function (): void {
            expect(
                assertExpect('2024-01-01')->toBeBefore('2024-12-31'),
            )->not->toThrow(Throwable::class);
        });

        test('passes with DateTime objects', function (): void {
            $early = new DateTime('2024-01-01');
            $late = new DateTime('2024-12-31');

            expect(
                assertExpect($early)->toBeBefore($late),
            )->not->toThrow(Throwable::class);
        });

        test('fails when date is after target', function (): void {
            expect(
                fn (): mixed => assertExpect('2024-12-31')->toBeBefore('2024-01-01'),
            )->toThrow(InvalidArgumentException::class);
        });

        test('fails when dates are equal', function (): void {
            expect(
                fn (): mixed => assertExpect('2024-06-15')->toBeBefore('2024-06-15'),
            )->toThrow(InvalidArgumentException::class);
        });
    });

    describe('->toBeAfter()', function (): void {
        test('passes when date is after target', function (): void {
            expect(
                assertExpect('2024-12-31')->toBeAfter('2024-01-01'),
            )->not->toThrow(Throwable::class);
        });

        test('passes with DateTime objects', function (): void {
            $late = new DateTime('2024-12-31');
            $early = new DateTime('2024-01-01');

            expect(
                assertExpect($late)->toBeAfter($early),
            )->not->toThrow(Throwable::class);
        });

        test('fails when date is before target', function (): void {
            expect(
                fn (): mixed => assertExpect('2024-01-01')->toBeAfter('2024-12-31'),
            )->toThrow(InvalidArgumentException::class);
        });
    });

    describe('->toBeBetween()', function (): void {
        test('passes when date is between range', function (): void {
            expect(
                assertExpect('2024-06-15')->toBeBetween('2024-01-01', '2024-12-31'),
            )->not->toThrow(Throwable::class);
        });

        test('passes when date equals start boundary', function (): void {
            expect(
                assertExpect('2024-01-01')->toBeBetween('2024-01-01', '2024-12-31'),
            )->not->toThrow(Throwable::class);
        });

        test('passes when date equals end boundary', function (): void {
            expect(
                assertExpect('2024-12-31')->toBeBetween('2024-01-01', '2024-12-31'),
            )->not->toThrow(Throwable::class);
        });

        test('fails when date is before range', function (): void {
            expect(
                fn (): mixed => assertExpect('2023-12-31')->toBeBetween('2024-01-01', '2024-12-31'),
            )->toThrow(InvalidArgumentException::class);
        });

        test('fails when date is after range', function (): void {
            expect(
                fn (): mixed => assertExpect('2025-01-01')->toBeBetween('2024-01-01', '2024-12-31'),
            )->toThrow(InvalidArgumentException::class);
        });
    });

    describe('->toBeToday()', function (): void {
        test('passes when date is today', function (): void {
            expect(
                assertExpect(date('Y-m-d'))->toBeToday(),
            )->not->toThrow(Throwable::class);
        });

        test('passes with DateTime for today', function (): void {
            expect(
                assertExpect(new DateTime('today'))->toBeToday(),
            )->not->toThrow(Throwable::class);
        });

        test('fails when date is yesterday', function (): void {
            expect(
                fn (): mixed => assertExpect(date('Y-m-d', strtotime('yesterday')))->toBeToday(),
            )->toThrow(InvalidArgumentException::class);
        });

        test('fails when date is tomorrow', function (): void {
            expect(
                fn (): mixed => assertExpect(date('Y-m-d', strtotime('tomorrow')))->toBeToday(),
            )->toThrow(InvalidArgumentException::class);
        });
    });

    describe('->toBeYesterday()', function (): void {
        test('passes when date is yesterday', function (): void {
            expect(
                assertExpect(date('Y-m-d', strtotime('yesterday')))->toBeYesterday(),
            )->not->toThrow(Throwable::class);
        });

        test('passes with DateTime for yesterday', function (): void {
            expect(
                assertExpect(new DateTime('yesterday'))->toBeYesterday(),
            )->not->toThrow(Throwable::class);
        });

        test('fails when date is today', function (): void {
            expect(
                fn (): mixed => assertExpect(date('Y-m-d'))->toBeYesterday(),
            )->toThrow(InvalidArgumentException::class);
        });
    });

    describe('->toBeTomorrow()', function (): void {
        test('passes when date is tomorrow', function (): void {
            expect(
                assertExpect(date('Y-m-d', strtotime('tomorrow')))->toBeTomorrow(),
            )->not->toThrow(Throwable::class);
        });

        test('passes with DateTime for tomorrow', function (): void {
            expect(
                assertExpect(new DateTime('tomorrow'))->toBeTomorrow(),
            )->not->toThrow(Throwable::class);
        });

        test('fails when date is today', function (): void {
            expect(
                fn (): mixed => assertExpect(date('Y-m-d'))->toBeTomorrow(),
            )->toThrow(InvalidArgumentException::class);
        });
    });
});
