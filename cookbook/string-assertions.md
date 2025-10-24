# String Assertions

String assertions validate and check string values for various conditions including length, patterns, and content.

## Available Assertions

### regex()

Assert that a string matches a regular expression pattern.

```php
use Cline\Assert\Assertion;

Assertion::regex($value, '/^[A-Z][a-z]+$/');
Assertion::regex($phoneNumber, '/^\d{3}-\d{3}-\d{4}$/');

// With custom message
Assertion::regex($code, '/^[A-Z]{3}\d{3}$/', 'Code must be 3 letters followed by 3 digits');
```

### notRegex()

Assert that a string does NOT match a regular expression pattern.

```php
Assertion::notRegex($username, '/[^a-zA-Z0-9_]/', 'Username contains invalid characters');
```

### length()

Assert that a string has an exact length.

```php
Assertion::length($zipCode, 5);
Assertion::length($countryCode, 2, 'Country code must be exactly 2 characters');

// With encoding
Assertion::length($value, 10, null, null, 'utf8');
```

### minLength()

Assert that a string is at least a minimum number of characters long.

```php
Assertion::minLength($password, 8);
Assertion::minLength($username, 3, 'Username must be at least 3 characters');
```

### maxLength()

Assert that a string is no longer than a maximum number of characters.

```php
Assertion::maxLength($username, 20);
Assertion::maxLength($title, 100, 'Title cannot exceed 100 characters');
```

### betweenLength()

Assert that a string length is within a range.

```php
Assertion::betweenLength($password, 8, 100);
Assertion::betweenLength($name, 2, 50, 'Name must be between 2 and 50 characters');
```

### startsWith()

Assert that a string starts with a specific substring.

```php
Assertion::startsWith($url, 'https://');
Assertion::startsWith($phoneNumber, '+1', 'Phone number must start with country code +1');
```

### endsWith()

Assert that a string ends with a specific substring.

```php
Assertion::endsWith($filename, '.pdf');
Assertion::endsWith($email, '@example.com', 'Email must be from example.com domain');
```

### contains()

Assert that a string contains a specific substring.

```php
Assertion::contains($content, 'keyword');
Assertion::contains($url, '://secure.', 'URL must contain secure subdomain');
```

### notContains()

Assert that a string does NOT contain a specific substring.

```php
Assertion::notContains($password, $username, 'Password cannot contain username');
Assertion::notContains($content, '<script', 'Content cannot contain script tags');
```

### alnum()

Assert that a string is alphanumeric (starts with letter, contains only letters and numbers).

```php
Assertion::alnum($identifier);
Assertion::alnum($code, 'Code must be alphanumeric');
```

## Chaining String Assertions

Use `Assert::that()` for fluent string validation:

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

## Encoding Support

String assertions support different character encodings:

```php
// UTF-8 (default)
Assertion::length($japanese, 5, null, null, 'utf8');
Assertion::minLength($text, 10, null, null, 'utf8');

// Other encodings
Assertion::length($text, 20, null, null, 'ISO-8859-1');
```

## Common Patterns

### Email Validation Prefix

```php
Assert::that($email)
    ->string()
    ->notEmpty()
    ->contains('@')
    ->email(); // See validation-assertions.md
```

### URL Validation

```php
Assert::that($url)
    ->string()
    ->notEmpty()
    ->startsWith('https://')
    ->url(); // See validation-assertions.md
```

### Username Validation

```php
Assert::that($username)
    ->string()
    ->betweenLength(3, 20)
    ->regex('/^[a-zA-Z0-9_]+$/', 'Username can only contain letters, numbers, and underscores');
```

### Password Strength

```php
Assert::that($password)
    ->string()
    ->minLength(8)
    ->regex('/[A-Z]/', 'Password must contain at least one uppercase letter')
    ->regex('/[a-z]/', 'Password must contain at least one lowercase letter')
    ->regex('/[0-9]/', 'Password must contain at least one number')
    ->notContains($username, 'Password cannot contain username');
```

## Next Steps

- **[Validation Assertions](validation-assertions.md)** - Email, URL, UUID, and more
- **[Type Assertions](type-assertions.md)** - Type checking including string type
- **[Assertion Chains](assertion-chains.md)** - Learn more about fluent API
