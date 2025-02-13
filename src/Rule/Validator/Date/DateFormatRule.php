<?php

declare(strict_types=1);

/**
 * Derafu: Data Processor - Four-Phase Data Processing Library.
 *
 * Copyright (c) 2025 Esteban De La Fuente Rubio / Derafu <https://www.derafu.org>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\DataProcessor\Rule\Validator\Date;

use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use Derafu\DataProcessor\Contract\ValidatorRuleInterface;
use Derafu\DataProcessor\Exception\ValidationException;

final class DateFormatRule implements ValidatorRuleInterface
{
    public function validate(mixed $value, array $parameters = []): void
    {
        if (!is_string($value)) {
            throw new ValidationException('Value must be a string.');
        }

        if (empty($parameters)) {
            throw new ValidationException('Date format must be specified.');
        }

        $format = $parameters[0];

        try {
            $date = Carbon::createFromFormat($format, $value);
            if (!$date instanceof Carbon || $date->format($format) !== $value) {
                throw new ValidationException(
                    sprintf('Must match format %s.', $format)
                );
            }
            return ;
        } catch (InvalidFormatException $e) {
            throw new ValidationException('Invalid date format specified.');
        }
    }
}
