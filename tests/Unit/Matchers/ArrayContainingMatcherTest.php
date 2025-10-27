<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Assert\Matchers\AnythingMatcher;
use Cline\Assert\Matchers\ArrayContainingMatcher;
use Cline\Assert\Matchers\StringContainingMatcher;

describe('ArrayContainingMatcher', function (): void {
    test('matches arrays containing all specified key-value pairs', function (): void {
        $matcher = new ArrayContainingMatcher(['id' => 1, 'name' => 'John']);

        expect($matcher->matches([
            'id' => 1,
            'name' => 'John',
            'email' => 'john@example.com',
        ]))->toBeTrue();
    });

    test('matches arrays with exact subset', function (): void {
        $matcher = new ArrayContainingMatcher(['id' => 1]);

        expect($matcher->matches(['id' => 1]))->toBeTrue();
    });

    test('rejects arrays missing required keys', function (): void {
        $matcher = new ArrayContainingMatcher(['id' => 1, 'name' => 'John']);

        expect($matcher->matches(['id' => 1]))->toBeFalse();
    });

    test('rejects arrays with mismatched values', function (): void {
        $matcher = new ArrayContainingMatcher(['id' => 1, 'name' => 'John']);

        expect($matcher->matches([
            'id' => 1,
            'name' => 'Jane',
        ]))->toBeFalse();
    });

    test('rejects non-array values', function (): void {
        $matcher = new ArrayContainingMatcher(['key' => 'value']);

        expect($matcher->matches('not-an-array'))->toBeFalse();
        expect($matcher->matches(123))->toBeFalse();
        expect($matcher->matches(12.34))->toBeFalse();
        expect($matcher->matches(true))->toBeFalse();
        expect($matcher->matches(false))->toBeFalse();
        expect($matcher->matches(null))->toBeFalse();
        expect($matcher->matches(new stdClass()))->toBeFalse();
    });

    test('supports nested asymmetric matchers', function (): void {
        $matcher = new ArrayContainingMatcher([
            'id' => new AnythingMatcher(),
            'name' => new StringContainingMatcher('John'),
        ]);

        expect($matcher->matches([
            'id' => 123,
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]))->toBeTrue();
    });

    test('rejects when nested matcher fails', function (): void {
        $matcher = new ArrayContainingMatcher([
            'name' => new StringContainingMatcher('John'),
        ]);

        expect($matcher->matches([
            'name' => 'Jane Smith',
        ]))->toBeFalse();
    });

    test('toString returns formatted string representation', function (): void {
        $matcher = new ArrayContainingMatcher(['id' => 1, 'name' => 'John']);

        expect($matcher->toString())->toBe('arrayContaining({"id":1,"name":"John"})');
    });

    test('toString handles empty array', function (): void {
        $matcher = new ArrayContainingMatcher([]);

        expect($matcher->toString())->toBe('arrayContaining([])');
    });

    test('toString handles nested arrays', function (): void {
        $matcher = new ArrayContainingMatcher([
            'user' => ['id' => 1],
        ]);

        expect($matcher->toString())->toBe('arrayContaining({"user":{"id":1}})');
    });

    test('toString handles special characters', function (): void {
        $matcher = new ArrayContainingMatcher(['key' => "it's"]);

        expect($matcher->toString())->toBe('arrayContaining({"key":"it\'s"})');
    });
});
