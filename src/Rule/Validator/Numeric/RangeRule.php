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

final class RangeRule implements ValidatorRuleInterface
{
    public function validate(mixed $value, array $parameters = []): void
    {
        if (!is_numeric($value)) {
            throw new ValidationException('Value must be numeric.');
        }

        if (count($parameters) < 2) {
            throw new ValidationException('Min and max values must be specified.');
        }

        $min = (float)$parameters[0];
        $max = (float)$parameters[1];
        $number = (float)$value;

        if ($number < $min || $number > $max) {
            throw new ValidationException(
                sprintf('Must be between %s and %s.', $parameters[0], $parameters[1])
            );
        }
    }
}
