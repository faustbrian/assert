# Lazy Assertions

Lazy assertions collect multiple validation errors before throwing, allowing you to report all validation failures at once.

## Why Lazy Assertions?

Traditional assertions fail on the first error:

```php
// Stops at first failure
Assertion::notEmpty($name);     // ✗ Fails here
Assertion::email($email);       // Never checked
Assertion::integer($age);       // Never checked
```

Lazy assertions collect all errors:

```php
// Collects all failures
Assert::lazy()
    ->that($name, 'name')->notEmpty()
    ->that($email, 'email')->email()
    ->that($age, 'age')->integer()
    ->verifyNow(); // Throws with all errors
```

## Basic Usage

### Creating Lazy Assertions

```php
use Cline\Assert\Assert;

Assert::lazy()
    ->that($value, 'field_name')->assertion()
    ->that($value2, 'field_name2')->assertion()
    ->verifyNow();
```

### Simple Example

```php
Assert::lazy()
    ->that($email, 'email')->notEmpty()->email()
    ->that($name, 'name')->notEmpty()->minLength(2)
    ->that($age, 'age')->integer()->greaterOrEqualThan(18)
    ->verifyNow();
```

## Form Validation

### User Registration

```php
$errors = [];

try {
    Assert::lazy()
        ->that($data['username'] ?? null, 'username')
            ->notNull('Username is required')
            ->notEmpty('Username cannot be empty')
            ->minLength(3, 'Username must be at least 3 characters')
            ->maxLength(20, 'Username cannot exceed 20 characters')
        ->that($data['email'] ?? null, 'email')
            ->notNull('Email is required')
            ->notEmpty('Email cannot be empty')
            ->email('Invalid email format')
        ->that($data['password'] ?? null, 'password')
            ->notNull('Password is required')
            ->notEmpty('Password cannot be empty')
            ->minLength(8, 'Password must be at least 8 characters')
        ->that($data['age'] ?? null, 'age')
            ->notNull('Age is required')
            ->integer('Age must be a number')
            ->greaterOrEqualThan(18, 'You must be at least 18 years old')
        ->verifyNow();
} catch (LazyAssertionException $e) {
    foreach ($e->getErrorExceptions() as $error) {
        $errors[$error->getPropertyPath()] = $error->getMessage();
    }
}
```

### Profile Update

```php
Assert::lazy()
    ->that($profile['name'], 'name')
        ->notBlank('Name is required')
        ->maxLength(100, 'Name too long')
    ->that($profile['bio'] ?? null, 'bio')
        ->nullOr()->string()->maxLength(500, 'Bio too long')
    ->that($profile['website'] ?? null, 'website')
        ->nullOr()->url('Invalid website URL')
    ->verifyNow();
```

## API Request Validation

### JSON API Payload

```php
public function validateRequest(array $data): void
{
    Assert::lazy()
        ->that($data['action'] ?? null, 'action')
            ->notNull('Action is required')
            ->inArray(['create', 'update', 'delete'], 'Invalid action')
        ->that($data['resource_id'] ?? null, 'resource_id')
            ->notNull('Resource ID is required')
            ->uuid('Invalid resource ID format')
        ->that($data['timestamp'] ?? null, 'timestamp')
            ->notNull('Timestamp is required')
            ->integer('Timestamp must be a number')
            ->greaterThan(0, 'Invalid timestamp')
        ->verifyNow();
}
```

### Query Parameters

```php
Assert::lazy()
    ->that($params['page'] ?? null, 'page')
        ->nullOr()->integer()->greaterThan(0)
    ->that($params['limit'] ?? null, 'limit')
        ->nullOr()->integer()->between(1, 100)
    ->that($params['sort'] ?? null, 'sort')
        ->nullOr()->inArray(['asc', 'desc'])
    ->that($params['filter'] ?? null, 'filter')
        ->nullOr()->string()->notEmpty()
    ->verifyNow();
```

## Configuration Validation

### Application Config

```php
Assert::lazy()
    ->that($config['app_name'], 'app_name')
        ->notEmpty('App name is required')
        ->string()
    ->that($config['environment'], 'environment')
        ->inArray(['development', 'staging', 'production'])
    ->that($config['debug'], 'debug')
        ->boolean()
    ->that($config['timezone'], 'timezone')
        ->notEmpty()
        ->string()
    ->verifyNow();
```

### Database Config

```php
Assert::lazy()
    ->that($dbConfig['host'], 'database.host')
        ->notEmpty('Database host is required')
    ->that($dbConfig['port'], 'database.port')
        ->integer('Database port must be a number')
        ->between(1, 65535, 'Invalid port number')
    ->that($dbConfig['database'], 'database.name')
        ->notEmpty('Database name is required')
    ->that($dbConfig['username'], 'database.username')
        ->notEmpty('Database username is required')
    ->that($dbConfig['password'], 'database.password')
        ->notEmpty('Database password is required')
    ->verifyNow();
```

## tryAll() Mode

By default, lazy assertions stop validating a field after the first failure. Use `tryAll()` to validate all assertions even after failures:

