<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Assert\Exceptions;

/**
 * Concrete exception thrown when assertions fail.
 *
 * This is the default instantiable exception class used by the assertion library.
 * Extends the abstract InvalidArgumentException to provide a final, concrete implementation.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class AssertionException extends InvalidArgumentException {}
