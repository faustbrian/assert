# Expect API

The `expect()` API provides a Jest/Pest-style fluent interface for assertions, ideal for testing and validation with a natural, readable syntax.

## Installation

```php
use function Cline\Assert\expect;
```

## Core Expectations

### Value Equality

```php
// Strict equality (===)
expect(42)->toBe(42);
expect('hello')->toBe('hello');
expect(null)->toBe(null);

// Alias for toBe()
expect($result)->toEqual(42);
```

### Null Checks

```php
expect(null)->toBeNull();
expect($value)->not->toBeNull();
```

### Boolean Checks

```php
// Strict boolean checks
expect(true)->toBeTrue();
expect(false)->toBeFalse();

// Truthy/Falsy (type coercion)
expect(1)->toBeTruthy();
expect('yes')->toBeTruthy();
expect([1])->toBeTruthy();

expect(0)->toBeFalsy();
expect('')->toBeFalsy();
expect([])->toBeFalsy();
```

### Empty Checks

```php
expect('')->toBeEmpty();
expect([])->toBeEmpty();
expect(0)->toBeEmpty();

expect('hello')->not->toBeEmpty();
```

## Type Expectations

```php
// Scalar types
expect($value)->toBeString();
expect($count)->toBeInt();
expect($price)->toBeFloat();
expect($active)->toBeBool();

// Composite types
expect($items)->toBeArray();
expect($user)->toBeObject();

// Callables
expect($callback)->toBeCallable();
expect([$obj, 'method'])->toBeCallable();

// Iterables & Countables
expect($collection)->toBeIterable();
expect($list)->toBeCountable();

// Special types
expect($number)->toBeNumeric();
expect('hello')->toBeScalar();
expect($handle)->toBeResource();
```

## Numeric Comparisons

```php
// Greater than
expect(10)->toBeGreaterThan(5);
expect(10)->toBeGreaterThanOrEqual(10);

// Less than
expect(5)->toBeLessThan(10);
expect(5)->toBeLessThanOrEqual(5);

// Range checks
expect(5)->toBeBetween(1, 10);
expect(1)->toBeBetween(1, 10); // Inclusive

// Edge cases
expect(INF)->toBeInfinite();
expect(-INF)->toBeInfinite();
expect(NAN)->toBeNan();

// Equality with tolerance
expect(3.14159)->toEqualWithDelta(3.14, 0.01);
expect(100)->toEqualWithDelta(99, 1.0);
```

## String Expectations

```php
// Pattern matching
expect('hello world')->toStartWith('hello');
expect('hello world')->toEndWith('world');
expect('hello world')->toContain('lo wo');

// Regex matching
expect('test@example.com')->toMatch('/^.+@.+\..+$/');
expect('hello123')->toMatch('/^[a-z]+\d+$/');

// Length checks
expect('hello')->toHaveLength(5);
expect('')->toHaveLength(0);

// Character types
expect('abc')->toBeAlpha();
expect('abc123')->toBeAlphaNumeric();
expect('123')->toBeDigits();

// Case styles
expect('hello_world')->toBeSnakeCase();
expect('hello-world')->toBeKebabCase();
expect('helloWorld')->toBeCamelCase();
expect('HelloWorld')->toBeStudlyCase();
expect('HELLO')->toBeUppercase();
expect('hello')->toBeLowercase();
```

## Collection Expectations

