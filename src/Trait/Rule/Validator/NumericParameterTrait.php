<?php

declare(strict_types=1);

/**
 * Derafu: Data Processor - Four-Phase Data Processing Library.
 *
 * Copyright (c) 2025 Esteban De La Fuente Rubio / Derafu <https://www.derafu.dev>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\DataProcessor\Trait\Rule\Validator;

use Derafu\DataProcessor\Exception\ValidationException;

/**
 * Reads the parameters of the validator rules that must be a number.
 *
 * A parameter that is not valid is an error of the rule, and it is not turned
 * into another value.
 */
trait NumericParameterTrait
{
    /**
     * Reads a parameter that is a whole number, 0 or more (a length or a number
     * of items).
     *
     * @param string|int|float $parameter The parameter, as it was written.
     * @return int The number.
     * @throws ValidationException If it is not a whole number of 0 or more.
     */
    private function countParameter(string|int|float $parameter): int
    {
        $text = (string) $parameter;
        if (!ctype_digit($text)) {
            throw new ValidationException([
                'Invalid parameter: {parameter}',
                'parameter' => $text,
            ]);
        }

        return (int) $text;
    }

    /**
     * Reads a parameter that is a number (a limit to compare with).
     *
     * @param string|int|float $parameter The parameter, as it was written.
     * @return float The number.
     * @throws ValidationException If it is not a finite number.
     */
    private function numberParameter(string|int|float $parameter): float
    {
        $text = (string) $parameter;
        if (!is_numeric($text) || !is_finite((float) $text)) {
            throw new ValidationException([
                'Invalid parameter: {parameter}',
                'parameter' => $text,
            ]);
        }

        return (float) $text;
    }
}
