<?php

declare(strict_types=1);

/**
 * Derafu: Data Processor - Four-Phase Data Processing Library.
 *
 * Copyright (c) 2025 Esteban De La Fuente Rubio / Derafu <https://www.derafu.dev>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\DataProcessor\Rule\Caster;

use Derafu\DataProcessor\Contract\CasterRuleInterface;

final class BooleanRule implements CasterRuleInterface
{
    public function cast(mixed $value, array $parameters = []): bool
    {
        if (is_string($value)) {
            $value = strtolower($value);
            if (in_array($value, ['false', '0', 'no', 'off', ''], true)) {
                return false;
            }
            if (in_array($value, ['true', '1', 'yes', 'on'], true)) {
                return true;
            }
        }
        return (bool)$value;
    }
}
