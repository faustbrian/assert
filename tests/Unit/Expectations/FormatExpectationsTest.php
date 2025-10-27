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
            assertExpect('test@example.com')->toBeEmail();
            assertExpect('user+tag@domain.co.uk')->toBeEmail();
            assertExpect('name@subdomain.domain.com')->toBeEmail();
            expect(true)->toBeTrue();
        });

        test('toBeEmail() rejects invalid email addresses', function (): void {
            assertExpect(fn (): Expectation => assertExpect('not-an-email')->toBeEmail())
                ->toThrow(AssertionFailedException::class);
            assertExpect(fn (): Expectation => assertExpect('missing@domain')->toBeEmail())
                ->toThrow(AssertionFailedException::class);
            expect(true)->toBeTrue();
        });

        test('not->toBeEmail() accepts non-email strings', function (): void {
            assertExpect('not-an-email')->not->toBeEmail();
            expect(true)->toBeTrue();
        });
    });

    describe('URL Format', function (): void {
        test('toBeUrl() accepts valid URLs', function (): void {
            assertExpect('https://example.com')->toBeUrl();
            assertExpect('http://localhost:8080')->toBeUrl();
            assertExpect('https://sub.domain.com/path?query=value')->toBeUrl();
            expect(true)->toBeTrue();
        });

        test('toBeUrl() rejects invalid URLs', function (): void {
            assertExpect(fn (): Expectation => assertExpect('not a url')->toBeUrl())
                ->toThrow(AssertionFailedException::class);
            assertExpect(fn (): Expectation => assertExpect('example.com')->toBeUrl())
                ->toThrow(AssertionFailedException::class);
            expect(true)->toBeTrue();
        });

        test('not->toBeUrl() accepts non-URL strings', function (): void {
            assertExpect('not a url')->not->toBeUrl();
            expect(true)->toBeTrue();
        });
    });

    describe('UUID Format', function (): void {
        test('toBeUuid() accepts valid UUIDs', function (): void {
            assertExpect('550e8400-e29b-41d4-a716-446655440000')->toBeUuid();
            assertExpect('6ba7b810-9dad-11d1-80b4-00c04fd430c8')->toBeUuid();
            expect(true)->toBeTrue();
        });

        test('toBeUuid() rejects invalid UUIDs', function (): void {
            assertExpect(fn (): Expectation => assertExpect('not-a-uuid')->toBeUuid())
                ->toThrow(AssertionFailedException::class);
            assertExpect(fn (): Expectation => assertExpect('550e8400-e29b-41d4')->toBeUuid())
                ->toThrow(AssertionFailedException::class);
            expect(true)->toBeTrue();
        });

        test('not->toBeUuid() accepts non-UUID strings', function (): void {
            assertExpect('not-a-uuid')->not->toBeUuid();
            expect(true)->toBeTrue();
        });
    });

    describe('JSON Format', function (): void {
        test('toBeJson() accepts valid JSON strings', function (): void {
            assertExpect('{"name":"John"}')->toBeJson();
            assertExpect('[1,2,3]')->toBeJson();
            assertExpect('null')->toBeJson();
            assertExpect('"string"')->toBeJson();
            expect(true)->toBeTrue();
        });

        test('toBeJson() rejects invalid JSON', function (): void {
            assertExpect(fn (): Expectation => assertExpect('not json')->toBeJson())
                ->toThrow(AssertionFailedException::class);
            assertExpect(fn (): Expectation => assertExpect('{invalid}')->toBeJson())
                ->toThrow(AssertionFailedException::class);
            expect(true)->toBeTrue();
        });

        test('not->toBeJson() accepts non-JSON strings', function (): void {
            assertExpect('not json')->not->toBeJson();
            expect(true)->toBeTrue();
        });
    });

    describe('Chaining Format Checks', function (): void {
        test('can chain format with type checks', function (): void {
            assertExpect('test@example.com')
                ->toBeString()
                ->toBeEmail()
                ->toContain('@');
        });

        test('can use format checks with collections', function (): void {
            assertExpect([
                'test@example.com',
                'user@domain.com',
            ])->each->toBeEmail();
        });

        test('can mix format and conditional checks', function (): void {
            $value = 'test@example.com';

            assertExpect($value)
                ->toBeString()
                ->when(str_contains($value, '@'), fn ($exp) => $exp->toBeEmail());
        });
    });
});
