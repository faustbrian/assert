# Getting Started

This library provides a comprehensive assertion library for PHP 8.4+, enabling robust validation and preconditions throughout your code.

## Installation

```bash
composer require cline/assert
```

## Basic Usage

The library provides three main ways to perform assertions:

### 1. Static Method Calls

Use `Assertion` class directly for immediate validation:

```php
use Cline\Assert\Assertion;

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

### 3. Lazy Assertions

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
use Cline\Assert\Assertion;
use Cline\Assert\AssertionFailedException;

try {
    Assertion::integer($value);
} catch (AssertionFailedException $e) {
    echo $e->getMessage();
    echo $e->getPropertyPath(); // Optional property path
    echo $e->getValue(); // The value that failed
    echo $e->getConstraints(); // Validation constraints
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

Custom messages support sprintf-style placeholders with consistent ordering:

- `%s` - The tested value as a string representation (e.g., `"foo"`, `42`, `true`)
- `%2$s`, `%3$s`, etc. - Additional assertion-specific values (e.g., min/max limits, allowed values)

```php
// Using placeholders for better context
Assertion::minLength($username, 3, 'Username must be at least %2$s characters. Got: %s');
// => "Username must be at least 3 characters. Got: ab"

Assertion::range($age, 18, 65, 'Age must be between %2$s and %3$s. Got: %s');
// => "Age must be between 18 and 65. Got: 17"

Assertion::inArray($status, ['active', 'pending'], 'Status must be one of: %2$s. Got: %s');
// => "Status must be one of: active, pending. Got: invalid"
```

## Property Paths

Use property paths to identify which field failed validation:

```php
Assertion::string($user->email, null, 'user.email');
Assertion::min($user->age, 18, null, 'user.age');

Assert::that($user->email, null, 'user.email')
    ->notEmpty()
    ->email();
```

## Next Steps

Explore specific assertion categories:

- **[String Assertions](string-assertions.md)** - String validation and checks
- **[Numeric Assertions](numeric-assertions.md)** - Number validation and comparisons
- **[Array Assertions](array-assertions.md)** - Array validation and operations
- **[Type Assertions](type-assertions.md)** - Type checking and validation
- **[Lazy Assertions](lazy-assertions.md)** - Batch validation patterns
- **[Assertion Chains](assertion-chains.md)** - Fluent API usage
