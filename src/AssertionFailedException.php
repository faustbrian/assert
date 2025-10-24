<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Assert;

use Throwable;

/**
 * @author Brian Faust <brian@cline.sh>
 */
interface AssertionFailedException extends Throwable
{
    /**
     * @return null|string
     */
    public function getPropertyPath();

    /**
     * @return mixed
     */
    public function getValue();

    /**
     * @return array<mixed>
     */
    public function getConstraints(): array;
}
