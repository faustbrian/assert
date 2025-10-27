# Expectation API - Recommended Additions

## Analysis Date: 2025-01-27

Based on research of Vitest, Jest, and Pest PHP expectation APIs, here are recommended additions to enhance our expectation API.

## Current Coverage

✅ **We have excellent coverage of:**
- Core equality and type checks
- String case validation (snake, kebab, camel, studly, upper, lower)
- Numeric comparisons including edge cases (infinite, nan, delta)
- Collection operations (contain, match, keys validation)
- Object property/method checks
- File system checks
- Format validation (email, url, uuid, json)
- Exception testing
- Modifiers: not, each, and, when, unless, sequence, json, match, dd, ray

## High-Priority Additions (10 features)

### 1. **Soft Assertions** (Vitest)
```php
// Continue test execution after failures, collect all errors
expect($user)
    ->soft->toHaveProperty('name')
    ->soft->toHaveProperty('email')
    ->soft->toHaveProperty('phone'); // Reports all 3 failures instead of stopping at first
```

**Value:** Better test feedback, see all failures at once
**Implementation:** Add `soft` property that catches exceptions and collects them

### 2. **toBeCloseTo()** (Jest/Vitest)
```php
// Already have toEqualWithDelta() ✅
// But could add alias for Jest compatibility
expect(0.1 + 0.2)->toBeCloseTo(0.3, 2); // precision parameter
```

**Value:** More intuitive API for floating point comparison
**Implementation:** Alias for `toEqualWithDelta()`

### 3. **toBeDefined() / toBeUndefined()** (Jest)
```php
expect($var)->toBeDefined();
expect($undefined)->toBeUndefined();
```

**Value:** Explicit undefined checks (especially useful for array keys, object properties)
**Implementation:** Check `isset()` vs `!isset()`

### 4. **Asymmetric Matchers** (Jest/Vitest)
```php
// Match partial object structures
expect($response)->toEqual([
    'id' => expect()->any('integer'),
    'name' => expect()->stringContaining('John'),
    'data' => expect()->arrayContaining(['key' => 'value']),
    'timestamp' => expect()->anything(),
]);
```

**Value:** Flexible assertions without exact equality
**Implementation:** Create matcher objects that return comparison results

### 5. **toHaveLength() for objects** (Extend existing)
```php
// Already works for strings and arrays ✅
// Extend to Countable objects
expect(new Collection([1,2,3]))->toHaveLength(3);
```

**Value:** Better Countable support
**Implementation:** Extend existing `toHaveLength()` to check `instanceof Countable`

### 6. **toSatisfy()** (Custom predicate)
```php
expect($user)->toSatisfy(fn($u) => $u->age > 18 && $u->verified === true);
expect($data)->toSatisfy(fn($d) => count($d) > 0 && isset($d['required_key']));
```

**Value:** Complex custom validations without creating custom matchers
**Implementation:** Accept callable, execute and check boolean result

### 7. **Mock/Spy Assertions** (Jest style)
```php
$mock = mock(UserService::class);

expect($mock)->toHaveBeenCalled();
expect($mock)->toHaveBeenCalledTimes(3);
expect($mock)->toHaveBeenCalledWith('arg1', 'arg2');
expect($mock)->toHaveBeenLastCalledWith('final_arg');
expect($mock)->toHaveReturned();
expect($mock)->toHaveReturnedWith($expectedValue);
```

**Value:** First-class mock testing support
**Implementation:** Integrate with existing mock framework

### 8. **toContainValue() / toContainKey()** (Explicit naming)
```php
// More explicit than toContain() and toHaveKey()
expect(['a' => 1, 'b' => 2])->toContainValue(1);
expect(['a' => 1, 'b' => 2])->toContainKey('a');
```

**Value:** Clearer intent, less ambiguity
**Implementation:** Aliases for existing methods

### 9. **toBeOneOf()** (Multiple valid values)
```php
expect($status)->toBeOneOf(['pending', 'active', 'completed']);
expect($role)->toBeOneOf(['admin', 'user']);
```

**Value:** Simpler than multiple OR assertions
**Implementation:** Alias for `toBeIn()` (we already have this! ✅)

### 10. **toContainAllValues() / toContainAllKeys()**
```php
expect($array)->toContainAllValues([1, 2, 3]); // All must be present
expect($array)->toContainAllKeys(['name', 'email', 'phone']);
```

**Value:** Batch validation
**Implementation:** Loop checking each value/key exists

## Medium-Priority Additions (5 features)

### 11. **toHaveBeenCalledBefore() / toHaveBeenCalledAfter()** (Vitest)
```php
expect($eventA)->toHaveBeenCalledBefore($eventB);
expect($cleanup)->toHaveBeenCalledAfter($operation);
```

**Value:** Verify execution order in complex flows
**Implementation:** Track call timestamps on mocks

### 12. **expect.poll()** (Vitest - Async polling)
```php
// Poll until condition is true or timeout
expect()->poll(fn() => $cache->has('key'), 1000, 100); // timeout, interval
```

