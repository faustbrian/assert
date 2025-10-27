<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Assert\Matchers\AnythingMatcher;

describe('AnythingMatcher', function (): void {
    test('matches any non-null value', function (): void {
        $matcher = new AnythingMatcher();

        expect($matcher->matches(123))->toBeTrue();
        expect($matcher->matches(12.34))->toBeTrue();
        expect($matcher->matches('string'))->toBeTrue();
        expect($matcher->matches(''))->toBeTrue();
        expect($matcher->matches(true))->toBeTrue();
        expect($matcher->matches(false))->toBeTrue();
        expect($matcher->matches([]))->toBeTrue();
        expect($matcher->matches(['foo']))->toBeTrue();
        expect($matcher->matches(new stdClass()))->toBeTrue();
    });

    test('rejects null values', function (): void {
        $matcher = new AnythingMatcher();

        expect($matcher->matches(null))->toBeFalse();
    });

    test('toString returns formatted string representation', function (): void {
        $matcher = new AnythingMatcher();

        expect($matcher->toString())->toBe('anything()');
    });
});
