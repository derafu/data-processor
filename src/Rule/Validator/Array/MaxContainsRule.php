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
 * Validates that at most N elements of the array are one of the values
 * (`max_contains:1,a,b`). The first parameter is the number.
 */
final class MaxContainsRule extends AbstractContainsRule
{
    use NumericParameterTrait;

    /**
     * {@inheritDoc}
     */
    public function validate(mixed $value, array $parameters = []): void
    {
        if (!isset($parameters[0]) || $parameters[0] === '') {
            throw new ValidationException('Maximum number of items must be specified.');
        }

        $max = $this->countParameter($parameters[0]);
        $values = $this->values(array_slice($parameters, 1));

        if ($this->count($value, $values) > $max) {
            throw new ValidationException([
                'Array must not contain more than {max} of: {values}.',
                'max' => $max,
                'values' => implode(', ', $values),
            ]);
        }
    }
}
