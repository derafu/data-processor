<?php

declare(strict_types=1);

/**
 * Derafu: Data Processor - Four-Phase Data Processing Library.
 *
 * Copyright (c) 2025 Esteban De La Fuente Rubio / Derafu <https://www.derafu.dev>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\DataProcessor\Abstract\Rule\Validator\Array;

use Derafu\DataProcessor\Contract\ValidatorRuleInterface;
use Derafu\DataProcessor\Exception\ValidationException;

/**
 * Base of the validators that count the elements of an array that are some
 * values (`contains`, `min_contains` and `max_contains`).
 *
 * An element is compared with the values as text, as `in` does: `2`, `2.0` and
 * `'2'` are the same value. A boolean, `null`, an array or an object is never
 * one of the values.
 */
abstract class AbstractContainsRule implements ValidatorRuleInterface
{
    /**
     * Reads the values to look for, which are the parameters that are left.
     *
     * @param list<string> $parameters The parameters of the rule.
     * @return list<string> The values.
     * @throws ValidationException If there are no values.
     */
    protected function values(array $parameters): array
    {
        if ($parameters === []) {
            throw new ValidationException('Choices must be specified.');
        }

        return $parameters;
    }

    /**
     * Counts the elements of the array that are one of the values.
     *
     * @param mixed $array The value to validate.
     * @param list<string> $values The values to look for.
     * @return int The number of elements.
     * @throws ValidationException If the value is not an array.
     */
    protected function count(mixed $array, array $values): int
    {
        if (!is_array($array)) {
            throw new ValidationException('Value must be an array.');
        }

        $count = 0;
        foreach ($array as $element) {
            if (
                (is_int($element) || is_float($element) || is_string($element))
                && in_array((string) $element, $values, true)
            ) {
                $count++;
            }
        }

        return $count;
    }
}
