# Type Assertions

Type assertions verify that values match expected PHP types.

## Available Assertions

### integer()

Assert that a value is a PHP integer.

```php
use Cline\Assert\Assertion;

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

### digit()

Assert that a value is a digit (0-9).

```php
Assertion::digit($singleDigit);
Assertion::digit($value, 'Value must be a single digit');
```

### integerish()

Assert that a value is an integer or can be cast to an integer.

```php
Assertion::integerish('123');  // Passes
Assertion::integerish(123);    // Passes
Assertion::integerish('123.0'); // Fails
```

### scalar()

Assert that a value is a PHP scalar (int, float, string, or bool).

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
Assertion::isResource($handle, 'Handle must be a resource');
```

### isCallable()

Assert that a value is callable.

```php
Assertion::isCallable($callback);
Assertion::isCallable($handler, 'Handler must be callable');

// Works with closures, function names, and callable arrays
Assertion::isCallable(fn() => true);
Assertion::isCallable('strlen');
Assertion::isCallable([$object, 'method']);
```

## Chaining Type Assertions

Use `Assert::that()` for fluent type validation:

```php
use Cline\Assert\Assert;

Assert::that($age)
    ->integer()
    ->greaterThan(0);

Assert::that($name)
    ->string()
    ->notEmpty();

Assert::that($price)
    ->float()
    ->greaterThan(0);
```

## Common Patterns

### Input Validation

```php
Assert::that($userId)
    ->integer('User ID must be an integer')
    ->greaterThan(0, 'User ID must be positive');

Assert::that($email)
    ->string('Email must be a string')
    ->notEmpty('Email is required')
    ->email('Invalid email format');
```

### Configuration Validation

```php
Assert::that($config)
    ->isArray('Config must be an array')
    ->keyExists('database')
    ->keyExists('cache');

Assert::that($config['port'])
    ->integer('Port must be an integer')
    ->between(1, 65535);
```

### Numeric Type Checking

```php
// Strict integer check
Assert::that($count)
    ->integer()
    ->greaterOrEqualThan(0);

// Flexible numeric check (accepts "123", 123, 123.0)
Assert::that($amount)
    ->numeric()
    ->greaterThan(0);

// Integer-like check (accepts "123" and 123, but not "123.45")
Assert::that($id)
    ->integerish()
    ->greaterThan(0);
```

### Callback Validation

```php
Assert::that($middleware)
    ->isCallable('Middleware must be callable');

Assert::that($transformer)
    ->isCallable()
    ->satisfy(function($cb) {
        return is_callable($cb) && !is_string($cb);
    }, 'Transformer must be a closure or callable array');
```

### Boolean Flag Validation

```php
Assert::that($isEnabled)
    ->boolean('isEnabled must be a boolean');

Assert::that($hasAccess)
    ->boolean()
    ->true('User must have access');
```

## Type Coercion vs Strict Types

### Strict Type Checking

```php
// Only accepts exact type
Assertion::integer(123);     // ✓ Pass
Assertion::integer('123');   // ✗ Fail
Assertion::integer(123.0);   // ✗ Fail
```

### Flexible Type Checking

```php
// Accepts numeric values
Assertion::numeric(123);     // ✓ Pass
Assertion::numeric('123');   // ✓ Pass
Assertion::numeric(123.45);  // ✓ Pass

// Accepts integer-like values
Assertion::integerish(123);      // ✓ Pass
Assertion::integerish('123');    // ✓ Pass
Assertion::integerish(123.0);    // ✗ Fail (float)
Assertion::integerish('123.0');  // ✗ Fail (string with decimal)
```

## Validating Multiple Types

For union types or alternative types, use custom logic:

```php
// Accept string or integer
if (!is_string($value) && !is_int($value)) {
    throw new \InvalidArgumentException('Value must be string or integer');
}

// Or use satisfy()
Assertion::satisfy($value, function($v) {
    return is_string($v) || is_int($v);
}, 'Value must be string or integer');
```

## Best Practices

### Be Specific

```php
// ✗ Too loose
Assertion::scalar($age);

// ✓ Specific
Assertion::integer($age);
```

### Chain Related Assertions

```php
// ✗ Separate assertions
Assertion::string($email);
Assertion::notEmpty($email);

// ✓ Chained assertions
Assert::that($email)
    ->string()
    ->notEmpty()
    ->email();
```

### Use Integerish for Flexible Input

```php
// When accepting form input that might be strings
Assert::that($_POST['quantity'])
    ->integerish()
    ->greaterThan(0);
```

## Next Steps

- **[Numeric Assertions](numeric-assertions.md)** - Number validation and comparisons
- **[String Assertions](string-assertions.md)** - String validation and checks
- **[Object Assertions](object-assertions.md)** - Object and class validation
- **[Array Assertions](array-assertions.md)** - Array validation
