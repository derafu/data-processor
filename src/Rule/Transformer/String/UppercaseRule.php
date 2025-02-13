<?php

declare(strict_types=1);

/**
 * Derafu: Derafu: Data Processor - Four-Phase Data Processing Library.
 *
 * Copyright (c) 2025 Esteban De La Fuente Rubio / Derafu <https://www.derafu.org>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\DataProcessor\Rule\Transformer\String;

use Derafu\DataProcessor\Contract\TransformerRuleInterface;
use Derafu\DataProcessor\Exception\TransformationException;

final class UppercaseRule implements TransformerRuleInterface
{
    public function transform(mixed $value, array $parameters = []): mixed
    {
        if (!is_string($value)) {
            throw new TransformationException('Value must be string.');
        }

        return mb_strtoupper($value);
    }
}
