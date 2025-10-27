# Fluent Behavioral API Plan

## Overview

Introduce two complementary fluent assertion APIs inspired by behavioral testing patterns:

1. **Assert::that() API** - Method-chained behavioral assertions
2. **expect() API** - Pest-style expectation syntax

Both APIs provide natural, readable test assertions that mirror how developers think about behavior.

## 1. Assert::that() Behavioral API

### Core Entry Points

```php
Assert::that($value)          // Start assertion chain
Assert::thatAll($values)      // Assert on all array elements
Assert::thatNullOr($value)    // Allow null values
```

### Behavioral Method Categories

#### A. State Assertions (`->is()` family)

**Positive Assertions:**
```php
->is($expected)                    // Strict equality (===)
->isNot($unexpected)               // Strict inequality (!==)
->isEqual($expected)               // Loose equality (==)
->isNotEqual($unexpected)          // Loose inequality (!=)
->isIdentical($expected)           // Alias for is()
->isNull()                         // Value is null
->isTrue()                         // Value is boolean true
->isFalse()                        // Value is boolean false
->isEmpty()                        // Value is empty
->isNotEmpty()                     // Value is not empty
->isBlank()                        // String is blank (whitespace only)
->isNotBlank()                     // String is not blank
```

**Type State:**
```php
->isString()                       // Value is string
->isInt()                          // Value is integer
->isFloat()                        // Value is float
->isBool()                         // Value is boolean
->isArray()                        // Value is array
->isObject()                       // Value is object
->isResource()                     // Value is resource
->isCallable()                     // Value is callable
->isIterable()                     // Value is iterable
->isCountable()                    // Value is countable
->isNumeric()                      // Value is numeric
->isScalar()                       // Value is scalar
```

**Instance State:**
```php
->isInstanceOf(User::class)        // Value is instance of class
->isNotInstanceOf(Admin::class)    // Value is not instance of class
->isInstanceOfAny([User::class, Admin::class])
->isSubclassOf(Model::class)       // Value is subclass
```

**Collection State:**
```php
->isList()                         // Array is sequential list
->isNonEmptyList()                 // Non-empty sequential list
->isMap()                          // Array is associative map
->isNonEmptyMap()                  // Non-empty associative map
->isArrayAccessible()              // Implements ArrayAccess
->isTraversable()                  // Is traversable
```

#### B. Behavioral Assertions (`->does()` family)

**Positive Behaviors:**
```php
->does(fn($v) => $v->isValid())    // Custom behavior check
->doesNot(fn($v) => $v->isFail())  // Negated behavior
->doesExist()                      // File/class/method exists
->doesNotExist()                   // Does not exist
->doesContain($needle)             // Contains value/substring
->doesNotContain($needle)          // Does not contain
->doesStartWith($prefix)           // Starts with prefix
->doesEndWith($suffix)             // Ends with suffix
->doesMatch($pattern)              // Matches regex
->doesNotMatch($pattern)           // Does not match regex
->doesSatisfy($callback)           // Satisfies callback
```

**Structural Behaviors:**
```php
->doesHaveKey($key)                // Array has key
->doesNotHaveKey($key)             // Array lacks key
->doesHaveProperty($prop)          // Object has property
->doesNotHaveProperty($prop)       // Object lacks property
->doesHaveMethod($method)          // Object has method
->doesNotHaveMethod($method)       // Object lacks method
->doesImplement($interface)        // Implements interface
->doesNotImplement($interface)     // Does not implement
```

#### C. Relational Assertions (`->has()` family)

**Count/Length:**
```php
->hasCount($n)                     // Exact count
->hasMinCount($min)                // Minimum count
->hasMaxCount($max)                // Maximum count
->hasCountBetween($min, $max)      // Count in range
->hasLength($n)                    // String length
->hasMinLength($min)               // Minimum string length
->hasMaxLength($max)               // Maximum string length
->hasLengthBetween($min, $max)     // Length in range
->hasSize($n)                      // Alias for hasCount
```

**Ownership:**
```php
->hasKey($key)                     // Array has key (alias of doesHaveKey)
->hasKeys($keys)                   // Array has all keys
->hasProperty($prop)               // Object has property
->hasProperties($props)            // Object has all properties
->hasMethod($method)               // Object has method
->hasMethods($methods)             // Object has all methods
```

**Content:**
```php
->hasValue($value)                 // Array contains value
->hasValues($values)               // Array contains all values
->hasSubset($subset)               // Array contains subset
->hasUniqueValues()                // All values are unique
```

#### D. Comparison Assertions (`->compare()` family)

