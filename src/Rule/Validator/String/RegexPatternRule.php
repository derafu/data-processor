<?php

declare(strict_types=1);

/**
 * Derafu: Data Processor - Four-Phase Data Processing Library.
 *
 * Copyright (c) 2025 Esteban De La Fuente Rubio / Derafu <https://www.derafu.dev>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\DataProcessor\Rule\Validator\String;

use Derafu\DataProcessor\Exception\ValidationException;

/**
 * The value must be a regular expression.
 *
 * It is the value that is the pattern (a field where the user writes a
 * regular expression), not the pattern that the value must match, that is the
 * rule `regex`. As the patterns of JSON Schema, it is written without
 * delimiters: the rule puts them, and escapes the slashes that it has (the
 * ones that are not escaped already).
 */
final class RegexPatternRule extends AbstractRegexRule
{
    public function validate(mixed $value, array $parameters = []): void
    {
        if (!is_string($value)) {
            throw new ValidationException('Value must be a string.');
        }

        $this->matches('/' . $this->escapeSlashes($value) . '/', '');
    }

    /**
     * Escapes the slashes that are not escaped yet, so the ones that the
     * pattern already has (`\/`) are not escaped twice.
     */
    private function escapeSlashes(string $pattern): string
    {
        $escaped = '';
        $length = strlen($pattern);

        for ($i = 0; $i < $length; $i++) {
            $character = $pattern[$i];

            if ($character === '\\' && $i + 1 < $length) {
                // A backslash takes the character that follows it as it is.
                $escaped .= $character . $pattern[++$i];
            } elseif ($character === '/') {
                $escaped .= '\\/';
            } else {
                $escaped .= $character;
            }
        }

        return $escaped;
    }
}
