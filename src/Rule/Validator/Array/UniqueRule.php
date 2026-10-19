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
 * Validates that the array has no repeated values.
 *
 * The comparison is strict: two values are the same only if they have the same
 * type and the same value, so `'1e1'` and `'10'`, or `'1'` and `1`, are
 * different. Arrays are the same if they have the same keys and the same
 * values, and objects if they have the same class and the same properties.
 */
final class UniqueRule implements ValidatorRuleInterface
{
    /**
     * {@inheritDoc}
     */
    public function validate(mixed $value, array $parameters = []): void
    {
        if (!is_array($value)) {
            throw new ValidationException('Value must be an array.');
        }

        $seen = [];
        foreach ($value as $item) {
            foreach ($seen as $other) {
                if ($this->same($item, $other)) {
                    throw new ValidationException('Array must contain unique values.');
                }
            }
            $seen[] = $item;
        }
    }

    /**
     * Checks if two values are the same, with the same type and value.
     *
     * @param mixed $a A value.
     * @param mixed $b Another value.
     * @return bool True if they are the same.
     */
    private function same(mixed $a, mixed $b): bool
    {
        if (is_object($a) && is_object($b)) {
            return $a::class === $b::class && $this->same((array) $a, (array) $b);
        }

        if (is_array($a) && is_array($b)) {
            if (array_keys($a) !== array_keys($b)) {
                return false;
            }

            foreach ($a as $key => $item) {
                if (!$this->same($item, $b[$key])) {
                    return false;
                }
            }

            return true;
        }

        return $a === $b;
    }
}
