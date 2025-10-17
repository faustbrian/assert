# Validation Assertions

Validation assertions check common data formats like emails, URLs, UUIDs, and IP addresses.

## Available Assertions

### email()

Assert that a value is a valid email address.

```php
use Cline\Assert\Assertion;

Assertion::email('user@example.com');
Assertion::email($emailAddress, 'Invalid email address');
```

### url()

Assert that a value is a valid URL.

```php
Assertion::url('https://example.com');
Assertion::url($websiteUrl, 'Invalid URL format');
```

### uuid()

Assert that a value is a valid UUID.

```php
Assertion::uuid('550e8400-e29b-41d4-a716-446655440000');
Assertion::uuid($id, 'Invalid UUID format');
```

### ip()

Assert that a value is a valid IPv4 or IPv6 address.

```php
Assertion::ip('192.168.1.1');
Assertion::ip('2001:0db8:85a3:0000:0000:8a2e:0370:7334');
Assertion::ip($address, null, 'Invalid IP address');
```

### ipv4()

Assert that a value is a valid IPv4 address.

```php
Assertion::ipv4('192.168.1.1');
Assertion::ipv4($address, null, 'Invalid IPv4 address');
```

### ipv6()

Assert that a value is a valid IPv6 address.

```php
Assertion::ipv6('2001:0db8:85a3:0000:0000:8a2e:0370:7334');
Assertion::ipv6($address, null, 'Invalid IPv6 address');
```

### e164()

Assert that a value is a valid E.164 phone number format.

```php
Assertion::e164('+14155552671');
Assertion::e164($phoneNumber, 'Invalid phone number format');
```

### base64()

Assert that a value is valid base64 encoded data.

```php
Assertion::base64('SGVsbG8gV29ybGQ=');
Assertion::base64($encoded, 'Invalid base64 encoding');
```

### isJsonString()

Assert that a value is a valid JSON string.

```php
Assertion::isJsonString('{"key":"value"}');
Assertion::isJsonString($jsonData, 'Invalid JSON format');
```

### date()

Assert that a date string matches a specific format.

```php
Assertion::date('2024-01-15', 'Y-m-d');
Assertion::date($dateString, 'Y-m-d H:i:s', 'Invalid date format');
```

## Chaining Validation Assertions

```php
use Cline\Assert\Assert;

Assert::that($email)
    ->string()
    ->notEmpty()
    ->email('Please provide a valid email address');

Assert::that($website)
    ->string()
    ->url('Invalid website URL')
    ->startsWith('https://', 'Website must use HTTPS');
```

## Common Patterns

### User Registration Validation

```php
Assert::lazy()
    ->that($data['email'], 'email')
        ->notEmpty('Email is required')
        ->email('Invalid email address')
    ->that($data['phone'] ?? null, 'phone')
        ->nullOr()->e164('Invalid phone number format')
    ->verifyNow();
```

### API Input Validation

```php
Assert::that($payload)
    ->isJsonString('Request body must be valid JSON')
    ->satisfy(function($json) {
        $data = json_decode($json, true);
        return isset($data['user_id']) && isset($data['action']);
    }, 'Missing required fields');
```

### URL Validation with Protocol

```php
Assert::that($redirectUrl)
    ->url('Invalid redirect URL')
    ->regex('/^https:\/\//', 'Only HTTPS URLs are allowed')
    ->notContains('..', 'Path traversal detected');
```

### UUID Primary Key Validation

```php
Assert::that($userId)
    ->notEmpty('User ID is required')
    ->uuid('Invalid user ID format');
```

### IP Whitelist Validation

```php
$allowedIps = ['192.168.1.1', '192.168.1.2', '10.0.0.1'];

Assert::that($clientIp)
    ->ipv4('Invalid IP address')
    ->inArray($allowedIps, 'IP address not allowed');
```

### Date Range Validation

```php
Assert::that($startDate)
    ->date('Y-m-d', 'Invalid start date format');

Assert::that($endDate)
    ->date('Y-m-d', 'Invalid end date format');

// Additional business logic validation
assert(
    strtotime($endDate) >= strtotime($startDate),
    'End date must be after start date'
);
```

## Email Validation

### Basic Email

```php
Assert::that($email)
    ->email();
```

