# Object Assertions

Object assertions validate objects, classes, interfaces, and their properties/methods.

## Available Assertions

### isInstanceOf()

Assert that a value is an instance of a given class.

```php
use Cline\Assert\Assertion;

Assertion::isInstanceOf($user, User::class);
Assertion::isInstanceOf($model, Model::class, 'Expected Model instance');
```

### notIsInstanceOf()

Assert that a value is NOT an instance of a given class.

```php
Assertion::notIsInstanceOf($value, LegacyUser::class);
Assertion::notIsInstanceOf($object, DeprecatedClass::class, 'Cannot use deprecated class');
```

### classExists()

Assert that a class exists.

```php
Assertion::classExists(User::class);
Assertion::classExists($className, 'Class does not exist');
```

### interfaceExists()

Assert that an interface exists.

```php
Assertion::interfaceExists(UserInterface::class);
Assertion::interfaceExists($interfaceName, 'Interface does not exist');
```

### subclassOf()

Assert that a class is a subclass of another class.

```php
Assertion::subclassOf(AdminUser::class, User::class);
Assertion::subclassOf($childClass, $parentClass, 'Must extend parent class');
```

### implementsInterface()

Assert that a class implements an interface.

```php
Assertion::implementsInterface(User::class, UserInterface::class);
Assertion::implementsInterface($class, $interface, 'Must implement interface');
```

### methodExists()

Assert that a method exists on an object.

```php
Assertion::methodExists('save', $model);
Assertion::methodExists('handle', $handler, 'Handler must have handle method');
```

### propertyExists()

Assert that a property exists on an object or class.

```php
Assertion::propertyExists($user, 'email');
Assertion::propertyExists($model, 'id', 'Model must have id property');
```

### propertiesExist()

Assert that multiple properties exist on an object or class.

```php
$required = ['id', 'name', 'email'];
Assertion::propertiesExist($user, $required);
Assertion::propertiesExist($model, $required, 'Missing required properties');
```

### objectOrClass()

Assert that a value is an object or a class name that exists.

```php
Assertion::objectOrClass($value);
Assertion::objectOrClass($userOrClass, 'Expected object or class name');
```

## Chaining Object Assertions

Use `Assert::that()` for fluent object validation:

```php
use Cline\Assert\Assert;

Assert::that($user)
    ->isObject()
    ->isInstanceOf(User::class);

Assert::that(User::class)
    ->classExists()
    ->implementsInterface(UserInterface::class);
```

## Common Patterns

### Dependency Injection Validation

```php
Assert::that($logger)
    ->isObject()
    ->implementsInterface(LoggerInterface::class);

Assert::that($cache)
    ->isInstanceOf(CacheInterface::class, 'Invalid cache driver');
```

### Model Validation

```php
Assert::that($model)
    ->isObject()
    ->isInstanceOf(Model::class)
    ->propertyExists('id')
    ->propertyExists('created_at');
```

### Class Hierarchy Validation

```php
Assert::that($admin)
    ->isInstanceOf(AdminUser::class);

Assertion::subclassOf(AdminUser::class, User::class);
Assertion::implementsInterface(AdminUser::class, AuthorizableInterface::class);
```

### Plugin/Extension Validation

```php
Assert::that($plugin)
    ->isObject()
    ->implementsInterface(PluginInterface::class)
    ->methodExists('register')
    ->methodExists('boot');
```

### Factory Pattern Validation

```php
public function make(string $class)
{
    Assertion::classExists($class);
    Assertion::subclassOf($class, BaseService::class);
    
    return new $class();
}
```

### Configuration Object Validation

```php
$requiredProperties = ['host', 'port', 'database', 'username'];

Assert::that($config)
    ->isObject()
    ->propertiesExist($requiredProperties, 'Missing required configuration');
```

## Working with Interfaces

### Interface Implementation Check

```php
// Check if class implements interface
Assertion::implementsInterface(JsonSerializer::class, Serializer::class);

// Check instance
Assert::that($serializer)
    ->isObject()
    ->isInstanceOf(Serializer::class);
```

### Multiple Interface Validation

```php
Assertion::implementsInterface($class, Serializable::class);
Assertion::implementsInterface($class, Jsonable::class);
Assertion::implementsInterface($class, Arrayable::class);
```

## Inheritance Validation

### Parent Class Checks

```php
Assert::that(AdminController::class)
    ->classExists()
    ->subclassOf(Controller::class)
    ->implementsInterface(AuthorizableInterface::class);
```

### Abstract Class Validation

```php
Assertion::classExists($handlerClass);
Assertion::subclassOf($handlerClass, AbstractHandler::class);
```

## Method and Property Validation

### Required Methods

```php
$requiredMethods = ['handle', 'validate', 'authorize'];

foreach ($requiredMethods as $method) {
    Assertion::methodExists($method, $handler);
}
```

### Dynamic Property Checks

```php
if (property_exists($object, 'timestamps')) {
    Assertion::propertyExists($object, 'created_at');
    Assertion::propertyExists($object, 'updated_at');
}
```

### Trait Validation

```php
// Check if object uses a trait (requires custom check)
Assert::that($model)
    ->isObject()
    ->satisfy(function($obj) {
        return in_array(HasUuid::class, class_uses_recursive($obj));
    }, 'Model must use HasUuid trait');
```

## Best Practices

### Type Hint vs Runtime Check

```php
// ✗ Redundant with type hints
public function process(User $user) {
    Assertion::isInstanceOf($user, User::class);
}

// ✓ Useful for dynamic class names
public function make(string $className) {
    Assertion::classExists($className);
    Assertion::subclassOf($className, BaseClass::class);
    return new $className();
}
```

### Interface Over Implementation

```php
// ✗ Tightly coupled
Assert::that($logger)
    ->isInstanceOf(MonologLogger::class);

// ✓ Depends on interface
Assert::that($logger)
    ->isInstanceOf(LoggerInterface::class);
```

### Combine with Method Checks

```php
Assert::that($handler)
    ->isObject()
    ->isInstanceOf(HandlerInterface::class)
    ->methodExists('handle');
```

## Advanced Patterns

### Plugin System

```php
public function registerPlugin($plugin): void
{
    Assert::that($plugin)
        ->isObject()
        ->implementsInterface(PluginInterface::class)
        ->methodExists('getName')
        ->methodExists('getVersion')
        ->methodExists('boot');
    
    $this->plugins[] = $plugin;
}
```

### Strategy Pattern Validation

```php
public function setStrategy($strategy): void
{
    Assert::that($strategy)
        ->isObject()
        ->implementsInterface(StrategyInterface::class)
        ->methodExists('execute');
    
    $this->strategy = $strategy;
}
```

### Serialization Validation

```php
public function serialize($object): string
{
    Assert::that($object)
        ->isObject()
        ->implementsInterface(Serializable::class)
        ->methodExists('serialize');
    
    return $object->serialize();
}
```

## Next Steps

- **[Type Assertions](type-assertions.md)** - Basic type checking including isObject()
- **[Custom Assertions](custom-assertions.md)** - Create custom object validation rules
- **[Lazy Assertions](lazy-assertions.md)** - Validate multiple object properties
