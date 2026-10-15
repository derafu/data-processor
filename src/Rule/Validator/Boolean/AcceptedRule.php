<?php

declare(strict_types=1);

/**
 * Derafu: Data Processor - Four-Phase Data Processing Library.
 *
 * Copyright (c) 2025 Esteban De La Fuente Rubio / Derafu <https://www.derafu.dev>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\DataProcessor\Rule\Validator\Boolean;

use Derafu\DataProcessor\Contract\ValidatorRuleInterface;
use Derafu\DataProcessor\Exception\ValidationException;

/**
 * The value must be `true`, like the checkbox of the terms and conditions.
 *
 * The value is checked as it is, so the rule goes after the `boolean` cast:
 * the value that is not a boolean fails, it is not read as one.
 */
final class AcceptedRule implements ValidatorRuleInterface
{
    public function validate(mixed $value, array $parameters = []): void
    {
        if ($value !== true) {
            throw new ValidationException('Must be accepted.');
        }
    }
}