### Email with Domain Check

```php
Assert::that($email)
    ->email('Invalid email format')
    ->endsWith('@company.com', 'Must use company email');
```

### Multiple Email Validation

```php
Assert::thatAll($recipients)
    ->email('All recipients must have valid email addresses');
```

## URL Validation

### Basic URL

```php
Assert::that($url)
    ->url();
```

### HTTPS Only

```php
Assert::that($url)
    ->url('Invalid URL')
    ->startsWith('https://', 'Only HTTPS URLs allowed');
```

### Domain Restriction

```php
Assert::that($callbackUrl)
    ->url()
    ->contains('example.com', 'Callback must be on example.com domain');
```

## IP Address Validation

### Any IP Version

```php
Assert::that($ip)
    ->ip();
```

### IPv4 Only

```php
Assert::that($serverIp)
    ->ipv4('Server IP must be IPv4');
```

### IPv6 Only

```php
Assert::that($ipv6Address)
    ->ipv6('Invalid IPv6 address');
```

### Private IP Range

```php
Assert::that($internalIp)
    ->ipv4()
    ->satisfy(function($ip) {
        return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE) === false;
    }, 'Must be private IP address');
```

## Phone Number Validation

### E.164 Format

```php
Assert::that($phone)
    ->e164('Phone must be in E.164 format (+1234567890)');
```

### With Country Code Validation

```php
Assert::that($phone)
    ->e164()
    ->startsWith('+1', 'Must be US/Canada phone number');
```

## JSON Validation

### Valid JSON String

```php
Assert::that($jsonData)
    ->isJsonString('Invalid JSON');
```

### JSON with Structure Validation

```php
Assert::that($jsonData)
    ->isJsonString('Invalid JSON')
    ->satisfy(function($json) {
        $data = json_decode($json, true);
        return isset($data['type']) && isset($data['payload']);
    }, 'JSON must contain type and payload fields');
```

## Date Format Validation

### ISO 8601 Format

```php
Assert::that($timestamp)
    ->date('Y-m-d\TH:i:sP', 'Must be ISO 8601 format');
```

### Custom Format

```php
Assert::that($birthDate)
    ->date('m/d/Y', 'Date must be in MM/DD/YYYY format');
```

### Multiple Formats

```php
$formats = ['Y-m-d', 'Y-m-d H:i:s', 'm/d/Y'];
$valid = false;

foreach ($formats as $format) {
    try {
        Assertion::date($dateString, $format);
        $valid = true;
        break;
    } catch (AssertionFailedException) {
        continue;
    }
}

assert($valid, 'Date must match one of the supported formats');
```

## Best Practices

### Validate Format Before Processing

```php
// ✓ Validate first
Assert::that($email)
    ->email();

$user = User::where('email', $email)->first();

// ✗ Process without validation
$user = User::where('email', $email)->first(); // SQL injection risk
```

### Use Specific Validators

```php
// ✗ Generic regex
Assertion::regex($email, '/^[^@]+@[^@]+\.[^@]+$/');

// ✓ Use built-in validator
Assertion::email($email);
```

### Combine with Other Assertions

```php
// ✓ Type and format validation
Assert::that($email)
    ->string()
    ->notEmpty()
    ->email()
    ->maxLength(255);
```

### Clear Error Messages

```php
// ✗ Generic
Assertion::email($input);

// ✓ Contextual
Assertion::email($input, 'Please provide a valid email address for registration');
```

## Security Considerations

### URL Validation for Redirects

```php
function redirect(string $url): void
{
    Assert::that($url)
        ->url('Invalid redirect URL')
        ->startsWith('https://example.com/', 'Can only redirect to example.com')
        ->notContains('..', 'Path traversal attempt detected');
    
    header("Location: {$url}");
}
```

### Email Injection Prevention

```php
Assert::that($email)
    ->email()
    ->notContains("\n", 'Email contains line breaks')
    ->notContains("\r", 'Email contains carriage returns');
```

## Next Steps

- **[String Assertions](string-assertions.md)** - String format validation
- **[Custom Assertions](custom-assertions.md)** - Create custom validators
- **[Lazy Assertions](lazy-assertions.md)** - Validate multiple fields at once
