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

final class HtmlEntitiesRule implements SanitizerRuleInterface
{
    public function sanitize(mixed $value, array $parameters = []): mixed
    {
        if (!is_string($value)) {
            throw new SanitizationException('Value must be string.');
        }

        return htmlentities($value, ENT_QUOTES | ENT_DISALLOWED | ENT_HTML5);
    }
}
