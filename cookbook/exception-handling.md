# Exception Handling

When an assertion fails, the Assert library throws an exception. Understanding how these exceptions work and how to handle them is crucial for building robust applications.

## Exception Hierarchy

All assertion failures throw exceptions implementing the `Assert\AssertionFailedException` interface. The default implementation is `Assert\InvalidArgumentException`, which extends PHP's SPL `InvalidArgumentException`.

```php
<?php
use Cline\Assert\Assertion;
use Cline\Assert\AssertionFailedException;
use Cline\Assert\InvalidArgumentException;

try {
    Assertion::integer("not a number");
} catch (AssertionFailedException $e) {
    // Catches any assertion failure
} catch (InvalidArgumentException $e) {
    // Catches the default implementation
}
```

## Basic Exception Handling

```php
<?php
use Cline\Assert\Assertion;
use Cline\Assert\AssertionFailedException;

function processPayment($amount)
{
    try {
        Assertion::integer($amount, "The pressure of gas is measured in integers.");
    } catch (AssertionFailedException $e) {
        // Get detailed error information
        $failedValue = $e->getValue();           // The value that caused the failure
        $constraints = $e->getConstraints();     // Additional constraint information
        $message = $e->getMessage();             // The error message

        // Handle the error appropriately
        logError($message);
        return ['error' => 'Invalid payment amount'];
    }
}
```

## Custom Error Messages

Every assertion accepts an optional message parameter:

```php
<?php
use Cline\Assert\Assertion;

// Default message
Assertion::integer($value);

// Custom message
Assertion::integer($value, "Payment amount must be an integer");

// With property path in message
Assertion::integer($user->age, "User age must be an integer");
```

## Callback-Based Messages

For expensive operations, you can pass a callback that generates the message only when the assertion fails:

```php
<?php
use Cline\Assert\Assertion;

Assertion::integer($value, function($constraints) {
    // This callback is only invoked if the assertion fails
    $assertion = $constraints['::assertion'];

    if ($assertion === 'integer') {
        return sprintf(
            'Expected integer but got %s',
            gettype($constraints['value'])
        );
    }

    return 'Validation failed';
});
```

The callback receives an array of constraint information:
- `::assertion` - The name of the assertion that failed
- `value` - The value that was tested
- Any other assertion-specific constraints

## Constraint Information

Different assertions provide different constraint information:

```php
<?php
use Cline\Assert\Assertion;
use Cline\Assert\AssertionFailedException;

try {
    Assertion::between($value, 10, 100);
} catch (AssertionFailedException $e) {
    $constraints = $e->getConstraints();
    // $constraints = [
    //     '::assertion' => 'between',
    //     'min' => 10,
    //     'max' => 100,
    // ]
}

try {
    Assertion::minLength($string, 5);
} catch (AssertionFailedException $e) {
    $constraints = $e->getConstraints();
    // $constraints = [
    //     '::assertion' => 'minLength',
    //     'min_length' => 5,
    // ]
}
```

## Default Messages and Error Codes

Each assertion has a default message and unique error code:

```php
<?php
use Cline\Assert\Assertion;
use Cline\Assert\AssertionFailedException;

try {
    Assertion::email("not-an-email");
} catch (AssertionFailedException $e) {
    echo $e->getCode();     // Unique error code for 'email' assertion
    echo $e->getMessage();  // "Value 'not-an-email' was expected to be a valid e-mail address."
}

try {
    Assertion::integer("123");
} catch (AssertionFailedException $e) {
    echo $e->getCode();     // Unique error code for 'integer' assertion
    echo $e->getMessage();  // "Value '123' is not an integer."
}
```

Each assertion type has its own error code, making it easy to programmatically handle specific assertion failures.

## Real-World Error Handling

### API Request Validation

