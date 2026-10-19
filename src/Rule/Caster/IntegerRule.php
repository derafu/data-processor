<?php

declare(strict_types=1);

/**
 * Derafu: Data Processor - Four-Phase Data Processing Library.
 *
 * Copyright (c) 2025 Esteban De La Fuente Rubio / Derafu <https://www.derafu.dev>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\DataProcessor\Rule\Caster;

use Derafu\DataProcessor\Contract\CasterRuleInterface;
use Derafu\DataProcessor\Exception\CastingException;

/**
 * Casts to integer. A number with a decimal part is not an integer, and it is
 * not truncated: `'1.9'` is an error, `'1.0'` and `'1e2'` are `1` and `100`.
 */
final class IntegerRule implements CasterRuleInterface
{
    public function cast(mixed $value, array $parameters = []): int
    {
        if (is_string($value) && !is_numeric($value)) {
            throw new CastingException('Cannot cast non-numeric string to integer.');
        }
        if (is_array($value)) {
            throw new CastingException('Cannot cast array to integer.');
        }
        if ($value !== null && !is_scalar($value)) {
            throw new CastingException([
                'Cannot cast {type} to integer.',
                'type' => get_debug_type($value),
            ]);
        }
        if (!is_string($value) && !is_float($value)) {
            return (int)$value;
        }

        // A text of an integer in the range of the integers is read as it is,
        // the rest of the numbers are read as floats.
        $integer = is_string($value) ? filter_var($value, FILTER_VALIDATE_INT) : false;
        if ($integer !== false) {
            return $integer;
        }

        $number = (float) $value;
        if (is_nan($number)) {
            throw new CastingException('Cannot cast non-integer number to integer.');
        }

        if (!is_finite($number) || $number >= 9.2233720368547758E+18 || $number < -9.2233720368547758E+18) {
            throw new CastingException('Cannot cast out-of-range number to integer.');
        }

        if (floor($number) !== $number) {
            throw new CastingException('Cannot cast non-integer number to integer.');
        }

        return (int)$number;
    }
}
