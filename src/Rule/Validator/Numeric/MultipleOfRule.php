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

final class MultipleOfRule implements ValidatorRuleInterface
{
    public function validate(mixed $value, array $parameters = []): void
    {
        if (!is_numeric($value)) {
            throw new ValidationException('Value must be numeric.');
        }

        if (empty($parameters)) {
            throw new ValidationException('Multiple value must be specified.');
        }

        $multiple = (float)$parameters[0];
        if ($multiple == 0) {
            throw new ValidationException('Multiple cannot be zero.');
        }

        // Uso de fmod para soportar decimales.
        if (fmod((float)$value, $multiple) !== 0.0) {
            throw new ValidationException(
                sprintf('Must be a multiple of %s.', $parameters[0])
            );
        }
    }
}
