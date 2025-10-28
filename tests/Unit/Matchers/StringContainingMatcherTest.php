<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Assert\Matchers\StringContainingMatcher;

describe('StringContainingMatcher', function (): void {
    test('matches strings containing the substring', function (): void {
        $matcher = new StringContainingMatcher('foo');

        expect($matcher->matches('foo'))->toBeTrue();
        expect($matcher->matches('foobar'))->toBeTrue();
        expect($matcher->matches('barfoo'))->toBeTrue();
        expect($matcher->matches('barfoobaz'))->toBeTrue();
    });

    test('rejects strings not containing the substring', function (): void {
        $matcher = new StringContainingMatcher('foo');

        expect($matcher->matches('bar'))->toBeFalse();
        expect($matcher->matches('baz'))->toBeFalse();
        expect($matcher->matches(''))->toBeFalse();
    });

    test('rejects non-string values', function (): void {
        $matcher = new StringContainingMatcher('foo');

        expect($matcher->matches(123))->toBeFalse();
        expect($matcher->matches(12.34))->toBeFalse();
        expect($matcher->matches(true))->toBeFalse();
        expect($matcher->matches(false))->toBeFalse();
        expect($matcher->matches(null))->toBeFalse();
        expect($matcher->matches([]))->toBeFalse();
        expect($matcher->matches(['foo']))->toBeFalse();
        expect($matcher->matches(
            new stdClass(),
        ))->toBeFalse();
    });

    test('toString returns formatted string representation', function (): void {
        $matcher = new StringContainingMatcher('example');

        expect($matcher->toString())->toBe("stringContaining('example')");
    });

    test('toString handles special characters', function (): void {
        $matcher = new StringContainingMatcher("it's");

        expect($matcher->toString())->toBe("stringContaining('it's')");
    });

    test('toString handles empty string', function (): void {
        $matcher = new StringContainingMatcher('');

        expect($matcher->toString())->toBe("stringContaining('')");
    });
});
