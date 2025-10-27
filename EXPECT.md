# expect() API - Complete Pest Compatibility Implementation

## Overview

This document outlines the complete implementation of Pest-compatible `expect()` API for the assertion library. The goal is to provide 100% feature parity with Pest's expectation API while leveraging our existing assertion infrastructure.

---

## Architecture

### Core Components

**File Structure:**
```
src/
├── Expectations/
│   ├── Expectation.php          # Main expectation class
│   ├── ExpectationChain.php     # Chaining support for ->and()
│   ├── HigherOrderExpectation.php # ->not, ->each, ->sequence support
│   ├── JsonExpectation.php      # ->json() modifier
│   └── Concerns/
│       ├── Debugging.php        # ->dd(), ->ray() support
│       ├── Conditional.php      # ->when(), ->unless() support
│       └── Matching.php         # ->match() support
└── functions.php                # Global expect() function
```

---

## Individual Expectations

### Value & Equality Expectations

#### `toBe($expected)`
Maps to: `Assertion::same($value, $expected)` (strict ===)
```php
expect(42)->toBe(42);
expect('foo')->toBe('foo');
```

#### `toEqual($expected)`
Maps to: `Assertion::same($value, $expected)` (deep equality)
```php
expect(['a' => 1])->toEqual(['a' => 1]);
```

#### `toEqualCanonicalizing($expected)`
Maps to: Custom implementation - compare arrays ignoring order
```php
expect([3, 2, 1])->toEqualCanonicalizing([1, 2, 3]);
```

#### `toEqualWithDelta($expected, $delta)`
Maps to: Custom implementation - float comparison with tolerance
```php
expect(3.14159)->toEqualWithDelta(3.14, 0.01);
```

---

### Boolean Expectations

#### `toBeTrue()`
Maps to: `Assertion::true($value)`
```php
expect(true)->toBeTrue();
```

#### `toBeFalse()`
Maps to: `Assertion::false($value)`
```php
expect(false)->toBeFalse();
```

#### `toBeTruthy()`
Maps to: Custom - `$value == true` (loose)
```php
expect(1)->toBeTruthy();
expect('yes')->toBeTruthy();
```

#### `toBeFalsy()`
Maps to: Custom - `$value == false` (loose)
```php
expect(0)->toBeFalsy();
expect('')->toBeFalsy();
```

---

### Null Expectations

#### `toBeNull()`
Maps to: `Assertion::null($value)`
```php
expect(null)->toBeNull();
```

---

### Type Expectations

#### `toBeArray()`
Maps to: `Assertion::isArray($value)`
```php
expect([])->toBeArray();
```

#### `toBeBool()`
Maps to: `Assertion::boolean($value)`
```php
expect(true)->toBeBool();
```

#### `toBeCallable()`
Maps to: `Assertion::isCallable($value)`
```php
expect(fn() => true)->toBeCallable();
```

#### `toBeFloat()`
Maps to: `Assertion::float($value)`
```php
expect(3.14)->toBeFloat();
```

#### `toBeInt()`
Maps to: `Assertion::integer($value)`
```php
expect(42)->toBeInt();
```

#### `toBeIterable()`
Maps to: `Assertion::isTraversable($value)`
```php
expect([])->toBeIterable();
```

#### `toBeNumeric()`
Maps to: `Assertion::numeric($value)`
```php
expect('42')->toBeNumeric();
```

#### `toBeDigits()`
Maps to: `Assertion::digit($value)`
```php
expect(123)->toBeDigits();
```

#### `toBeObject()`
Maps to: `Assertion::isObject($value)`
```php
expect(new stdClass)->toBeObject();
```

#### `toBeResource()`
Maps to: `Assertion::isResource($value)`
```php
$handle = fopen('php://memory', 'r');
expect($handle)->toBeResource();
```

#### `toBeScalar()`
Maps to: `Assertion::scalar($value)`
```php
expect('string')->toBeScalar();
```

#### `toBeString()`
Maps to: `Assertion::string($value)`
```php
expect('hello')->toBeString();
```

#### `toBeJson()`
Maps to: `Assertion::isJsonString($value)`
```php
expect('{"a":1}')->toBeJson();
```

---

### Numeric Expectations

#### `toBeBetween($min, $max)`
Maps to: `Assertion::between($value, $min, $max)`
```php
expect(5)->toBeBetween(1, 10);
```

