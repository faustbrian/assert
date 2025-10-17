# Complete Assertion Reference

All functions below are in the `Cline\Assert\Assertion` namespace. Remember: The value to validate is always the first parameter, and configuration parameters always come after.

Every assertion below automatically has `nullOr*` and `all*` variants available. See the [helpers cookbook](helpers.md) for details.

## Type Assertions

Validate the type of a value.

### Basic Types

```php
Assertion::boolean($value)
```
Validates that the value is a boolean (true or false).

```php
Assertion::integer($value)
```
Validates that the value is an integer.

```php
Assertion::float($value)
```
Validates that the value is a float.

```php
Assertion::string($value)
```
Validates that the value is a string.

```php
Assertion::numeric($value)
```
Validates that the value is numeric (integer, float, or numeric string).

```php
Assertion::integerish($value)
```
Validates that the value is integer-like (integer or numeric string representing an integer).

```php
Assertion::scalar($value)
```
Validates that the value is a scalar (integer, float, string, or boolean).

### Special Types

```php
Assertion::isArray($value)
```
Validates that the value is an array.

```php
Assertion::isObject($value)
```
Validates that the value is an object.

```php
Assertion::isResource($value)
```
Validates that the value is a resource.

```php
Assertion::isCallable($value)
```
Validates that the value is callable.

```php
Assertion::isTraversable($value)
```
Validates that the value is traversable (array or implements Traversable).

```php
Assertion::isArrayAccessible($value)
```
Validates that the value is array-accessible (array or implements ArrayAccess).

```php
Assertion::isCountable($value)
```
Validates that the value is countable (array, Countable, ResourceBundle, or SimpleXMLElement).

```php
Assertion::isJsonString($value)
```
Validates that the value is a valid JSON string.

```php
Assertion::typeIs($value, $type)
```
Validates that the value is of the specified type (using `gettype()`).

## Null and Empty Checks

```php
Assertion::null($value)
```
Validates that the value is null.

```php
Assertion::notNull($value)
```
Validates that the value is not null.

