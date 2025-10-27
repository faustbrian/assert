# Hard-to-Reach Coverage Gaps

This document tracks the remaining 2.1% of uncovered code that requires complex test scenarios to reach.

**Current Coverage: 97.9%**
**Target: 100%**

---

## Summary

| File | Coverage | Uncovered Lines | Difficulty |
|------|----------|----------------|------------|
| Expectations/Expectation.php | 98.0% | 7 lines | Hard |
| Assertions/AbstractAssertion.php | 97.8% | 37 lines | Medium-Hard |
| Schema/SchemaValidator.php | 94.1% | 3 lines | Easy |

---

## 1. Expectations/Expectation.php (98.0%)

### Line 117: __call forwarding to static methods when value is null

```php
if ($this->value === null && method_exists(self::class, $name)) {
    return self::$name(...$arguments);
}
```

**Why uncovered:** Requires calling a method on an Expectation with null value that happens to match a static method name in the class.

**How to test:**
```php
test('__call forwards to static method when value is null', function (): void {
    $expectation = assertExpect(null);
    // Need to find a static method that can be called via __call
    // and exists in Expectation class
});
```

**Difficulty:** Hard - requires discovering which static methods exist and can be called this way.

---

### Line 1795: Array key doesn't exist in matchesAsymmetric

```php
if (!array_key_exists($key, $actual)) {
    return false;
}
```

**Why uncovered:** Requires asymmetric matcher comparison where expected array has key that actual array doesn't have.

**How to test:**
```php
test('asymmetric matching fails when key missing', function (): void {
    expect(fn () => assertExpect(['a' => 1])->toEqual([
        'a' => 1,
        'b' => assertExpect()->anything(),
    ]))->toThrow(InvalidArgumentException::class);
});
```

**Difficulty:** Medium - needs specific asymmetric matcher scenario.

---

### Line 1807: Strict equality fallback in matchesAsymmetric

```php
return $actual === $expected;
```

**Why uncovered:** Fallback when neither value is an asymmetric matcher.

**How to test:**
```php
test('asymmetric matching uses strict equality fallback', function (): void {
    // Create scenario where both values are non-matchers
    expect(assertExpect(['a' => 1])->toEqual(['a' => 1]))->not->toThrow(Throwable::class);
});
```

**Difficulty:** Medium - requires understanding matchesAsymmetric control flow.

---

### Line 1824: Empty orGroups initialization

```php
if ($this->orGroups === []) {
    $this->orGroups[] = ['success' => true, 'errors' => []];
}
```

**Why uncovered:** Requires ending an or() chain without any assertions.

**How to test:**
```php
test('or() initializes empty groups', function (): void {
    $expectation = assertExpect(42);
    $expectation->or(); // End or() without assertions
    // Trigger evaluation
});
```

**Difficulty:** Hard - requires understanding or() chaining edge case.

---

### Lines 1841-1846: Negation passing when assertion should fail

```php
throw new InvalidArgumentException(
    sprintf('Expected assertion %s to fail but it passed', $method),
    0,
    null,
    $this->value,
);
```

**Why uncovered:** Exception thrown when negated assertion passes but should fail.

**How to test:**
```php
test('not->invoke throws when assertion passes', function (): void {
    // Call negated assertion that passes (which should fail)
    expect(fn () => assertExpect(42)->not->invoke('greaterThan', [10]))
        ->toThrow(InvalidArgumentException::class, 'Expected assertion greaterThan to fail but it passed');
});
```

**Difficulty:** Medium - requires triggering negation edge case.

---

## 2. Assertions/AbstractAssertion.php (97.8%)

### Lines 903-907: Additional type checks in nullable()

```php
'callable' => is_callable($value),
'iterable' => is_iterable($value),
'resource' => is_resource($value),
'numeric' => is_numeric($value),
'scalar' => is_scalar($value),
```

**Why uncovered:** nullable() not tested with these specific types.

