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

final class BetweenRule implements ValidatorRuleInterface
{
    public function validate(mixed $value, array $parameters = []): void
    {
        if (count($parameters) !== 2) {
            throw new ValidationException('From and to dates must be specified.');
        }

        try {
            $date = Carbon::parse($value);
            $from = Carbon::parse($parameters[0]);
            $to = Carbon::parse($parameters[1]);
        } catch (InvalidFormatException $e) {
            throw new ValidationException('Invalid date format.');
        }

        if (!$date->between($from, $to, true)) { // true para que sea inclusivo.
            throw new ValidationException(
                sprintf(
                    'Date must be between %s and %s.',
                    $from->toDateString(),
                    $to->toDateString()
                )
            );
        }
    }
}
