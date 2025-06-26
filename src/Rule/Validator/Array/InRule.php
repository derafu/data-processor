<?php

declare(strict_types=1);

/**
 * Derafu: Data Processor - Four-Phase Data Processing Library.
 *
 * Copyright (c) 2025 Esteban De La Fuente Rubio / Derafu <https://www.derafu.dev>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\DataProcessor\Rule\Validator\Array;

use Derafu\DataProcessor\Contract\ValidatorRuleInterface;
use Derafu\DataProcessor\Exception\ValidationException;

final class InRule implements ValidatorRuleInterface
{
    public function validate(mixed $value, array $parameters = []): void
    {
        if (empty($parameters)) {
            throw new ValidationException('Choices must be specified.');
        }

        if (!in_array($value, $parameters, true)) {
            throw new ValidationException(
                sprintf(
                    'Must be one of: %s.',
                    implode(', ', array_map('strval', $parameters))
                )
            );
        }
    }
}
