<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Assert\Exceptions\AssertionFailedException;
use Cline\Assert\Expectations\Expectation;

use function Cline\Assert\expect as assertExpect;

describe('Format Expectations', function (): void {
    describe('Email Format', function (): void {
        test('toBeEmail() accepts valid email addresses', function (): void {
            expect(fn() => assertExpect('test@example.com')->toBeEmail())->not->toThrow();
            expect(fn() => assertExpect('user+tag@domain.co.uk')->toBeEmail())->not->toThrow();
            expect(fn() => assertExpect('name@subdomain.domain.com')->toBeEmail())->not->toThrow();
        });

        test('toBeEmail() rejects invalid email addresses', function (): void {
            assertExpect(fn (): Expectation => assertExpect('not-an-email')->toBeEmail())
                ->toThrow(AssertionFailedException::class);
            assertExpect(fn (): Expectation => assertExpect('missing@domain')->toBeEmail())
                ->toThrow(AssertionFailedException::class);
        });

        test('not->toBeEmail() accepts non-email strings', function (): void {
            expect(fn() => assertExpect('not-an-email')->not->toBeEmail())->not->toThrow();
        });
    });

    describe('URL Format', function (): void {
        test('toBeUrl() accepts valid URLs', function (): void {
            expect(fn() => assertExpect('https://example.com')->toBeUrl())->not->toThrow();
            expect(fn() => assertExpect('http://localhost:8080')->toBeUrl())->not->toThrow();
            expect(fn() => assertExpect('https://sub.domain.com/path?query=value')->toBeUrl())->not->toThrow();
        });

        test('toBeUrl() rejects invalid URLs', function (): void {
            assertExpect(fn (): Expectation => assertExpect('not a url')->toBeUrl())
                ->toThrow(AssertionFailedException::class);
            assertExpect(fn (): Expectation => assertExpect('example.com')->toBeUrl())
                ->toThrow(AssertionFailedException::class);
        });

        test('not->toBeUrl() accepts non-URL strings', function (): void {
            expect(fn() => assertExpect('not a url')->not->toBeUrl())->not->toThrow();
        });
    });

    describe('UUID Format', function (): void {
        test('toBeUuid() accepts valid UUIDs', function (): void {
            expect(fn() => assertExpect('550e8400-e29b-41d4-a716-446655440000')->toBeUuid())->not->toThrow();
            expect(fn() => assertExpect('6ba7b810-9dad-11d1-80b4-00c04fd430c8')->toBeUuid())->not->toThrow();
        });

        test('toBeUuid() rejects invalid UUIDs', function (): void {
            assertExpect(fn (): Expectation => assertExpect('not-a-uuid')->toBeUuid())
                ->toThrow(AssertionFailedException::class);
            assertExpect(fn (): Expectation => assertExpect('550e8400-e29b-41d4')->toBeUuid())
                ->toThrow(AssertionFailedException::class);
        });

        test('not->toBeUuid() accepts non-UUID strings', function (): void {
            expect(fn() => assertExpect('not-a-uuid')->not->toBeUuid())->not->toThrow();
        });
    });

    describe('JSON Format', function (): void {
        test('toBeJson() accepts valid JSON strings', function (): void {
            expect(fn() => assertExpect('{"name":"John"}')->toBeJson())->not->toThrow();
            expect(fn() => assertExpect('[1,2,3]')->toBeJson())->not->toThrow();
            expect(fn() => assertExpect('null')->toBeJson())->not->toThrow();
            expect(fn() => assertExpect('"string"')->toBeJson())->not->toThrow();
        });

        test('toBeJson() rejects invalid JSON', function (): void {
            assertExpect(fn (): Expectation => assertExpect('not json')->toBeJson())
                ->toThrow(AssertionFailedException::class);
            assertExpect(fn (): Expectation => assertExpect('{invalid}')->toBeJson())
                ->toThrow(AssertionFailedException::class);
        });

        test('not->toBeJson() accepts non-JSON strings', function (): void {
            expect(fn() => assertExpect('not json')->not->toBeJson())->not->toThrow();
        });
    });

    describe('Chaining Format Checks', function (): void {
        test('can chain format with type checks', function (): void {
            expect(fn() => assertExpect('test@example.com')
                ->toBeString()
                ->toBeEmail()
                ->toContain('@'))->not->toThrow();
        });

        test('can use format checks with collections', function (): void {
            expect(fn() => assertExpect([
                'test@example.com',
                'user@domain.com',
            ])->each->toBeEmail())->not->toThrow();
        });

        test('can mix format and conditional checks', function (): void {
            $value = 'test@example.com';

            expect(fn() => assertExpect($value)
                ->toBeString()
                ->when(str_contains($value, '@'), fn ($exp) => $exp->toBeEmail()))->not->toThrow();
        });
    });
});
