<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\PhpCsFixer\Preset\Standard;
use Cline\PhpCsFixer\ConfigurationFactory;

$config = ConfigurationFactory::createFromPreset(
    new Standard(),
);

/** @var PhpCsFixer\Finder $finder */
$finder = $config->getFinder();
$finder->in([__DIR__.'/src', __DIR__.'/tests'])
    ->notPath('set.php')
    ->notPath('range.php')
    ->notPath('TakeTest.php')
    ->notPath('SetTest.php')
    ->notPath('Assertion.php')
    ->notPath('AssertionChain.php')
    ->notPath('InvalidArgumentException.php')
    ->notPath('LazyAssertionException.php')
    ->notPath('Assertions/ComparisonAssertions.php'); // Uses == and != intentionally for loose comparison

return $config;
