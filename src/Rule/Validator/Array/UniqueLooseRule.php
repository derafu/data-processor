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
 * Validates that the array has no repeated values, with a loose comparison.
 *
 * It is the comparison of `==` in PHP (`SORT_REGULAR`): a numeric text is
 * compared as a number, so `'1e1'` and `'10'`, or `'1'` and `1`, are the same
 * value. `unique` compares the type too.
 */
final class UniqueLooseRule implements ValidatorRuleInterface
{
    /**
     * {@inheritDoc}
     */
    public function validate(mixed $value, array $parameters = []): void
    {
        if (!is_array($value)) {
            throw new ValidationException('Value must be an array.');
        }

        if (count($value) !== count(array_unique($value, SORT_REGULAR))) {
            throw new ValidationException('Array must contain unique values.');
        }
    }
}
