<?php

declare(strict_types=1);

/**
 * Derafu: Data Processor - Four-Phase Data Processing Library.
 *
 * Copyright (c) 2025 Esteban De La Fuente Rubio / Derafu <https://www.derafu.dev>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\DataProcessor\Rule\Sanitizer;

use Derafu\DataProcessor\Contract\SanitizerRuleInterface;
use Derafu\DataProcessor\Exception\SanitizationException;

final class TrimRule implements SanitizerRuleInterface
{
    public function sanitize(mixed $value, array $parameters = []): mixed
    {
        if (!is_string($value)) {
            throw new SanitizationException('Value must be string.');
        }

        return isset($parameters[0])
            ? trim($value, $parameters[0])
            : trim($value)
        ;
    }
}