### Default Behavior (Stop on First Error Per Field)

```php
Assert::lazy()
    ->that($age, 'age')
        ->integer()        // ✗ Fails here for "abc"
        ->greaterThan(0)   // Not checked
    ->verifyNow();
```

### tryAll() Mode (Check All Assertions)

```php
Assert::lazy()
    ->tryAll()
    ->that($age, 'age')
        ->integer()        // ✗ Fails
        ->greaterThan(0)   // ✗ Also checked and fails
    ->verifyNow(); // Reports both errors
```

### Practical Example

```php
Assert::lazy()
    ->tryAll()
    ->that($password, 'password')
        ->string()
        ->minLength(8)          // Check all length requirements
        ->maxLength(100)
        ->regex('/[A-Z]/')      // Check all complexity requirements
        ->regex('/[a-z]/')
        ->regex('/[0-9]/')
    ->verifyNow(); // Reports ALL password requirement failures
```

## Error Handling

### Catching Errors

```php
use Cline\Assert\LazyAssertionException;

try {
    Assert::lazy()
        ->that($email, 'email')->email()
        ->that($age, 'age')->integer()->greaterOrEqualThan(18)
        ->verifyNow();
} catch (LazyAssertionException $e) {
    // Handle all errors
    $errors = $e->getErrorExceptions();
    
    foreach ($errors as $error) {
        echo $error->getPropertyPath() . ': ' . $error->getMessage() . "\n";
    }
}
```

### Building Error Response

```php
function validateAndReturnErrors(array $data): ?array
{
    try {
        Assert::lazy()
            ->that($data['name'], 'name')->notEmpty()
            ->that($data['email'], 'email')->email()
            ->verifyNow();
        
        return null; // No errors
    } catch (LazyAssertionException $e) {
        $errors = [];
        foreach ($e->getErrorExceptions() as $error) {
            $errors[$error->getPropertyPath()] = $error->getMessage();
        }
        return $errors;
    }
}
```

### JSON API Error Response

```php
try {
    Assert::lazy()
        ->that($request['email'], 'email')->email()
        ->that($request['age'], 'age')->integer()
        ->verifyNow();
} catch (LazyAssertionException $e) {
    $errors = array_map(function($error) {
        return [
            'field' => $error->getPropertyPath(),
            'message' => $error->getMessage(),
            'code' => $error->getCode(),
        ];
    }, $e->getErrorExceptions());
    
    return response()->json(['errors' => $errors], 422);
}
```

## Nested Property Paths

Use dot notation for nested validation:

```php
Assert::lazy()
    ->that($user['email'], 'user.email')->email()
    ->that($user['address']['city'], 'user.address.city')->notEmpty()
    ->that($user['address']['zip'], 'user.address.zip')->regex('/^\d{5}$/')
    ->verifyNow();
```

## Best Practices

### Always Use Property Paths

```php
// ✗ No property paths
Assert::lazy()
    ->that($email)->email()
    ->that($age)->integer()
    ->verifyNow();

// ✓ With property paths
Assert::lazy()
    ->that($email, 'email')->email()
    ->that($age, 'age')->integer()
    ->verifyNow();
```

### Group Related Validations

```php
// Validate contact info together
Assert::lazy()
    // Contact fields
    ->that($data['email'], 'email')->notEmpty()->email()
    ->that($data['phone'], 'phone')->notEmpty()->e164()
    // Address fields
    ->that($data['street'], 'street')->notEmpty()
    ->that($data['city'], 'city')->notEmpty()
    ->that($data['zip'], 'zip')->notEmpty()->regex('/^\d{5}$/')
    ->verifyNow();
```

### Use Meaningful Property Paths

```php
// ✗ Generic
Assert::lazy()
    ->that($email, 'field1')->email()
    ->that($phone, 'field2')->e164()
    ->verifyNow();

// ✓ Descriptive
Assert::lazy()
    ->that($email, 'contact_email')->email()
    ->that($phone, 'contact_phone')->e164()
    ->verifyNow();
```

### Separate Validation from Business Logic

```php
// ✓ Clear separation
public function createUser(array $data): User
{
    // Validation first
    $this->validateUserData($data);
    
    // Business logic after
    return User::create($data);
}

private function validateUserData(array $data): void
{
    Assert::lazy()
        ->that($data['email'], 'email')->email()
        ->that($data['name'], 'name')->notEmpty()
        ->verifyNow();
}
```

## Performance Considerations

Lazy assertions are slightly slower than regular assertions because they catch and collect exceptions. Use them when:

- ✓ Validating user input (forms, APIs)
- ✓ Need to report all errors at once
- ✓ Better UX is important

Avoid when:

- ✗ Performance-critical code
- ✗ Internal validation (use regular assertions)
- ✗ Only one field to validate

## Next Steps

- **[Assertion Chains](assertion-chains.md)** - Fluent single-field validation
- **[Getting Started](getting-started.md)** - Review basic assertion concepts
- **[Custom Assertions](custom-assertions.md)** - Create custom validation rules
