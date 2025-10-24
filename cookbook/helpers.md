# NullOr and All Helpers

The Assert library provides two powerful helper patterns that automatically generate variants of every assertion: `nullOr*` for handling nullable values, and `all*` for validating arrays of values.

## NullOr Helper

The `nullOr*` helper checks if a value is null OR satisfies the assertion. This is particularly useful for optional parameters or nullable fields.

### Basic Usage

```php
<?php
use Cline\Assert\Assertion;

Assertion::nullOrMax(null, 42); // success - null is allowed
Assertion::nullOrMax(1, 42);    // success - 1 is less than 42
Assertion::nullOrMax(1337, 42); // exception - 1337 exceeds maximum
```

### Function Arguments

```php
<?php
use Cline\Assert\Assertion;

function updateUser($userId, $email = null, $age = null)
{
    Assertion::integer($userId);
    Assertion::nullOrEmail($email);
    Assertion::nullOrInteger($age);

    if ($age !== null) {
        Assertion::range($age, 0, 120);
    }

    // Update user...
}
```

In this example, `$email` and `$age` are optional parameters that can be null. The `nullOrEmail()` and `nullOrInteger()` helpers validate these values only when they're not null.

### Available on Every Assertion

The `nullOr*` pattern works with ANY assertion in the library:

```php
Assertion::nullOrString($value);
Assertion::nullOrInteger($value);
Assertion::nullOrUrl($value);
Assertion::nullOrEmail($value);
Assertion::nullOrUuid($value);
Assertion::nullOrBetween($value, 10, 100);
Assertion::nullOrMinLength($value, 5);
Assertion::nullOrFile($value);
// ...and so on for every assertion
```

## All Helper

The `all*` helper checks if ALL values in an array satisfy the assertion. It will throw an exception if the assertion fails for any single value.

### Basic Usage

```php
<?php
use Cline\Assert\Assertion;

// All items are stdClass instances - success
Assertion::allIsInstanceOf([new \stdClass, new \stdClass], 'stdClass');

// One item is not a PDO instance - exception
Assertion::allIsInstanceOf([new \stdClass, new \stdClass], 'PDO');
```

### Validating Collections

```php
<?php
use Cline\Assert\Assertion;

function processUsers(array $users)
{
    Assertion::allIsArray($users);
    Assertion::allKeyExists($users, 'id');
    Assertion::allKeyExists($users, 'email');

    foreach ($users as $user) {
        // Process each user knowing they have required structure...
    }
}
```

### Type Validation

```php
<?php
use Cline\Assert\Assertion;

function calculateTotal(array $prices)
{
    Assertion::allFloat($prices);
    Assertion::allGreaterOrEqualThan($prices, 0);

    return array_sum($prices);
}

calculateTotal([10.5, 20.0, 5.99]); // success
calculateTotal([10.5, -20.0, 5.99]); // exception - negative value
calculateTotal([10.5, "20.0", 5.99]); // exception - string instead of float
```

### String Validation

```php
<?php
use Cline\Assert\Assertion;

function validateEmails(array $emails)
{
    Assertion::allEmail($emails);

    // All emails are valid format...
}
```

### Available on Every Assertion

Like `nullOr*`, the `all*` pattern works with ANY assertion:

```php
Assertion::allString($values);
Assertion::allInteger($values);
Assertion::allUrl($values);
Assertion::allEmail($values);
Assertion::allUuid($values);
Assertion::allBetween($values, 10, 100);
Assertion::allMinLength($values, 5);
Assertion::allFile($values);
// ...and so on for every assertion
```

## Combining Helpers

You can combine both helpers for powerful validation patterns:

```php
<?php
use Cline\Assert\Assertion;

function processOptionalTags($tags = null)
{
    // $tags can be null, OR if present, all items must be strings
    Assertion::nullOrIsArray($tags);

    if ($tags !== null) {
        Assertion::allString($tags);
        Assertion::allNotEmpty($tags);
    }

    // Process tags...
}
```

Note: While there's no direct `allNullOr*` helper, you can achieve this by combining both patterns as shown above.

## Performance Considerations

### NullOr Pattern

The `nullOr*` helpers perform a simple null check first, then delegate to the underlying assertion. This means they have minimal overhead:

```php
// These are equivalent in performance:
if ($value !== null) {
    Assertion::string($value);
}

Assertion::nullOrString($value);
```

### All Pattern

The `all*` helpers iterate through the entire array and apply the assertion to each element. The assertion stops at the first failure:

```php
// Checks all 1000 items if they're all valid
Assertion::allInteger(range(1, 1000));

// Stops immediately at index 0 (string '1' is not an integer)
Assertion::allInteger(['1', 2, 3, 4, 5]);
```

For large collections, consider whether you need to validate every item or just a sample, depending on your use case.
