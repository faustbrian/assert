# Assert::that() Chaining

The `Assert::that()` fluent API provides a more concise and readable way to validate values against multiple assertions. Instead of repeating the value in each assertion call, you specify it once and chain the assertions you want to apply.

## Basic Chaining

Using the static API is verbose when checking values against multiple assertions:

```php
<?php
use Cline\Assert\Assertion;

// Verbose way
Assertion::notEmpty($username);
Assertion::string($username);
Assertion::minLength($username, 3);
Assertion::maxLength($username, 20);
```

The fluent API makes this much cleaner:

```php
<?php
use Cline\Assert\Assert;

// Fluent way
Assert::that($username)
    ->notEmpty()
    ->string()
    ->minLength(3)
    ->maxLength(20);
```

## Common Use Cases

### String Validation

```php
<?php
use Cline\Assert\Assert;

Assert::that($email)
    ->notEmpty()
    ->string()
    ->email();

Assert::that($url)
    ->notEmpty()
    ->string()
    ->url();

Assert::that($password)
    ->notEmpty()
    ->string()
    ->minLength(8)
    ->regex('/[A-Z]/', 'Password must contain uppercase letter')
    ->regex('/[0-9]/', 'Password must contain number');
```

### Numeric Validation

```php
<?php
use Cline\Assert\Assert;

Assert::that($age)
    ->integer()
    ->greaterOrEqualThan(0)
    ->lessThan(120);

Assert::that($price)
    ->float()
    ->greaterThan(0)
    ->lessThan(1000000);

Assert::that($quantity)
    ->integer()
    ->between(1, 100);
```

### Array Validation

```php
<?php
use Cline\Assert\Assert;

Assert::that($config)
    ->isArray()
    ->keyExists('api_key')
    ->keyExists('api_secret');

Assert::that($items)
    ->isArray()
    ->minCount(1)
    ->maxCount(50);
```

## NullOr with Chaining

Use `Assert::thatNullOr()` to create a chain that accepts null values:

```php
<?php
use Cline\Assert\Assert;

// Long form - breaks chain early if null
Assert::that($value)->nullOr()->string()->startsWith("Foo");

// Shortcut
Assert::thatNullOr($value)->string()->startsWith("Foo");
```

Both forms above are equivalent. If `$value` is null, the chain short-circuits and no further assertions are checked. If `$value` is not null, all subsequent assertions must pass.

### Optional Parameters

```php
<?php
use Cline\Assert\Assert;

function createUser($username, $email = null, $phone = null)
{
    Assert::that($username)
        ->notEmpty()
        ->string()
        ->minLength(3);

    Assert::thatNullOr($email)
        ->string()
        ->email();

    Assert::thatNullOr($phone)
        ->string()
        ->regex('/^\+?[1-9]\d{1,14}$/');

    // Create user...
}
```

## All with Chaining

Use `Assert::thatAll()` to create a chain that validates all items in an array:

```php
<?php
use Cline\Assert\Assert;

// All items must be positive integers
Assert::thatAll($quantities)->integer()->greaterThan(0);

// All items must be non-empty strings
Assert::thatAll($tags)->string()->notEmpty();

// All items must be valid email addresses
Assert::thatAll($emails)->string()->email();
```

### Complex Collection Validation

```php
<?php
use Cline\Assert\Assert;

function processProducts(array $productIds)
{
    Assert::that($productIds)
        ->isArray()
        ->notEmpty()
        ->minCount(1)
        ->maxCount(100);

    Assert::thatAll($productIds)
        ->string()
        ->uuid();

    // Process products...
}
```

## Combining with Functional Constructors

The library also provides functional constructors as an alternative to the static methods:

```php
<?php
use function Cline\Assert\{that, thatAll, thatNullOr};

// These are functionally identical to the static methods
that($value)->notEmpty()->integer();
thatNullOr($value)->string()->startsWith("Foo");
thatAll($values)->float();
```

Some developers prefer the functional style as it reads more naturally:

```php
<?php
use function Cline\Assert\{that, thatNullOr};

function register($username, $email, $phone = null)
{
    that($username)->notEmpty()->string()->minLength(3);
    that($email)->notEmpty()->email();
    thatNullOr($phone)->string()->regex('/^\+?[1-9]\d{1,14}$/');

    // Register user...
}
```

## Advantages of Chaining

1. **Less Repetition**: Specify the value once instead of repeating it for each assertion
2. **Readability**: Reads like natural language: "Assert that value is not empty and is string"
3. **Flexibility**: Mix and match assertions as needed
4. **Short-circuiting**: With `nullOr()`, chains exit early if value is null

## When to Use Static vs Chaining

**Use Static API (`Assertion::*`) when:**
- Performing a single assertion
- Assertions are scattered throughout a function
- Working with different values

**Use Chaining API (`Assert::that()`) when:**
- Validating one value against multiple rules
- Validating function parameters
- Building validators with many rules

```php
<?php
use Cline\Assert\Assertion;
use Cline\Assert\Assert;

function example($value, $anotherValue)
{
    // Good use of chaining - multiple rules for one value
    Assert::that($value)
        ->notEmpty()
        ->string()
        ->minLength(3);

    // Good use of static - single check
    Assertion::isArray($anotherValue);

    // Process...
}
```
