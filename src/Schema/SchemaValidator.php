<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Assert\Schema;

use function array_key_exists;
use function gettype;
use function implode;
use function in_array;
use function is_array;
use function is_bool;
use function is_int;
use function is_numeric;
use function is_object;
use function is_string;
use function mb_strlen;
use function preg_match;
use function sprintf;

/**
 * Basic schema validator for toMatchSchema().
 *
 * Supports simple schema validation without external dependencies.
 *
 * @author Brian Faust <brian@cline.sh>
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
        if (array_key_exists('type', $schema)) {
            $typeError = self::validateType($data, $schema['type']);

            if ($typeError !== null) {
                $errors[] = $typeError;

                return $errors; // Stop early if type is wrong
            }
        }

        // Object properties validation
        if (($schema['type'] ?? null) === 'object' && array_key_exists('properties', $schema)) {
            $data = (array) $data;

            foreach ($schema['properties'] as $property => $propertySchema) {
                if (!array_key_exists($property, $data)) {
                    if (in_array($property, $schema['required'] ?? [], true)) {
                        $errors[] = 'Missing required property: '.$property;
                    }

                    continue;
                }

                $propertyErrors = self::validate($data[$property], $propertySchema);

                foreach ($propertyErrors as $error) {
                    $errors[] = sprintf('%s: %s', $property, $error);
                }
            }

            // Check required properties
            if (array_key_exists('required', $schema)) {
                foreach ($schema['required'] as $required) {
                    if (!array_key_exists($required, $data)) {
                        $errors[] = 'Missing required property: '.$required;
                    }
                }
            }
        }

        // Array items validation
        if (($schema['type'] ?? null) === 'array' && array_key_exists('items', $schema) && is_array($data)) {
            foreach ($data as $index => $item) {
                $itemErrors = self::validate($item, $schema['items']);

                foreach ($itemErrors as $error) {
                    $errors[] = sprintf('[%s]: %s', $index, $error);
                }
            }
        }

        // Numeric validations
        if (is_numeric($data)) {
            if (array_key_exists('minimum', $schema) && $data < $schema['minimum']) {
                $errors[] = sprintf('Value %s is less than minimum %s', $data, $schema['minimum']);
            }

            if (array_key_exists('maximum', $schema) && $data > $schema['maximum']) {
                $errors[] = sprintf('Value %s is greater than maximum %s', $data, $schema['maximum']);
            }
        }

        // String validations
        if (is_string($data)) {
            if (array_key_exists('minLength', $schema) && mb_strlen($data) < $schema['minLength']) {
                $errors[] = 'String length '.mb_strlen($data).(' is less than minLength '.$schema['minLength']);
            }

            if (array_key_exists('maxLength', $schema) && mb_strlen($data) > $schema['maxLength']) {
                $errors[] = 'String length '.mb_strlen($data).(' is greater than maxLength '.$schema['maxLength']);
            }

            if (array_key_exists('pattern', $schema) && !preg_match($schema['pattern'], $data)) {
                $errors[] = 'String does not match pattern '.$schema['pattern'];
            }
        }

        // Enum validation
        if (array_key_exists('enum', $schema) && !in_array($data, $schema['enum'], true)) {
            $errors[] = 'Value must be one of: '.implode(', ', $schema['enum']);
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

        return $valid ? null : sprintf('Expected type %s, got ', $type).gettype($value);
    }
}
