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
use Derafu\DataProcessor\Trait\Rule\Validator\NumericParameterTrait;

/**
 * Validates that at least N elements of the array are one of the values
 * (`min_contains:2,a,b`). The first parameter is the number.
 */
final class MinContainsRule extends AbstractContainsRule
{
    use NumericParameterTrait;

    /**
     * {@inheritDoc}
     */
    public function validate(mixed $value, array $parameters = []): void
    {
        if (!isset($parameters[0]) || $parameters[0] === '') {
            throw new ValidationException('Minimum number of items must be specified.');
        }

        $min = $this->countParameter($parameters[0]);
        $values = $this->values(array_slice($parameters, 1));

        if ($this->count($value, $values) < $min) {
            throw new ValidationException([
                'Array must contain at least {min} of: {values}.',
                'min' => $min,
                'values' => implode(', ', $values),
            ]);
        }
    }
}
