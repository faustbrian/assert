<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Assert;

use function count;
use function sprintf;

/**
 * @author Brian Faust <brian@cline.sh>
 */
class LazyAssertionException extends InvalidArgumentException
{
    public function __construct(
        $message,
        /** @var array<InvalidArgumentException> */
        private readonly array $errors,
    ) {
        parent::__construct($message, 0, null, null);
    }

    /**
     * @param array<InvalidArgumentException> $errors
     */
    public static function fromErrors(array $errors): static
    {
        $message = sprintf('The following %d assertions failed:', count($errors))."\n";

        $i = 1;

        foreach ($errors as $error) {
            $message .= sprintf("%d) %s: %s\n", $i++, $error->getPropertyPath(), $error->getMessage());
        }

        /** @phpstan-ignore new.static */
        return new static($message, $errors);
    }

    /**
     * @return array<InvalidArgumentException>
     */
    public function getErrorExceptions(): array
    {
        return $this->errors;
    }
}