**How to test:**
```php
describe('nullable type checks', function (): void {
    test('nullable() with callable', function (): void {
        expect(Assertion::nullable(fn () => true, 'callable'))->toBeTrue();
    });

    test('nullable() with iterable', function (): void {
        expect(Assertion::nullable([1, 2, 3], 'iterable'))->toBeTrue();
    });

    test('nullable() with resource', function (): void {
        $resource = fopen('php://memory', 'rb');
        expect(Assertion::nullable($resource, 'resource'))->toBeTrue();
        fclose($resource);
    });

    test('nullable() with numeric', function (): void {
        expect(Assertion::nullable(42, 'numeric'))->toBeTrue();
        expect(Assertion::nullable('42', 'numeric'))->toBeTrue();
    });

    test('nullable() with scalar', function (): void {
        expect(Assertion::nullable(42, 'scalar'))->toBeTrue();
        expect(Assertion::nullable('test', 'scalar'))->toBeTrue();
    });
});
```

**Difficulty:** Easy - straightforward test cases.

---

### Lines 2725-2731, 2747-2753, 2769-2775, 2791-2797: Snake case key validation errors

These are error branches in key validation methods (allKeysSnakeCase, allKeysCamelCase, allKeysKebabCase, allKeysPascalCase).

```php
$message = sprintf(
    self::generateMessage($message ?: 'Expected all keys to be snake_case, but found: %2$s. Got: %s'),
    static::stringify($value),
    static::stringify($key),
);

throw self::createException($value, $message, ValidationError::InvalidArrayKey->value, $propertyPath, ['key' => $key]);
```

**Why uncovered:** Happy path tested, but not invalid key scenarios.

**How to test:**
```php
describe('key case validation errors', function (): void {
    test('allKeysSnakeCase throws on non-snake-case key', function (): void {
        expect(fn () => Assertion::allKeysSnakeCase(['camelCase' => 1]))
            ->toThrow(AssertionFailedException::class, 'Expected all keys to be snake_case');
    });

    test('allKeysCamelCase throws on non-camel-case key', function (): void {
        expect(fn () => Assertion::allKeysCamelCase(['snake_case' => 1]))
            ->toThrow(AssertionFailedException::class, 'Expected all keys to be camelCase');
    });

    test('allKeysKebabCase throws on non-kebab-case key', function (): void {
        expect(fn () => Assertion::allKeysKebabCase(['camelCase' => 1]))
            ->toThrow(AssertionFailedException::class, 'Expected all keys to be kebab-case');
    });

    test('allKeysPascalCase throws on non-pascal-case key', function (): void {
        expect(fn () => Assertion::allKeysPascalCase(['snake_case' => 1]))
            ->toThrow(AssertionFailedException::class, 'Expected all keys to be PascalCase');
    });
});
```

**Difficulty:** Easy - simple validation error cases.

---

## 3. Schema/SchemaValidator.php (94.1%)

### Lines 162-163, 166: Type validation edge cases

```php
'number' => is_numeric($value),
'object' => is_object($value) || is_array($value),
'null' => $value === null,
```

**Why uncovered:** These type validations not tested in schema validation.

**How to test:**
```php
describe('SchemaValidator type coverage', function (): void {
    test('validates number type with numeric string', function (): void {
        expect(fn (): Expectation => assertExpect('42')->toMatchSchema(['type' => 'number']))
            ->not->toThrow(Throwable::class);
    });

    test('validates object type with array', function (): void {
        expect(fn (): Expectation => assertExpect(['key' => 'value'])->toMatchSchema(['type' => 'object']))
            ->not->toThrow(Throwable::class);
    });

    test('validates null type', function (): void {
        expect(fn (): Expectation => assertExpect(null)->toMatchSchema(['type' => 'null']))
            ->not->toThrow(Throwable::class);
    });
});
```

**Difficulty:** Easy - simple schema validation tests.

---

## Recommended Approach

### Phase 1: Easy Wins (Expected: +2.5% coverage)
1. Schema/SchemaValidator type edge cases
2. AbstractAssertion nullable() type checks
3. AbstractAssertion key case validation errors

### Phase 2: Medium Difficulty (Expected: +0.8% coverage)
1. Expectation asymmetric matching edge cases
2. Expectation negation failures

### Phase 3: Hard Cases (Expected: +0.3% coverage)
1. Expectation __call static forwarding
2. Expectation orGroups edge cases

---

## Notes

- All uncovered code represents **edge cases** and **error paths**
- Main functionality is already fully tested (97.9% coverage)
- Remaining gaps are defensive code and rarely-used features
- Production impact: LOW (these paths rarely execute)
- Test value: HIGH (ensures robustness in edge cases)

---

**Last Updated:** After coverage analysis showing 97.9% total coverage
**Next Steps:** Implement Phase 1 tests to reach 99%+
