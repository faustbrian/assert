<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Assert\Assertion;
use Cline\Assert\AssertionFailedException;

describe('Happy Path', function (): void {
    test('custom error message via callback for simple assertion', function (): void {
        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('The assertion '.Assertion::class.'::string() failed for 3.1415926535898');
        Assertion::string(
            \M_PI,
            fn (array $parameters): string => sprintf(
                'The assertion %s() failed for %s',
                $parameters['::assertion'],
                $parameters['value'],
            ),
            'M_PI',
        );
    });

    test('custom error message via callback when regex fails at string type check', function (): void {
        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('The assertion '.Assertion::class.'::string() failed for 3.1415926535898');
        Assertion::regex(
            \M_PI,
            '`[A-Z]++`',
            fn (array $parameters): string => sprintf(
                'The assertion %s() failed for %s',
                $parameters['::assertion'],
                $parameters['value'],
            ),
            'M_PI',
        );
    });

    test('custom error message via callback when regex fails at pattern match', function (): void {
        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('The assertion '.Assertion::class.'::regex() failed for 3.1415926535898 against the pattern `^[0-9]++$`');
        Assertion::regex(
            (string) \M_PI,
            '`^[0-9]++$`',
            fn (array $parameters): string => sprintf(
                'The assertion %s() failed for %s against the pattern %s',
                $parameters['::assertion'],
                $parameters['value'],
                $parameters['pattern'],
            ),
            'M_PI',
        );
    });

    test('custom error message via callback for method with default parameter', function (): void {
        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('The assertion '.Assertion::class.'::ipv4() failed for invalid-ip');
        Assertion::ipv4(
            'invalid-ip',
            null,
            fn (array $parameters): string => sprintf(
                'The assertion %s() failed for %s',
                $parameters['::assertion'],
                $parameters['value'],
            ),
        );
    });
});
