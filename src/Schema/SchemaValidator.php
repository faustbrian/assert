<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Assert\Schema;

/**
 * Basic schema validator for toMatchSchema().
 *
 * Supports simple schema validation without external dependencies.
 */
final class SchemaValidator
{
    /**
     * Validate data against a schema definition.
     *
     * Schema format:
     * [
     *   'type' => 'object',
     *   'properties' => [
     *     'name' => ['type' => 'string'],
     *     'age' => ['type' => 'integer', 'minimum' => 0],
     *   ],
     *   'required' => ['name', 'age']
     * ]
     */
    public static function validate(mixed $data, array $schema): array
    {
        $errors = [];

        // Type validation
        if (isset($schema['type'])) {
            $typeError = self::validateType($data, $schema['type']);
            if ($typeError !== null) {
                $errors[] = $typeError;
                return $errors; // Stop early if type is wrong
            }
        }

        // Object properties validation
        if (($schema['type'] ?? null) === 'object' && isset($schema['properties'])) {
            $data = (array) $data;

            foreach ($schema['properties'] as $property => $propertySchema) {
                if (!array_key_exists($property, $data)) {
                    if (in_array($property, $schema['required'] ?? [], true)) {
                        $errors[] = "Missing required property: {$property}";
                    }
                    continue;
                }

                $propertyErrors = self::validate($data[$property], $propertySchema);
                foreach ($propertyErrors as $error) {
                    $errors[] = "{$property}: {$error}";
                }
            }

            // Check required properties
            if (isset($schema['required'])) {
                foreach ($schema['required'] as $required) {
                    if (!array_key_exists($required, $data)) {
                        $errors[] = "Missing required property: {$required}";
                    }
                }
            }
        }

        // Array items validation
        if (($schema['type'] ?? null) === 'array' && isset($schema['items']) && is_array($data)) {
            foreach ($data as $index => $item) {
                $itemErrors = self::validate($item, $schema['items']);
                foreach ($itemErrors as $error) {
                    $errors[] = "[{$index}]: {$error}";
                }
            }
        }

        // Numeric validations
        if (is_numeric($data)) {
            if (isset($schema['minimum']) && $data < $schema['minimum']) {
                $errors[] = "Value {$data} is less than minimum {$schema['minimum']}";
            }
            if (isset($schema['maximum']) && $data > $schema['maximum']) {
                $errors[] = "Value {$data} is greater than maximum {$schema['maximum']}";
            }
        }

        // String validations
        if (is_string($data)) {
            if (isset($schema['minLength']) && strlen($data) < $schema['minLength']) {
                $errors[] = "String length " . strlen($data) . " is less than minLength {$schema['minLength']}";
            }
            if (isset($schema['maxLength']) && strlen($data) > $schema['maxLength']) {
                $errors[] = "String length " . strlen($data) . " is greater than maxLength {$schema['maxLength']}";
            }
            if (isset($schema['pattern']) && !preg_match($schema['pattern'], $data)) {
                $errors[] = "String does not match pattern {$schema['pattern']}";
            }
        }

        // Enum validation
        if (isset($schema['enum']) && !in_array($data, $schema['enum'], true)) {
            $errors[] = "Value must be one of: " . implode(', ', $schema['enum']);
        }

        return $errors;
    }

    private static function validateType(mixed $value, string $type): ?string
    {
        $valid = match ($type) {
            'string' => is_string($value),
            'integer' => is_int($value),
            'number' => is_numeric($value),
            'boolean' => is_bool($value),
            'array' => is_array($value),
            'object' => is_object($value) || is_array($value),
            'null' => $value === null,
            default => true,
        };

        return $valid ? null : "Expected type {$type}, got " . gettype($value);
    }
}
