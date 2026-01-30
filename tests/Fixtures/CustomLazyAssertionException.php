<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Fixtures;

use Cline\Assert\Exceptions\InvalidArgumentException;
use Cline\Assert\Exceptions\LazyAssertionExceptionInterface;

use function count;
use function sprintf;

/**
 * @author Brian Faust <brian@cline.sh>
 */
final class CustomLazyAssertionException extends InvalidArgumentException implements LazyAssertionExceptionInterface
{
    /**
     * @param array<InvalidArgumentException> $errors
     */
    public function __construct(
        string $message,
        private readonly array $errors,
    ) {
        parent::__construct($message, 0);
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

        return new self($message, $errors);
    }

    /**
     * @return array<InvalidArgumentException>
     */
    public function getErrorExceptions(): array
    {
        return $this->errors;
    }
}
