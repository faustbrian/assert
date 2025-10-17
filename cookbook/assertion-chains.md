# Assertion Chains

Assertion chains provide a fluent interface for validating values with multiple assertions.

## Basic Chain Usage

Instead of multiple static calls:

```php
// Traditional approach
Assertion::string($email);
Assertion::notEmpty($email);
Assertion::email($email);
```

Use a fluent chain:

```php
// Chain approach
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
// If $middleName is null, all assertions are skipped
Assert::thatNullOr($middleName)
    ->string()
    ->minLength(2)
    ->maxLength(50);

// Equivalent to:
if ($middleName !== null) {
    Assert::that($middleName)
        ->string()
        ->minLength(2)
        ->maxLength(50);
}
```

### all() - Validate Array Elements

Validate every element in an array:

```php
// Validate all emails in array
Assert::thatAll($emailList)
    ->email();

// Validate all IDs
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
    ->regex('/^[a-z0-9_]+$/', 'Username can only contain lowercase letters, numbers, and underscores');
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

### URL Validation

```php
Assert::that($website)
    ->string()
    ->notEmpty()
    ->url('Invalid URL')
    ->startsWith('https://', 'Only HTTPS URLs allowed');
```

### Password Validation

```php
Assert::that($password)
    ->string()
    ->minLength(8, 'Password must be at least 8 characters')
    ->maxLength(100, 'Password too long')
    ->regex('/[A-Z]/', 'Password must contain uppercase letter')
    ->regex('/[a-z]/', 'Password must contain lowercase letter')
    ->regex('/[0-9]/', 'Password must contain number');
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
    ->minCount(1, 'At least one item required')
    ->maxCount(100, 'Maximum 100 items allowed');
```

## Using nullOr()

### Optional Fields

```php
// Required field
Assert::that($email)
    ->notEmpty()
    ->email();

// Optional field (can be null)
Assert::thatNullOr($phoneNumber)
    ->string()
    ->e164('Invalid phone format');
```

### Configuration Values

```php
Assert::thatNullOr($config['timeout'])
    ->integer()
    ->greaterThan(0)
    ->lessThan(3600);

Assert::thatNullOr($config['debug'])
    ->boolean();
```

### Form Input

```php
// Required fields
Assert::that($form['email'])
    ->notEmpty()
    ->email();

// Optional fields
Assert::thatNullOr($form['middle_name'])
    ->string()
    ->notEmpty();  // If provided, cannot be empty string

Assert::thatNullOr($form['company_website'])
    ->url();
```

## Using all()

### Validate Array of Values

```php
// All emails must be valid
Assert::thatAll($recipientEmails)
    ->email('All recipients must have valid email addresses');

// All quantities must be positive
Assert::thatAll($quantities)
    ->integer()
    ->greaterThan(0, 'All quantities must be positive');
```

### Complex Array Validation

```php
// Validate array of IDs
Assert::that($userIds)
    ->isArray()
    ->notEmpty()
    ->uniqueValues();

Assert::thatAll($userIds)
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

## Combining Modifiers

### nullOr() with all()

```php
// Array can be null, but if provided, all elements must be valid
Assert::thatNullOr($tags)
    ->isArray();

if ($tags !== null) {
    Assert::thatAll($tags)
        ->string()
        ->notEmpty();
}
```

### Multiple Chains

```php
// Validate related fields
Assert::that($startDate)
    ->notEmpty()
    ->date('Y-m-d');

Assert::that($endDate)
    ->notEmpty()
    ->date('Y-m-d');

// Additional logic check
assert(
    strtotime($endDate) >= strtotime($startDate),
    'End date must be after start date'
);
```

## Error Messages

### Default Messages

```php
// Uses default error messages
Assert::that($email)
    ->notEmpty()  // "Value is required"
    ->email();    // "Value is not a valid email"
```

### Custom Messages per Assertion

```php
// Custom message for each assertion
Assert::that($password)
    ->notEmpty('Password is required')
    ->minLength(8, 'Password must be at least 8 characters')
    ->regex('/[A-Z]/', 'Password must contain an uppercase letter');
```

### Default Message for Chain

```php
// All assertions use this message if they fail
Assert::that($username, 'Username is invalid')
    ->notEmpty()
    ->minLength(3)
    ->maxLength(20);
```

## Best Practices

### Order Assertions Logically

```php
// ✓ Good order: type → null/empty → format → constraints
Assert::that($email)
    ->string()           // 1. Type check first
    ->notEmpty()         // 2. Then null/empty
    ->email()            // 3. Then format
    ->maxLength(255);    // 4. Finally constraints

// ✗ Bad order
Assert::that($email)
    ->email()            // Fails if not string
    ->string();          // Redundant after email check
```

### Fail Fast with Type Checks

```php
// ✓ Type check prevents confusing errors
Assert::that($count)
    ->integer()          // Ensures numeric operations work
    ->greaterThan(0);

// ✗ No type check
Assert::that($count)
    ->greaterThan(0);    // Confusing error if $count is string
```

### Use Specific Assertions

```php
// ✗ Too generic
Assert::that($email)
    ->string()
    ->notEmpty();

// ✓ Specific validation
Assert::that($email)
    ->string()
    ->notEmpty()
    ->email();
```

### Group Related Validations

```php
// ✓ Clear grouping
// Validate type and emptiness
Assert::that($name)
    ->string()
    ->notEmpty()
    // Validate format
    ->minLength(2)
    ->maxLength(100)
    ->regex('/^[\p{L}\s]+$/u', 'Name can only contain letters and spaces');
```

## Common Mistakes

### Redundant Checks

```php
// ✗ email() already checks string
Assert::that($email)
    ->string()
    ->email();

// ✓ Just check email
Assert::that($email)
    ->email();  // email() includes string check
```

### Wrong Order

```php
// ✗ Length check before type check
Assert::that($name)
    ->minLength(3)  // May fail with wrong type
    ->string();

// ✓ Type check first
Assert::that($name)
    ->string()
    ->minLength(3);
```

### Confusing nullOr() Usage

```php
// ✗ notEmpty after nullOr is confusing
Assert::thatNullOr($value)
    ->notEmpty();  // If null, skipped; if empty string, fails

// ✓ Clear intention
Assert::that($value)
    ->notNull()
    ->notEmpty();
```

## Advanced Patterns

### Conditional Validation

```php
$user = Assert::that($userId)
    ->integer()
    ->greaterThan(0);

// Additional validation based on context
if ($requireActive) {
    Assert::that($user->status)
        ->same('active');
}
```

### Validation with Side Effects

```php
$sanitized = trim($input);

Assert::that($sanitized)
    ->string()
    ->notEmpty()
    ->minLength(3);
```

### Complex Business Rules

```php
Assert::that($order->total)
    ->float()
    ->greaterThan(0)
    ->satisfy(function($total) use ($order) {
        $calculatedTotal = array_sum(array_column($order->items, 'price'));
        return abs($total - $calculatedTotal) < 0.01;
    }, 'Order total must match sum of item prices');
```

## Next Steps

- **[Lazy Assertions](lazy-assertions.md)** - Validate multiple fields together
- **[Custom Assertions](custom-assertions.md)** - Add custom validation rules
- **[Getting Started](getting-started.md)** - Review core concepts