```php
Assertion::notEmpty($value)
```
Validates that the value is not empty (using PHP's `empty()` function).

```php
Assertion::noContent($value)
```
Validates that the value is empty.

```php
Assertion::notBlank($value)
```
Validates that the value is not blank (not empty string after trimming).

```php
Assertion::isEmpty($value)
```
Validates that the value is empty (empty array, empty string, etc.).

```php
Assertion::isNotEmpty($value)
```
Validates that the value is not empty.

## Boolean Assertions

```php
Assertion::true($value)
```
Validates that the value is exactly `true`.

```php
Assertion::false($value)
```
Validates that the value is exactly `false`.

## Comparison Assertions

```php
Assertion::eq($value1, $value2)
```
Validates that `$value1 == $value2` (loose equality).

```php
Assertion::notEq($value1, $value2)
```
Validates that `$value1 != $value2` (loose inequality).

```php
Assertion::same($value1, $value2)
```
Validates that `$value1 === $value2` (strict equality).

```php
Assertion::notSame($value1, $value2)
```
Validates that `$value1 !== $value2` (strict inequality).

```php
Assertion::strictEquals($value1, $value2)
```
Same as `same()` - validates strict equality.

```php
Assertion::eqArraySubset($superset, $subset)
```
Validates that `$subset` array is contained within `$superset` array.

## Numeric Comparisons

```php
Assertion::greaterThan($value, $limit)
```
Validates that `$value > $limit`.

```php
Assertion::greaterOrEqualThan($value, $limit)
```
Validates that `$value >= $limit`.

```php
Assertion::lessThan($value, $limit)
```
Validates that `$value < $limit`.

```php
Assertion::lessOrEqualThan($value, $limit)
```
Validates that `$value <= $limit`.

### Numeric Comparison Shortcuts

```php
Assertion::gt($value, $limit)
```
Shortcut for `greaterThan()`.

```php
Assertion::gte($value, $limit)
```
Shortcut for `greaterOrEqualThan()`.

```php
Assertion::lt($value, $limit)
```
Shortcut for `lessThan()`.

```php
Assertion::lte($value, $limit)
```
Shortcut for `lessOrEqualThan()`.

### Range Comparisons

```php
Assertion::between($value, $lowerLimit, $upperLimit)
```
Validates that `$lowerLimit <= $value <= $upperLimit` (inclusive).

```php
Assertion::betweenExclusive($value, $lowerLimit, $upperLimit)
```
Validates that `$lowerLimit < $value < $upperLimit` (exclusive).

```php
Assertion::range($value, $minValue, $maxValue)
```
Same as `between()` - validates inclusive range.

```php
Assertion::min($value, $minValue)
```
Validates that `$value >= $minValue`.

```php
Assertion::max($value, $maxValue)
```
Validates that `$value <= $maxValue`.

## String Assertions

### String Content

```php
Assertion::contains($string, $needle)
```
Validates that `$string` contains `$needle`.

```php
Assertion::notContains($string, $needle)
```
Validates that `$string` does not contain `$needle`.

```php
Assertion::startsWith($string, $needle)
```
Validates that `$string` starts with `$needle`.

```php
Assertion::endsWith($string, $needle)
```
Validates that `$string` ends with `$needle`.

### String Length

```php
Assertion::length($value, $length)
```
Validates that the string has exactly `$length` characters.

```php
Assertion::minLength($value, $minLength)
```
Validates that the string has at least `$minLength` characters.

```php
Assertion::maxLength($value, $maxLength)
```
Validates that the string has at most `$maxLength` characters.

```php
Assertion::betweenLength($value, $minLength, $maxLength)
```
Validates that the string length is between `$minLength` and `$maxLength` (inclusive).

### Regular Expressions

```php
Assertion::regex($value, $pattern)
```
Validates that `$value` matches the regular expression `$pattern`.

```php
Assertion::notRegex($value, $pattern)
```
Validates that `$value` does not match the regular expression `$pattern`.

```php
Assertion::pregMatch($value, $pattern)
```
Same as `regex()`.

```php
Assertion::matchAll($value, $pattern)
```
Validates that all matches of `$pattern` in `$value` are found.

## Format Assertions

### String Formats

```php
Assertion::alnum($value)
```
Validates that the value contains only alphanumeric characters.

```php
Assertion::digit($value)
```
Validates that the value contains only digits.

```php
Assertion::base64($value)
```
Validates that the value is a valid base64-encoded string.

### Network Formats

```php
Assertion::email($value)
```
Validates that the value is a valid email address.

```php
Assertion::url($value)
```
Validates that the value is a valid URL.

```php
Assertion::ip($value, $flag = null)
```
Validates that the value is a valid IP address. Optional `$flag` can be FILTER_FLAG_IPV4 or FILTER_FLAG_IPV6.

```php
Assertion::ipv4($value, $flag = null)
```
Validates that the value is a valid IPv4 address.

```php
Assertion::ipv6($value, $flag = null)
```
Validates that the value is a valid IPv6 address.

```php
Assertion::e164($value)
```
Validates that the value is a valid E.164 phone number format.

### Identifiers

```php
Assertion::uuid($value)
```
Validates that the value is a valid UUID.

## Date and Time

```php
Assertion::date($value, $format)
```
Validates that the value is a date string matching the specified format.

## Array and Collection Assertions

### Array Structure

```php
Assertion::keyExists($array, $key)
```
Validates that the array has the specified key.

```php
Assertion::keyNotExists($array, $key)
```
Validates that the array does not have the specified key.

```php
Assertion::keyIsset($array, $key)
```
Validates that the array key exists and is not null.

```php
Assertion::notEmptyKey($array, $key)
```
Validates that the array key exists and its value is not empty.

### Array Counts

```php
Assertion::count($countable, $count)
```
Validates that the countable has exactly `$count` items.

```php
Assertion::minCount($countable, $count)
```
Validates that the countable has at least `$count` items.

```php
Assertion::maxCount($countable, $count)
```
Validates that the countable has at most `$count` items.

### Array Values

```php
Assertion::inArray($value, $choices)
```
Validates that `$value` is in the `$choices` array.

```php
Assertion::notInArray($value, $choices)
```
Validates that `$value` is not in the `$choices` array.

```php
Assertion::choice($value, $choices)
```
Same as `inArray()` - validates that value is one of the choices.

```php
Assertion::choicesNotEmpty($values, $choices)
```
Validates that all values in `$values` are in `$choices` and not empty.

```php
Assertion::uniqueValues($values)
```
Validates that all values in the array are unique.

## Object and Class Assertions

### Instance Checks

```php
Assertion::isInstanceOf($value, $className)
```
Validates that `$value` is an instance of `$className`.

```php
Assertion::notIsInstanceOf($value, $className)
```
Validates that `$value` is not an instance of `$className`.

### Class Checks

```php
Assertion::classExists($value)
```
Validates that the class name exists.

```php
Assertion::interfaceExists($value)
```
Validates that the interface name exists.

```php
Assertion::implementsInterface($class, $interfaceName)
```
Validates that the class implements the specified interface.

```php
Assertion::subclassOf($value, $className)
```
Validates that `$value` is a subclass of `$className`.

### Object Properties

```php
Assertion::propertyExists($value, $property)
```
Validates that the object has the specified property.

```php
Assertion::propertiesExist($value, $properties)
```
Validates that the object has all the specified properties.

```php
Assertion::methodExists($value, $object)
```
Validates that the method exists on the object.

```php
Assertion::objectOrClass($value)
```
Validates that the value is an object or a class name string.

## File System Assertions

```php
Assertion::file($value)
```
Validates that the path points to an existing file.

```php
Assertion::directory($value)
```
Validates that the path points to an existing directory.

```php
Assertion::readable($value)
```
Validates that the file/directory is readable.

```php
Assertion::writeable($value)
```
Validates that the file/directory is writeable.

## PHP Environment Assertions

```php
Assertion::phpVersion($operator, $version)
```
Validates the PHP version against the specified version using the operator.

```php
Assertion::extensionLoaded($value)
```
Validates that the PHP extension is loaded.

```php
Assertion::extensionVersion($extension, $operator, $version)
```
Validates the extension version against the specified version.

```php
Assertion::defined($constant)
```
Validates that the constant is defined.

## Version Assertions

```php
Assertion::version($version1, $operator, $version2)
```
Validates version comparison using version_compare().

## Advanced Assertions

### Custom Callbacks

```php
Assertion::satisfy($value, $callback)
```
Validates that the callback returns true when passed the value.

### Aggregation

```php
Assertion::sum($values)
```
Returns the sum of all values in the array.

```php
Assertion::product($values)
```
Returns the product of all values in the array.

```php
Assertion::average($values)
```
Returns the average of all values in the array (alias: `mean()`).

```php
Assertion::mean($values)
```
Same as `average()`.

```php
Assertion::median($values)
```
Returns the median of all values in the array.

```php
Assertion::minValue($values)
```
Returns the minimum value from the array.

```php
Assertion::maxValue($values)
```
Returns the maximum value from the array.

## Quick Reference by Use Case

### User Input Validation
- `notEmpty()`, `email()`, `minLength()`, `maxLength()`, `regex()`

### Numeric Validation
- `integer()`, `float()`, `greaterThan()`, `between()`, `range()`

### String Validation
- `string()`, `minLength()`, `maxLength()`, `startsWith()`, `endsWith()`, `contains()`

### Array Validation
- `isArray()`, `keyExists()`, `count()`, `minCount()`, `maxCount()`, `inArray()`

### Type Safety
- `integer()`, `string()`, `boolean()`, `isArray()`, `isObject()`, `isInstanceOf()`

### File Operations
- `file()`, `directory()`, `readable()`, `writeable()`

### API/Form Data
- Use with `Assert::lazy()` to collect all errors - see [lazy-assertions cookbook](lazy-assertions.md)

## Helper Patterns

Remember, ALL assertions automatically support:

### nullOr Pattern
```php
Assertion::nullOrEmail($value);      // Passes if null OR valid email
Assertion::nullOrInteger($value);     // Passes if null OR integer
Assertion::nullOrBetween($value, 10, 100);  // Passes if null OR between 10-100
```

### all Pattern
```php
Assertion::allEmail($emails);        // All must be valid emails
Assertion::allInteger($numbers);     // All must be integers
Assertion::allBetween($values, 10, 100);  // All must be between 10-100
```

See the [helpers cookbook](helpers.md) for complete details on these patterns.
