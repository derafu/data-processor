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

final class WeekendRule extends AbstractDateRule
{
    public function validate(mixed $value, array $parameters = []): void
    {
        $date = $this->parseDate($value);
        if (!$date->isWeekend()) {
            throw new ValidationException('Must be a weekend day.');
        }
    }
}
