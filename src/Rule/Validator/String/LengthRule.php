<?php

declare(strict_types=1);

/**
 * Derafu: Data Processor - Four-Phase Data Processing Library.
 *
 * Copyright (c) 2025 Esteban De La Fuente Rubio / Derafu <https://www.derafu.dev>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\DataProcessor\Rule\Validator\String;

use Derafu\DataProcessor\Contract\ValidatorRuleInterface;
use Derafu\DataProcessor\Exception\ValidationException;

final class LengthRule implements ValidatorRuleInterface
{
    public function validate(mixed $value, array $parameters = []): void
    {
        if (!is_string($value)) {
            throw new ValidationException('Value must be string.');
        }

        if (empty($parameters[0])) {
            throw new ValidationException('Length parameter must be specified.');
        }

        $length = (int)$parameters[0];

        if (mb_strlen($value) !== $length) {
            throw new ValidationException(
                sprintf('Must be exactly %d characters.', $length)
            );
        }
    }
}
