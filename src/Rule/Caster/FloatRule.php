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
use Derafu\DataProcessor\Exception\CastingException;

final class FloatRule implements CasterRuleInterface
{
    public function cast(mixed $value, array $parameters = []): float
    {
        if (is_string($value) && !is_numeric($value)) {
            throw new CastingException('Cannot cast non-numeric string to float.');
        }
        if (is_array($value)) {
            throw new CastingException('Cannot cast array to float.');
        }
        $result = (float)$value;
        if (isset($parameters[0])) {
            $decimals = (int)$parameters[0];
            return round($result, $decimals);
        }
        return $result;
    }
}
