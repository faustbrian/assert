# Custom Assertions

Create custom validation rules using the `satisfy()` assertion and custom assertion classes.

## The satisfy() Assertion

The `satisfy()` assertion allows you to define custom validation logic using callbacks.

### Basic Usage

```php
use Cline\Assert\Assertion;

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
// Username validation
Assert::that($username)
    ->string()
    ->satisfy(function($v) {
        return preg_match('/^[a-z0-9_]{3,20}$/', $v) === 1;
    }, 'Username must be 3-20 characters, lowercase letters, numbers, and underscores only');
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
// Order total must match sum of items
Assertion::satisfy($order, function($order) {
    $calculatedTotal = array_sum(array_column($order['items'], 'price'));
    return abs($order['total'] - $calculatedTotal) < 0.01;
}, 'Order total does not match sum of items');
```

### File Size Validation

```php
Assertion::satisfy($uploadedFile, function($file) {
    return filesize($file) <= 5 * 1024 * 1024; // 5MB
}, 'File size must not exceed 5MB');
```

## Creating Reusable Custom Validators

### Standalone Functions

```php
function assertEvenNumber($value, ?string $message = null): void
{
    Assertion::satisfy($value, fn($v) => $v % 2 === 0, $message ?? 'Value must be even');
}

// Usage
assertEvenNumber($quantity);
assertEvenNumber($count, 'Count must be even');
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

// Usage
CustomAssertions::strongPassword($password);
CustomAssertions::slug($urlSlug);
CustomAssertions::hexColor($brandColor);
```

## Complex Custom Validations

### Credit Card Validation

```php
function validateCreditCard(string $number): bool
{
    // Remove spaces and dashes
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

### Social Security Number

```php
Assertion::satisfy($ssn, function($v) {
    // Remove separators
    $digits = preg_replace('/[^0-9]/', '', $v);
    
    // Must be 9 digits
    if (strlen($digits) !== 9) {
        return false;
    }
    
    // Cannot be all same digit
    if (preg_match('/^(\d)\1{8}$/', $digits)) {
        return false;
    }
    
    return true;
}, 'Invalid Social Security Number');
```

### Domain-Specific Validation

```php
// ISBN-13 validation
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

### Nested Validation

```php
Assertion::satisfy($order, function($order) {
    // Validate order structure
    if (!isset($order['items']) || !is_array($order['items'])) {
        return false;
    }
    
    // Validate each item
    foreach ($order['items'] as $item) {
        if (!isset($item['product_id']) || !isset($item['quantity'])) {
            return false;
        }
        
        if ($item['quantity'] < 1 || $item['quantity'] > 100) {
            return false;
        }
    }
    
    return true;
}, 'Invalid order structure or item data');
```

## Best Practices

### Keep Callbacks Simple

```php
// ✗ Too complex
Assertion::satisfy($data, function($d) {
    // 50 lines of validation logic...
}, 'Invalid data');

// ✓ Extract to function
function validateComplexData($data): bool {
    // 50 lines of validation logic...
}

Assertion::satisfy($data, 'validateComplexData', 'Invalid data');
```

### Provide Clear Messages

```php
// ✗ Generic message
Assertion::satisfy($value, $callback);

// ✓ Specific message
Assertion::satisfy($value, $callback, 'Value must be between 1 and 100 and divisible by 5');
```

### Type Check First

```php
// ✓ Type-safe
Assert::that($quantity)
    ->integer()
    ->satisfy(fn($v) => $v % 12 === 0, 'Quantity must be multiple of 12');

// ✗ No type check
Assertion::satisfy($quantity, fn($v) => $v % 12 === 0); // Might fail on non-numeric
```

### Reuse Common Patterns

```php
// ✗ Duplicate validation logic
Assertion::satisfy($username1, fn($v) => preg_match('/^[a-z0-9_]+$/', $v));
Assertion::satisfy($username2, fn($v) => preg_match('/^[a-z0-9_]+$/', $v));

// ✓ Reusable function
function assertUsername($value) {
    Assertion::satisfy($value, fn($v) => preg_match('/^[a-z0-9_]+$/', $v), 'Invalid username');
}
```

## Performance Considerations

### Avoid Expensive Operations

```php
// ✗ Expensive database query in validation
Assertion::satisfy($email, function($e) {
    return !User::where('email', $e)->exists(); // Queries DB
}, 'Email already exists');

// ✓ Separate validation from business logic
Assertion::email($email);
if (User::where('email', $email)->exists()) {
    throw new \Exception('Email already exists');
}
```

### Cache Complex Validations

```php
class Validator
{
    private static array $compiledPatterns = [];
    
    public static function assertPattern(string $value, string $pattern): void
    {
        if (!isset(self::$compiledPatterns[$pattern])) {
            self::$compiledPatterns[$pattern] = $pattern;
        }
        
        Assertion::satisfy($value, fn($v) => preg_match($pattern, $v), 'Pattern mismatch');
    }
}
```

## Testing Custom Assertions

```php
use PHPUnit\Framework\TestCase;

class CustomAssertionTest extends TestCase
{
    public function test_even_number_validation(): void
    {
        // Should pass
        assertEvenNumber(2);
        assertEvenNumber(100);
        
        // Should fail
        $this->expectException(AssertionFailedException::class);
        assertEvenNumber(3);
    }
    
    public function test_strong_password(): void
    {
        CustomAssertions::strongPassword('Abcd1234');
        
        $this->expectException(AssertionFailedException::class);
        CustomAssertions::strongPassword('weak');
    }
}
```

## Next Steps

- **[Lazy Assertions](lazy-assertions.md)** - Combine multiple custom validations
- **[Assertion Chains](assertion-chains.md)** - Chain custom rules with built-ins
- **[Getting Started](getting-started.md)** - Review core assertion concepts
