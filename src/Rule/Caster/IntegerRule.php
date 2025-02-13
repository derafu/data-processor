<?php

declare(strict_types=1);

/**
 * Derafu: Derafu: Data Processor - Four-Phase Data Processing Library.
 *
 * Copyright (c) 2025 Esteban De La Fuente Rubio / Derafu <https://www.derafu.org>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\DataProcessor\Rule\Caster;

use Derafu\DataProcessor\Contract\CasterRuleInterface;
use Derafu\DataProcessor\Exception\CastingException;

final class IntegerRule implements CasterRuleInterface
{
    public function cast(mixed $value, array $parameters = []): int
    {
        if (is_string($value) && !is_numeric($value)) {
            throw new CastingException('Cannot cast non-numeric string to integer.');
        }
        if (is_array($value)) {
            throw new CastingException('Cannot cast array to integer.');
        }
        return (int)$value;
    }
}