#### `toBeGreaterThan($limit)`
Maps to: `Assertion::greaterThan($value, $limit)`
```php
expect(10)->toBeGreaterThan(5);
```

#### `toBeGreaterThanOrEqual($limit)`
Maps to: `Assertion::greaterOrEqualThan($value, $limit)`
```php
expect(10)->toBeGreaterThanOrEqual(10);
```

#### `toBeLessThan($limit)`
Maps to: `Assertion::lessThan($value, $limit)`
```php
expect(5)->toBeLessThan(10);
```

#### `toBeLessThanOrEqual($limit)`
Maps to: `Assertion::lessOrEqualThan($value, $limit)`
```php
expect(5)->toBeLessThanOrEqual(5);
```

#### `toBeInfinite()`
Maps to: Custom - `is_infinite($value)`
```php
expect(INF)->toBeInfinite();
```

#### `toBeNan()`
Maps to: Custom - `is_nan($value)`
```php
expect(NAN)->toBeNan();
```

---

### String Expectations

#### `toStartWith($prefix)`
Maps to: `Assertion::startsWith($value, $prefix)`
```php
expect('hello world')->toStartWith('hello');
```

#### `toEndWith($suffix)`
Maps to: `Assertion::endsWith($value, $suffix)`
```php
expect('hello world')->toEndWith('world');
```

#### `toMatch($pattern)`
Maps to: `Assertion::regex($value, $pattern)`
```php
expect('test@example.com')->toMatch('/^.+@.+$/');
```

#### `toMatchConstraint($constraint)`
Maps to: Custom - PHPUnit constraint matching
```php
expect($value)->toMatchConstraint(new CustomConstraint());
```

#### `toBeUppercase()`
Maps to: Custom - `$value === strtoupper($value)`
```php
expect('HELLO')->toBeUppercase();
```

#### `toBeLowercase()`
Maps to: Custom - `$value === strtolower($value)`
```php
expect('hello')->toBeLowercase();
```

#### `toBeAlpha()`
Maps to: Custom - `ctype_alpha($value)`
```php
expect('abcXYZ')->toBeAlpha();
```

#### `toBeAlphaNumeric()`
Maps to: `Assertion::alnum($value)`
```php
expect('abc123')->toBeAlphaNumeric();
```

#### `toBeSnakeCase()`
Maps to: Custom - matches `snake_case` pattern
```php
expect('hello_world')->toBeSnakeCase();
```

#### `toBeKebabCase()`
Maps to: Custom - matches `kebab-case` pattern
```php
expect('hello-world')->toBeKebabCase();
```

#### `toBeCamelCase()`
Maps to: Custom - matches `camelCase` pattern
```php
expect('helloWorld')->toBeCamelCase();
```

#### `toBeStudlyCase()`
Maps to: Custom - matches `StudlyCase` pattern
```php
expect('HelloWorld')->toBeStudlyCase();
```

---

### Array/Collection Expectations

#### `toBeEmpty()`
Maps to: `Assertion::noContent($value)`
```php
expect([])->toBeEmpty();
expect('')->toBeEmpty();
```

#### `toContain($needle)`
Maps to: `Assertion::contains($value, $needle)` for strings, custom for arrays
```php
expect('hello world')->toContain('world');
expect([1, 2, 3])->toContain(2);
```

#### `toContainEqual($needle)`
Maps to: Custom - array contains value with deep equality
```php
expect([['a' => 1]])->toContainEqual(['a' => 1]);
```

#### `toContainOnlyInstancesOf($class)`
Maps to: Custom - all array elements are instances of class
```php
expect([$user1, $user2])->toContainOnlyInstancesOf(User::class);
```

#### `toHaveCount($count)`
Maps to: `Assertion::count($value, $count)`
```php
expect([1, 2, 3])->toHaveCount(3);
```

#### `toHaveKey($key)`
Maps to: `Assertion::keyExists($value, $key)`
```php
expect(['name' => 'John'])->toHaveKey('name');
```

#### `toHaveKeys($keys)`
Maps to: Custom - multiple `keyExists()` checks
```php
expect(['name' => 'John', 'email' => 'j@ex.com'])
    ->toHaveKeys(['name', 'email']);
```

#### `toHaveLength($length)`
Maps to: `Assertion::length($value, $length)` for strings, `count()` for arrays
```php
expect('hello')->toHaveLength(5);
expect([1, 2, 3])->toHaveLength(3);
```

