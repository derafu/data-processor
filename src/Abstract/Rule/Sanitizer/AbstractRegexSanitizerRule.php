<?php

declare(strict_types=1);

/**
 * Derafu: Data Processor - Four-Phase Data Processing Library.
 *
 * Copyright (c) 2025 Esteban De La Fuente Rubio / Derafu <https://www.derafu.dev>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\DataProcessor\Abstract\Rule\Sanitizer;

use Derafu\DataProcessor\Contract\RawParameterInterface;
use Derafu\DataProcessor\Contract\SanitizerRuleInterface;
use Derafu\DataProcessor\Exception\SanitizationException;

/**
 * Base of the sanitizers that apply a regular expression given as parameter.
 *
 * A pattern that is not valid is an error of the sanitizer, with no warning of
 * PHP.
 */
abstract class AbstractRegexSanitizerRule implements SanitizerRuleInterface, RawParameterInterface
{
    /**
     * Applies the regular expression and returns its result.
     *
     * @param callable(): (string|false|null) $callback The function of `preg_*`
     * that applies the expression. It returns `false` or `null` if it fails.
     * @return string The result.
     * @throws SanitizationException If the expression can not be applied.
     */
    protected function apply(callable $callback): string
    {
        $warning = null;
        set_error_handler(static function (int $level, string $message) use (&$warning): bool {
            $warning = $message;

            return true;
        });

        try {
            $result = $callback();
        } finally {
            restore_error_handler();
        }

        if ($result === false || $result === null) {
            throw new SanitizationException([
                'Invalid regular expression pattern: {error}',
                'error' => $warning ?? preg_last_error_msg(),
            ]);
        }

        return $result;
    }
}