```php
->isGreaterThan($value)            // Greater than
->isGreaterThanOrEqual($value)     // Greater than or equal
->isLessThan($value)               // Less than
->isLessThanOrEqual($value)        // Less than or equal
->isBetween($min, $max)            // Between (inclusive)
->isBetweenExclusive($min, $max)   // Between (exclusive)
->isInRange($min, $max)            // Alias for isBetween
```

#### E. Format Assertions (`->matches()` family)

```php
->matchesFormat($format)           // Matches sprintf format
->matchesEmail()                   // Valid email format
->matchesUrl()                     // Valid URL format
->matchesUuid()                    // Valid UUID format
->matchesIp()                      // Valid IP address
->matchesIpv4()                    // Valid IPv4 address
->matchesIpv6()                    // Valid IPv6 address
->matchesE164()                    // Valid E164 phone number
->matchesDate($format)             // Valid date format
->matchesRegex($pattern)           // Matches regex (alias)
->matchesJson()                    // Valid JSON string
->matchesBase64()                  // Valid base64 string
```

#### F. Modifiers & Control Flow

```php
->not()                            // Negate next assertion
->all()                            // Assert on all array elements
->nullOr()                         // Allow null values
->and()                            // Alias for chaining (readability)
->or($callback)                    // Alternative assertion path
->when($condition, $callback)      // Conditional assertion
```

### Usage Examples

```php
// Behavioral state checking
Assert::that($user)
    ->isInstanceOf(User::class)
    ->isNotNull()
    ->doesHaveProperty('email')
    ->doesHaveMethod('isActive');

// String behavior
Assert::that($email)
    ->isString()
    ->isNotEmpty()
    ->doesContain('@')
    ->doesMatch('/^[a-z]+@[a-z]+\.[a-z]+$/');

// Collection behavior
Assert::that($items)
    ->isArray()
    ->isNotEmpty()
    ->hasMinCount(1)
    ->hasMaxCount(100)
    ->hasUniqueValues();

// Numeric behavior
Assert::that($age)
    ->isInt()
    ->isGreaterThan(0)
    ->isLessThan(150);

// Negation
Assert::that($password)
    ->isString()
    ->not()->isEmpty()
    ->not()->doesContain(' ');

// Conditional
Assert::that($value)
    ->when($value > 0, fn($chain) => $chain->isPositive())
    ->or(fn($chain) => $chain->isZero());
```

## 2. expect() API (Pest-style)

### Core Function

```php
expect($value)  // Start expectation chain
```

### Method Mapping

Map behavioral methods to `to*` pattern:

#### State Expectations

```php
->toBe($expected)                  // Strict equality
->notToBe($unexpected)             // Strict inequality
->toEqual($expected)               // Loose equality
->toBeNull()                       // Is null
->toBeTrue()                       // Is true
->toBeFalse()                      // Is false
->toBeEmpty()                      // Is empty
->toBeString()                     // Is string
->toBeInt()                        // Is integer
->toBeFloat()                      // Is float
->toBeBool()                       // Is boolean
->toBeArray()                      // Is array
->toBeObject()                     // Is object
->toBeCallable()                   // Is callable
->toBeIterable()                   // Is iterable
->toBeNumeric()                    // Is numeric
->toBeInstanceOf($class)           // Instance of
->toBeSubclassOf($class)           // Subclass of
```

#### Behavioral Expectations

```php
->toContain($needle)               // Contains value
->notToContain($needle)            // Does not contain
->toStartWith($prefix)             // Starts with
->toEndWith($suffix)               // Ends with
->toMatch($pattern)                // Matches regex
->toSatisfy($callback)             // Satisfies callback
->toExist()                        // Exists (file/class/method)
```

#### Relational Expectations

```php
->toHaveCount($n)                  // Has exact count
->toHaveLength($n)                 // Has exact length
->toHaveKey($key)                  // Has array key
->toHaveKeys($keys)                // Has all keys
->toHaveProperty($prop)            // Has object property
->toHaveProperties($props)         // Has all properties
->toHaveMethod($method)            // Has object method
->toHaveMethods($methods)          // Has all methods
->toHaveValue($value)              // Array contains value
->toHaveValues($values)            // Array contains all values
```

#### Comparison Expectations

```php
->toBeGreaterThan($value)          // Greater than
->toBeGreaterThanOrEqual($value)   // Greater than or equal
->toBeLessThan($value)             // Less than
->toBeLessThanOrEqual($value)      // Less than or equal
->toBeBetween($min, $max)          // Between (inclusive)
```

#### Format Expectations

```php
->toMatchEmail()                   // Valid email
->toMatchUrl()                     // Valid URL
->toMatchUuid()                    // Valid UUID
->toMatchIp()                      // Valid IP
->toMatchDate($format)             // Valid date
->toMatchJson()                    // Valid JSON
```

