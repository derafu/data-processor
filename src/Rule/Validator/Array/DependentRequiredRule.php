<?php

declare(strict_types=1);

/**
 * Derafu: Data Processor - Four-Phase Data Processing Library.
 *
 * Copyright (c) 2025 Esteban De La Fuente Rubio / Derafu <https://www.derafu.dev>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\DataProcessor\Rule\Validator\Array;

use Derafu\DataProcessor\Contract\ValidatorRuleInterface;
use Derafu\DataProcessor\Exception\ValidationException;
use Derafu\Support\Value;

/**
 * Validates that some properties of an object are there when another one has
 * a value (`dependent_required:card,address,zip`). The first parameter is the
 * property that triggers the rule, the rest are the ones that are required.
 *
 * A property has a value when it exists and it is not empty (`null`, `''` or
 * `[]`), as the processor reads it.
 */
final class DependentRequiredRule implements ValidatorRuleInterface
{
    /**
     * {@inheritDoc}
     */
    public function validate(mixed $value, array $parameters = []): void
    {
        if (!is_array($value)) {
            throw new ValidationException('Value must be an array.');
        }

        if (count($parameters) < 2) {
            throw new ValidationException('Choices must be specified.');
        }

        $trigger = $parameters[0];
        if (!$this->hasValue($value, $trigger)) {
            return;
        }

        $missing = array_values(array_filter(
            array_slice($parameters, 1),
            fn (string $property): bool => !$this->hasValue($value, $property)
        ));
        if ($missing !== []) {
            throw new ValidationException([
                'Properties required when {trigger} is present: {properties}.',
                'trigger' => $trigger,
                'properties' => implode(', ', $missing),
            ]);
        }
    }

    /**
     * Checks if a property of the object has a value.
     *
     * @param array<array-key, mixed> $object The object.
     * @param string $property The name of the property.
     * @return bool True if it exists and it is not empty.
     */
    private function hasValue(array $object, string $property): bool
    {
        return array_key_exists($property, $object) && !Value::isBlank($object[$property]);
    }
}
