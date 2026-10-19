<?php

declare(strict_types=1);

/**
 * Derafu: Data Processor - Four-Phase Data Processing Library.
 *
 * Copyright (c) 2025 Esteban De La Fuente Rubio / Derafu <https://www.derafu.dev>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\DataProcessor\Abstract\Rule\Validator\String;

use Derafu\DataProcessor\Contract\ValidatorRuleInterface;
use Derafu\DataProcessor\Exception\ValidationException;

/**
 * Base of the validators that match a value with a regular expression.
 */
abstract class AbstractRegexRule implements ValidatorRuleInterface
{
    /**
     * Tells if a string matches a regular expression.
     *
     * The warning that PHP raises for a pattern that is not valid is not
     * hidden: its text is part of the error that is thrown.
     *
     * @param string $pattern The regular expression, with its delimiters.
     * @param string $value The string to test.
     * @return bool True if the string matches.
     * @throws ValidationException If the pattern is not valid.
     */
    protected function matches(string $pattern, string $value): bool
    {
        $warning = null;
        set_error_handler(static function (int $level, string $message) use (&$warning): bool {
            $warning = $message;

            return true;
        });

        try {
            $result = preg_match($pattern, $value);
        } finally {
            restore_error_handler();
        }

        if ($result === false) {
            throw new ValidationException([
                'Invalid regular expression pattern: {error}',
                'error' => $warning ?? preg_last_error_msg(),
            ]);
        }

        return $result === 1;
    }
}
