<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Assert\Assertions;

use Cline\Assert\AssertionFailedException;
use ReflectionClass;
use ReflectionException;

use function class_exists;
use function implode;
use function interface_exists;
use function is_array;
use function is_bool;
use function is_object;
use function is_resource;
use function is_string;
use function is_subclass_of;
use function method_exists;
use function property_exists;
use function sprintf;

/**
 * Object and class assertion methods.
 *
 * Dependencies:
 * - Base::createException()
 * - Base::stringify()
 * - Base::generateMessage()
 * - TypeAssertions::isObject()
 * - StringAssertions::allString()
 *
 * @author Brian Faust <brian@cline.sh>
 */
trait ObjectAssertions
{
    public const int INVALID_INSTANCE_OF = 28;

    public const int INVALID_SUBCLASS_OF = 29;

    public const int INVALID_CLASS = 105;

    public const int INVALID_INTERFACE = 106;

    public const int INTERFACE_NOT_IMPLEMENTED = 202;

    public const int INVALID_NOT_INSTANCE_OF = 204;

    public const int INVALID_METHOD = 208;

    public const int INVALID_PROPERTY = 224;

    public const int INVALID_PROPERTY_NOT_EXISTS = 225;

    public const int INVALID_METHOD_NOT_EXISTS = 244;

    /**
     * Assert that the class exists.
     *
     * @param mixed                $value
     * @param null|callable|string $message
     *
     * @psalm-assert class-string $value
     *
     * @throws AssertionFailedException
     */
    public static function classExists($value, $message = null, ?string $propertyPath = null): bool
    {
        if (is_object($value) || is_array($value) || is_resource($value)) {
            $className = '';
        } else {
            /** @var null|bool|float|int|string $value */
            $className = is_string($value) ? $value : (string) (is_bool($value) || null === $value ? '' : $value);
        }

        if (!class_exists($className)) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected an existing class. Got: %s'),
                static::stringify($value),
            );

