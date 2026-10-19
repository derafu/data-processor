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
 * Validates a month as `<input type="month">` sends it: `YYYY-MM`.
 *
 * The month goes from `01` to `12`, and the year from `0001`.
 */
final class MonthRule implements ValidatorRuleInterface
{
    /**
     * {@inheritDoc}
     */
    public function validate(mixed $value, array $parameters = []): void
    {
        if (!is_string($value)) {
            throw new ValidationException('Value must be a string.');
        }

        if (!preg_match('/^(?!0000)\d{4}-(?:0[1-9]|1[0-2])$/D', $value)) {
            throw new ValidationException('Invalid month format.');
        }
    }
}
