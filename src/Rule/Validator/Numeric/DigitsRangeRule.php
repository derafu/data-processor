<?php

declare(strict_types=1);

/**
 * Derafu: Data Processor - Four-Phase Data Processing Library.
 *
 * Copyright (c) 2025 Esteban De La Fuente Rubio / Derafu <https://www.derafu.dev>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\DataProcessor\Rule\Validator\Numeric;

use Derafu\DataProcessor\Contract\ValidatorRuleInterface;
use Derafu\DataProcessor\Exception\ValidationException;

final class DigitsRangeRule implements ValidatorRuleInterface
{
    public function validate(mixed $value, array $parameters = []): void
    {
        $text = is_int($value) ? (string) $value : $value;
        if (!is_string($text) || !ctype_digit($text)) {
            throw new ValidationException('Value must contain only digits.');
        }

        if (count($parameters) < 2) {
            throw new ValidationException('Min and max digits must be specified.');
        }

        $length = strlen($text);
        $min = (int)$parameters[0];
        $max = (int)$parameters[1];

        if ($length < $min || $length > $max) {
            throw new ValidationException([
                'Must have between {min} and {max} digits.',
                'min' => $min,
                'max' => $max,
            ]);
        }
    }
}
