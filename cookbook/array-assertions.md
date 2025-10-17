# Array Assertions

Array assertions validate arrays, collections, and array-accessible objects.

## Available Assertions

### isCountable()

Assert that a value is countable.

```php
use Cline\Assert\Assertion;

Assertion::isCountable($array);
Assertion::isCountable($collection, 'Value must be countable');
```

### count()

Assert that a countable has an exact number of elements.

```php
Assertion::count($array, 5);
Assertion::count($items, 3, 'Must have exactly 3 items');
```

### minCount()

Assert that a countable has at least a minimum number of elements.

```php
Assertion::minCount($array, 1);
Assertion::minCount($options, 2, 'Must provide at least 2 options');
```

### maxCount()

Assert that a countable has at most a maximum number of elements.

```php
Assertion::maxCount($array, 10);
Assertion::maxCount($tags, 5, 'Maximum 5 tags allowed');
```

### isArrayAccessible()

Assert that a value is an array or implements ArrayAccess.

```php
Assertion::isArrayAccessible($value);
Assertion::isArrayAccessible($collection, 'Value must be array accessible');
```

### isTraversable()

Assert that a value is traversable (array or Traversable).

```php
Assertion::isTraversable($value);
Assertion::isTraversable($collection, 'Value must be traversable');
```

### keyExists()

Assert that a key exists in an array.

```php
Assertion::keyExists($array, 'name');
Assertion::keyExists($config, 'database', 'Database configuration is required');
```

### keyNotExists()

Assert that a key does NOT exist in an array.

```php
Assertion::keyNotExists($array, 'legacy_field');
Assertion::keyNotExists($data, 'password', 'Password should not be included');
```

### keyIsset()

Assert that a key exists and is set (using isset()).

```php
Assertion::keyIsset($array, 'id');
Assertion::keyIsset($_POST, 'csrf_token', 'CSRF token is required');
```

### notEmptyKey()

Assert that a key exists and its value is not empty.

```php
Assertion::notEmptyKey($data, 'name');
Assertion::notEmptyKey($form, 'email', 'Email field cannot be empty');
```

### uniqueValues()

Assert that all values in an array are unique (strict equality).

```php
Assertion::uniqueValues($array);
Assertion::uniqueValues($ids, 'All IDs must be unique');
```

### inArray()

Assert that a value exists in an array of choices.

```php
Assertion::inArray($status, ['draft', 'published', 'archived']);
Assertion::inArray($role, $validRoles, 'Invalid role selected');
```

### notInArray()

Assert that a value does NOT exist in an array of choices.

```php
Assertion::notInArray($username, $bannedNames);
Assertion::notInArray($value, $blacklist, 'This value is not allowed');
```

### choice()

Alias for `inArray()` - assert that a value is in an array of choices.

```php
Assertion::choice($color, ['red', 'green', 'blue']);
Assertion::choice($size, ['S', 'M', 'L', 'XL'], 'Invalid size');
```

### choicesNotEmpty()

Assert that specific keys exist in an array and all have non-empty values.

```php
$requiredFields = ['name', 'email', 'password'];
Assertion::choicesNotEmpty($data, $requiredFields);
Assertion::choicesNotEmpty($form, $requiredFields, 'All required fields must be filled');
```

### eqArraySubset()

Assert that an array contains a subset.

```php
$expected = ['name' => 'John', 'active' => true];
Assertion::eqArraySubset($user, $expected);
```

## Chaining Array Assertions

Use `Assert::that()` for fluent array validation:

```php
use Cline\Assert\Assert;

Assert::that($array)
    ->isArray()
    ->notEmpty()
    ->minCount(1)
    ->maxCount(10);

Assert::that($status)
    ->string()
    ->inArray(['pending', 'approved', 'rejected']);
```

## Common Patterns

### Required Array Keys

```php
Assert::that($config)
    ->isArray()
    ->keyExists('host', 'host')
    ->keyExists('port', 'port')
    ->keyExists('database', 'database');
```

### Enum Validation

```php
$validStatuses = ['draft', 'published', 'archived'];

Assert::that($post['status'])
    ->notEmpty()
    ->inArray($validStatuses, 'Invalid post status');
```

### Form Validation

```php
Assert::lazy()
    ->that($form, 'form')->isArray()
    ->that($form, 'form')->keyExists('name')
    ->that($form, 'form')->keyExists('email')
    ->that($form, 'form')->notEmptyKey('name')
    ->that($form, 'form')->notEmptyKey('email')
    ->verifyNow();
```

### Collection Size Validation

```php
Assert::that($items)
    ->isArray()
    ->minCount(1, 'At least one item is required')
    ->maxCount(100, 'Maximum 100 items allowed');
```

### Unique Values Check

```php
Assert::that($userIds)
    ->isArray()
    ->notEmpty()
    ->uniqueValues('User IDs must be unique');
```

### Multiple Choice Validation

```php
$validCountries = ['US', 'CA', 'MX'];

foreach ($selectedCountries as $country) {
    Assert::that($country)
        ->string()
        ->inArray($validCountries, 'Invalid country code');
}
```

## Validating All Elements

Use the `all()` modifier to validate every element in an array:

```php
// Validate all elements are strings
Assert::thatAll($tags)->string();

// Validate all elements are positive integers
Assert::thatAll($quantities)
    ->integer()
    ->greaterThan(0);

// Validate all email addresses
Assert::thatAll($emailList)->email();
```

## Working with Nested Arrays

```php
// Validate nested structure
Assert::that($data)
    ->isArray()
    ->keyExists('user')
    ->keyExists('settings');

Assert::that($data['user'])
    ->isArray()
    ->notEmptyKey('id')
    ->notEmptyKey('name');

// Or use property paths
Assertion::keyExists($data, 'user', null, 'data.user');
Assertion::notEmptyKey($data['user'], 'name', null, 'data.user.name');
```

## Next Steps

- **[Type Assertions](type-assertions.md)** - Type checking including array type
- **[Null and Empty Assertions](null-empty-assertions.md)** - Empty array checks
- **[Lazy Assertions](lazy-assertions.md)** - Validate multiple array conditions