#### `toHaveSnakeCaseKeys()`
Maps to: Custom - all array keys are snake_case
```php
expect(['first_name' => 'John', 'last_name' => 'Doe'])->toHaveSnakeCaseKeys();
```

#### `toHaveKebabCaseKeys()`
Maps to: Custom - all array keys are kebab-case
```php
expect(['first-name' => 'John', 'last-name' => 'Doe'])->toHaveKebabCaseKeys();
```

#### `toHaveCamelCaseKeys()`
Maps to: Custom - all array keys are camelCase
```php
expect(['firstName' => 'John', 'lastName' => 'Doe'])->toHaveCamelCaseKeys();
```

#### `toHaveStudlyCaseKeys()`
Maps to: Custom - all array keys are StudlyCase
```php
expect(['FirstName' => 'John', 'LastName' => 'Doe'])->toHaveStudlyCaseKeys();
```

#### `toHaveSameSize($expected)`
Maps to: Custom - count comparison
```php
expect([1, 2, 3])->toHaveSameSize([4, 5, 6]);
```

#### `toMatchArray($expected)`
Maps to: Custom - array subset matching (order doesn't matter)
```php
expect(['a' => 1, 'b' => 2, 'c' => 3])->toMatchArray(['a' => 1, 'c' => 3]);
```

#### `toBeIn($haystack)`
Maps to: `Assertion::inArray($value, $haystack)`
```php
expect(2)->toBeIn([1, 2, 3]);
```

---

### Object Expectations

#### `toBeInstanceOf($class)`
Maps to: `Assertion::isInstanceOf($value, $class)`
```php
expect($user)->toBeInstanceOf(User::class);
```

#### `toHaveProperty($property, $value = null)`
Maps to: `Assertion::propertyExists($value, $property)` + optional value check
```php
expect($user)->toHaveProperty('email');
expect($user)->toHaveProperty('email', 'test@example.com');
```

#### `toHaveProperties($properties)`
Maps to: `Assertion::propertiesExist($value, $properties)`
```php
expect($user)->toHaveProperties(['name', 'email', 'age']);
```

#### `toMatchObject($expected)`
Maps to: Custom - object property matching (like toMatchArray for objects)
```php
expect($user)->toMatchObject(['email' => 'test@example.com']);
```

---

### File Expectations

#### `toBeFile()`
Maps to: `Assertion::file($value)`
```php
expect('/path/to/file.txt')->toBeFile();
```

#### `toBeReadableFile()`
Maps to: Custom - `Assertion::file($value) && Assertion::readable($value)`
```php
expect('/path/to/file.txt')->toBeReadableFile();
```

#### `toBeWritableFile()`
Maps to: Custom - `Assertion::file($value) && Assertion::writeable($value)`
```php
expect('/path/to/file.txt')->toBeWritableFile();
```

#### `toBeReadableDirectory()`
Maps to: Custom - `Assertion::directory($value) && Assertion::readable($value)`
```php
expect('/path/to/dir')->toBeReadableDirectory();
```

#### `toBeWritableDirectory()`
Maps to: Custom - `Assertion::directory($value) && Assertion::writeable($value)`
```php
expect('/path/to/dir')->toBeWritableDirectory();
```

---

### Format Expectations

#### `toBeUrl()`
Maps to: `Assertion::url($value)`
```php
expect('https://example.com')->toBeUrl();
```

#### `toBeUuid()`
Maps to: `Assertion::uuid($value)`
```php
expect('550e8400-e29b-41d4-a716-446655440000')->toBeUuid();
```

---

### Exception Expectations

#### `toThrow($exception = null, $message = null)`
Maps to: Custom - wrap value (callable) execution and catch exception
```php
expect(fn() => throw new Exception('error'))->toThrow(Exception::class);
expect(fn() => throw new Exception('error'))->toThrow(Exception::class, 'error');
```

---

## Modifiers

### Negation Modifier

#### `->not`
Property that negates the next expectation.

**Implementation:**
```php
class Expectation
{
    private bool $negate = false;

    public function __get(string $name): self
    {
        if ($name === 'not') {
            $clone = clone $this;
            $clone->negate = true;
            return $clone;
        }
        throw new BadMethodCallException("Property $name does not exist");
    }
}
```

**Usage:**
```php
expect($value)->not->toBeNull();
expect($value)->not->toBeEmpty();
expect($value)->not->toBe(42);
```

---

### Chaining Modifier

#### `->and($value = null)`
Chain a new expectation on a different value, or continue chain on same value.

**Signature:**
- `and($value)` - Start new expectation on different value
- `and()` - Continue chaining on same value (syntactic sugar)

**Implementation:**
```php
public function and(mixed $value = null): self|ExpectationChain
{
    if (func_num_args() === 0) {
        // Continue on same value
        return $this;
    }

    // New value - return new expectation wrapped in chain
    return new ExpectationChain($this, new Expectation($value));
}
```

**Usage:**
```php
expect($email)->toBeString()->toContain('@')
    ->and($username)->toBeString()->toHaveMinLength(3)
    ->and($age)->toBeInt()->toBeGreaterThan(0);
```

---

### Collection Iteration Modifier

#### `->each(callable $callback)`
Apply expectations to each element in a collection.

**Implementation:**
```php
public function each(callable $callback): self
{
    Assertion::isTraversable($this->value);

    foreach ($this->value as $key => $item) {
        $expectation = new Expectation($item);
        $callback($expectation, $key);
    }

    return $this;
}
```

**Usage:**
```php
expect($users)->each(fn($user) =>
    $user->toBeInstanceOf(User::class)
         ->toHaveProperty('email')
);

expect([1, 2, 3])->each->toBeInt();
```

---

### Sequence Modifier

#### `->sequence(...$callbacks)`
Apply different expectations to each element in order.

**Implementation:**
```php
public function sequence(callable ...$callbacks): self
{
    Assertion::isTraversable($this->value);

    $values = is_array($this->value) ? $this->value : iterator_to_array($this->value);

    foreach ($callbacks as $index => $callback) {
        if (!array_key_exists($index, $values)) {
            throw new InvalidArgumentException("Sequence expects at least " . ($index + 1) . " items");
        }
        $expectation = new Expectation($values[$index]);
        $callback($expectation);
    }

    return $this;
}
```

**Usage:**
```php
expect($items)->sequence(
    fn($item) => $item->toBe(1),
    fn($item) => $item->toBe(2),
    fn($item) => $item->toBe(3)
);
```

---

### JSON Modifier

#### `->json()`
Parse JSON string and return new expectation on decoded value.

**Implementation:**
```php
public function json(): JsonExpectation
{
    Assertion::isJsonString($this->value);
    $decoded = json_decode($this->value, true);
    return new JsonExpectation($decoded, $this->value);
}
```

**Usage:**
```php
expect('{"name":"John","age":30}')
    ->json()
    ->toHaveKey('name')
    ->toHaveKey('age');

expect('{"name":"John"}')
    ->json()
    ->name->toBe('John');
```

**JsonExpectation Features:**
- Magic property access for nested keys
- Array access for nested paths
- All standard expectations available

---

### Conditional Modifiers

#### `->when(bool|callable $condition, callable $callback)`
Apply expectations only when condition is true.

**Implementation:**
```php
public function when(bool|callable $condition, callable $callback): self
{
    $result = is_callable($condition) ? $condition($this->value) : $condition;

    if ($result) {
        $callback($this);
    }

    return $this;
}
```

**Usage:**
```php
expect($value)
    ->when($value > 0, fn($e) => $e->toBePositive())
    ->when($value < 0, fn($e) => $e->toBeNegative());
```

#### `->unless(bool|callable $condition, callable $callback)`
Apply expectations only when condition is false (inverse of `when`).

**Implementation:**
```php
public function unless(bool|callable $condition, callable $callback): self
{
    $result = is_callable($condition) ? $condition($this->value) : $condition;

    if (!$result) {
        $callback($this);
    }

    return $this;
}
```

**Usage:**
```php
expect($value)
    ->unless(is_null($value), fn($e) => $e->toBeString());
```

---

### Pattern Matching Modifier

#### `->match(...$patterns)`
Match value against multiple patterns (similar to switch/match).

**Implementation:**
```php
public function match(array ...$patterns): self
{
    foreach ($patterns as [$matcher, $callback]) {
        $matched = false;

        if (is_callable($matcher)) {
            $matched = $matcher($this->value);
        } elseif ($matcher instanceof \Closure) {
            $matched = $matcher($this->value);
        } else {
            $matched = $this->value === $matcher;
        }

        if ($matched) {
            $callback($this);
            return $this;
        }
    }

    throw new InvalidArgumentException('No pattern matched the value');
}
```

**Usage:**
```php
expect($status)->match(
    ['pending', fn($e) => $e->toBeString()],
    ['active', fn($e) => $e->toBeString()],
    [fn($v) => is_int($v), fn($e) => $e->toBeInt()]
);
```

---

### Debugging Modifiers

#### `->dd(...$args)`
Dump and die - output value and stop execution.

**Implementation:**
```php
public function dd(mixed ...$args): never
{
    dump($this->value, ...$args);
    exit(1);
}
```

**Usage:**
```php
expect($value)
    ->toBeArray()
    ->dd() // Dumps value and stops
    ->toHaveCount(3);
```

#### `->ddWhen(bool|callable $condition, mixed ...$args)`
Dump and die only when condition is true.

**Implementation:**
```php
public function ddWhen(bool|callable $condition, mixed ...$args): self
{
    $result = is_callable($condition) ? $condition($this->value) : $condition;

    if ($result) {
        $this->dd(...$args);
    }

    return $this;
}
```

**Usage:**
```php
expect($value)
    ->ddWhen($value === null) // Only dumps if null
    ->toBeString();
```

#### `->ddUnless(bool|callable $condition, mixed ...$args)`
Dump and die only when condition is false.

**Implementation:**
```php
public function ddUnless(bool|callable $condition, mixed ...$args): self
{
    $result = is_callable($condition) ? $condition($this->value) : $condition;

    if (!$result) {
        $this->dd(...$args);
    }

    return $this;
}
```

**Usage:**
```php
expect($value)
    ->ddUnless($value !== null) // Dumps if null
    ->toBeString();
```

#### `->ray(string $label = null)`
Send value to Ray debugger (if available).

**Implementation:**
```php
public function ray(string $label = null): self
{
    if (function_exists('ray')) {
        if ($label) {
            ray($label, $this->value);
        } else {
            ray($this->value);
        }
    }

    return $this;
}
```

**Usage:**
```php
expect($user)
    ->ray('User object') // Sends to Ray
    ->toBeInstanceOf(User::class);
```

---

## Implementation Priority

### Phase 1: Core Foundation (Week 1)
1. Create `Expectation` class with basic structure
2. Implement `expect()` global function
3. Add `->not` modifier
4. Implement core type expectations: `toBe()`, `toBeString()`, `toBeInt()`, `toBeArray()`, `toBeNull()`

### Phase 2: Basic Expectations (Week 1-2)
1. Boolean expectations: `toBeTrue()`, `toBeFalse()`, `toBeTruthy()`, `toBeFalsy()`
2. All type expectations: `toBeBool()`, `toBeFloat()`, `toBeObject()`, etc.
3. Numeric comparisons: `toBeGreaterThan()`, `toBeLessThan()`, `toBeBetween()`
4. Basic string: `toStartWith()`, `toEndWith()`, `toMatch()`

### Phase 3: Collection Expectations (Week 2)
1. Array expectations: `toHaveCount()`, `toHaveKey()`, `toContain()`
2. Object expectations: `toBeInstanceOf()`, `toHaveProperty()`
3. Collection modifiers: `->each()`, `->sequence()`

### Phase 4: Advanced Expectations (Week 3)
1. String case expectations: `toBeSnakeCase()`, `toBeCamelCase()`, etc.
2. Array key case expectations: `toHaveSnakeCaseKeys()`, etc.
3. File expectations: `toBeFile()`, `toBeReadableFile()`, etc.
4. Format expectations: `toBeUrl()`, `toBeUuid()`

### Phase 5: Modifiers & Utilities (Week 3-4)
1. Chaining: `->and()`
2. JSON: `->json()` with nested access
3. Conditional: `->when()`, `->unless()`, `->match()`
4. Debugging: `->dd()`, `->ray()`, `->ddWhen()`, `->ddUnless()`

### Phase 6: Advanced Features (Week 4)
1. Exception expectations: `toThrow()`
2. Custom constraints: `toMatchConstraint()`
3. Advanced equality: `toEqualCanonicalizing()`, `toEqualWithDelta()`
4. Complex matchers: `toMatchArray()`, `toMatchObject()`

### Phase 7: Polish & Testing (Week 4-5)
1. Comprehensive test suite for all expectations
2. Test all modifiers in combination
3. Edge case testing
4. Documentation and examples
5. Performance optimization

---

## Testing Strategy

### Test Organization

**Directory Structure:**
```
tests/Unit/Expectations/
├── ExpectationTest.php                    # Basic functionality
├── TypeExpectationsTest.php               # Type checks
├── NumericExpectationsTest.php            # Numeric comparisons
├── StringExpectationsTest.php             # String operations
├── ArrayExpectationsTest.php              # Array/collection
├── ObjectExpectationsTest.php             # Object checks
├── FileExpectationsTest.php               # File operations
├── FormatExpectationsTest.php             # URL, UUID, etc.
├── Modifiers/
│   ├── NegationTest.php                   # ->not
│   ├── ChainingTest.php                   # ->and()
│   ├── EachTest.php                       # ->each()
│   ├── SequenceTest.php                   # ->sequence()
│   ├── JsonTest.php                       # ->json()
│   ├── ConditionalTest.php                # ->when(), ->unless()
│   ├── MatchingTest.php                   # ->match()
│   └── DebuggingTest.php                  # ->dd(), ->ray()
└── IntegrationTest.php                    # Complex real-world scenarios
```

### Test Coverage Requirements
- ✅ 100% code coverage for all expectations
- ✅ Happy path, sad path, edge cases
- ✅ All modifiers tested in isolation
- ✅ All modifiers tested in combination
- ✅ Type coverage maintained at 100%

---

## Backward Compatibility

**Zero Breaking Changes:**
- All existing code continues to work
- `expect()` is opt-in via function
- Does not modify existing `Assert` or `Assertion` classes
- Separate namespace for expectations

---

## Performance Considerations

1. **Lazy evaluation** - Don't process until expectation method called
2. **Clone for immutability** - Modifiers return clones (e.g., `->not`)
3. **Minimal overhead** - Direct delegation to existing `Assertion` methods
4. **No reflection** - Use explicit mapping for better performance

---

## Documentation Plan

1. **Quick Start Guide** - Basic expect() usage
2. **Complete API Reference** - All expectations documented
3. **Modifier Guide** - How to use each modifier
4. **Migration Guide** - Moving from `Assert::that()` to `expect()`
5. **Cookbook** - Common patterns and examples
6. **Comparison Matrix** - Pest compatibility chart

---

## Open Questions

1. **Ray integration** - Should we make Ray optional dependency or feature-detect?
   - **Recommendation:** Feature-detect with `function_exists('ray')`

2. **Error messages** - Use Pest-style or keep existing assertion messages?
   - **Recommendation:** Pest-style for familiarity ("Expected X to be Y, got Z")

3. **->json() implementation** - Should it return special JsonExpectation or regular Expectation?
   - **Recommendation:** JsonExpectation with magic property access

4. **Type coverage** - How to maintain 100% type coverage with dynamic magic methods?
   - **Recommendation:** Use @method PHPDoc annotations extensively

5. **Chaining limit** - Should we limit ->and() chaining depth?
   - **Recommendation:** No limit, but document performance considerations

---

## Success Criteria

✅ **Feature Parity:** All Pest expectations implemented
✅ **Full Compatibility:** Drop-in replacement for Pest expect() in most cases
✅ **Performance:** Negligible overhead vs direct Assertion calls
✅ **Type Safety:** 100% type coverage maintained
✅ **Test Coverage:** 100% code coverage for all expectations
✅ **Documentation:** Complete API docs and migration guide
✅ **Developer Experience:** IDE autocomplete for all methods
✅ **Backward Compatible:** Zero breaking changes to existing code

---

## Estimated Effort

**Total Implementation Time: 4-5 weeks**

- Week 1: Core foundation + basic expectations (20-25 expectations)
- Week 2: Collection/object expectations + each/sequence (25-30 expectations)
- Week 3: Advanced string/format expectations + conditional modifiers (20 expectations)
- Week 4: Advanced features + debugging + polish (remaining expectations)
- Week 5: Comprehensive testing + documentation + optimization

**Breakdown by Role:**
- Core development: ~80 hours
- Testing: ~40 hours
- Documentation: ~20 hours
- **Total: ~140 hours**

---

## Next Steps

1. ✅ Review and approve EXPECT.md plan
2. Create implementation issues/tasks for each phase
3. Set up basic Expectation class structure
4. Implement Phase 1 (core foundation)
5. Write tests as we implement (TDD approach)
6. Document each expectation as implemented
7. Regular review/demo after each phase
