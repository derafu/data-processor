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
use Derafu\DataProcessor\Contract\ValidatorRuleInterface;
use Derafu\DataProcessor\Exception\ValidationException;

final class WeekendRule implements ValidatorRuleInterface
{
    public function validate(mixed $value, array $parameters = []): void
    {
        try {
            $date = Carbon::parse($value);
            if (!$date->isWeekend()) {
                throw new ValidationException('Must be a weekend day.');
            }
        } catch (InvalidFormatException $e) {
            throw new ValidationException('Invalid date format.');
        }
    }
}
