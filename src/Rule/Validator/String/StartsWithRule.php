<?php

declare(strict_types=1);

/**
 * Derafu: Derafu: Data Processor - Four-Phase Data Processing Library.
 *
 * Copyright (c) 2025 Esteban De La Fuente Rubio / Derafu <https://www.derafu.org>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\DataProcessor\Rule\Validator\String;

use Derafu\DataProcessor\Contract\ValidatorRuleInterface;
use Derafu\DataProcessor\Exception\ValidationException;

final class StartsWithRule implements ValidatorRuleInterface
{
    public function validate(mixed $value, array $parameters = []): void
    {
        if (!is_string($value)) {
            throw new ValidationException('Value must be a string.');
        }

        if (empty($parameters)) {
            throw new ValidationException('Start string must be specified.');
        }

        if (!str_starts_with($value, $parameters[0])) {
            throw new ValidationException(
                sprintf('Value must start with "%s"', $parameters[0])
            );
        }
    }
}
