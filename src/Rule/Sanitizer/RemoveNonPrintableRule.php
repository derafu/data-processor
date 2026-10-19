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

/**
 * Removes the characters that are not visible and are not text.
 *
 * Removed: the ASCII control characters (except tab and line feed), the C1
 * control characters (U+0080 to U+009F) and the invisible format characters
 * (Unicode category `Cf`, as U+200B, U+200E, U+202A to U+202E, U+2060, U+FEFF
 * and U+00AD). The zero width non-joiner (U+200C) and the zero width joiner
 * (U+200D) are kept, because some scripts need them (Persian, Indic) and they
 * form the sequences of emojis.
 */
final class RemoveNonPrintableRule implements SanitizerRuleInterface
{
    public function sanitize(mixed $value, array $parameters = []): mixed
    {
        if (!is_string($value)) {
            throw new SanitizationException('Value must be string.');
        }

        return preg_replace('/[\x00-\x08\x0B-\x1F\x7F\x{80}-\x{9F}]|(?![\x{200C}\x{200D}])\p{Cf}/u', '', $value);
    }
}
