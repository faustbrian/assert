<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Fixtures;

use Cline\Assert\Assert;

/**
 * @author Brian Faust <brian@cline.sh>
 */
final class CustomAssert extends Assert
{
    protected static $assertionClass = CustomAssertion::class;

    protected static $lazyAssertionExceptionClass = CustomLazyAssertionException::class;
}
