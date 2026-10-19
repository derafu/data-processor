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
 * Validates that the value is one of the choices.
 *
 * The value and the choices are compared as text, so `2`, `2.0` and `'2'` are
 * all the same choice for `in:1,2,3`. An array is valid when each one of its
 * elements is a choice. A boolean, `null` or any other type is never a choice.
 */
final class InRule implements ValidatorRuleInterface
{
    /**
     * {@inheritDoc}
     */
    public function validate(mixed $value, array $parameters = []): void
    {
        if (empty($parameters)) {
            throw new ValidationException('Choices must be specified.');
        }

        $choices = array_map('strval', $parameters);

        foreach (is_array($value) ? $value : [$value] as $item) {
            if (!$this->isChoice($item, $choices)) {
                throw new ValidationException([
                    'Must be one of: {choices}.',
                    'choices' => implode(', ', $choices),
                ]);
            }
        }
    }

    /**
     * Checks if a value is one of the choices.
     *
     * @param mixed $value The value to check.
     * @param list<string> $choices The choices, as text.
     * @return bool True if it is one of the choices.
     */
    private function isChoice(mixed $value, array $choices): bool
    {
        if (!is_int($value) && !is_float($value) && !is_string($value)) {
            return false;
        }

        return in_array((string) $value, $choices, true);
    }
}
