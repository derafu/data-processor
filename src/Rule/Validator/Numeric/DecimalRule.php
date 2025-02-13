<?php

declare(strict_types=1);

/**
 * Derafu: Data Processor - Four-Phase Data Processing Library.
 *
 * Copyright (c) 2025 Esteban De La Fuente Rubio / Derafu <https://www.derafu.org>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\DataProcessor\Rule\Validator\Numeric;

use Derafu\DataProcessor\Contract\ValidatorRuleInterface;
use Derafu\DataProcessor\Exception\ValidationException;

final class DecimalRule implements ValidatorRuleInterface
{
    public function validate(mixed $value, array $parameters = []): void
    {
        if (!is_numeric($value)) {
            throw new ValidationException('Value must be numeric.');
        }

        if (empty($parameters)) {
            throw new ValidationException('Decimal places must be specified.');
        }

        $decimals = explode('.', (string)$value)[1] ?? '';

        // Si hay dos parámetros, es un rango (min,max).
        if (isset($parameters[1])) {
            $minDecimals = (int)$parameters[0];
            $maxDecimals = (int)$parameters[1];

            if (strlen($decimals) < $minDecimals || strlen($decimals) > $maxDecimals) {
                throw new ValidationException(
                    sprintf(
                        'Decimal places must be between %d and %d.',
                        $minDecimals,
                        $maxDecimals
                    )
                );
            }
        } else {
            // Un solo parámetro indica exactitud decimal.
            $exactDecimals = (int)$parameters[0];
            if (strlen($decimals) !== $exactDecimals) {
                throw new ValidationException(
                    sprintf('Must have exactly %d decimal places.', $exactDecimals)
                );
            }
        }
    }
}
