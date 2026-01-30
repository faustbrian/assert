<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Fixtures;

use Cline\Assert\Assertions\AbstractAssertion;
use Override;

/**
 * @author Brian Faust <brian@cline.sh>
 */
final class PR142_OverrideStringify extends AbstractAssertion
{
    #[Override()]
    protected static function stringify($value): string
    {
        return '***'.parent::stringify($value).'***';
    }
}
