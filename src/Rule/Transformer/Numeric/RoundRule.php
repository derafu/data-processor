<?php

declare(strict_types=1);

/**
 * Derafu: Derafu: Data Processor - Four-Phase Data Processing Library.
 *
 * Copyright (c) 2025 Esteban De La Fuente Rubio / Derafu <https://www.derafu.org>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\DataProcessor\Rule\Transformer\Numeric;

use Derafu\DataProcessor\Contract\TransformerRuleInterface;
use Derafu\DataProcessor\Exception\TransformationException;

final class RoundRule implements TransformerRuleInterface
{
    public function transform(mixed $value, array $parameters = []): float|int
    {
        if (!is_numeric($value)) {
            throw new TransformationException('Value must be numeric.');
        }

        $precision = isset($parameters[0]) ? (int)$parameters[0] : 0;
        $rounded = round((float)$value, $precision);

        return $precision === 0 ? (int)$rounded : $rounded;
    }
}
