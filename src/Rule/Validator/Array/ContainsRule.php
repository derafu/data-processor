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

use Derafu\DataProcessor\Abstract\Rule\Validator\Array\AbstractContainsRule;
use Derafu\DataProcessor\Exception\ValidationException;

/**
 * Validates that at least one element of the array is one of the values
 * (`contains:a,b`).
 */
final class ContainsRule extends AbstractContainsRule
{
    /**
     * {@inheritDoc}
     */
    public function validate(mixed $value, array $parameters = []): void
    {
        $values = $this->values($parameters);

        if ($this->count($value, $values) < 1) {
            throw new ValidationException([
                'Array must contain one of: {values}.',
                'values' => implode(', ', $values),
            ]);
        }
    }
}
