<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Fixtures;

use Cline\Assert\AbstractAssertion;
use Cline\Assert\Assertions\ArrayAssertions;
use Cline\Assert\Assertions\BooleanAssertions;
use Cline\Assert\Assertions\ComparisonAssertions;
use Cline\Assert\Assertions\CustomAssertions;
use Cline\Assert\Assertions\EnvironmentAssertions;
use Cline\Assert\Assertions\FileSystemAssertions;
use Cline\Assert\Assertions\NullEmptyAssertions;
use Cline\Assert\Assertions\NumericAssertions;
use Cline\Assert\Assertions\ObjectAssertions;
use Cline\Assert\Assertions\StringAssertions;
use Cline\Assert\Assertions\TypeAssertions;
use Cline\Assert\Assertions\ValidationAssertions;

/**
 * @author Brian Faust <brian@cline.sh>
 */
final class CustomAssertion extends AbstractAssertion
{
    use TypeAssertions;
    use ComparisonAssertions;
    use NumericAssertions;
    use StringAssertions;
    use ArrayAssertions;
    use NullEmptyAssertions;
    use ObjectAssertions;
    use FileSystemAssertions;
    use ValidationAssertions;
    use EnvironmentAssertions;
    use BooleanAssertions;
    use CustomAssertions;

    protected static $exceptionClass = CustomException::class;

    private static array $calls = [];

    public static function clearCalls(): void
    {
        self::$calls = [];
    }

    public static function getCalls(): array
    {
        return self::$calls;
    }

    public static function string($value, $message = null, ?string $propertyPath = null): bool
    {
        self::$calls[] = ['string', $value];

        // Can't call parent::string() because AbstractAssertion doesn't have it (it's in the trait)
        // Can't call trait method directly. Instead, we need to delegate to the actual implementation
        // by calling the method from the trait via self, but we're overriding it here, so we need
        // to use the trait's implementation directly
        if (!is_string($value)) {
            $message = sprintf(
                self::generateMessage($message ?: 'Value "%s" expected to be string, type %s given.'),
                self::stringify($value),
                gettype($value),
            );

            throw self::createException($value, $message, 18, $propertyPath);
        }

        return true;
    }
}
