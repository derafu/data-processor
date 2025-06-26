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

final class AfterRule implements ValidatorRuleInterface
{
    public function validate(mixed $value, array $parameters = []): void
    {
        if (empty($parameters)) {
            throw new ValidationException('Comparison date must be specified.');
        }

        try {
            $date = Carbon::parse($value);
            $comparison = $parameters[0] === 'now'
                ? Carbon::now()
                : Carbon::parse($parameters[0]);

            if (!$date->isAfter($comparison)) {
                throw new ValidationException(
                    sprintf('Must be after %s.', $comparison->toDateTimeString())
                );
            }
        } catch (InvalidFormatException $e) {
            throw new ValidationException('Invalid date format.');
        }
    }
}
