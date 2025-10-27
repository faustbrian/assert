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
expect(5)->toBeWithinRange(1, 10); // Alias for toBeBetween

// Edge cases
expect(INF)->toBeInfinite();
expect(-INF)->toBeInfinite();
expect(NAN)->toBeNan();

// Equality with tolerance
expect(3.14159)->toEqualWithDelta(3.14, 0.01);
expect(100)->toEqualWithDelta(99, 1.0);

// Floating point comparison (alias)
expect(3.14159)->toBeCloseTo(3.14, 0.01);
expect(3.005)->toBeCloseTo(3.0); // Default delta 0.01

// Numeric properties
expect(42)->toBePositive();
expect(-10)->toBeNegative();
expect(4)->toBeEven();
expect(7)->toBeOdd();
expect(10)->toBeDivisibleBy(5);
expect(15)->toBeDivisibleBy(3);

// Date validation
expect('2024-01-15')->toBeValidDate('Y-m-d');
expect('01/15/2024')->toBeValidDate('m/d/Y');
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

// Batch key/value checks
expect(['a' => 1, 'b' => 2, 'c' => 3])->toContainAllKeys(['a', 'c']);
expect([1, 2, 3, 4, 5])->toContainAllValues([1, 3, 5]);

// Value membership
expect([1, 2, 3])->toContain(2);
expect(['a', 'b', 'c'])->toContain('b');
expect([1, 2, 3])->toContainValue(2); // Alias for toContain
expect(['a' => 1, 'b' => 2])->toContainKey('a'); // Alias for toHaveKey
expect(2)->toBeIn([1, 2, 3]);
expect('active')->toBeOneOf(['pending', 'active', 'completed']); // Alias for toBeIn

// Array key comparison
expect(['a' => 1, 'b' => 2])->toHaveSameKeys(['a' => 99, 'b' => 88]); // Keys match, values differ

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

## Custom Validation

### toSatisfy() - Custom Predicate

Apply custom validation logic via callback:

```php
// Simple conditions
expect($age)->toSatisfy(fn($v) => $v > 18);
expect($email)->toSatisfy(fn($v) => filter_var($v, FILTER_VALIDATE_EMAIL) !== false);

// Complex business logic
expect($user)->toSatisfy(function($u) {
    return $u->age > 18
        && $u->verified === true
        && !empty($u->email);
});

// With data structures
expect($array)->toSatisfy(fn($a) => count($a) > 0 && isset($a['required_key']));
```

### toMatchSchema() - Schema Validation

Validate data against JSON schema-style definitions:

```php
use function Cline\Assert\expect;

// Simple type validation
expect('hello')->toMatchSchema(['type' => 'string']);
expect(123)->toMatchSchema(['type' => 'integer']);

// Object schemas
$user = ['name' => 'John', 'age' => 30];
expect($user)->toMatchSchema([
    'type' => 'object',
    'properties' => [
        'name' => ['type' => 'string'],
        'age' => ['type' => 'integer'],
    ],
]);

// Required properties
expect($user)->toMatchSchema([
    'type' => 'object',
    'properties' => [
        'name' => ['type' => 'string'],
        'email' => ['type' => 'string'],
    ],
    'required' => ['name', 'email'], // Will fail if email is missing
]);

// Numeric constraints
expect(5)->toMatchSchema([
    'type' => 'integer',
    'minimum' => 0,
    'maximum' => 10,
]);

// String constraints
expect('hello@example.com')->toMatchSchema([
    'type' => 'string',
    'pattern' => '/^.+@.+\..+$/',
    'minLength' => 5,
    'maxLength' => 100,
]);

// Enum validation
expect('active')->toMatchSchema([
    'type' => 'string',
    'enum' => ['pending', 'active', 'completed'],
]);

// Array items
expect([1, 2, 3])->toMatchSchema([
    'type' => 'array',
    'items' => ['type' => 'integer'],
]);

// Nested objects
$data = [
    'user' => [
        'profile' => [
            'age' => 30,
        ],
    ],
];

expect($data)->toMatchSchema([
    'type' => 'object',
    'properties' => [
        'user' => [
            'type' => 'object',
            'properties' => [
                'profile' => [
                    'type' => 'object',
                    'properties' => [
                        'age' => ['type' => 'integer', 'minimum' => 0],
                    ],
                ],
            ],
        ],
    ],
]);
```

## Asymmetric Matchers

Match partial patterns without requiring exact equality. Use `expect()` without arguments to access matcher methods:

