<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Assert\Assertion;
use Cline\Assert\AssertionFailedException;
use Tests\Fixtures\PR142_OverrideStringify;

describe('Regression', function (): void {
    dataset('dataInvalidString', fn (): array => [
        'Float value' => [1.23, 'Expected a string. Got: ***1.23***'],
        'Boolean false' => [false, 'Expected a string. Got: ***<FALSE>***'],
        'Object value' => [new ArrayObject(), 'Expected a string. Got: ***ArrayObject***'],
        'Null value' => [null, 'Expected a string. Got: ***<NULL>***'],
        'Integer value' => [10, 'Expected a string. Got: ***10***'],
        'Boolean true' => [true, 'Expected a string. Got: ***<TRUE>***'],
    ]);

    test('custom stringify method formats error messages correctly (PR #142)', function ($invalidString, $exceptionMessage): void {
        try {
            PR142_OverrideStringify::string($invalidString);
        } catch (AssertionFailedException $assertionFailedException) {
            expect($assertionFailedException->getCode())->toBe(Assertion::INVALID_STRING);
            expect($assertionFailedException->getMessage())->toBe($exceptionMessage);
        }
    })->with('dataInvalidString');
});
