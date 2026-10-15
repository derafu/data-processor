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

use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use DateTimeInterface;
use Derafu\DataProcessor\Contract\ValidatorRuleInterface;
use Derafu\DataProcessor\Exception\ValidationException;

/**
 * Base of the validators of dates.
 */
abstract class AbstractDateRule implements ValidatorRuleInterface
{
    /**
     * Reads the value to validate as a date.
     *
     * Only a string or an object that is a date can be one: any other value is
     * not a date.
     *
     * @param mixed $value The value to read.
     * @return Carbon The date.
     * @throws ValidationException If the value is not a date.
     */
    protected function parseDate(mixed $value): Carbon
    {
        if (!is_string($value) && !$value instanceof DateTimeInterface) {
            throw new ValidationException('Invalid date format.');
        }

        try {
            return Carbon::parse($value);
        } catch (InvalidFormatException $e) {
            throw new ValidationException('Invalid date format.');
        }
    }
}
