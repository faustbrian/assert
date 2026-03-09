## Table of Contents

1. [Overview](#doc-docs-readme) (`docs/README.md`)
2. [Array Assertions](#doc-docs-array-assertions) (`docs/array-assertions.md`)
3. [Assertion Chains](#doc-docs-assertion-chains) (`docs/assertion-chains.md`)
4. [Boolean Assertions](#doc-docs-boolean-assertions) (`docs/boolean-assertions.md`)
5. [Comparison Assertions](#doc-docs-comparison-assertions) (`docs/comparison-assertions.md`)
6. [Custom Assertions](#doc-docs-custom-assertions) (`docs/custom-assertions.md`)
7. [Expect Api](#doc-docs-expect-api) (`docs/expect-api.md`)
8. [Filesystem Assertions](#doc-docs-filesystem-assertions) (`docs/filesystem-assertions.md`)
9. [Lazy Assertions](#doc-docs-lazy-assertions) (`docs/lazy-assertions.md`)
10. [Null Empty Assertions](#doc-docs-null-empty-assertions) (`docs/null-empty-assertions.md`)
11. [Numeric Assertions](#doc-docs-numeric-assertions) (`docs/numeric-assertions.md`)
12. [Object Assertions](#doc-docs-object-assertions) (`docs/object-assertions.md`)
13. [String Assertions](#doc-docs-string-assertions) (`docs/string-assertions.md`)
14. [Type Assertions](#doc-docs-type-assertions) (`docs/type-assertions.md`)
15. [Validation Assertions](#doc-docs-validation-assertions) (`docs/validation-assertions.md`)
<a id="doc-docs-readme"></a>

Assert is a comprehensive assertion library for PHP 8.4+ that enables robust validation and preconditions throughout your code.

## Installation

```bash
composer require cline/assert
```

## Basic Usage

The library provides four main ways to perform assertions:

### 1. Static Method Calls

Use `Assertion` class directly for immediate validation:

```php
use Cline\Assert\Assertions\Assertion;

Assertion::string($value);
Assertion::notEmpty($value);
Assertion::minLength($value, 3);
```

### 2. Assertion Chains

Use `Assert::that()` for fluent, chainable assertions:

```php
use Cline\Assert\Assert;

Assert::that($email)
    ->notEmpty()
    ->email();

Assert::that($password)
    ->string()
    ->minLength(8)
    ->maxLength(100);
```

### 3. Expect API (Jest/Pest-style)

Use `expect()` for test-friendly, chainable expectations:

```php
use function Cline\Assert\expect;

expect($value)->toBeString();
expect($count)->toBeInt();
expect($result)->toBe(42);
expect($items)->toHaveCount(3);
expect($value)->not->toBeNull();
```

### 4. Lazy Assertions

Collect multiple validation errors before throwing:

```php
use Cline\Assert\Assert;

Assert::lazy()
    ->that($email, 'email')->email()
    ->that($age, 'age')->integer()->min(18)
    ->that($name, 'name')->notEmpty()->minLength(2)
    ->verifyNow(); // Throws exception with all errors
```

## Exception Handling

All assertions throw `AssertionFailedException` when validation fails:

```php
use Cline\Assert\Assertions\Assertion;
use Cline\Assert\Exceptions\AssertionFailedException;

try {
    Assertion::integer($value);
} catch (AssertionFailedException $e) {
    echo $e->getMessage();
    echo $e->getPropertyPath();
    echo $e->getValue();
    echo $e->getConstraints();
}
```

## Custom Messages

All assertions accept optional custom error messages:

```php
Assertion::notEmpty($username, 'Username is required');
Assertion::email($email, 'Please provide a valid email address');

Assert::that($age)
    ->integer('Age must be a number')
    ->min(18, 'You must be at least 18 years old');
```

### Message Placeholders

Custom messages support sprintf-style placeholders:

```php
Assertion::minLength($username, 3, 'Username must be at least %2$s characters. Got: %s');
// => "Username must be at least 3 characters. Got: ab"

Assertion::range($age, 18, 65, 'Age must be between %2$s and %3$s. Got: %s');
// => "Age must be between 18 and 65. Got: 17"
```

## Property Paths

Use property paths to identify which field failed validation:

```php
Assertion::string($user->email, null, 'user.email');

Assert::that($user->email, null, 'user.email')
    ->notEmpty()
    ->email();
```

## Next Steps

- [Expect API](#doc-docs-expect-api) - Jest/Pest-style fluent expectations
- [Assertion Chains](#doc-docs-assertion-chains) - Fluent API usage
- [Lazy Assertions](#doc-docs-lazy-assertions) - Batch validation patterns
- [String Assertions](#doc-docs-string-assertions) - String validation

<a id="doc-docs-array-assertions"></a>

Array assertions validate arrays, collections, and array-accessible objects.

## Available Assertions

### isCountable()

Assert that a value is countable.

```php
use Cline\Assert\Assertions\Assertion;

Assertion::isCountable($array);
Assertion::isCountable($collection, 'Value must be countable');
```

### count()

Assert exact number of elements.

```php
Assertion::count($array, 5);
Assertion::count($items, 3, 'Must have exactly 3 items');
```

### minCount()

Assert minimum number of elements.

```php
Assertion::minCount($array, 1);
Assertion::minCount($options, 2, 'Must provide at least 2 options');
```

### maxCount()

Assert maximum number of elements.

```php
Assertion::maxCount($array, 10);
Assertion::maxCount($tags, 5, 'Maximum 5 tags allowed');
```

### keyExists()

Assert that a key exists.

```php
Assertion::keyExists($array, 'name');
Assertion::keyExists($config, 'database', 'Database configuration is required');
```

### keyNotExists()

Assert that a key does NOT exist.

```php
Assertion::keyNotExists($array, 'legacy_field');
Assertion::keyNotExists($data, 'password', 'Password should not be included');
```

### notEmptyKey()

Assert that a key exists and its value is not empty.

```php
Assertion::notEmptyKey($data, 'name');
Assertion::notEmptyKey($form, 'email', 'Email field cannot be empty');
```

### uniqueValues()

Assert all values are unique.

```php
Assertion::uniqueValues($array);
Assertion::uniqueValues($ids, 'All IDs must be unique');
```

### inArray()

Assert that a value exists in an array.

```php
Assertion::inArray($status, ['draft', 'published', 'archived']);
Assertion::inArray($role, $validRoles, 'Invalid role selected');
```

### notInArray()

Assert that a value does NOT exist in an array.

```php
Assertion::notInArray($username, $bannedNames);
Assertion::notInArray($value, $blacklist, 'This value is not allowed');
```

### choice()

Alias for `inArray()`.

```php
Assertion::choice($color, ['red', 'green', 'blue']);
```

### eqArraySubset()

Assert that an array contains a subset.

```php
$expected = ['name' => 'John', 'active' => true];
Assertion::eqArraySubset($user, $expected);
```

## Chaining Array Assertions

```php
use Cline\Assert\Assert;

Assert::that($array)
    ->isArray()
    ->notEmpty()
    ->minCount(1)
    ->maxCount(10);

Assert::that($status)
    ->string()
    ->inArray(['pending', 'approved', 'rejected']);
```

## Common Patterns

### Required Array Keys

```php
Assert::that($config)
    ->isArray()
    ->keyExists('host')
    ->keyExists('port')
    ->keyExists('database');
```

### Form Validation

```php
Assert::lazy()
    ->that($form, 'form')->isArray()
    ->that($form, 'form')->keyExists('name')
    ->that($form, 'form')->notEmptyKey('name')
    ->verifyNow();
```

### Collection Size Validation

```php
Assert::that($items)
    ->isArray()
    ->minCount(1, 'At least one item is required')
    ->maxCount(100, 'Maximum 100 items allowed');
```

## Validating All Elements

Use the `all()` modifier to validate every element:

```php
Assert::thatAll($tags)->string();
Assert::thatAll($quantities)
    ->integer()
    ->greaterThan(0);
Assert::thatAll($emailList)->email();
```

## Nested Arrays

```php
Assert::that($data)
    ->isArray()
    ->keyExists('user')
    ->keyExists('settings');

Assert::that($data['user'])
    ->isArray()
    ->notEmptyKey('id')
    ->notEmptyKey('name');
```

## Next Steps

- [Type Assertions](#doc-docs-type-assertions) - Type checking
- [Null and Empty Assertions](#doc-docs-null-empty-assertions) - Empty array checks
- [Lazy Assertions](#doc-docs-lazy-assertions) - Validate multiple conditions

<a id="doc-docs-assertion-chains"></a>

Assertion chains provide a fluent interface for validating values with multiple assertions.

## Basic Chain Usage

Instead of multiple static calls:

```php
Assertion::string($email);
Assertion::notEmpty($email);
Assertion::email($email);
```

Use a fluent chain:

```php
Assert::that($email)
    ->string()
    ->notEmpty()
    ->email();
```

## Creating Chains

### Basic Chain

```php
use Cline\Assert\Assert;

Assert::that($value)
    ->assertion1()
    ->assertion2()
    ->assertion3();
```

### With Custom Message

```php
Assert::that($age, 'Age must be valid')
    ->integer()
    ->greaterOrEqualThan(18);
```

### With Property Path

```php
Assert::that($user->email, null, 'user.email')
    ->notEmpty()
    ->email();
```

## Available Modifiers

### nullOr() - Allow Null Values

Skip all subsequent assertions if the value is null:

```php
Assert::thatNullOr($middleName)
    ->string()
    ->minLength(2)
    ->maxLength(50);

// Equivalent to:
if ($middleName !== null) {
    Assert::that($middleName)
        ->string()
        ->minLength(2);
}
```

### all() - Validate Array Elements

Validate every element in an array:

```php
Assert::thatAll($emailList)->email();

Assert::thatAll($userIds)
    ->integer()
    ->greaterThan(0);
```

## Common Patterns

### String Validation

```php
Assert::that($username)
    ->string()
    ->notEmpty()
    ->minLength(3)
    ->maxLength(20)
    ->regex('/^[a-z0-9_]+$/');
```

### Number Validation

```php
Assert::that($price)
    ->float()
    ->greaterThan(0, 'Price must be positive')
    ->lessThan(1000000, 'Price too high');
```

### Email Validation

```php
Assert::that($email)
    ->string()
    ->notEmpty('Email is required')
    ->email('Invalid email format')
    ->maxLength(255, 'Email too long');
```

### Password Validation

```php
Assert::that($password)
    ->string()
    ->minLength(8, 'Password must be at least 8 characters')
    ->regex('/[A-Z]/', 'Must contain uppercase letter')
    ->regex('/[a-z]/', 'Must contain lowercase letter')
    ->regex('/[0-9]/', 'Must contain number');
```

### Object Validation

```php
Assert::that($user)
    ->notNull('User not found')
    ->isObject()
    ->isInstanceOf(User::class)
    ->propertyExists('email');
```

### Array Validation

```php
Assert::that($items)
    ->isArray()
    ->notEmpty('Items cannot be empty')
    ->minCount(1)
    ->maxCount(100);
```

## Using nullOr()

### Optional Fields

```php
// Required field
Assert::that($email)->notEmpty()->email();

// Optional field (can be null)
Assert::thatNullOr($phoneNumber)
    ->string()
    ->e164('Invalid phone format');
```

### Configuration Values

```php
Assert::thatNullOr($config['timeout'])
    ->integer()
    ->greaterThan(0);
```

## Using all()

### Validate Array of Values

```php
Assert::thatAll($recipientEmails)
    ->email('All recipients must have valid emails');

Assert::thatAll($quantities)
    ->integer()
    ->greaterThan(0);
```

### With Type Checks

```php
Assert::thatAll($tags)
    ->string()
    ->notEmpty()
    ->minLength(2)
    ->maxLength(30);
```

## Error Messages

### Default Messages

```php
Assert::that($email)
    ->notEmpty()  // "Value is required"
    ->email();    // "Value is not a valid email"
```

### Custom Messages per Assertion

```php
Assert::that($password)
    ->notEmpty('Password is required')
    ->minLength(8, 'Password must be at least 8 characters');
```

### Default Message for Chain

```php
Assert::that($username, 'Username is invalid')
    ->notEmpty()
    ->minLength(3);
```

## Best Practices

### Order Assertions Logically

```php
// Good order: type → null/empty → format → constraints
Assert::that($email)
    ->string()           // 1. Type check first
    ->notEmpty()         // 2. Then null/empty
    ->email()            // 3. Then format
    ->maxLength(255);    // 4. Finally constraints
```

### Fail Fast with Type Checks

```php
Assert::that($count)
    ->integer()          // Ensures numeric operations work
    ->greaterThan(0);
```

### Group Related Validations

```php
Assert::that($name)
    ->string()
    ->notEmpty()
    ->minLength(2)
    ->maxLength(100)
    ->regex('/^[\p{L}\s]+$/u', 'Name can only contain letters');
```

## Common Mistakes

### Wrong Order

```php
// Bad - length check before type check
Assert::that($name)
    ->minLength(3)
    ->string();

// Good - type check first
Assert::that($name)
    ->string()
    ->minLength(3);
```

## Next Steps

- [Lazy Assertions](#doc-docs-lazy-assertions) - Validate multiple fields
- [Custom Assertions](#doc-docs-custom-assertions) - Add custom rules
- [Getting Started](#doc-docs-readme) - Core concepts

<a id="doc-docs-boolean-assertions"></a>

Boolean assertions validate boolean values and truthiness.

## Available Assertions

### true()

Assert that a value is boolean true.

```php
use Cline\Assert\Assertions\Assertion;

Assertion::true($value);
Assertion::true($isActive, 'Must be active');
```

### false()

Assert that a value is boolean false.

```php
Assertion::false($value);
Assertion::false($isDeleted, 'Must not be deleted');
```

### boolean()

Assert that a value is a boolean (true or false).

```php
Assertion::boolean($value);
Assertion::boolean($flag, 'Value must be boolean');
```

## Strict Type Checking

These assertions check for **actual boolean values only**, not truthy/falsy values:

```php
// Pass
Assertion::true(true);
Assertion::false(false);
Assertion::boolean(true);

// Fail - These are NOT booleans
Assertion::true(1);           // integer, not boolean
Assertion::false(0);          // integer, not boolean
Assertion::false(null);       // null, not boolean
```

## Chaining Boolean Assertions

```php
use Cline\Assert\Assert;

Assert::that($isActive)
    ->boolean()
    ->true('User must be active');

Assert::that($isDeleted)
    ->boolean()
    ->false('Record must not be deleted');
```

## Common Patterns

### Feature Flag Validation

```php
Assert::that($config['feature_enabled'])
    ->boolean()
    ->true('Feature must be enabled');
```

### State Validation

```php
Assert::that($user->is_active)
    ->boolean('is_active must be boolean')
    ->true('User account must be active');
```

### Configuration Validation

```php
Assert::lazy()
    ->that($config['debug'], 'debug')->boolean()
    ->that($config['cache_enabled'], 'cache_enabled')->boolean()
    ->verifyNow();
```

### Access Control

```php
Assert::that($user->hasPermission('admin'))
    ->boolean('Permission check must return boolean')
    ->true('User must have admin permission');
```

## Working with Truthy/Falsy Values

If you need to accept truthy/falsy values, convert them first:

```php
$isActive = (bool) $value;
Assertion::boolean($isActive);
```

## Database Boolean Fields

Many databases store booleans as integers:

```php
// Database returns 1/0
$row['is_active'] = 1;

// Convert first
$isActive = (bool) $row['is_active'];
Assertion::boolean($isActive);
```

## Best Practices

### Type Safety First

```php
function setActive(bool $value) {
    Assertion::boolean($value);
    $this->isActive = $value;
}
```

### Clear Error Messages

```php
Assertion::true($isVerified, 'Email address must be verified before proceeding');
```

## Next Steps

- [Type Assertions](#doc-docs-type-assertions) - Type checking
- [Comparison Assertions](#doc-docs-comparison-assertions) - Comparing values
- [Custom Assertions](#doc-docs-custom-assertions) - Custom validators

<a id="doc-docs-comparison-assertions"></a>

Comparison assertions validate equality and identity between values.

## Available Assertions

### eq()

Assert equality using loose comparison (==).

```php
use Cline\Assert\Assertions\Assertion;

Assertion::eq($actual, $expected);
Assertion::eq(123, '123');  // Passes (loose comparison)
Assertion::eq($result, 42, 'Result must equal 42');
```

### same()

Assert identity using strict comparison (===).

```php
Assertion::same($actual, $expected);
Assertion::same(123, 123);   // Passes
Assertion::same(123, '123'); // Fails (different types)
```

### notEq()

Assert values are NOT equal (loose comparison).

```php
Assertion::notEq($actual, $unwanted);
Assertion::notEq($status, 'deleted', 'Status cannot be deleted');
```

### notSame()

Assert values are NOT identical (strict comparison).

```php
Assertion::notSame($actual, $unwanted);
Assertion::notSame($newPassword, $oldPassword, 'New password must be different');
```

## Loose vs Strict Comparison

### Loose Comparison (eq/notEq)

Uses `==` operator with type coercion:

```php
Assertion::eq(123, '123');     // Pass
Assertion::eq(1, true);        // Pass
Assertion::eq(0, false);       // Pass
```

### Strict Comparison (same/notSame)

Uses `===` operator without type coercion:

```php
Assertion::same(123, '123');   // Fail
Assertion::same(1, true);      // Fail
Assertion::same(0, false);     // Fail
```

## Chaining Comparison Assertions

```php
use Cline\Assert\Assert;

Assert::that($result)
    ->integer()
    ->same(42);

Assert::that($status)
    ->string()
    ->notEq('deleted')
    ->notEq('archived');
```

## Common Patterns

### Expected Value Validation

```php
Assert::that($response->status)
    ->integer()
    ->same(200, 'Expected HTTP 200 status');
```

### State Validation

```php
Assert::that($order->status)
    ->string()
    ->notEq('cancelled', 'Order is cancelled')
    ->notEq('refunded', 'Order is refunded');
```

### Preventing Duplicate Values

```php
Assert::that($newEmail)
    ->email()
    ->notSame($currentEmail, 'New email must be different');
```

## When to Use Which

### Use eq() when:
- Comparing form input (strings) with expected values
- Type flexibility is acceptable

### Use same() when:
- Type safety is important
- Comparing computed values
- Validating configuration values

### Use notSame() when:
- Ensuring different passwords or tokens
- Type-safe blacklisting

## Best Practices

### Prefer Strict Comparison

```php
// Loose comparison can hide bugs
Assertion::eq($count, '0');

// Strict comparison catches type issues
Assertion::same($count, 0);
```

### Combine with Type Checks

```php
Assert::that($value)
    ->integer()
    ->same(42);
```

## Next Steps

- [Numeric Assertions](#doc-docs-numeric-assertions) - Range comparisons
- [Type Assertions](#doc-docs-type-assertions) - Type validation
- [Array Assertions](#doc-docs-array-assertions) - Array comparisons

<a id="doc-docs-custom-assertions"></a>

Create custom validation rules using the `satisfy()` assertion and custom assertion classes.

## The satisfy() Assertion

The `satisfy()` assertion allows you to define custom validation logic using callbacks.

### Basic Usage

```php
use Cline\Assert\Assertions\Assertion;

Assertion::satisfy($value, function($v) {
    return $v % 2 === 0;
}, 'Value must be even');
```

### With Type Checking

```php
use Cline\Assert\Assert;

Assert::that($age)
    ->integer()
    ->satisfy(fn($v) => $v >= 18 && $v <= 65, 'Age must be between 18 and 65');
```

## Common Custom Validation Patterns

### Custom String Validation

```php
Assert::that($username)
    ->string()
    ->satisfy(function($v) {
        return preg_match('/^[a-z0-9_]{3,20}$/', $v) === 1;
    }, 'Username: 3-20 chars, lowercase letters, numbers, underscores only');
```

### Password Strength

```php
Assertion::satisfy($password, function($pass) {
    return strlen($pass) >= 8
        && preg_match('/[A-Z]/', $pass)
        && preg_match('/[a-z]/', $pass)
        && preg_match('/[0-9]/', $pass)
        && preg_match('/[^A-Za-z0-9]/', $pass);
}, 'Password must contain uppercase, lowercase, number, and special character');
```

### Custom Date Range

```php
Assertion::satisfy($date, function($d) {
    $timestamp = strtotime($d);
    $now = time();
    $oneYearAgo = strtotime('-1 year');
    return $timestamp >= $oneYearAgo && $timestamp <= $now;
}, 'Date must be within the last year');
```

### Business Rule Validation

```php
Assertion::satisfy($order, function($order) {
    $calculatedTotal = array_sum(array_column($order['items'], 'price'));
    return abs($order['total'] - $calculatedTotal) < 0.01;
}, 'Order total must match sum of items');
```

## Creating Reusable Custom Validators

### Standalone Functions

```php
function assertEvenNumber($value, ?string $message = null): void
{
    Assertion::satisfy($value, fn($v) => $v % 2 === 0, $message ?? 'Value must be even');
}

assertEvenNumber($quantity);
```

### Helper Class

```php
class CustomAssertions
{
    public static function strongPassword($value, ?string $message = null): void
    {
        Assertion::satisfy($value, function($pass) {
            return strlen($pass) >= 8
                && preg_match('/[A-Z]/', $pass)
                && preg_match('/[a-z]/', $pass)
                && preg_match('/[0-9]/', $pass);
        }, $message ?? 'Password does not meet strength requirements');
    }

    public static function slug($value, ?string $message = null): void
    {
        Assertion::satisfy($value, function($v) {
            return preg_match('/^[a-z0-9-]+$/', $v) === 1;
        }, $message ?? 'Invalid slug format');
    }

    public static function hexColor($value, ?string $message = null): void
    {
        Assertion::satisfy($value, function($v) {
            return preg_match('/^#[0-9A-Fa-f]{6}$/', $v) === 1;
        }, $message ?? 'Invalid hex color format');
    }
}

CustomAssertions::strongPassword($password);
CustomAssertions::slug($urlSlug);
CustomAssertions::hexColor($brandColor);
```

## Complex Custom Validations

### Credit Card Validation

```php
function validateCreditCard(string $number): bool
{
    $number = preg_replace('/[\s-]/', '', $number);

    // Luhn algorithm
    $sum = 0;
    $numDigits = strlen($number);
    $parity = $numDigits % 2;

    for ($i = 0; $i < $numDigits; $i++) {
        $digit = (int) $number[$i];
        if ($i % 2 == $parity) {
            $digit *= 2;
        }
        if ($digit > 9) {
            $digit -= 9;
        }
        $sum += $digit;
    }

    return $sum % 10 === 0;
}

Assertion::satisfy($cardNumber, 'validateCreditCard', 'Invalid credit card number');
```

### ISBN-13 Validation

```php
Assertion::satisfy($isbn, function($v) {
    $isbn = preg_replace('/[^0-9]/', '', $v);

    if (strlen($isbn) !== 13) {
        return false;
    }

    $sum = 0;
    for ($i = 0; $i < 12; $i++) {
        $sum += (int) $isbn[$i] * ($i % 2 === 0 ? 1 : 3);
    }

    $checkDigit = (10 - ($sum % 10)) % 10;
    return $checkDigit === (int) $isbn[12];
}, 'Invalid ISBN-13');
```

## Combining Custom Assertions

### Chainable Custom Rules

```php
class UserValidator
{
    public static function assertValid(array $user): void
    {
        Assert::lazy()
            ->that($user['username'], 'username')
                ->notEmpty()
                ->satisfy(fn($v) => preg_match('/^[a-z0-9_]+$/', $v), 'Invalid username format')
            ->that($user['email'], 'email')
                ->notEmpty()
                ->email()
            ->that($user['age'], 'age')
                ->integer()
                ->satisfy(fn($v) => $v >= 13, 'Must be at least 13 years old')
            ->verifyNow();
    }
}
```

## Best Practices

### Keep Callbacks Simple

```php
// Extract complex logic to function
function validateComplexData($data): bool {
    // Complex validation logic...
}

Assertion::satisfy($data, 'validateComplexData', 'Invalid data');
```

### Provide Clear Messages

```php
Assertion::satisfy($value, $callback, 'Value must be between 1 and 100 and divisible by 5');
```

### Type Check First

```php
Assert::that($quantity)
    ->integer()
    ->satisfy(fn($v) => $v % 12 === 0, 'Quantity must be multiple of 12');
```

### Reuse Common Patterns

```php
function assertUsername($value) {
    Assertion::satisfy($value, fn($v) => preg_match('/^[a-z0-9_]+$/', $v), 'Invalid username');
}
```

## Next Steps

- [Lazy Assertions](#doc-docs-lazy-assertions) - Combine multiple validations
- [Assertion Chains](#doc-docs-assertion-chains) - Chain custom rules
- [Getting Started](#doc-docs-readme) - Core concepts

<a id="doc-docs-expect-api"></a>

The `expect()` API provides a Jest/Pest-style fluent interface for assertions, ideal for testing and validation with a natural, readable syntax.

## Installation

```php
use function Cline\Assert\expect;
```

## Core Expectations

### Value Equality

```php
expect(42)->toBe(42);
expect('hello')->toBe('hello');
expect($result)->toEqual(42);
expect(42)->toStrictEqual(42);
```

### Null Checks

```php
expect(null)->toBeNull();
expect($value)->not->toBeNull();
expect('hello')->toBeDefined();
expect(null)->toBeUndefined();
expect(null)->toBeNullable('string');
```

### Boolean Checks

```php
expect(true)->toBeTrue();
expect(false)->toBeFalse();
expect(1)->toBeTruthy();
expect(0)->toBeFalsy();
```

### Empty Checks

```php
expect('')->toBeEmpty();
expect([])->toBeEmpty();
expect('hello')->not->toBeEmpty();
```

## Type Expectations

```php
expect($value)->toBeString();
expect($count)->toBeInt();
expect($price)->toBeFloat();
expect($active)->toBeBool();
expect($items)->toBeArray();
expect($user)->toBeObject();
expect($callback)->toBeCallable();
expect($collection)->toBeIterable();
expect($number)->toBeNumeric();
```

## Numeric Comparisons

```php
expect(10)->toBeGreaterThan(5);
expect(10)->toBeGreaterThanOrEqual(10);
expect(5)->toBeLessThan(10);
expect(5)->toBeLessThanOrEqual(5);
expect(5)->toBeBetween(1, 10);
expect(3.14159)->toEqualWithDelta(3.14, 0.01);
expect(42)->toBePositive();
expect(-10)->toBeNegative();
expect(4)->toBeEven();
expect(7)->toBeOdd();
expect(10)->toBeDivisibleBy(5);
```

## String Expectations

```php
expect('hello world')->toStartWith('hello');
expect('hello world')->toEndWith('world');
expect('hello world')->toContain('lo wo');
expect('test@example.com')->toMatch('/^.+@.+\..+$/');
expect('hello')->toHaveLength(5);
expect('abc')->toBeAlpha();
expect('abc123')->toBeAlphaNumeric();
expect('hello_world')->toBeSnakeCase();
expect('hello-world')->toBeKebabCase();
expect('helloWorld')->toBeCamelCase();
```

## Collection Expectations

```php
expect([1, 2, 3])->toHaveCount(3);
expect(['name' => 'John'])->toHaveKey('name');
expect(['a' => 1, 'b' => 2])->toHaveKeys(['a', 'b']);
expect([1, 2, 3])->toContain(2);
expect(2)->toBeIn([1, 2, 3]);
expect([3, 2, 1])->toEqualCanonicalizing([1, 2, 3]);
expect([1, 2])->toBeSubsetOf([1, 2, 3, 4, 5]);
expect([1, 2, 3])->toHaveUniqueValues();
expect([1, 2, 3])->toBeSorted();
```

## Object Expectations

```php
expect($user)->toHaveProperty('name');
expect($user)->toHaveProperties(['name', 'email']);
expect($user)->toHaveMethod('save');
expect($iterator)->toBeInstanceOf(ArrayIterator::class);
expect($obj)->toMatchObject(['name' => 'John']);
```

## Format Validations

```php
expect('test@example.com')->toBeEmail();
expect('https://example.com')->toBeUrl();
expect('550e8400-e29b-41d4-a716-446655440000')->toBeUuid();
expect('{"key": "value"}')->toBeJson();
expect('2024-01-15')->toBeValidDate('Y-m-d');
```

## File System Expectations

```php
expect('/path/to/file.txt')->toBeFile();
expect('/path/to/file.txt')->toBeReadableFile();
expect('/path/to/directory')->toBeReadableDirectory();
expect('/path/to/directory')->toBeWritableDirectory();
```

## Exception Expectations

```php
expect(fn() => throw new RuntimeException())->toThrow();
expect(fn() => throw new InvalidArgumentException())
    ->toThrow(InvalidArgumentException::class);
expect(fn() => throw new Exception('Custom error'))
    ->toThrow(Exception::class, 'Custom error');
expect(fn() => $user->save())->not->toThrow();
```

## Date/Time Expectations

```php
expect('2024-01-01')->toBeBefore('2024-12-31');
expect('2024-12-31')->toBeAfter('2024-01-01');
expect(date('Y-m-d'))->toBeToday();
```

## Performance Expectations

```php
$fastOperation = fn () => 1 + 1;
expect($fastOperation)->toCompleteWithin(100); // milliseconds
```

## Negation Modifier

Use `->not` to negate any expectation:

```php
expect(42)->not->toBe(43);
expect('hello')->not->toBeNull();
expect([])->not->toBeEmpty();
expect(42)->not->toBeString();
```

## Each Modifier

Apply expectations to each element in a collection:

```php
expect([1, 2, 3])->each->toBeInt();
expect(['a', 'b', 'c'])->each->toBeString();
expect([1, 2, 3])->each->not->toBeString();

expect(['a' => 1, 'b' => 2])->each(function($expectation, $key) {
    $expectation->toBeInt();
    expect($key)->toBeString();
});
```

## And Modifier

Chain multiple values in a single expression:

```php
expect($user)->toHaveProperty('email')
    ->and($admin)->toHaveProperty('email')
    ->and($guest)->not->toHaveProperty('email');
```

## Conditional Modifiers

### When Modifier

```php
expect($user)->when(
    $isAdmin,
    fn($exp) => $exp->toHaveProperty('adminLevel')
);
```

### Unless Modifier

```php
expect($user)->unless(
    $isGuest,
    fn($exp) => $exp->toHaveProperty('subscription')
);
```

## Sequence Modifier

Apply different expectations to each element in order:

```php
expect([1, 'test', 3.14])->sequence(
    fn($e) => $e->toBeInt(),
    fn($e) => $e->toBeString(),
    fn($e) => $e->toBeFloat()
);
```

## JSON Modifier

Parse JSON and continue chaining:

```php
expect('{"name":"John","age":30}')
    ->json()
    ->toHaveKey('name')
    ->toHaveKey('age');
```

## Custom Validation

### toSatisfy()

```php
expect($age)->toSatisfy(fn($v) => $v > 18);
expect($user)->toSatisfy(function($u) {
    return $u->age > 18 && $u->verified === true;
});
```

### toMatchSchema()

```php
expect($user)->toMatchSchema([
    'type' => 'object',
    'properties' => [
        'name' => ['type' => 'string'],
        'age' => ['type' => 'integer', 'minimum' => 0],
    ],
    'required' => ['name', 'email'],
]);
```

## Asymmetric Matchers

Match partial patterns without requiring exact equality:

```php
expect(['name' => 'John', 'age' => 30])->toEqual([
    'name' => expect()->any('string'),
    'age' => expect()->any('int'),
]);

expect(['id' => 123, 'data' => 'test'])->toEqual([
    'id' => expect()->anything(),
    'data' => expect()->anything(),
]);

expect(['message' => 'Error: Invalid input'])->toEqual([
    'message' => expect()->stringContaining('Error'),
]);

expect(['a' => 1, 'b' => 2, 'c' => 3])->toEqual(
    expect()->arrayContaining(['a' => 1, 'c' => 3])
);
```

## Soft Assertions

Collect multiple assertion failures before throwing:

```php
expect(5)->soft->toBeGreaterThan(10);
expect('hello')->soft->toBeInt();
expect([])->soft->toHaveCount(5);

Expectation::assertSoft(); // Throws with all 3 errors
```

## OR Operator

Value must match at least one group:

```php
expect($value)
    ->or
    ->toBeString()
    ->or
    ->toBeInt()
    ->or
    ->toBeNull();

expect($input)
    ->or
    ->toBeString()
    ->toHaveLength(10)
    ->or
    ->toBeInt()
    ->toBePositive();
```

## XOR Operator

Exactly one group must pass:

```php
expect($configValue)
    ->xor
    ->toBeBoolean()
    ->xor
    ->toBeString()
    ->xor
    ->toBeNumeric();
```

## Snapshot Testing

```php
use Cline\Assert\Snapshots\SnapshotManager;

SnapshotManager::setSnapshotDirectory('__snapshots__');

expect($data)->toMatchSnapshot('user-data');
expect($data)->toMatchInlineSnapshot($expected);
```

## Debugging Helpers

```php
expect($data)->dd();
expect($data)->ddWhen($isDebug);
expect($data)->ddUnless($isProduction);
expect($data)->ray();
```

## Next Steps

- [Getting Started](#doc-docs-readme) - Basic assertion concepts
- [Assertion Chains](#doc-docs-assertion-chains) - Fluent API usage
- [Custom Assertions](#doc-docs-custom-assertions) - Create custom rules

<a id="doc-docs-filesystem-assertions"></a>

File system assertions validate files, directories, and file permissions.

## Available Assertions

### file()

Assert that a file exists.

```php
use Cline\Assert\Assertions\Assertion;

Assertion::file('/path/to/file.txt');
Assertion::file($configPath, 'Config file not found');
```

### directory()

Assert that a directory exists.

```php
Assertion::directory('/path/to/dir');
Assertion::directory($uploadsPath, 'Uploads directory not found');
```

### readable()

Assert that a file or directory is readable.

```php
Assertion::readable('/path/to/file.txt');
Assertion::readable($logFile, 'Cannot read log file');
```

### writeable()

Assert that a file or directory is writeable.

```php
Assertion::writeable('/path/to/file.txt');
Assertion::writeable($cacheDir, 'Cache directory is not writeable');
```

## Chaining File System Assertions

```php
use Cline\Assert\Assert;

Assert::that($configFile)
    ->string()
    ->notEmpty()
    ->file('Config file does not exist')
    ->readable('Config file is not readable');

Assert::that($uploadDir)
    ->directory('Upload directory missing')
    ->writeable('Upload directory is not writeable');
```

## Common Patterns

### Configuration File Validation

```php
$configPath = __DIR__ . '/config/app.php';

Assert::that($configPath)
    ->file('Configuration file not found')
    ->readable('Cannot read configuration file');

$config = require $configPath;
```

### Upload Directory Validation

```php
$uploadPath = storage_path('uploads');

Assert::that($uploadPath)
    ->directory('Upload directory does not exist')
    ->writeable('Cannot write to upload directory');
```

### Multiple Directory Validation

```php
$directories = [
    storage_path('cache'),
    storage_path('sessions'),
    storage_path('views'),
];

foreach ($directories as $dir) {
    Assert::that($dir)
        ->directory("Directory does not exist: {$dir}")
        ->writeable("Directory not writeable: {$dir}");
}
```

## Permission Checks

### Read and Write

```php
Assert::that($dataFile)
    ->file()
    ->readable('Cannot read data file')
    ->writeable('Cannot write to data file');
```

## Best Practices

### Check Existence First

```php
Assert::that($file)
    ->file('File does not exist')
    ->readable('File is not readable');
```

### Validate Before Operations

```php
Assert::that($sourceFile)->file()->readable();
Assert::that($destDir)->directory()->writeable();

copy($sourceFile, $destDir . '/' . basename($sourceFile));
```

### Use Absolute Paths

```php
// Use absolute path
Assertion::file(__DIR__ . '/config/app.php');

// Or use path helper
Assertion::file(config_path('app.php'));
```

## Application Examples

### Asset Loading

```php
public function loadAsset(string $name): string
{
    $assetPath = public_path("assets/{$name}");

    Assert::that($assetPath)
        ->file("Asset not found: {$name}")
        ->readable("Cannot read asset: {$name}");

    return file_get_contents($assetPath);
}
```

### Cache Management

```php
public function setupCache(): void
{
    $cacheDir = storage_path('framework/cache');

    if (!is_dir($cacheDir)) {
        mkdir($cacheDir, 0755, true);
    }

    Assert::that($cacheDir)
        ->directory('Failed to create cache directory')
        ->writeable('Cache directory must be writeable');
}
```

## Next Steps

- [String Assertions](#doc-docs-string-assertions) - Path validation
- [Custom Assertions](#doc-docs-custom-assertions) - Custom file validators
- [Lazy Assertions](#doc-docs-lazy-assertions) - Validate multiple paths

<a id="doc-docs-lazy-assertions"></a>

Lazy assertions collect multiple validation errors before throwing, allowing you to report all validation failures at once.

## Why Lazy Assertions?

Traditional assertions fail on the first error:

```php
Assertion::notEmpty($name);     // Fails here
Assertion::email($email);       // Never checked
Assertion::integer($age);       // Never checked
```

Lazy assertions collect all errors:

```php
Assert::lazy()
    ->that($name, 'name')->notEmpty()
    ->that($email, 'email')->email()
    ->that($age, 'age')->integer()
    ->verifyNow(); // Throws with all errors
```

## Basic Usage

```php
use Cline\Assert\Assert;

Assert::lazy()
    ->that($email, 'email')->notEmpty()->email()
    ->that($name, 'name')->notEmpty()->minLength(2)
    ->that($age, 'age')->integer()->greaterOrEqualThan(18)
    ->verifyNow();
```

## Form Validation

### User Registration

```php
$errors = [];

try {
    Assert::lazy()
        ->that($data['username'] ?? null, 'username')
            ->notNull('Username is required')
            ->notEmpty('Username cannot be empty')
            ->minLength(3, 'Username must be at least 3 characters')
        ->that($data['email'] ?? null, 'email')
            ->notNull('Email is required')
            ->email('Invalid email format')
        ->that($data['password'] ?? null, 'password')
            ->notNull('Password is required')
            ->minLength(8, 'Password must be at least 8 characters')
        ->verifyNow();
} catch (LazyAssertionException $e) {
    foreach ($e->getErrorExceptions() as $error) {
        $errors[$error->getPropertyPath()] = $error->getMessage();
    }
}
```

## API Request Validation

```php
public function validateRequest(array $data): void
{
    Assert::lazy()
        ->that($data['action'] ?? null, 'action')
            ->notNull('Action is required')
            ->inArray(['create', 'update', 'delete'], 'Invalid action')
        ->that($data['resource_id'] ?? null, 'resource_id')
            ->notNull('Resource ID is required')
            ->uuid('Invalid resource ID format')
        ->verifyNow();
}
```

## Configuration Validation

```php
Assert::lazy()
    ->that($config['app_name'], 'app_name')
        ->notEmpty('App name is required')
    ->that($config['environment'], 'environment')
        ->inArray(['development', 'staging', 'production'])
    ->that($config['debug'], 'debug')
        ->boolean()
    ->verifyNow();
```

## tryAll() Mode

By default, lazy assertions stop validating a field after the first failure. Use `tryAll()` to validate all assertions:

### Default Behavior

```php
Assert::lazy()
    ->that($age, 'age')
        ->integer()        // Fails here for "abc"
        ->greaterThan(0)   // Not checked
    ->verifyNow();
```

### tryAll() Mode

```php
Assert::lazy()
    ->tryAll()
    ->that($password, 'password')
        ->string()
        ->minLength(8)          // Check all length requirements
        ->regex('/[A-Z]/')      // Check all complexity requirements
        ->regex('/[a-z]/')
        ->regex('/[0-9]/')
    ->verifyNow(); // Reports ALL failures
```

## Error Handling

### Catching Errors

```php
use Cline\Assert\LazyAssertionException;

try {
    Assert::lazy()
        ->that($email, 'email')->email()
        ->that($age, 'age')->integer()
        ->verifyNow();
} catch (LazyAssertionException $e) {
    foreach ($e->getErrorExceptions() as $error) {
        echo $error->getPropertyPath() . ': ' . $error->getMessage() . "\n";
    }
}
```

### JSON API Error Response

```php
try {
    Assert::lazy()
        ->that($request['email'], 'email')->email()
        ->verifyNow();
} catch (LazyAssertionException $e) {
    $errors = array_map(function($error) {
        return [
            'field' => $error->getPropertyPath(),
            'message' => $error->getMessage(),
        ];
    }, $e->getErrorExceptions());

    return response()->json(['errors' => $errors], 422);
}
```

## Nested Property Paths

```php
Assert::lazy()
    ->that($user['email'], 'user.email')->email()
    ->that($user['address']['city'], 'user.address.city')->notEmpty()
    ->that($user['address']['zip'], 'user.address.zip')->regex('/^\d{5}$/')
    ->verifyNow();
```

## Best Practices

### Always Use Property Paths

```php
Assert::lazy()
    ->that($email, 'email')->email()
    ->that($age, 'age')->integer()
    ->verifyNow();
```

### Use Meaningful Property Paths

```php
Assert::lazy()
    ->that($email, 'contact_email')->email()
    ->that($phone, 'contact_phone')->e164()
    ->verifyNow();
```

### Separate Validation from Business Logic

```php
public function createUser(array $data): User
{
    $this->validateUserData($data);
    return User::create($data);
}

private function validateUserData(array $data): void
{
    Assert::lazy()
        ->that($data['email'], 'email')->email()
        ->verifyNow();
}
```

## When to Use

Use lazy assertions when:
- Validating user input (forms, APIs)
- Need to report all errors at once
- Better UX is important

Avoid when:
- Performance-critical code
- Internal validation
- Only one field to validate

## Next Steps

- [Assertion Chains](#doc-docs-assertion-chains) - Single-field validation
- [Getting Started](#doc-docs-readme) - Basic concepts
- [Custom Assertions](#doc-docs-custom-assertions) - Custom validation rules

<a id="doc-docs-null-empty-assertions"></a>

Assertions for validating null values, empty values, and blank strings.

## Available Assertions

### null()

Assert that a value is null.

```php
use Cline\Assert\Assertions\Assertion;

Assertion::null($value);
Assertion::null($optionalField, 'Field must be null');
```

### notNull()

Assert that a value is not null.

```php
Assertion::notNull($value);
Assertion::notNull($user, 'User is required');
```

### notEmpty()

Assert that a value is not empty (using `empty()` check).

```php
Assertion::notEmpty($value);
Assertion::notEmpty($name, 'Name is required');
```

### noContent()

Assert that a value is empty (using `empty()` check).

```php
Assertion::noContent($value);
Assertion::noContent($deletedField, 'Field must be empty');
```

### notBlank()

Assert that a value is not blank (not empty string or whitespace-only).

```php
Assertion::notBlank($value);
Assertion::notBlank($description, 'Description cannot be blank');
```

## Understanding Empty Checks

### What `empty()` Considers Empty

```php
Assertion::noContent('');       // empty string
Assertion::noContent(0);        // integer zero
Assertion::noContent(null);     // null
Assertion::noContent(false);    // boolean false
Assertion::noContent([]);       // empty array
```

## Null vs Empty vs Blank

### null() - Strict Null Check

```php
Assertion::null(null);      // Pass
Assertion::null('');        // Fail - empty string, not null
```

### noContent() - PHP Empty Check

```php
Assertion::noContent(null);     // Pass
Assertion::noContent('');       // Pass
Assertion::noContent(0);        // Pass
```

### notBlank() - Non-Whitespace String

```php
Assertion::notBlank('hello');   // Pass
Assertion::notBlank(' ');       // Fail - whitespace only
Assertion::notBlank('');        // Fail - empty string
```

## Chaining Null/Empty Assertions

```php
use Cline\Assert\Assert;

Assert::that($username)
    ->notNull('Username is required')
    ->notEmpty('Username cannot be empty')
    ->string();

Assert::that($description)
    ->string()
    ->notBlank('Description cannot be blank');
```

## Common Patterns

### Required Field Validation

```php
Assert::that($email)
    ->notNull('Email is required')
    ->notEmpty('Email cannot be empty')
    ->notBlank('Email cannot be blank')
    ->email('Invalid email format');
```

### Optional Field Validation

```php
Assert::thatNullOr($phoneNumber)
    ->string()
    ->e164('Invalid phone format');
```

### Form Input Validation

```php
Assert::lazy()
    ->that($form['name'] ?? null, 'name')->notNull()->notBlank()
    ->that($form['email'] ?? null, 'email')->notNull()->notBlank()->email()
    ->verifyNow();
```

## Using nullOr()

```php
// Allow null OR validate if not null
Assert::thatNullOr($middleName)
    ->string()
    ->minLength(2);
```

## String Whitespace Handling

```php
// notEmpty allows whitespace
Assertion::notEmpty(' ');      // Pass

// notBlank rejects whitespace
Assertion::notBlank(' ');      // Fail
```

## Best Practices

### Check Null First

```php
Assert::that($user)
    ->notNull('User not found')
    ->isObject();
```

### Use notBlank for User Input

```php
Assert::that($comment)
    ->notBlank('Comment cannot be empty');
```

### Combine with Type Checks

```php
Assert::that($name)
    ->notNull()
    ->string()
    ->notBlank();
```

## Next Steps

- [Type Assertions](#doc-docs-type-assertions) - Type validation
- [String Assertions](#doc-docs-string-assertions) - String validation
- [Assertion Chains](#doc-docs-assertion-chains) - Using nullOr()

<a id="doc-docs-numeric-assertions"></a>

Numeric assertions validate numbers and perform comparison operations.

## Available Assertions

### lessThan()

Assert that a value is less than a limit.

```php
use Cline\Assert\Assertions\Assertion;

Assertion::lessThan($age, 18);
Assertion::lessThan($score, 100, 'Score must be less than 100');
```

### lessOrEqualThan()

Assert that a value is less than or equal to a limit.

```php
Assertion::lessOrEqualThan($percentage, 100);
Assertion::lessOrEqualThan($quantity, 10, 'Maximum quantity is 10');
```

### greaterThan()

Assert that a value is greater than a limit.

```php
Assertion::greaterThan($age, 17);
Assertion::greaterThan($price, 0, 'Price must be positive');
```

### greaterOrEqualThan()

Assert that a value is greater than or equal to a limit.

```php
Assertion::greaterOrEqualThan($age, 18);
Assertion::greaterOrEqualThan($rating, 1, 'Rating must be at least 1');
```

### between()

Assert that a value is between two limits (inclusive).

```php
Assertion::between($age, 18, 65);
Assertion::between($score, 0, 100, 'Score must be between 0 and 100');
```

### betweenExclusive()

Assert that a value is between two limits (exclusive).

```php
Assertion::betweenExclusive($temperature, 0, 100);
```

### range()

Alias for `between()`.

```php
Assertion::range($month, 1, 12);
Assertion::range($hour, 0, 23);
```

### min()

Assert minimum value.

```php
Assertion::min($quantity, 1);
Assertion::min($price, 0.01, 'Price must be at least $0.01');
```

### max()

Assert maximum value.

```php
Assertion::max($discount, 100);
Assertion::max($items, 50, 'Maximum 50 items allowed');
```

## Chaining Numeric Assertions

```php
use Cline\Assert\Assert;

Assert::that($age)
    ->integer()
    ->greaterOrEqualThan(18)
    ->lessThan(100);

Assert::that($price)
    ->float()
    ->greaterThan(0)
    ->max(9999.99);

Assert::that($percentage)
    ->numeric()
    ->between(0, 100);
```

## Common Patterns

### Age Validation

```php
Assert::that($age)
    ->integer()
    ->greaterOrEqualThan(0)
    ->lessThan(150);
```

### Price Validation

```php
Assert::that($price)
    ->float()
    ->greaterThan(0)
    ->max(1000000);
```

### Rating System (1-5)

```php
Assert::that($rating)
    ->integer()
    ->between(1, 5, 'Rating must be between 1 and 5');
```

### Percentage Validation

```php
Assert::that($percentage)
    ->numeric()
    ->greaterOrEqualThan(0)
    ->lessOrEqualThan(100);
```

### Quantity Validation

```php
Assert::that($quantity)
    ->integer()
    ->greaterThan(0, 'Quantity must be positive')
    ->max(999, 'Maximum quantity is 999');
```

## Working with Different Numeric Types

### Integer Validation

```php
Assert::that($count)
    ->integer()
    ->greaterOrEqualThan(0);
```

### Float Validation

```php
Assert::that($latitude)
    ->float()
    ->between(-90, 90);
```

### Numeric Strings

```php
Assert::that($value)
    ->numeric()
    ->greaterThan(0);
```

## Next Steps

- [Type Assertions](#doc-docs-type-assertions) - Type checking for integers, floats
- [Comparison Assertions](#doc-docs-comparison-assertions) - Equality operations
- [Lazy Assertions](#doc-docs-lazy-assertions) - Validate multiple fields

<a id="doc-docs-object-assertions"></a>

Object assertions validate objects, classes, interfaces, and their properties/methods.

## Available Assertions

### isInstanceOf()

Assert that a value is an instance of a given class.

```php
use Cline\Assert\Assertions\Assertion;

Assertion::isInstanceOf($user, User::class);
Assertion::isInstanceOf($model, Model::class, 'Expected Model instance');
```

### notIsInstanceOf()

Assert that a value is NOT an instance of a given class.

```php
Assertion::notIsInstanceOf($value, LegacyUser::class);
```

### classExists()

Assert that a class exists.

```php
Assertion::classExists(User::class);
Assertion::classExists($className, 'Class does not exist');
```

### interfaceExists()

Assert that an interface exists.

```php
Assertion::interfaceExists(UserInterface::class);
```

### subclassOf()

Assert that a class is a subclass of another.

```php
Assertion::subclassOf(AdminUser::class, User::class);
```

### implementsInterface()

Assert that a class implements an interface.

```php
Assertion::implementsInterface(User::class, UserInterface::class);
```

### methodExists()

Assert that a method exists on an object.

```php
Assertion::methodExists('save', $model);
Assertion::methodExists('handle', $handler, 'Handler must have handle method');
```

### propertyExists()

Assert that a property exists on an object or class.

```php
Assertion::propertyExists($user, 'email');
```

### propertiesExist()

Assert that multiple properties exist.

```php
$required = ['id', 'name', 'email'];
Assertion::propertiesExist($user, $required);
```

## Chaining Object Assertions

```php
use Cline\Assert\Assert;

Assert::that($user)
    ->isObject()
    ->isInstanceOf(User::class);

Assert::that(User::class)
    ->classExists()
    ->implementsInterface(UserInterface::class);
```

## Common Patterns

### Dependency Injection Validation

```php
Assert::that($logger)
    ->isObject()
    ->implementsInterface(LoggerInterface::class);
```

### Model Validation

```php
Assert::that($model)
    ->isObject()
    ->isInstanceOf(Model::class)
    ->propertyExists('id')
    ->propertyExists('created_at');
```

### Plugin Validation

```php
Assert::that($plugin)
    ->isObject()
    ->implementsInterface(PluginInterface::class)
    ->methodExists('register')
    ->methodExists('boot');
```

### Factory Pattern Validation

```php
public function make(string $class)
{
    Assertion::classExists($class);
    Assertion::subclassOf($class, BaseService::class);

    return new $class();
}
```

## Working with Interfaces

```php
Assertion::implementsInterface(JsonSerializer::class, Serializer::class);

Assert::that($serializer)
    ->isObject()
    ->isInstanceOf(Serializer::class);
```

## Best Practices

### Interface Over Implementation

```php
// Tightly coupled
Assert::that($logger)->isInstanceOf(MonologLogger::class);

// Depends on interface
Assert::that($logger)->isInstanceOf(LoggerInterface::class);
```

### Combine with Method Checks

```php
Assert::that($handler)
    ->isObject()
    ->isInstanceOf(HandlerInterface::class)
    ->methodExists('handle');
```

## Next Steps

- [Type Assertions](#doc-docs-type-assertions) - Basic type checking
- [Custom Assertions](#doc-docs-custom-assertions) - Custom object validation
- [Lazy Assertions](#doc-docs-lazy-assertions) - Validate multiple properties

<a id="doc-docs-string-assertions"></a>

String assertions validate and check string values for various conditions including length, patterns, and content.

## Available Assertions

### regex()

Assert that a string matches a regular expression pattern.

```php
use Cline\Assert\Assertions\Assertion;

Assertion::regex($value, '/^[A-Z][a-z]+$/');
Assertion::regex($code, '/^[A-Z]{3}\d{3}$/', 'Code must be 3 letters followed by 3 digits');
```

### notRegex()

Assert that a string does NOT match a pattern.

```php
Assertion::notRegex($username, '/[^a-zA-Z0-9_]/', 'Username contains invalid characters');
```

### length()

Assert that a string has an exact length.

```php
Assertion::length($zipCode, 5);
Assertion::length($value, 10, null, null, 'utf8');
```

### minLength()

Assert minimum string length.

```php
Assertion::minLength($password, 8);
Assertion::minLength($username, 3, 'Username must be at least 3 characters');
```

### maxLength()

Assert maximum string length.

```php
Assertion::maxLength($username, 20);
Assertion::maxLength($title, 100, 'Title cannot exceed 100 characters');
```

### betweenLength()

Assert string length is within a range.

```php
Assertion::betweenLength($password, 8, 100);
Assertion::betweenLength($name, 2, 50, 'Name must be between 2 and 50 characters');
```

### startsWith()

Assert that a string starts with a substring.

```php
Assertion::startsWith($url, 'https://');
Assertion::startsWith($phoneNumber, '+1', 'Phone number must start with +1');
```

### endsWith()

Assert that a string ends with a substring.

```php
Assertion::endsWith($filename, '.pdf');
Assertion::endsWith($email, '@example.com', 'Email must be from example.com');
```

### contains()

Assert that a string contains a substring.

```php
Assertion::contains($content, 'keyword');
Assertion::contains($url, '://secure.', 'URL must contain secure subdomain');
```

### notContains()

Assert that a string does NOT contain a substring.

```php
Assertion::notContains($password, $username, 'Password cannot contain username');
Assertion::notContains($content, '<script', 'Content cannot contain script tags');
```

### alnum()

Assert that a string is alphanumeric.

```php
Assertion::alnum($identifier);
Assertion::alnum($code, 'Code must be alphanumeric');
```

## Chaining String Assertions

```php
use Cline\Assert\Assert;

Assert::that($password)
    ->string()
    ->notEmpty()
    ->minLength(8)
    ->maxLength(100)
    ->notContains($username);

Assert::that($slug)
    ->string()
    ->regex('/^[a-z0-9-]+$/')
    ->minLength(3);
```

## Common Patterns

### Username Validation

```php
Assert::that($username)
    ->string()
    ->betweenLength(3, 20)
    ->regex('/^[a-zA-Z0-9_]+$/', 'Letters, numbers, and underscores only');
```

### Password Strength

```php
Assert::that($password)
    ->string()
    ->minLength(8)
    ->regex('/[A-Z]/', 'Must contain uppercase letter')
    ->regex('/[a-z]/', 'Must contain lowercase letter')
    ->regex('/[0-9]/', 'Must contain number')
    ->notContains($username, 'Password cannot contain username');
```

### URL Validation

```php
Assert::that($url)
    ->string()
    ->notEmpty()
    ->startsWith('https://')
    ->url();
```

## Encoding Support

String assertions support different character encodings:

```php
Assertion::length($japanese, 5, null, null, 'utf8');
Assertion::minLength($text, 10, null, null, 'ISO-8859-1');
```

## Next Steps

- [Validation Assertions](#doc-docs-validation-assertions) - Email, URL, UUID validation
- [Type Assertions](#doc-docs-type-assertions) - Type checking
- [Assertion Chains](#doc-docs-assertion-chains) - Fluent API

<a id="doc-docs-type-assertions"></a>

Type assertions verify that values match expected PHP types.

## Available Assertions

### integer()

Assert that a value is a PHP integer.

```php
use Cline\Assert\Assertions\Assertion;

Assertion::integer($count);
Assertion::integer($id, 'ID must be an integer');
```

### float()

Assert that a value is a PHP float.

```php
Assertion::float($price);
Assertion::float($temperature, 'Temperature must be a float');
```

### string()

Assert that a value is a string.

```php
Assertion::string($name);
Assertion::string($email, 'Email must be a string');
```

### boolean()

Assert that a value is a PHP boolean.

```php
Assertion::boolean($isActive);
Assertion::boolean($flag, 'Flag must be a boolean');
```

### numeric()

Assert that a value is numeric (int, float, or numeric string).

```php
Assertion::numeric($value);
Assertion::numeric($amount, 'Amount must be numeric');
```

### integerish()

Assert that a value is an integer or can be cast to an integer.

```php
Assertion::integerish('123');  // Passes
Assertion::integerish(123);    // Passes
Assertion::integerish('123.0'); // Fails
```

### scalar()

Assert that a value is a PHP scalar.

```php
Assertion::scalar($value);
Assertion::scalar($input, 'Input must be scalar');
```

### isArray()

Assert that a value is an array.

```php
Assertion::isArray($items);
Assertion::isArray($config, 'Config must be an array');
```

### isObject()

Assert that a value is an object.

```php
Assertion::isObject($instance);
Assertion::isObject($model, 'Model must be an object');
```

### isResource()

Assert that a value is a resource.

```php
$file = fopen('file.txt', 'r');
Assertion::isResource($file);
```

### isCallable()

Assert that a value is callable.

```php
Assertion::isCallable($callback);
Assertion::isCallable(fn() => true);
Assertion::isCallable('strlen');
Assertion::isCallable([$object, 'method']);
```

## Chaining Type Assertions

```php
use Cline\Assert\Assert;

Assert::that($age)
    ->integer()
    ->greaterThan(0);

Assert::that($name)
    ->string()
    ->notEmpty();
```

## Common Patterns

### Input Validation

```php
Assert::that($userId)
    ->integer('User ID must be an integer')
    ->greaterThan(0, 'User ID must be positive');
```

### Numeric Type Checking

```php
// Strict integer check
Assert::that($count)->integer();

// Flexible numeric check
Assert::that($amount)->numeric();

// Integer-like check
Assert::that($id)->integerish();
```

## Type Coercion vs Strict Types

### Strict Type Checking

```php
Assertion::integer(123);     // Pass
Assertion::integer('123');   // Fail
Assertion::integer(123.0);   // Fail
```

### Flexible Type Checking

```php
Assertion::numeric(123);     // Pass
Assertion::numeric('123');   // Pass
Assertion::numeric(123.45);  // Pass

Assertion::integerish(123);      // Pass
Assertion::integerish('123');    // Pass
Assertion::integerish(123.0);    // Fail
```

## Best Practices

### Be Specific

```php
// Too loose
Assertion::scalar($age);

// Specific
Assertion::integer($age);
```

### Chain Related Assertions

```php
Assert::that($email)
    ->string()
    ->notEmpty()
    ->email();
```

### Use Integerish for Flexible Input

```php
Assert::that($_POST['quantity'])
    ->integerish()
    ->greaterThan(0);
```

## Next Steps

- [Numeric Assertions](#doc-docs-numeric-assertions) - Number validation
- [Object Assertions](#doc-docs-object-assertions) - Object validation
- [Array Assertions](#doc-docs-array-assertions) - Array validation

<a id="doc-docs-validation-assertions"></a>

Validation assertions check common data formats like emails, URLs, UUIDs, and IP addresses.

## Available Assertions

### email()

Assert that a value is a valid email address.

```php
use Cline\Assert\Assertions\Assertion;

Assertion::email('user@example.com');
Assertion::email($emailAddress, 'Invalid email address');
```

### url()

Assert that a value is a valid URL.

```php
Assertion::url('https://example.com');
Assertion::url($websiteUrl, 'Invalid URL format');
```

### uuid()

Assert that a value is a valid UUID.

```php
Assertion::uuid('550e8400-e29b-41d4-a716-446655440000');
Assertion::uuid($id, 'Invalid UUID format');
```

### ip()

Assert that a value is a valid IPv4 or IPv6 address.

```php
Assertion::ip('192.168.1.1');
Assertion::ip('2001:0db8:85a3:0000:0000:8a2e:0370:7334');
```

### ipv4()

Assert that a value is a valid IPv4 address.

```php
Assertion::ipv4('192.168.1.1');
```

### ipv6()

Assert that a value is a valid IPv6 address.

```php
Assertion::ipv6('2001:0db8:85a3:0000:0000:8a2e:0370:7334');
```

### e164()

Assert that a value is a valid E.164 phone number format.

```php
Assertion::e164('+14155552671');
Assertion::e164($phoneNumber, 'Invalid phone number format');
```

### base64()

Assert that a value is valid base64 encoded data.

```php
Assertion::base64('SGVsbG8gV29ybGQ=');
```

### isJsonString()

Assert that a value is a valid JSON string.

```php
Assertion::isJsonString('{"key":"value"}');
Assertion::isJsonString($jsonData, 'Invalid JSON format');
```

### date()

Assert that a date string matches a specific format.

```php
Assertion::date('2024-01-15', 'Y-m-d');
Assertion::date($dateString, 'Y-m-d H:i:s', 'Invalid date format');
```

## Chaining Validation Assertions

```php
use Cline\Assert\Assert;

Assert::that($email)
    ->string()
    ->notEmpty()
    ->email('Please provide a valid email address');

Assert::that($website)
    ->string()
    ->url('Invalid website URL')
    ->startsWith('https://', 'Website must use HTTPS');
```

## Common Patterns

### User Registration Validation

```php
Assert::lazy()
    ->that($data['email'], 'email')
        ->notEmpty('Email is required')
        ->email('Invalid email address')
    ->that($data['phone'] ?? null, 'phone')
        ->nullOr()->e164('Invalid phone number format')
    ->verifyNow();
```

### URL Validation with Protocol

```php
Assert::that($redirectUrl)
    ->url('Invalid redirect URL')
    ->regex('/^https:\/\//', 'Only HTTPS URLs are allowed');
```

### UUID Primary Key Validation

```php
Assert::that($userId)
    ->notEmpty('User ID is required')
    ->uuid('Invalid user ID format');
```

### Date Range Validation

```php
Assert::that($startDate)
    ->date('Y-m-d', 'Invalid start date format');

Assert::that($endDate)
    ->date('Y-m-d', 'Invalid end date format');
```

## Email Validation

### Email with Domain Check

```php
Assert::that($email)
    ->email('Invalid email format')
    ->endsWith('@company.com', 'Must use company email');
```

### Multiple Email Validation

```php
Assert::thatAll($recipients)
    ->email('All recipients must have valid email addresses');
```

## URL Validation

### HTTPS Only

```php
Assert::that($url)
    ->url('Invalid URL')
    ->startsWith('https://', 'Only HTTPS URLs allowed');
```

### Domain Restriction

```php
Assert::that($callbackUrl)
    ->url()
    ->contains('example.com', 'Callback must be on example.com');
```

## IP Address Validation

### Private IP Range

```php
Assert::that($internalIp)
    ->ipv4()
    ->satisfy(function($ip) {
        return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE) === false;
    }, 'Must be private IP address');
```

## Best Practices

### Validate Format Before Processing

```php
Assert::that($email)->email();
$user = User::where('email', $email)->first();
```

### Use Specific Validators

```php
// Use built-in validator
Assertion::email($email);

// Instead of generic regex
Assertion::regex($email, '/^[^@]+@[^@]+\.[^@]+$/');
```

## Next Steps

- [String Assertions](#doc-docs-string-assertions) - String format validation
- [Custom Assertions](#doc-docs-custom-assertions) - Create custom validators
- [Lazy Assertions](#doc-docs-lazy-assertions) - Validate multiple fields
