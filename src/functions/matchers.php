<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Assert;

use Cline\Assert\Matchers\AnyMatcher;
use Cline\Assert\Matchers\AnythingMatcher;
use Cline\Assert\Matchers\ArrayContainingMatcher;
use Cline\Assert\Matchers\StringContainingMatcher;

/**
 * Create an "any type" matcher for use in asymmetric matching.
 *
 * @param string $type Type name ('string', 'int', etc.) or class name
 */
function any(string $type): AnyMatcher
{
    return new AnyMatcher($type);
}

/**
 * Create an "anything" matcher that matches any non-null value.
 */
function anything(): AnythingMatcher
{
    return new AnythingMatcher();
}

/**
 * Create a string containing matcher.
 */
function stringContaining(string $substring): StringContainingMatcher
{
    return new StringContainingMatcher($substring);
}

/**
 * Create an array containing matcher.
 */
function arrayContaining(array $subset): ArrayContainingMatcher
{
    return new ArrayContainingMatcher($subset);
}
