<?php

declare(strict_types=1);

/**
 * Derafu: Data Processor - Four-Phase Data Processing Library.
 *
 * Copyright (c) 2025 Esteban De La Fuente Rubio / Derafu <https://www.derafu.dev>
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

        // Validate standard timezone identifiers,
        if (in_array($value, DateTimeZone::listIdentifiers())) {
            return;
        }

        // Validate UTC offsets,
        if (preg_match('/^UTC([+-])(\d{1,2})$/', $value, $matches)) {
            $offset = (int)$matches[2] * ($matches[1] === '-' ? -1 : 1);
            if ($offset >= -12 && $offset <= 14) {
                return;
            }
        }

        throw new ValidationException('Invalid timezone identifier.');
    }
}
