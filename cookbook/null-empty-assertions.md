# Null and Empty Assertions

Assertions for validating null values, empty values, and blank strings.

## Available Assertions

### null()

Assert that a value is null.

```php
use Cline\Assert\Assertion;

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

Assert that a value is not blank (not empty string or whitespace-only string).

```php
Assertion::notBlank($value);
Assertion::notBlank($description, 'Description cannot be blank');
```

## Understanding Empty Checks

### What `empty()` Considers Empty

```php
// These are all empty
Assertion::noContent('');       // empty string
Assertion::noContent(0);        // integer zero
Assertion::noContent(0.0);      // float zero  
Assertion::noContent('0');      // string zero
Assertion::noContent(null);     // null
Assertion::noContent(false);    // boolean false
Assertion::noContent([]);       // empty array
```

### Not Empty Examples

```php
// These are NOT empty
Assertion::notEmpty('hello');   // non-empty string
Assertion::notEmpty(1);         // non-zero integer
Assertion::notEmpty(true);      // boolean true
Assertion::notEmpty([1, 2]);    // non-empty array
Assertion::notEmpty(' ');       // whitespace (use notBlank for this)
```

## Null vs Empty vs Blank

### null() - Strict Null Check

```php
Assertion::null(null);      // ✓ Pass
Assertion::null('');        // ✗ Fail - empty string, not null
Assertion::null(0);         // ✗ Fail - zero, not null
Assertion::null(false);     // ✗ Fail - boolean, not null
```

### noContent() - PHP Empty Check

```php
Assertion::noContent(null);     // ✓ Pass
Assertion::noContent('');       // ✓ Pass
Assertion::noContent(0);        // ✓ Pass
Assertion::noContent(false);    // ✓ Pass
Assertion::noContent([]);       // ✓ Pass
```

### notBlank() - Non-Whitespace String

```php
Assertion::notBlank('hello');   // ✓ Pass
Assertion::notBlank(' ');       // ✗ Fail - whitespace only
Assertion::notBlank('');        // ✗ Fail - empty string
Assertion::notBlank("\n\t");    // ✗ Fail - whitespace only
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
// Allow null, but if provided must be valid
if ($middleName !== null) {
    Assert::that($middleName)
        ->string()
        ->notEmpty()
        ->minLength(1);
}
```

### Form Input Validation

```php
Assert::lazy()
    ->that($form['name'] ?? null, 'name')->notNull()->notBlank()
    ->that($form['email'] ?? null, 'email')->notNull()->notBlank()->email()
    ->that($form['phone'] ?? null, 'phone')->notNull()->notEmpty()
    ->verifyNow();
```

### Database Record Validation

```php
Assert::that($user)
    ->notNull('User not found')
    ->isObject()
    ->isInstanceOf(User::class);

Assert::that($user->email)
    ->notEmpty('User email is missing');
```

### Array Validation

```php
Assert::that($items)
    ->notEmpty('Items array cannot be empty')
    ->isArray();

Assert::that($config)
    ->notEmpty('Configuration is required')
    ->keyExists('database');
```

## Working with Nullable Values

### Using nullOr()

```php
// Allow null OR validate if not null
Assert::thatNullOr($middleName)
    ->string()
    ->minLength(2);

// Equivalent to:
if ($middleName !== null) {
    Assert::that($middleName)
        ->string()
        ->minLength(2);
}
```

### Optional with Default

```php
$timeout = $config['timeout'] ?? null;

Assert::thatNullOr($timeout)
    ->integer()
    ->greaterThan(0);
```

## String-Specific Empty Checks

### Whitespace Handling

```php
// notEmpty allows whitespace
Assertion::notEmpty(' ');      // ✓ Pass

// notBlank rejects whitespace
Assertion::notBlank(' ');      // ✗ Fail
Assertion::notBlank('  hello  '); // ✓ Pass
```

### Trimming Before Validation

```php
$name = trim($input['name'] ?? '');

Assert::that($name)
    ->notEmpty('Name is required')
    ->string()
    ->minLength(2);
```

## Best Practices

### Check Null First

```php
// ✓ Clear intention
Assert::that($user)
    ->notNull('User not found')
    ->isObject();

// ✗ Confusing error
Assert::that($user)
    ->isObject(); // "null is not an object"
```

### Use notBlank for User Input

```php
// ✗ Allows whitespace
Assert::that($comment)
    ->notEmpty();

// ✓ Rejects whitespace-only
Assert::that($comment)
    ->notBlank('Comment cannot be empty');
```

### Combine with Type Checks

```php
// ✓ Type-safe
Assert::that($name)
    ->notNull()
    ->string()
    ->notBlank();

// ✗ Could fail with wrong type
Assert::that($name)
    ->notBlank(); // Fails if $name is not a string
```

## Common Mistakes

### Confusing Empty and Null

```php
// ✗ Empty string is not null
$value = '';
Assertion::null($value); // Fails

// ✓ Check for empty
Assertion::noContent($value);
```

### Zero Values

```php
// ✗ Zero is considered empty
$count = 0;
Assertion::notEmpty($count); // Fails

// ✓ Check type and value separately
Assert::that($count)
    ->integer()
    ->greaterOrEqualThan(0);
```

### Boolean False

```php
// ✗ False is considered empty
$flag = false;
Assertion::notEmpty($flag); // Fails

// ✓ Check boolean explicitly
Assert::that($flag)
    ->boolean()
    ->false();
```

## Database NULL Handling

```php
// Database returns NULL for missing values
$result = $db->query('SELECT optional_field FROM users WHERE id = ?', [$id]);

Assert::thatNullOr($result['optional_field'])
    ->string()
    ->notBlank();
```

## API Response Validation

```php
// Handle missing/null API fields
$data = json_decode($response, true);

Assert::that($data['user_id'] ?? null)
    ->notNull('Missing required field: user_id')
    ->integer();

Assert::thatNullOr($data['middle_name'] ?? null)
    ->string()
    ->notBlank();
```

## Validation Patterns

### Required vs Optional

```php
// Required field
Assert::that($email)
    ->notNull('Email is required')
    ->notBlank('Email cannot be empty');

// Optional field
Assert::thatNullOr($phoneNumber)
    ->string()
    ->regex('/^\d{10}$/');
```

### Empty Array Check

```php
// Not empty array
Assert::that($items)
    ->isArray()
    ->notEmpty('Must have at least one item')
    ->minCount(1);

// Empty array is valid
Assert::that($items)
    ->isArray(); // Can be empty
```

## Next Steps

- **[Type Assertions](type-assertions.md)** - Type checking before null/empty checks
- **[String Assertions](string-assertions.md)** - String validation including blank checks
- **[Assertion Chains](assertion-chains.md)** - Using nullOr() modifier
