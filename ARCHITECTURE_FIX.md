# Expectation API - Proper Architecture Implementation

## Date: 2025-01-27

## Objective

Convert inline logic in Expectation methods to first-class assertions in the Assertion class, maintaining the Expectation API as a pure facade/wrapper.

## Problem

The Expectation API had 23 methods with inline validation logic that should have been first-class assertions in the Assertion class.

## Solution

Added 23 new assertion methods to the Assertion class and updated Expectation methods to properly delegate using `$this->invoke()`.

## Added Assertion Methods (23 total)

### String Case Methods (6)
1. **`snakeCase(string $value)`** - Validates snake_case format
2. **`kebabCase(string $value)`** - Validates kebab-case format
3. **`camelCase(string $value)`** - Validates camelCase format
4. **`studlyCase(string $value)`** - Validates StudlyCase format
5. **`uppercase(string $value)`** - Validates uppercase strings
6. **`lowercase(string $value)`** - Validates lowercase strings

### Numeric Edge Cases (2)
7. **`infinite(mixed $value)`** - Validates infinite values
8. **`nan(mixed $value)`** - Validates NaN values

### Equality Helpers (2)
9. **`equalCanonicalizing(array $value, array $expected)`** - Array equality ignoring order
10. **`equalWithDelta(float|int $value, float|int $expected, float $delta)`** - Numeric comparison with delta

### Collection Methods (5)
11. **`containEqual(array $value, mixed $needle)`** - Deep equality check
12. **`matchArray(array $value, array $expected)`** - Array subset matching
13. **`matchObject(object $value, array $expected)`** - Object property matching
14. **`sameSize(array|Countable $value, array|Countable $expected)`** - Count equality
15. **`containOnlyInstancesOf(array $value, string $className)`** - Type checking

### Array Key Case Methods (4)
16. **`snakeCaseKeys(array $value)`** - All keys snake_case
17. **`kebabCaseKeys(array $value)`** - All keys kebab-case
18. **`camelCaseKeys(array $value)`** - All keys camelCase
19. **`studlyCaseKeys(array $value)`** - All keys StudlyCase

### File Methods (4)
20. **`readableFile(string $value)`** - file() + readable()
21. **`writableFile(string $value)`** - file() + writeable()
22. **`readableDirectory(string $value)`** - directory() + readable()
23. **`writableDirectory(string $value)`** - directory() + writeable()

## Updated Expectation Methods

All 23 corresponding Expectation methods now properly delegate:

**Before (inline logic)**:
```php
public function toBeSnakeCase(): self
{
    throw_unless(is_string($this->value), ...);
    $pattern = '/^[a-z]+(_[a-z]+)*$/';
    $isSnakeCase = preg_match($pattern, $this->value) === 1;
    throw_unless($isSnakeCase, ...);
    return $this;
}
```

**After (proper delegation)**:
```php
public function toBeSnakeCase(): self
{
    return $this->invoke('snakeCase');
}
```

## Architecture Benefits

1. **Single Source of Truth** - All assertion logic in Assertion class
2. **Reusability** - Assertion methods can be used directly: `Assertion::snakeCase('test_value')`
3. **Consistency** - Expectation API behavior guaranteed to match Assertion class
4. **Maintainability** - Changes only need to happen in one place
5. **Testability** - Can test assertions directly without going through Expectation wrapper

## Test Results

- **Total tests**: 907 passing ✅
- **Expectation tests**: 237 passing ✅
- **All existing functionality preserved**

## Implementation Pattern

All new Assertion methods follow the established pattern:

```php
public static function methodName(
    mixed $value,
    string|callable $message = null,
    string $propertyPath = null
): bool {
    // Validation logic
    if (!condition) {
        self::createException(
            $value,
            $message ?: 'Default error message',
            ValidationError::CONSTANT,
            $propertyPath
        );
    }

    return true;
}
```

## Conclusion

The Expectation API is now properly architected as a **pure facade** that delegates all assertion logic to the Assertion class. All 23 methods that previously had inline logic now use first-class assertions, maintaining consistency and proper separation of concerns.
