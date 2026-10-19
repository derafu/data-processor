<?php

declare(strict_types=1);

/**
 * Derafu: Data Processor - Four-Phase Data Processing Library.
 *
 * Copyright (c) 2025 Esteban De La Fuente Rubio / Derafu <https://www.derafu.dev>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\DataProcessor\Rule\Validator\Numeric;

use Derafu\DataProcessor\Contract\ValidatorRuleInterface;
use Derafu\DataProcessor\Exception\ValidationException;
use Derafu\DataProcessor\Trait\Rule\Validator\NumericParameterTrait;

final class LessThanOrEqualRule implements ValidatorRuleInterface
{
    use NumericParameterTrait;

    public function validate(mixed $value, array $parameters = []): void
    {
        if (!is_numeric($value)) {
            throw new ValidationException('Value must be numeric.');
        }

        if (empty($parameters)) {
            throw new ValidationException('Comparison value must be specified.');
        }

        $limit = $this->numberParameter($parameters[0]);

        if ((float)$value > $limit) {
            throw new ValidationException([
                'Must be less than or equal to {value}.',
                'value' => $parameters[0],
            ]);
        }
    }
}
