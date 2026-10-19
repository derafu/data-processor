<?php

declare(strict_types=1);

/**
 * Derafu: Data Processor - Four-Phase Data Processing Library.
 *
 * Copyright (c) 2025 Esteban De La Fuente Rubio / Derafu <https://www.derafu.dev>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\DataProcessor\Abstract\Rule\Validator\Date;

use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use DateTimeInterface;
use Derafu\DataProcessor\Contract\ValidatorRuleInterface;
use Derafu\DataProcessor\Exception\ValidationException;
use Derafu\DataProcessor\Trait\Rule\AbsoluteDateTrait;

/**
 * Base of the validators of dates.
 */
abstract class AbstractDateRule implements ValidatorRuleInterface
{
    use AbsoluteDateTrait;

    /**
     * Reads the value to validate as a date.
     *
     * A string must be an absolute date in ISO 8601: `YYYY-MM-DD`, or with a
     * time, `YYYY-MM-DDTHH:MM[:SS[.fraction]]` (a space can replace the `T`),
     * and with `Z` or an offset (`+03:00`) at the end. The date and the time
     * must exist. A relative text as `tomorrow` or `+1 day`, or a date in
     * another format, is not a date (`date_format` validates those). An object
     * that is a date is valid.
     *
     * @param mixed $value The value to read.
     * @return Carbon The date.
     * @throws ValidationException If the value is not a date.
     */
    protected function parseDate(mixed $value): Carbon
    {
        if ($value instanceof DateTimeInterface) {
            return Carbon::instance($value);
        }

        if (!is_string($value) || !$this->isAbsoluteDate($value)) {
            throw new ValidationException('Invalid date format.');
        }

        try {
            return Carbon::parse($value);
        } catch (InvalidFormatException $e) {
            throw new ValidationException('Invalid date format.');
        }
    }

    /**
     * Parses the date that a rule compares with, given as its parameter.
     *
     * It can be relative, as `now` or `tomorrow`.
     *
     * @param string $parameter The parameter, as it was written.
     * @return Carbon
     * @throws ValidationException If the parameter is not a date.
     */
    protected function parseComparison(string $parameter): Carbon
    {
        try {
            return $parameter === 'now' ? Carbon::now() : Carbon::parse($parameter);
        } catch (InvalidFormatException $e) {
            throw new ValidationException([
                'Invalid parameter: {parameter}',
                'parameter' => $parameter,
            ]);
        }
    }
}
