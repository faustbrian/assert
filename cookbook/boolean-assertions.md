# Boolean Assertions

Boolean assertions validate boolean values and truthiness.

## Available Assertions

### true()

Assert that a value is boolean true.

```php
use Cline\Assert\Assertion;

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

## Important: Strict Type Checking

These assertions check for **actual boolean values only**, not truthy/falsy values:

```php
// ✓ Pass
Assertion::true(true);
Assertion::false(false);
Assertion::boolean(true);
Assertion::boolean(false);

// ✗ Fail - These are NOT booleans
Assertion::true(1);           // integer, not boolean
Assertion::true('1');         // string, not boolean
Assertion::false(0);          // integer, not boolean
Assertion::false('');         // string, not boolean
Assertion::false(null);       // null, not boolean
```

## Chaining Boolean Assertions

Use `Assert::that()` for fluent boolean validation:

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

Assert::that($post->is_published)
    ->boolean()
    ->false('Post must not be published yet');
```

### Configuration Validation

```php
Assert::lazy()
    ->that($config['debug'], 'debug')->boolean()
    ->that($config['cache_enabled'], 'cache_enabled')->boolean()
    ->that($config['mail_enabled'], 'mail_enabled')->boolean()
    ->verifyNow();
```

### Access Control

```php
Assert::that($user->hasPermission('admin'))
    ->boolean('Permission check must return boolean')
    ->true('User must have admin permission');
```

### Form Validation

```php
Assert::that($form['agree_to_terms'])
    ->boolean()
    ->true('You must agree to the terms');
```

## Working with Truthy/Falsy Values

If you need to accept truthy/falsy values, convert them first:

```php
// Convert to boolean
$isActive = (bool) $value;
Assertion::boolean($isActive);

// Or use custom validation
Assertion::satisfy($value, function($v) {
    return in_array($v, [true, false, 1, 0, '1', '0'], true);
}, 'Value must be boolean-like');
```

## Database Boolean Fields

Many databases store booleans as integers (0/1) or strings:

```php
// ✗ Direct assertion fails
$row['is_active'] = 1; // from database
Assertion::true($row['is_active']); // Fails - 1 is not true

// ✓ Convert first
$isActive = (bool) $row['is_active'];
Assertion::boolean($isActive);

// ✓ Or check the integer
Assertion::integer($row['is_active']);
Assertion::inArray($row['is_active'], [0, 1]);
```

## API Response Validation

```php
// API returns boolean
Assert::that($response['success'])
    ->boolean()
    ->true('API request must be successful');

// API returns 0/1
Assert::that($response['success'])
    ->integer()
    ->same(1, 'API request failed');
```

## Best Practices

### Type Safety First

```php
// ✗ Implicit conversion
function setActive($value) {
    $this->isActive = $value;
}

// ✓ Explicit boolean check
function setActive(bool $value) {
    Assertion::boolean($value);
    $this->isActive = $value;
}
```

### Clear Error Messages

```php
// ✗ Generic message
Assertion::true($isVerified);

// ✓ Specific message
Assertion::true($isVerified, 'Email address must be verified before proceeding');
```

### Combine with Business Logic

```php
// Validate boolean type and business rule
Assert::that($user->email_verified)
    ->boolean('email_verified must be a boolean')
    ->true('User must verify email before accessing this feature');

Assert::that($payment->is_refunded)
    ->boolean()
    ->false('Cannot process refunded payment');
```

## Common Mistakes

### Confusing Truthy with True

```php
// ✗ This fails - 1 is not true
$value = 1;
Assertion::true($value);

// ✓ Convert first
$value = 1;
Assertion::true((bool) $value);

// ✓ Or check for truthy
Assertion::satisfy($value, fn($v) => (bool) $v === true);
```

### Boolean Strings

```php
// ✗ Strings are not booleans
Assertion::true('true');   // Fails
Assertion::false('false'); // Fails

// ✓ Convert first
Assertion::true(filter_var('true', FILTER_VALIDATE_BOOLEAN));
```

## Validation Patterns

### Checkbox Validation

```php
// HTML checkbox: checked = "on", unchecked = not present
$agreedToTerms = isset($_POST['agree']) && $_POST['agree'] === 'on';

Assert::that($agreedToTerms)
    ->boolean()
    ->true('You must agree to the terms');
```

### Toggle Validation

```php
function toggle(bool $current): bool
{
    Assertion::boolean($current);
    return !$current;
}
```

### Multiple Boolean Flags

```php
Assert::lazy()
    ->that($flags['create'], 'create')->boolean()->true()
    ->that($flags['read'], 'read')->boolean()->true()
    ->that($flags['update'], 'update')->boolean()
    ->that($flags['delete'], 'delete')->boolean()->false()
    ->verifyNow();
```

## Next Steps

- **[Type Assertions](type-assertions.md)** - Basic type checking including boolean()
- **[Comparison Assertions](comparison-assertions.md)** - Comparing boolean values
- **[Custom Assertions](custom-assertions.md)** - Create custom truthy/falsy validators
