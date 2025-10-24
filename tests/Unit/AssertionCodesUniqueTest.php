<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Assert\Assertion;

describe('Edge Cases', function (): void {
    test('assertion codes are unique across all constants', function (): void {
        $assertReflection = new ReflectionClass(Assertion::class);
        $constants = $assertReflection->getConstants();

        expect(Assertion::eq(count($constants), count(array_unique($constants))))->toBeTrue();
    });
});