**Value:** Testing async/delayed operations
**Implementation:** Loop with sleep until success or timeout

### 13. **toMatchSchema()** (Vitest style)
```php
// Validate against JSON Schema or similar
expect($data)->toMatchSchema($jsonSchema);
```

**Value:** Schema validation integration
**Implementation:** Integrate with validation libraries (respect/validation, symfony/validator)

### 14. **toBeWithinRange()** (Explicit range)
```php
// We have toBeBetween() ✅
// Could add explicit range syntax
expect($value)->toBeWithinRange(1, 10);
```

**Value:** Clearer naming
**Implementation:** Alias for `toBeBetween()`

### 15. **Snapshot Testing** (Jest style)
```php
expect($output)->toMatchSnapshot();
expect($rendered)->toMatchInlineSnapshot('expected output');
```

**Value:** Visual/structure regression testing
**Implementation:** Save/compare snapshots to disk

## Low-Priority / Nice-to-Have (5 features)

### 16. **toBePositive() / toBeNegative()**
```php
expect($profit)->toBePositive();
expect($loss)->toBeNegative();
expect($zero)->toBeZero();
```

**Value:** Convenient numeric checks
**Implementation:** Simple > 0, < 0, === 0 checks

### 17. **toBeEven() / toBeOdd()**
```php
expect($count)->toBeEven();
expect($userId)->toBeOdd();
```

**Value:** Number property checks
**Implementation:** `$value % 2 === 0` / `$value % 2 !== 0`

### 18. **toBeDivisibleBy()**
```php
expect($value)->toBeDivisibleBy(5);
expect($count)->toBeDivisibleBy(10);
```

**Value:** Modulo checks
**Implementation:** `$value % $divisor === 0`

### 19. **toBeDate() / toBeDateTime()**
```php
expect($value)->toBeDate();
expect($value)->toBeDateTime();
expect($value)->toBeValidDate('Y-m-d');
```

**Value:** Date validation
**Implementation:** Check DateTime/DateTimeInterface or parse string

### 20. **toHaveSameKeys()**
```php
expect($array1)->toHaveSameKeys($array2);
```

**Value:** Compare array structures
**Implementation:** `array_keys($a) === array_keys($b)`

## Implementation Priority

### Phase 1: High-Impact, Low-Effort ✨
1. **toBeDefined/toBeUndefined** - Simple isset() check
2. **toSatisfy()** - Accept callable, run it
3. **toBeOneOf()** - Alias for toBeIn() ✅
4. **toBeCloseTo()** - Alias for toEqualWithDelta() ✅
5. **toContainAllValues/Keys** - Simple loops

### Phase 2: High-Impact, Medium-Effort 🎯
6. **Soft Assertions** - Requires error collection mechanism
7. **Asymmetric Matchers** - Requires matcher object system
8. **Mock Assertions** - Requires mock integration

### Phase 3: Medium-Impact, Medium-Effort 📊
9. **toMatchSchema()** - Requires validation library integration
10. **Snapshot Testing** - Requires file system management

### Phase 4: Nice-to-Have 🎁
11. **Numeric helpers** - toBePositive, toBeEven, toBeDivisibleBy
12. **Date helpers** - toBeDate, toBeDateTime
13. **Array structure** - toHaveSameKeys

## Features We Already Have (But May Not Be Documented) ✅

- `toBeIn()` - Same as toBeOneOf()
- `toEqualWithDelta()` - Same as toBeCloseTo()
- `toHaveKeys()` - Multiple key checking
- `toHaveProperties()` - Multiple property checking
- Comprehensive string case validation (snake, kebab, camel, studly)
- Array key case validation
- File system checks

## Recommendations Summary

**Implement Now:**
1. **Soft assertions** (`->soft`) - Game changer for test feedback
2. **toBeDefined/toBeUndefined** - Common need
3. **toSatisfy()** - Powerful custom validation
4. **Asymmetric matchers** - Modern flexible assertions

**Implement Soon:**
5. **Mock assertions** - Essential for comprehensive testing
6. **toContainAllValues/Keys** - Useful batch operations

**Consider Later:**
7. Snapshot testing (if regression testing is a common use case)
8. Schema validation integration
9. Numeric/date helpers (nice-to-have conveniences)

## Conclusion

Our expectation API already has **excellent coverage** compared to Vitest/Jest/Pest. The most valuable additions would be:

1. **Soft assertions** - unique testing workflow improvement
2. **Asymmetric matchers** - modern flexible assertions
3. **toBeDefined/toBeUndefined** - fills a common gap
4. **toSatisfy()** - powerful custom validation
5. **Mock assertions** - complete testing solution

Everything else is either:
- Already implemented (toBeIn, toEqualWithDelta)
- An alias/convenience method (toBeOneOf → toBeIn)
- A nice-to-have that can wait (numeric helpers, snapshot testing)
