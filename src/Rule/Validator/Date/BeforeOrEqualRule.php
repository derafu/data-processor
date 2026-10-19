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
use Derafu\DataProcessor\Contract\RawParameterInterface;
use Derafu\DataProcessor\Exception\ValidationException;

final class BeforeOrEqualRule extends AbstractDateRule implements RawParameterInterface
{
    public function validate(mixed $value, array $parameters = []): void
    {
        if (empty($parameters)) {
            throw new ValidationException('Comparison date must be specified.');
        }

        $comparison = $this->parseComparison($parameters[0]);
        $date = $this->parseDate($value);

        if ($date->isAfter($comparison)) {
            throw new ValidationException([
                'Must be before or equal to {date}.',
                'date' => $comparison->toDateTimeString(),
            ]);
        }
    }
}
