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
use Derafu\DataProcessor\Trait\Rule\Validator\NumericParameterTrait;

final class MinItemsRule implements ValidatorRuleInterface
{
    use NumericParameterTrait;

    public function validate(mixed $value, array $parameters = []): void
    {
        if (!is_array($value)) {
            throw new ValidationException('Value must be an array.');
        }

        if (!isset($parameters[0]) || $parameters[0] === '') {
            throw new ValidationException('Minimum number of items must be specified.');
        }

        $min = $this->countParameter($parameters[0]);
        if (count($value) < $min) {
            throw new ValidationException([
                'Array must contain at least {min} items.',
                'min' => $min,
            ]);
        }
    }
}