#### Modifiers

```php
->not                              // Property for negation
->and($value)                      // Chain new expectation
->each(fn($item) => ...)           // Assert on each element
```

### Usage Examples

```php
// Basic expectations
expect($user)->toBeInstanceOf(User::class);
expect($email)->toBeString()->toContain('@');
expect($items)->toBeArray()->toHaveCount(3);

// Negation
expect($value)->not->toBeNull();
expect($password)->not->toBeEmpty();

// Chaining
expect($user)
    ->toBeInstanceOf(User::class)
    ->toHaveProperty('email')
    ->toHaveMethod('isActive');

// Each (collection iteration)
expect($users)->each(fn($user) =>
    $user->toBeInstanceOf(User::class)
         ->toHaveProperty('email')
);

// And chaining (multiple values)
expect($email)->toBeString()->toContain('@')
    ->and($username)->toBeString()->toHaveMinLength(3);
```

## 3. Implementation Strategy

### Phase 1: Core Behavioral Methods

1. Create `BehavioralAssertions` trait with `is*`, `does*`, `has*` methods
2. Add trait to `AssertionChain`
3. Map each behavioral method to existing assertion methods
4. Add method aliases for improved readability

### Phase 2: expect() Function

1. Create `Expectation` class wrapping `AssertionChain`
2. Implement `to*` method mapping
3. Add `expect()` global helper function in `functions.php`
4. Support `->not` property for negation
5. Implement `->each()` for collection iteration
6. Implement `->and()` for value switching

### Phase 3: Enhanced Modifiers

1. `->when()` conditional assertions
2. `->or()` alternative assertion paths
3. `->and()` chaining sugar for Assert::that()
4. Better error messages for behavioral assertions

### Phase 4: Documentation & Examples

1. Update cookbook with behavioral API examples
2. Create migration guide from traditional assertions
3. Add behavioral testing patterns guide
4. Document Pest-style testing with expect()

## 4. Backward Compatibility

- All existing assertion methods remain unchanged
- Behavioral methods are additive, not breaking
- `expect()` is opt-in via helper function
- No changes to core assertion infrastructure

## 5. Benefits

### Readability
```php
// Before
Assert::that($user)->notNull()->isInstanceOf(User::class);

// After (behavioral)
Assert::that($user)->isNotNull()->isInstanceOf(User::class);
```

### Natural Language
```php
// Reads like natural language
Assert::that($items)->hasMinCount(1)->hasMaxCount(10);
Assert::that($email)->isString()->doesContain('@')->matchesEmail();
```

### Pest Integration
```php
// Familiar to Pest users
expect($value)->toBe(42);
expect($items)->toHaveCount(3);
expect($user)->toBeInstanceOf(User::class);
```

### Discoverability
- IDE autocomplete groups related methods
- Method names self-document behavior
- Clear intent from method naming

## 6. Method Count Summary

### Assert::that() Behavioral Methods

- **is()** family: ~25 methods
- **does()** family: ~15 methods
- **has()** family: ~20 methods
- **Comparison**: ~7 methods
- **Format**: ~12 methods
- **Modifiers**: ~5 methods

**Total: ~84 new behavioral methods**

### expect() API

- All behavioral methods mapped to `to*` pattern
- Additional expect-specific: `->not`, `->each()`, `->and()`

**Total: ~87 methods for expect() API**

## 7. Priority Order

1. **High Priority** - Core state checks: `is*()`, `isNot*()`
2. **High Priority** - Core behaviors: `doesContain()`, `hasCount()`, `hasKey()`
3. **Medium Priority** - Format matchers: `matchesEmail()`, `matchesUrl()`
4. **Medium Priority** - expect() API foundation
5. **Low Priority** - Advanced modifiers: `when()`, `or()`
6. **Low Priority** - Collection helpers: `hasUniqueValues()`, `hasSubset()`

## 8. Open Questions

1. Should `is()` be strict (===) or loose (==)? **Recommendation: strict**
2. Should we alias `has()` and `does()` for overlapping cases? **Recommendation: yes, for readability**
3. How to handle negation in expect() API? **Recommendation: `->not` property**
4. Should `expect()->each()` return new expectations or chain? **Recommendation: chain**
5. Error message format for behavioral assertions? **Recommendation: "Expected value to be X but got Y"**

## 9. Next Steps

1. Review and approve this plan
2. Create implementation issues for each phase
3. Start with Phase 1: Core behavioral methods
4. Write comprehensive tests for new methods
5. Update documentation with examples
6. Release as minor version (backward compatible)
