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
use Derafu\DataProcessor\Trait\Rule\Validator\NumericParameterTrait;

final class MaxLengthRule implements ValidatorRuleInterface
{
    use NumericParameterTrait;

    public function validate(mixed $value, array $parameters = []): void
    {
        if (!is_string($value)) {
            throw new ValidationException('Value must be string.');
        }

        if (!isset($parameters[0]) || $parameters[0] === '') {
            throw new ValidationException('Max length parameter must be specified.');
        }

        $maxLength = $this->countParameter($parameters[0]);

        if (mb_strlen($value) > $maxLength) {
            throw new ValidationException([
                'Must not exceed {max} characters.',
                'max' => $maxLength,
            ]);
        }
    }
}