```php
use function Cline\Assert\expect;

// expect()->any() - Match any value of specific type
expect(['name' => 'John', 'age' => 30])->toEqual([
    'name' => expect()->any('string'),
    'age' => expect()->any('int'),
]);

// Support for class names
expect(['user' => new stdClass()])->toEqual([
    'user' => expect()->any(stdClass::class),
]);

// expect()->anything() - Match any non-null value
expect(['id' => 123, 'data' => 'test'])->toEqual([
    'id' => expect()->anything(),
    'data' => expect()->anything(),
]);

// expect()->stringContaining() - Match strings with substring
expect(['message' => 'Error: Invalid input'])->toEqual([
    'message' => expect()->stringContaining('Error'),
]);

// expect()->arrayContaining() - Match arrays with subset of keys
expect(['a' => 1, 'b' => 2, 'c' => 3])->toEqual(
    expect()->arrayContaining(['a' => 1, 'c' => 3])
);

// Nested matchers
expect([
    'user' => [
        'name' => 'John',
        'email' => 'john@example.com',
        'age' => 30,
    ],
])->toEqual([
    'user' => expect()->arrayContaining([
        'email' => expect()->stringContaining('@'),
        'age' => expect()->any('int'),
    ]),
]);

// Complex patterns
$response = [
    'status' => 'success',
    'data' => [
        'id' => 123,
        'title' => 'Test Post',
        'author' => 'John Doe',
    ],
];

expect($response)->toEqual([
    'status' => expect()->any('string'),
    'data' => expect()->arrayContaining([
        'id' => expect()->anything(),
        'title' => expect()->stringContaining('Test'),
    ]),
]);
```

## Soft Assertions

Collect multiple assertion failures before throwing:

```php
use function Cline\Assert\expect;
use Cline\Assert\Expectations\Expectation;

// Regular assertions throw immediately
expect(5)->toBeGreaterThan(10); // Throws here

// Soft assertions collect errors
expect(5)->soft->toBeGreaterThan(10); // Stored
expect('hello')->soft->toBeInt(); // Stored
expect([])->soft->toHaveCount(5); // Stored

// Check all at once
Expectation::assertSoft(); // Throws with all 3 errors

// Practical example
$user = [
    'name' => 123,      // Should be string
    'age' => -5,        // Should be positive
    'email' => 'bad',   // Should be email
];

expect($user['name'])->soft->toBeString();
expect($user['age'])->soft->toBePositive();
expect($user['email'])->soft->toBeEmail();

// Throws with all validation failures
Expectation::assertSoft();

// Soft assertions with schema validation
expect($data)->soft->toMatchSchema($schema1);
expect($data2)->soft->toMatchSchema($schema2);
Expectation::assertSoft();
```

## OR Operator

The `or()` operator creates alternative expectation groups. If **any** group passes completely without errors, the entire expectation chain succeeds. This is useful for validating values that can match multiple different patterns.

### Basic OR Usage

```php
use function Cline\Assert\expect;

// Value must be string OR integer OR null
expect($value)
    ->or()
    ->toBeString()
    ->or()
    ->toBeInt()
    ->or()
    ->toBeNull();

// Value must match one of several specific values
expect($status)
    ->or()
    ->toBe('pending')
    ->or()
    ->toBe('active')
    ->or()
    ->toBe('completed');
```

### OR with Multiple Assertions per Group

Each group can contain multiple assertions. **All** assertions within a group must pass for that group to succeed:

```php
// String with length 10 OR positive integer
expect($input)
    ->or()
    ->toBeString()
    ->toHaveLength(10)
    ->or()
    ->toBeInt()
    ->toBePositive();

// Valid email format OR valid phone format
expect($contact)
    ->or()
    ->toBeString()
    ->toBeEmail()
    ->or()
    ->toBeString()
    ->toMatch('/^\+?[1-9]\d{1,14}$/');
```

### OR with Negation

The `not` modifier works within OR groups:

```php
// Must be non-empty string OR positive integer
expect($value)
    ->or()
    ->toBeString()
    ->not->toBeEmpty()
    ->or()
    ->toBeInt()
    ->toBePositive();
```

### Complex OR Patterns

```php
// API response validation: success OR error format
expect($response)
    ->or()
    ->toHaveKey('data')
    ->toHaveKey('status')
    ->or()
    ->toHaveKey('error')
    ->toHaveKey('message');

// Union type validation
expect($identifier)
    ->or()
    ->toBeInt()
    ->toBePositive()
    ->or()
    ->toBeString()
    ->toMatch('/^[A-Z]{3}\d{3}$/');
```

### Error Messages

When all groups fail, you get a combined error message showing why each group failed:

```php
expect('invalid')
    ->or()
    ->toBeInt()
    ->or()
    ->toBeNull();

// Throws: All OR groups failed:
// Group 1: Expected value to be integer. Got: string
// Group 2: Expected value to be null. Got: string
```

## Snapshot Testing

Store and compare snapshots for regression testing:

