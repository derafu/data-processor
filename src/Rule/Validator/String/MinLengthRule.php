<?php

declare(strict_types=1);

/**
 * Derafu: Data Processor - Four-Phase Data Processing Library.
 *
 * Copyright (c) 2025 Esteban De La Fuente Rubio / Derafu <https://www.derafu.org>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\DataProcessor\Rule\Validator\String;

use Derafu\DataProcessor\Contract\ValidatorRuleInterface;
use Derafu\DataProcessor\Exception\ValidationException;

final class MinLengthRule implements ValidatorRuleInterface
{
    public function validate(mixed $value, array $parameters = []): void
    {
        if (!is_string($value)) {
            throw new ValidationException('Value must be string.');
        }

        if (empty($parameters[0])) {
            throw new ValidationException('Min length parameter must be specified.');
        }

        $minLength = (int)$parameters[0];

        if (mb_strlen($value) < $minLength) {
            throw new ValidationException(
                sprintf('Must be at least %d characters long.', $minLength)
            );
        }
    }
}
