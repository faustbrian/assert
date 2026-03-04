<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\CodingStandard\EasyCodingStandard\Factory;
use PhpCsFixer\Fixer\ClassNotation\VisibilityRequiredFixer;
use PhpCsFixer\Fixer\Whitespace\TypesSpacesFixer;

return Factory::create(
    paths: [__DIR__.'/src', __DIR__.'/tests'],
    skip: [
        TypesSpacesFixer::class, // PHP 8.5 compatibility bug corrupts callable → callablepublic
        VisibilityRequiredFixer::class, // PHP 8.5 compatibility bug
    ],
);
