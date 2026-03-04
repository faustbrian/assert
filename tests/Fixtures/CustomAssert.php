<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Fixtures;

use Cline\Assert\Assert;
use Override;

/**
 * @author Brian Faust <brian@cline.sh>
 */
final class CustomAssert extends Assert
{
    #[Override()]
    protected static string $assertionClass = CustomAssertion::class;

    #[Override()]
    protected static string $lazyAssertionExceptionClass = CustomLazyAssertionException::class;
}
