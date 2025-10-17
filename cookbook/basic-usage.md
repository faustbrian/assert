# Basic Usage

The Assert library provides a simple way to validate input data and enforce pre-/post-conditions in your application code. When assertions fail, an exception is thrown, eliminating the need for if-clauses throughout your code.

## Simple Function Validation

Here's a basic example showing how to validate function arguments:

```php
<?php
use Cline\Assert\Assertion;

function duplicateFile($file, $times)
{
    Assertion::file($file);
    Assertion::digit($times);

    for ($i = 0; $i < $times; $i++) {
        copy($file, $file . $i);
    }
}
```

The function validates that `$file` exists and is a valid file, and that `$times` is a digit. If either validation fails, an exception is thrown immediately.

## Real-World Example: Azure Blob Storage

This example from the Azure Blob Storage library demonstrates multiple assertions in sequence:

```php
<?php
use Cline\Assert\Assertion;

public function putBlob($containerName = '', $blobName = '', $localFileName = '', $metadata = array(), $leaseId = null, $additionalHeaders = array())
{
    Assertion::notEmpty($containerName, 'Container name is not specified');
    self::assertValidContainerName($containerName);
    Assertion::notEmpty($blobName, 'Blob name is not specified.');
    Assertion::notEmpty($localFileName, 'Local file name is not specified.');
    Assertion::file($localFileName, 'Local file name is not specified.');
    self::assertValidRootContainerBlobName($containerName, $blobName);

    // Check file size
    if (filesize($localFileName) >= self::MAX_BLOB_SIZE) {
        return $this->putLargeBlob($containerName, $blobName, $localFileName, $metadata, $leaseId, $additionalHeaders);
    }

    // Put the data to Windows Azure Storage
    return $this->putBlobData($containerName, $blobName, file_get_contents($localFileName), $metadata, $leaseId, $additionalHeaders);
}
```

This method validates multiple inputs before proceeding with the operation. Each assertion includes a custom error message for clarity.

## Custom Error Messages

You can provide custom error messages as the second argument to any assertion:

```php
<?php
use Cline\Assert\Assertion;

function processPayment($amount)
{
    Assertion::integer($amount, 'Payment amount must be an integer');
    Assertion::greaterThan($amount, 0, 'Payment amount must be greater than zero');

    // Process the payment...
}
```

## Common Assertions

Here are some commonly used assertions:

### Type Checking

```php
Assertion::string($value);
Assertion::integer($value);
Assertion::float($value);
Assertion::boolean($value);
Assertion::isArray($value);
```

### Value Validation

```php
Assertion::notEmpty($value);
Assertion::notNull($value);
Assertion::email($value);
Assertion::url($value);
Assertion::uuid($value);
```

### Numeric Comparisons

```php
Assertion::greaterThan($value, 10);
Assertion::greaterOrEqualThan($value, 10);
Assertion::lessThan($value, 100);
Assertion::between($value, 10, 100);
Assertion::range($value, 10, 100);
```

### String Operations

```php
Assertion::startsWith($string, 'prefix');
Assertion::endsWith($string, 'suffix');
Assertion::contains($string, 'needle');
Assertion::length($string, 10);
Assertion::minLength($string, 5);
Assertion::maxLength($string, 20);
```

### File System

```php
Assertion::file($path);
Assertion::directory($path);
Assertion::readable($path);
Assertion::writeable($path);
```

### Arrays and Collections

```php
Assertion::count($array, 5);
Assertion::minCount($array, 1);
Assertion::maxCount($array, 10);
Assertion::keyExists($array, 'key');
Assertion::inArray($value, ['option1', 'option2']);
```

## Key Principles

1. **Value First**: The value to validate is always the first parameter
2. **Configuration After**: Any configuration parameters (like limits, needles, etc.) come after the value
3. **Fail Fast**: Assertions throw exceptions immediately on failure
4. **Chainable**: For complex validation, see the chaining cookbook
