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

final class RemoveSuffixRule implements SanitizerRuleInterface
{
    public function sanitize(mixed $value, array $parameters = []): mixed
    {
        if (!is_string($value)) {
            throw new SanitizationException('Value must be string.');
        }

        if (empty($parameters)) {
            throw new SanitizationException('Suffix parameter must be specified.');
        }

        foreach ($parameters as $suffix) {
            if (str_ends_with($value, $suffix)) {
                return substr($value, 0, -strlen($suffix));
            }
        }

        return $value;
    }
}