```php
use function Cline\Assert\expect;
use Cline\Assert\Snapshots\SnapshotManager;

// Configure snapshot directory (optional)
SnapshotManager::setSnapshotDirectory('__snapshots__');

// toMatchSnapshot() - File-based snapshots
$data = ['name' => 'John', 'age' => 30];

// First run: creates snapshot file
expect($data)->toMatchSnapshot('user-data');

// Subsequent runs: compares against stored snapshot
expect($data)->toMatchSnapshot('user-data'); // Passes

// Modified data fails
$modified = ['name' => 'Jane', 'age' => 25];
expect($modified)->toMatchSnapshot('user-data'); // Throws with diff

// toMatchInlineSnapshot() - Inline snapshots
$data = ['name' => 'John'];
$expected = <<<'JSON'
{
    "name": "John"
}
JSON;

expect($data)->toMatchInlineSnapshot($expected); // Passes

// Different data fails
$modified = ['name' => 'Jane'];
expect($modified)->toMatchInlineSnapshot($expected); // Throws with diff

// Snapshot formatting
// - Strings: stored as-is
// - Arrays/Objects: formatted as pretty JSON
// - Scalars: converted to strings

expect('plain text')->toMatchSnapshot('text-snapshot');
expect(['nested' => ['data' => 123]])->toMatchSnapshot('json-snapshot');
expect(42)->toMatchSnapshot('number-snapshot');

// Use cases
test('API response structure', function() {
    $response = api()->get('/users/1');
    expect($response->json())->toMatchSnapshot('user-response');
});

test('rendered component', function() {
    $html = render(UserProfile::class, ['user' => $user]);
    expect($html)->toMatchSnapshot('user-profile-html');
});

test('serialized data', function() {
    $export = exportUserData($user);
    expect($export)->toMatchSnapshot('user-export');
});
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

// OR operator - value must match at least one group
expect($status)
    ->or()
    ->toBe('pending')
    ->or()
    ->toBe('active')
    ->or()
    ->toBe('completed');

// OR with multiple assertions per group
expect($input)
    ->or()
    ->toBeString()
    ->toHaveLength(10)
    ->or()
    ->toBeInt()
    ->toBePositive();
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

### Core Expectations (60+)

**Equality & Boolean:**
- `toBe()`, `toEqual()`, `toBeNull()`, `toBeTrue()`, `toBeFalse()`, `toBeTruthy()`, `toBeFalsy()`, `toBeEmpty()`

**Types:**
- `toBeString()`, `toBeInt()`, `toBeFloat()`, `toBeBool()`, `toBeArray()`, `toBeObject()`
- `toBeCallable()`, `toBeIterable()`, `toBeCountable()`, `toBeNumeric()`, `toBeScalar()`, `toBeResource()`

**Numeric:**
- `toBeGreaterThan()`, `toBeGreaterThanOrEqual()`, `toBeLessThan()`, `toBeLessThanOrEqual()`
- `toBeBetween()`, `toBeWithinRange()`, `toBeInfinite()`, `toBeNan()`, `toEqualWithDelta()`, `toBeCloseTo()`
- `toBePositive()`, `toBeNegative()`, `toBeEven()`, `toBeOdd()`, `toBeDivisibleBy()`
- `toBeValidDate()`

**String:**
- `toStartWith()`, `toEndWith()`, `toMatch()`, `toHaveLength()`
- `toBeAlpha()`, `toBeAlphaNumeric()`, `toBeDigits()`
- `toBeSnakeCase()`, `toBeKebabCase()`, `toBeCamelCase()`, `toBeStudlyCase()`
- `toBeUppercase()`, `toBeLowercase()`

**Collections:**
- `toContain()`, `toContainValue()`, `toHaveCount()`, `toHaveKey()`, `toContainKey()`, `toHaveKeys()`, `toHaveLength()`
- `toBeIn()`, `toBeOneOf()`, `toContainEqual()`, `toContainOnlyInstancesOf()`, `toHaveSameSize()`, `toHaveSameKeys()`
- `toContainAllKeys()`, `toContainAllValues()`
- `toMatchArray()`, `toEqualCanonicalizing()`
- `toHaveSnakeCaseKeys()`, `toHaveKebabCaseKeys()`, `toHaveCamelCaseKeys()`, `toHaveStudlyCaseKeys()`

**Custom:**
- `toSatisfy()` - Custom predicate validation
- `toMatchSchema()` - JSON schema validation

**Objects:**
- `toBeInstanceOf()`, `toHaveProperty()`, `toHaveProperties()`, `toHaveMethod()`, `toMatchObject()`

**Files:**
- `toBeFile()`, `toBeReadableFile()`, `toBeWritableFile()`
- `toBeReadableDirectory()`, `toBeWritableDirectory()`

**Formats:**
- `toBeEmail()`, `toBeUrl()`, `toBeUuid()`, `toBeJson()`

**Exceptions:**
- `toThrow()`

**Snapshots:**
- `toMatchSnapshot()` - File-based snapshot testing
- `toMatchInlineSnapshot()` - Inline snapshot comparison

### Modifiers (13)

**Core:**
- `->not` - Negation
- `->soft` - Soft assertions (collect errors)
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

### Asymmetric Matchers (4)

**Matcher Methods (via `expect()`):**
- `expect()->any()` - Match any value of specific type
- `expect()->anything()` - Match any non-null value
- `expect()->stringContaining()` - Match strings with substring
- `expect()->arrayContaining()` - Match arrays with subset of keys

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