```php
// Count checks
expect([1, 2, 3])->toHaveCount(3);
expect([])->toHaveCount(0);
expect([1, 2])->toHaveSameSize([3, 4]);

// Key existence
expect(['name' => 'John'])->toHaveKey('name');
expect(['a' => 1, 'b' => 2])->toHaveKey('b');
expect(['name' => 'John', 'email' => 'j@ex.com'])->toHaveKeys(['name', 'email']);

// Value membership
expect([1, 2, 3])->toContain(2);
expect(['a', 'b', 'c'])->toContain('b');
expect(2)->toBeIn([1, 2, 3]);

// Deep equality
expect([['a' => 1], ['b' => 2]])->toContainEqual(['a' => 1]);

// Type checking
$objects = [new stdClass(), new stdClass()];
expect($objects)->toContainOnlyInstancesOf(stdClass::class);

// Array matching
expect(['a' => 1, 'b' => 2, 'c' => 3])->toMatchArray(['a' => 1, 'c' => 3]);

// Array equality (ignoring order)
expect([3, 2, 1])->toEqualCanonicalizing([1, 2, 3]);

// Key case validation
expect(['first_name' => 'John', 'last_name' => 'Doe'])->toHaveSnakeCaseKeys();
expect(['first-name' => 'John', 'last-name' => 'Doe'])->toHaveKebabCaseKeys();
expect(['firstName' => 'John', 'lastName' => 'Doe'])->toHaveCamelCaseKeys();
expect(['FirstName' => 'John', 'LastName' => 'Doe'])->toHaveStudlyCaseKeys();
```

## Object Expectations

```php
// Property checks
expect($user)->toHaveProperty('name');
expect($user)->toHaveProperty('email');
expect($user)->toHaveProperties(['name', 'email', 'role']);

// Method checks
expect($user)->toHaveMethod('save');
expect($user)->toHaveMethod('delete');

// Instance checks
expect($iterator)->toBeInstanceOf(ArrayIterator::class);
expect($iterator)->toBeInstanceOf(Traversable::class);

// Object matching
$obj = (object) ['name' => 'John', 'age' => 30, 'city' => 'NYC'];
expect($obj)->toMatchObject(['name' => 'John', 'age' => 30]);
```

## Format Validations

```php
// Email
expect('test@example.com')->toBeEmail();
expect('user+tag@domain.co.uk')->toBeEmail();

// URL
expect('https://example.com')->toBeUrl();
expect('http://localhost:8080')->toBeUrl();

// UUID
expect('550e8400-e29b-41d4-a716-446655440000')->toBeUuid();

// JSON
expect('{"key": "value"}')->toBeJson();
expect('[1,2,3]')->toBeJson();
```

## File System Expectations

```php
// File checks
expect('/path/to/file.txt')->toBeFile();
expect('/path/to/file.txt')->toBeReadableFile();
expect('/path/to/file.txt')->toBeWritableFile();

// Directory checks
expect('/path/to/directory')->toBeReadableDirectory();
expect('/path/to/directory')->toBeWritableDirectory();
```

## Exception Expectations

```php
// Expect callable to throw
expect(fn() => throw new RuntimeException())->toThrow();

// Expect specific exception type
expect(fn() => throw new InvalidArgumentException())
    ->toThrow(InvalidArgumentException::class);

// Expect exception with message
expect(fn() => throw new Exception('Custom error'))
    ->toThrow(Exception::class, 'Custom error');

// Verify no exception thrown
expect(fn() => $user->save())->not->toThrow();
```

## Negation Modifier

Use `->not` to negate any expectation:

```php
expect(42)->not->toBe(43);
expect('hello')->not->toBeNull();
expect([])->not->toBeEmpty();

expect(42)->not->toBeString();
expect('test')->not->toBeInt();

expect(5)->not->toBeGreaterThan(10);
expect([1, 2])->not->toHaveCount(3);
```

## Each Modifier

Apply expectations to each element in a collection:

```php
// Using callback
expect([1, 2, 3])->each(function($expectation) {
    $expectation->toBeInt();
});

// Using property syntax
expect([1, 2, 3])->each->toBeInt();
expect(['a', 'b', 'c'])->each->toBeString();

// With negation
expect([1, 2, 3])->each->not->toBeString();

// With key parameter
expect(['a' => 1, 'b' => 2])->each(function($expectation, $key) {
    $expectation->toBeInt();
    expect($key)->toBeString();
});
```

## And Modifier

Chain multiple values in a single expression:

```php
// Continue on same value
expect($user)
    ->toHaveProperty('email')
    ->and()
    ->toHaveProperty('name');

// Chain different values
expect($user)->toHaveProperty('email')
    ->and($admin)->toHaveProperty('email')
    ->and($guest)->not->toHaveProperty('email');
```

## Conditional Modifiers

### When Modifier

