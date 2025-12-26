<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\CodingStandard\Rector\Factory;
use Rector\TypeDeclaration\Rector\ArrowFunction\AddArrowFunctionReturnTypeRector;

return Factory::create(
    paths: [__DIR__.'/src', __DIR__.'/tests'],
    skip: [
        // Pest closures can return Expectation objects that Rector incorrectly types
        AddArrowFunctionReturnTypeRector::class => [
            __DIR__.'/tests/Unit/Expectations/UtilityModifiersTest.php',
        ],
    ],
);
