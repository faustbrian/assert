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
use Override;

/**
 * @author Brian Faust <brian@cline.sh>
 */
final class PR142_OverrideStringify extends AbstractAssertion
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

    #[Override()]
    protected static function stringify($value): string
    {
        return '***'.parent::stringify($value).'***';
    }
}
