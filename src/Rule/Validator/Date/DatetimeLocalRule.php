<?php

declare(strict_types=1);

/**
 * Derafu: Data Processor - Four-Phase Data Processing Library.
 *
 * Copyright (c) 2025 Esteban De La Fuente Rubio / Derafu <https://www.derafu.dev>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\DataProcessor\Rule\Validator\Date;

use Derafu\DataProcessor\Contract\ValidatorRuleInterface;
use Derafu\DataProcessor\Exception\ValidationException;

/**
 * Validates a date and time as `<input type="datetime-local">` sends it:
 * `YYYY-MM-DDTHH:MM` or `YYYY-MM-DDTHH:MM:SS`.
 *
 * It has no time zone and the separator is the `T`. The date must exist.
 */
final class DatetimeLocalRule implements ValidatorRuleInterface
{
    /**
     * {@inheritDoc}
     */
    public function validate(mixed $value, array $parameters = []): void
    {
        if (!is_string($value)) {
            throw new ValidationException('Value must be a string.');
        }

        $pattern = '/^(\d{4})-(\d{2})-(\d{2})T(?:[01]\d|2[0-3]):[0-5]\d(?::[0-5]\d)?$/D';

        if (
            !preg_match($pattern, $value, $parts)
            || !checkdate((int) $parts[2], (int) $parts[3], (int) $parts[1])
        ) {
            throw new ValidationException('Invalid datetime-local format.');
        }
    }
}
