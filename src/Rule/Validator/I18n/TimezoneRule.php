<?php

declare(strict_types=1);

/**
 * Derafu: Derafu: Data Processor - Four-Phase Data Processing Library.
 *
 * Copyright (c) 2025 Esteban De La Fuente Rubio / Derafu <https://www.derafu.org>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\DataProcessor\Rule\Validator\I18n;

use DateTimeZone;
use Derafu\DataProcessor\Contract\ValidatorRuleInterface;
use Derafu\DataProcessor\Exception\ValidationException;

final class TimezoneRule implements ValidatorRuleInterface
{
    public function validate(mixed $value, array $parameters = []): void
    {
        if (!is_string($value)) {
            throw new ValidationException('Value must be a string.');
        }

        // Validar identificadores de zona horaria estándar,
        if (in_array($value, DateTimeZone::listIdentifiers())) {
            return;
        }

        // Validar offsets UTC,
        if (preg_match('/^UTC[+-]\d{1,2}$/', $value)) {
            $offset = (int)substr($value, 4);
            if ($offset >= -12 && $offset <= 14) {
                return;
            }
        }

        throw new ValidationException('Invalid timezone identifier.');
    }
}