            throw self::createException($value, $message, self::INVALID_CLASS, $propertyPath);
        }

        return true;
    }

    /**
     * Assert that the interface exists.
     *
     * @param mixed                $value
     * @param null|callable|string $message
     *
     * @psalm-assert class-string $value
     *
     * @throws AssertionFailedException
     */
    public static function interfaceExists($value, $message = null, ?string $propertyPath = null): bool
    {
        if (is_object($value) || is_array($value) || is_resource($value)) {
            $interfaceName = '';
        } else {
            /** @var null|bool|float|int|string $value */
            $interfaceName = is_string($value) ? $value : (string) (is_bool($value) || null === $value ? '' : $value);
        }

        if (!interface_exists($interfaceName)) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected an existing interface. Got: %s'),
                static::stringify($value),
            );

            throw self::createException($value, $message, self::INVALID_INTERFACE, $propertyPath);
        }

        return true;
    }

    /**
     * Assert that value is instance of given class-name.
     *
     * @param mixed                $value
     * @param string               $className
     * @param null|callable|string $message
     *
     * @psalm-template ExpectedType of object
     *
     * @psalm-param class-string<ExpectedType> $className
     *
     * @psalm-assert ExpectedType $value
     *
     * @throws AssertionFailedException
     */
    public static function isInstanceOf($value, $className, $message = null, ?string $propertyPath = null): bool
    {
        if (!$value instanceof $className) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected an instance of %2$s. Got: %s'),
                static::stringify($value),
                $className,
            );

            throw self::createException($value, $message, self::INVALID_INSTANCE_OF, $propertyPath, ['class' => $className]);
        }

        return true;
    }

    /**
     * Assert that value is not instance of given class-name.
     *
     * @param mixed                $value
     * @param string               $className
     * @param null|callable|string $message
     *
     * @psalm-template ExpectedType of object
     *
     * @psalm-param class-string<ExpectedType> $className
     *
     * @psalm-assert !ExpectedType $value
     *
     * @throws AssertionFailedException
     */
    public static function notIsInstanceOf($value, $className, $message = null, ?string $propertyPath = null): bool
    {
        if ($value instanceof $className) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected not an instance of %2$s. Got: %s'),
                static::stringify($value),
                $className,
            );

            throw self::createException($value, $message, self::INVALID_NOT_INSTANCE_OF, $propertyPath, ['class' => $className]);
        }

        return true;
    }

    /**
     * Assert that value is subclass of given class-name.
     *
     * @param mixed                $value
     * @param string               $className
     * @param null|callable|string $message
     *
     * @throws AssertionFailedException
     */
    public static function subclassOf($value, $className, $message = null, ?string $propertyPath = null): bool
    {
        /** @var class-string|object $value */
        // @phpstan-ignore function.impossibleType (PHPDoc says className is string, but could be object at runtime)
        if (!is_subclass_of($value, is_object($className) ? $className::class : $className)) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected a subclass of %2$s. Got: %s'),
                static::stringify($value),
                $className,
            );

            throw self::createException($value, $message, self::INVALID_SUBCLASS_OF, $propertyPath, ['class' => $className]);
        }

        return true;
    }

    /**
     * Assert that the class implements the interface.
     *
     * @param mixed                $class
     * @param string               $interfaceName
     * @param null|callable|string $message
     *
     * @throws AssertionFailedException
     */
    public static function implementsInterface($class, $interfaceName, $message = null, ?string $propertyPath = null): bool
    {
        try {
            /** @var class-string|object $class */
            $reflection = new ReflectionClass($class);

            if (!$reflection->implementsInterface($interfaceName)) {
                $message = sprintf(
                    self::generateMessage($message ?: 'Expected a class implementing %2$s. Got: %s'),
                    static::stringify($class),
                    static::stringify($interfaceName),
                );

                throw self::createException($class, $message, self::INTERFACE_NOT_IMPLEMENTED, $propertyPath, ['interface' => $interfaceName]);
            }
        } catch (ReflectionException) {
            $message = sprintf(
                self::generateMessage($message ?: 'Class failed reflection. Got: %s'),
                static::stringify($class),
            );

            throw self::createException($class, $message, self::INTERFACE_NOT_IMPLEMENTED, $propertyPath, ['interface' => $interfaceName]);
        }

        return true;
    }

    /**
     * Determines that the named method is defined in the provided object.
     *
     * @param string               $value
     * @param mixed                $object
     * @param null|callable|string $message
     *
     * @throws AssertionFailedException
     */
    public static function methodExists($value, $object, $message = null, ?string $propertyPath = null): bool
    {
        self::isObject($object, $message, $propertyPath);

        if (!method_exists($object, $value)) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected property to exist. Got: %s'),
                static::stringify($value),
            );

            throw self::createException($value, $message, self::INVALID_METHOD, $propertyPath, ['object' => $object::class]);
        }

        return true;
    }

    /**
     * Assert that the value is an object, or a class that exists.
     *
     * @param mixed                $value
     * @param null|callable|string $message
     *
     * @throws AssertionFailedException
     */
    public static function objectOrClass($value, $message = null, ?string $propertyPath = null): bool
    {
        if (!is_object($value)) {
            self::classExists($value, $message, $propertyPath);
        }

        return true;
    }

    /**
     * Assert that the value is an object or class, and that the property exists.
     *
     * @param mixed                $value
     * @param string               $property
     * @param null|callable|string $message
     *
     * @throws AssertionFailedException
     */
    public static function propertyExists($value, $property, $message = null, ?string $propertyPath = null): bool
    {
        self::objectOrClass($value);

        /** @var class-string|object $value */
        if (!property_exists($value, $property)) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected a class with property %2$s. Got: %s'),
                static::stringify($value),
                static::stringify($property),
            );

            throw self::createException($value, $message, self::INVALID_PROPERTY, $propertyPath, ['property' => $property]);
        }

        return true;
    }

    /**
     * Assert that the value is an object or class, and that the properties all exist.
     *
     * @param mixed                $value
     * @param array<mixed>         $properties
     * @param null|callable|string $message
     *
     * @throws AssertionFailedException
     */
    public static function propertiesExist($value, array $properties, $message = null, ?string $propertyPath = null): bool
    {
        self::objectOrClass($value);
        self::allString($properties, $message, $propertyPath);

        /** @var class-string|object $value */
        $invalidProperties = [];

        /** @var string $property */
        foreach ($properties as $property) {
            if (!property_exists($value, $property)) {
                $invalidProperties[] = $property;
            }
        }

        if ($invalidProperties !== []) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected a class with properties %2$s. Got: %s'),
                static::stringify($value),
                static::stringify(implode(', ', $invalidProperties)),
            );

            throw self::createException($value, $message, self::INVALID_PROPERTY, $propertyPath, ['properties' => $properties]);
        }

        return true;
    }

    /**
     * Assert that the value is an object or class, and that the property does not exist.
     *
     * @param mixed                $value
     * @param string               $property
     * @param null|callable|string $message
     *
     * @throws AssertionFailedException
     */
    public static function propertyNotExists($value, $property, $message = null, ?string $propertyPath = null): bool
    {
        self::objectOrClass($value);

        /** @var class-string|object $value */
        if (property_exists($value, $property)) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected property %2$s to not exist. Got: %s'),
                static::stringify($value),
                static::stringify($property),
            );

            throw self::createException($value, $message, self::INVALID_PROPERTY_NOT_EXISTS, $propertyPath, ['property' => $property]);
        }

        return true;
    }

    /**
     * Assert that the value is an object or class, and that the method does not exist.
     *
     * @param mixed                $value
     * @param string               $method
     * @param null|callable|string $message
     *
     * @throws AssertionFailedException
     */
    public static function methodNotExists($value, $method, $message = null, ?string $propertyPath = null): bool
    {
        if ((is_string($value) || is_object($value)) && method_exists($value, $method)) {
            $message = sprintf(
                self::generateMessage($message ?: 'Expected method %2$s to not exist. Got: %s'),
                static::stringify($value),
                static::stringify($method),
            );

            throw self::createException($value, $message, self::INVALID_METHOD_NOT_EXISTS, $propertyPath, ['method' => $method]);
        }

        return true;
    }
}