```php
<?php
use Cline\Assert\Assertion;
use Cline\Assert\AssertionFailedException;

function handleApiRequest(array $request): array
{
    try {
        Assertion::keyExists($request, 'user_id', 'Missing required field: user_id');
        Assertion::keyExists($request, 'action', 'Missing required field: action');

        Assertion::integer($request['user_id'], 'user_id must be an integer');
        Assertion::choice($request['action'], ['create', 'update', 'delete'], 'Invalid action');

        // Process the request...
        return ['success' => true];

    } catch (AssertionFailedException $e) {
        http_response_code(400);
        return [
            'success' => false,
            'error' => $e->getMessage(),
        ];
    }
}
```

### Service Layer Validation

```php
<?php
use Cline\Assert\Assertion;
use Cline\Assert\AssertionFailedException;

class UserService
{
    public function createUser(string $username, string $email): User
    {
        try {
            Assertion::notEmpty($username, 'Username cannot be empty');
            Assertion::minLength($username, 3, 'Username must be at least 3 characters');
            Assertion::maxLength($username, 20, 'Username cannot exceed 20 characters');

            Assertion::email($email, 'Invalid email address');

            // Create user...

        } catch (AssertionFailedException $e) {
            throw new DomainException(
                sprintf('User validation failed: %s', $e->getMessage()),
                0,
                $e
            );
        }
    }
}
```

### Domain Model Validation

```php
<?php
use Cline\Assert\Assertion;

class Email
{
    private string $value;

    public function __construct(string $email)
    {
        Assertion::email($email, sprintf('Invalid email: %s', $email));
        $this->value = $email;
    }

    public function getValue(): string
    {
        return $this->value;
    }
}

// Usage
try {
    $email = new Email('invalid-email');
} catch (InvalidArgumentException $e) {
    // Handle invalid email
}
```

## Assertion vs Application Exceptions

Consider whether to catch assertion exceptions or let them propagate:

**Let exceptions propagate when:**
- Validating internal/trusted data that should never fail
- Assertion failures indicate programming errors
- You want the application to fail loudly during development

```php
<?php
use Cline\Assert\Assertion;

function processInternalData(array $data)
{
    // These are programming errors if they fail
    Assertion::keyExists($data, 'id');
    Assertion::integer($data['id']);

    // Don't catch - let it bubble up
}
```

**Catch exceptions when:**
- Validating user input or external data
- You can recover or provide meaningful feedback
- Building APIs or form validation

```php
<?php
use Cline\Assert\Assertion;
use Cline\Assert\AssertionFailedException;

function validateUserInput(array $input): array
{
    try {
        Assertion::email($input['email'] ?? '');
        return ['valid' => true];
    } catch (AssertionFailedException $e) {
        return [
            'valid' => false,
            'error' => $e->getMessage(),
        ];
    }
}
```

## Logging Assertion Failures

For debugging and monitoring:

```php
<?php
use Cline\Assert\Assertion;
use Cline\Assert\AssertionFailedException;

function processData($data)
{
    try {
        Assertion::isArray($data);
        // Process...
    } catch (AssertionFailedException $e) {
        // Log the failure with context
        error_log(sprintf(
            'Assertion failed: %s | Value: %s | Constraints: %s',
            $e->getMessage(),
            json_encode($e->getValue()),
            json_encode($e->getConstraints())
        ));

        // Re-throw or handle
        throw $e;
    }
}
```

## Testing with Assertions

When writing tests, you can assert that specific assertion exceptions are thrown:

```php
<?php
use PHPUnit\Framework\TestCase;
use Cline\Assert\Assertion;
use Cline\Assert\InvalidArgumentException;

class UserTest extends TestCase
{
    public function testInvalidEmailThrowsException()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('valid e-mail address');

        new Email('invalid-email');
    }

    public function testConstraintsAreAvailable()
    {
        try {
            Assertion::between(5, 10, 20);
            $this->fail('Expected exception was not thrown');
        } catch (InvalidArgumentException $e) {
            $constraints = $e->getConstraints();
            $this->assertEquals(10, $constraints['min']);
            $this->assertEquals(20, $constraints['max']);
        }
    }
}
```