Execute expectations conditionally:

```php
// With boolean condition
expect($user)->when(
    $isAdmin,
    fn($exp) => $exp->toHaveProperty('adminLevel')
);

// With callable condition
expect($user)->when(
    fn($value) => $value->role === 'admin',
    fn($exp) => $exp->toHaveProperty('permissions')
);

// Chaining after when
expect($user)
    ->when($isActive, fn($exp) => $exp->toHaveProperty('lastLogin'))
    ->toHaveProperty('email');
```

### Unless Modifier

Inverse of `when()`:

```php
// Execute unless condition is true
expect($user)->unless(
    $isGuest,
    fn($exp) => $exp->toHaveProperty('subscription')
);

// With callable condition
expect($data)->unless(
    fn($value) => empty($value),
    fn($exp) => $exp->toHaveCount(5)
);
```

## Advanced Modifiers

### Sequence Modifier

Apply different expectations to each element in order:

```php
expect([1, 'test', 3.14])->sequence(
    fn($e) => $e->toBeInt(),
    fn($e) => $e->toBeString(),
    fn($e) => $e->toBeFloat()
);

// Validates that first item is int, second is string, third is float
```

### JSON Modifier

Parse JSON and continue chaining on the decoded value:

```php
expect('{"name":"John","age":30}')
    ->json()
    ->toHaveKey('name')
    ->toHaveKey('age');

expect('{"count":5}')
    ->json()
    ->toHaveKey('count');
```

### Match Modifier

Pattern match against multiple conditions:

```php
// Exact match
expect('active')->match(
    ['pending', fn($e) => $e->toBeString()],
    ['active', fn($e) => $e->toBeString()]
);

// Callable matcher
expect(42)->match(
    [fn($v) => $v < 10, fn($e) => $e->toBeInt()],
    [fn($v) => $v > 10, fn($e) => $e->toBeInt()]
);
```

## Debugging Helpers

### dd() - Dump and Die

```php
expect($data)->dd(); // Dumps value and exits

expect($user)->dd($extra, $data); // Dumps multiple values
```

### ddWhen() - Conditional Dump

```php
// Dump only when condition is true
expect($data)->ddWhen($isDebug);

expect($value)->ddWhen(fn($v) => $v > 100);
```

### ddUnless() - Inverse Conditional Dump

```php
// Dump unless condition is true
expect($data)->ddUnless($isProduction);

expect($value)->ddUnless(fn($v) => $v < 10);
```

### ray() - Send to Ray Debugger

```php
// Send to Ray (if installed)
expect($data)->ray();

expect($value)->ray('Debug label');
```

## Chaining Examples

### Complex Validations

```php
// User validation
expect($user)
    ->toBeObject()
    ->toHaveProperty('email')
    ->toHaveProperty('name')
    ->toHaveMethod('save');

// Data validation
expect($data)
    ->toBeArray()
    ->toHaveCount(3)
    ->toHaveKey('id')
    ->toHaveKey('name');

// String validation
expect($password)
    ->toBeString()
    ->toHaveLength(12)
    ->toMatch('/[A-Z]/')
    ->toMatch('/[0-9]/');
```

### Combining Modifiers

```php
// Each with negation
expect([1, 2, 3])->each->not->toBeString();

// Each with when
expect($users)->each(function($exp, $key) {
    $exp->when(
        $key === 0,
        fn($e) => $e->toHaveProperty('isAdmin')
    );
});

// Multiple and() chains
expect($admin)
    ->toHaveProperty('role')
    ->and($user)->toHaveProperty('role')
    ->and($guest)->not->toHaveProperty('role');
```

## Best Practices

### Use for Testing

The `expect()` API is ideal for test suites:

```php
test('user has required properties', function() {
    $user = createUser();

    expect($user)->toBeObject()
        ->and(fn($u) => expect($u->email)->toBeEmail())
        ->and(fn($u) => expect($u->age)->toBeGreaterThan(0));
});
```

### Clear Error Messages

Exceptions include helpful context:

```php
// When this fails:
expect($age)->toBeBetween(18, 65);

// You get:
// InvalidArgumentException: Expected value between 18 and 65. Got: 17
```

