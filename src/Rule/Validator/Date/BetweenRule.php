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

use Derafu\DataProcessor\Abstract\Rule\Validator\Date\AbstractDateRule;
use Derafu\DataProcessor\Exception\ValidationException;

final class BetweenRule extends AbstractDateRule
{
    public function validate(mixed $value, array $parameters = []): void
    {
        if (count($parameters) !== 2) {
            throw new ValidationException('From and to dates must be specified.');
        }

        $from = $this->parseComparison($parameters[0]);
        $to = $this->parseComparison($parameters[1]);
        $date = $this->parseDate($value);

        if (!$date->between($from, $to, true)) { // true to make it inclusive.
            throw new ValidationException([
                'Date must be between {from} and {to}.',
                'from' => $from->toDateString(),
                'to' => $to->toDateString(),
            ]);
        }
    }
}
