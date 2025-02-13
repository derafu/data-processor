<?php

declare(strict_types=1);

/**
 * Derafu: Derafu: Data Processor - Four-Phase Data Processing Library.
 *
 * Copyright (c) 2025 Esteban De La Fuente Rubio / Derafu <https://www.derafu.org>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\DataProcessor\Rule\Sanitizer;

use Derafu\DataProcessor\Contract\SanitizerRuleInterface;
use Derafu\DataProcessor\Exception\SanitizationException;

final class SubStringRule implements SanitizerRuleInterface
{
    public function sanitize(mixed $value, array $parameters = []): mixed
    {
        if (!is_string($value)) {
            throw new SanitizationException('Value must be string.');
        }

        if (empty($parameters)) {
            throw new SanitizationException('Length parameter must be specified.');
        }

        // Si hay dos parámetros, el primero es start y el segundo length.
        if (isset($parameters[1])) {
            return mb_substr($value, (int)$parameters[0], (int)$parameters[1]);
        }

        // Si hay un parámetro, es length (start = 0).
        return mb_substr($value, 0, (int)$parameters[0]);
    }
}