### Type Safety

All expectations return the `Expectation` instance for chaining:

```php
$expectation = expect($value)
    ->toBeString()
    ->toHaveLength(10)
    ->toStartWith('user-');
// $expectation is instance of Expectation
```

## Complete Feature List

### Core Expectations (43)

**Equality & Boolean:**
- `toBe()`, `toEqual()`, `toBeNull()`, `toBeTrue()`, `toBeFalse()`, `toBeTruthy()`, `toBeFalsy()`, `toBeEmpty()`

**Types:**
- `toBeString()`, `toBeInt()`, `toBeFloat()`, `toBeBool()`, `toBeArray()`, `toBeObject()`
- `toBeCallable()`, `toBeIterable()`, `toBeCountable()`, `toBeNumeric()`, `toBeScalar()`, `toBeResource()`

**Numeric:**
- `toBeGreaterThan()`, `toBeGreaterThanOrEqual()`, `toBeLessThan()`, `toBeLessThanOrEqual()`
- `toBeBetween()`, `toBeInfinite()`, `toBeNan()`, `toEqualWithDelta()`

**String:**
- `toStartWith()`, `toEndWith()`, `toMatch()`, `toHaveLength()`
- `toBeAlpha()`, `toBeAlphaNumeric()`, `toBeDigits()`
- `toBeSnakeCase()`, `toBeKebabCase()`, `toBeCamelCase()`, `toBeStudlyCase()`
- `toBeUppercase()`, `toBeLowercase()`

**Collections:**
- `toContain()`, `toHaveCount()`, `toHaveKey()`, `toHaveKeys()`, `toHaveLength()`
- `toBeIn()`, `toContainEqual()`, `toContainOnlyInstancesOf()`, `toHaveSameSize()`
- `toMatchArray()`, `toEqualCanonicalizing()`
- `toHaveSnakeCaseKeys()`, `toHaveKebabCaseKeys()`, `toHaveCamelCaseKeys()`, `toHaveStudlyCaseKeys()`

**Objects:**
- `toBeInstanceOf()`, `toHaveProperty()`, `toHaveProperties()`, `toHaveMethod()`, `toMatchObject()`

**Files:**
- `toBeFile()`, `toBeReadableFile()`, `toBeWritableFile()`
- `toBeReadableDirectory()`, `toBeWritableDirectory()`

**Formats:**
- `toBeEmail()`, `toBeUrl()`, `toBeUuid()`, `toBeJson()`

**Exceptions:**
- `toThrow()`

### Modifiers (12)

**Core:**
- `->not` - Negation
- `->and()` - Chaining

**Collections:**
- `->each()` - Iterate elements
- `->sequence()` - Ordered expectations

**Data:**
- `->json()` - Parse and continue

**Control Flow:**
- `->when()` - Conditional execution
- `->unless()` - Inverse conditional
- `->match()` - Pattern matching

**Debugging:**
- `->dd()` - Dump and die
- `->ddWhen()` - Conditional dump
- `->ddUnless()` - Inverse conditional dump
- `->ray()` - Ray debugger

## Comparison with Assert API

| Expect API | Assert API |
|------------|------------|
| `expect($x)->toBeString()` | `Assert::that($x)->string()` |
| `expect($x)->toBe(42)` | `Assert::that($x)->same(42)` |
| `expect($x)->toBeGreaterThan(5)` | `Assert::that($x)->greaterThan(5)` |
| `expect($x)->not->toBeNull()` | `Assert::that($x)->notNull()` |
| `expect([1,2])->each->toBeInt()` | Manual loop required |
| `expect([3,2,1])->toEqualCanonicalizing([1,2,3])` | Manual sorting required |
| `expect('{"a":1}')->json()->toHaveKey('a')` | Manual json_decode required |

## Next Steps

- **[Getting Started](getting-started.md)** - Overview of all assertion styles
- **[Assertion Chains](assertion-chains.md)** - Traditional fluent API
- **[Type Assertions](type-assertions.md)** - Complete type checking reference
- **[String Assertions](string-assertions.md)** - String validation reference
