# Comparison Assertions

Comparison assertions validate equality and identity between values.

## Available Assertions

### eq()

Assert that two values are equal using loose comparison (==).

```php
use Cline\Assert\Assertion;

Assertion::eq($actual, $expected);
Assertion::eq(123, '123');  // Passes (loose comparison)
Assertion::eq($result, 42, 'Result must equal 42');
```

### same()

Assert that two values are identical using strict comparison (===).

```php
Assertion::same($actual, $expected);
Assertion::same(123, 123);   // Passes
Assertion::same(123, '123'); // Fails (different types)
Assertion::same($result, 42, 'Result must be exactly 42');
```

### notEq()

Assert that two values are NOT equal using loose comparison (==).

```php
Assertion::notEq($actual, $unwanted);
Assertion::notEq($status, 'deleted', 'Status cannot be deleted');
```

### notSame()

Assert that two values are NOT identical using strict comparison (===).

```php
Assertion::notSame($actual, $unwanted);
Assertion::notSame($newPassword, $oldPassword, 'New password must be different');
```

## Loose vs Strict Comparison

### Loose Comparison (eq/notEq)

Uses `==` operator - performs type coercion:

```php
Assertion::eq(123, '123');     // ✓ Pass
Assertion::eq(1, true);        // ✓ Pass
Assertion::eq(0, false);       // ✓ Pass
Assertion::eq(null, '');       // ✓ Pass (in some contexts)
Assertion::eq([], false);      // ✓ Pass
```

### Strict Comparison (same/notSame)

Uses `===` operator - no type coercion:

```php
Assertion::same(123, '123');   // ✗ Fail
Assertion::same(1, true);      // ✗ Fail
Assertion::same(0, false);     // ✗ Fail
Assertion::same(null, '');     // ✗ Fail
Assertion::same([], false);    // ✗ Fail
```

## Chaining Comparison Assertions

Use `Assert::that()` for fluent comparison validation:

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
// Strict type checking
Assert::that($response->status)
    ->integer()
    ->same(200, 'Expected HTTP 200 status');

// Loose type checking (accepts "success" or any truthy value)
Assert::that($result)
    ->eq('success');
```

### State Validation

```php
Assert::that($order->status)
    ->string()
    ->notEq('cancelled', 'Order is cancelled')
    ->notEq('refunded', 'Order is refunded');
```

### Enum-like Validation

```php
Assert::that($userRole)
    ->string()
    ->inArray(['admin', 'user', 'guest']);

// Or with strict identity
Assert::that($status)
    ->same('active')
    ->notSame('deleted');
```

### Preventing Duplicate Values

```php
Assert::that($newEmail)
    ->email()
    ->notSame($currentEmail, 'New email must be different from current email');

Assert::that($newPassword)
    ->string()
    ->minLength(8)
    ->notSame($oldPassword, 'New password cannot be the same as old password');
```

### Computed Result Validation

```php
$expected = calculateExpectedTotal($items);

Assert::that($actualTotal)
    ->float()
    ->same($expected, 'Total mismatch');
```

### API Response Validation

```php
Assert::that($response['status'])
    ->same('success', 'API request failed');

Assert::that($response['code'])
    ->integer()
    ->same(200, 'Unexpected response code');
```

## When to Use Which

### Use eq() when:
- Comparing form input (strings) with expected values
- Type flexibility is acceptable
- Working with legacy code that mixes types

### Use same() when:
- Type safety is important
- Comparing computed values
- Validating configuration values
- Checking API responses
- Working with enums or constants

### Use notEq() when:
- Preventing unwanted states with type flexibility
- Blacklisting values

### Use notSame() when:
- Ensuring different passwords or tokens
- Type-safe blacklisting
- Preventing duplicate strict values

## Comparison with Other Assertions

### vs Numeric Assertions

```php
// Comparison
Assertion::same($age, 18);           // Exact match

// Numeric range
Assertion::greaterOrEqualThan($age, 18);  // Range check
Assertion::between($age, 18, 65);         // Bounded range
```

### vs Choice Assertions

```php
// Single value
Assertion::same($role, 'admin');

// Multiple allowed values
Assertion::inArray($role, ['admin', 'moderator', 'user']);
```

## Best Practices

### Prefer Strict Comparison

```php
// ✗ Loose comparison can hide bugs
Assertion::eq($count, '0');

// ✓ Strict comparison catches type issues
Assertion::same($count, 0);
```

### Combine with Type Checks

```php
// ✗ No type safety
Assertion::same($value, 42);

// ✓ Type-safe comparison
Assert::that($value)
    ->integer()
    ->same(42);
```

### Use Clear Error Messages

```php
// ✗ Generic message
Assertion::same($status, 'active');

// ✓ Specific message
Assertion::same($status, 'active', 'User account must be active');
```

## Next Steps

- **[Numeric Assertions](numeric-assertions.md)** - Range and boundary comparisons
- **[Type Assertions](type-assertions.md)** - Type validation before comparison
- **[Array Assertions](array-assertions.md)** - Array choice and subset comparisons
