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

/**
 * Validates that the keys of the array (the properties of an object) are only
 * the allowed ones (`allowed_keys:a,b,c`).
 */
final class AllowedKeysRule implements ValidatorRuleInterface
{
    /**
     * {@inheritDoc}
     */
    public function validate(mixed $value, array $parameters = []): void
    {
        if (!is_array($value)) {
            throw new ValidationException('Value must be an array.');
        }

        if ($parameters === []) {
            throw new ValidationException('Choices must be specified.');
        }

        $notAllowed = array_values(array_diff(array_map('strval', array_keys($value)), $parameters));
        if ($notAllowed !== []) {
            throw new ValidationException([
                'Properties not allowed: {properties}.',
                'properties' => implode(', ', $notAllowed),
            ]);
        }
    }
}
