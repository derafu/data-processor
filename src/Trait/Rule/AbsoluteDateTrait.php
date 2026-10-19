<?php

declare(strict_types=1);

/**
 * Derafu: Data Processor - Four-Phase Data Processing Library.
 *
 * Copyright (c) 2025 Esteban De La Fuente Rubio / Derafu <https://www.derafu.dev>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\DataProcessor\Trait\Rule;

/**
 * Tells if a text is an absolute date in ISO 8601.
 *
 * The date validators and the date casters accept the same texts: `YYYY-MM-DD`,
 * or with a time, `YYYY-MM-DDTHH:MM[:SS[.fraction]]` (a space can replace the
 * `T`), and with `Z` or an offset (`+03:00`) at the end. The date and the time
 * must exist. A relative text (`tomorrow`, `+1 day`) or a date in another
 * format (`01/02/2024`) is not accepted, because its value depends on the clock
 * of the server or on how it is read.
 */
trait AbsoluteDateTrait
{
    /**
     * Checks if a value is a text with an absolute date in ISO 8601.
     *
     * @param string $value The value to check.
     * @return bool True if it is an absolute date that exists.
     */
    private function isAbsoluteDate(string $value): bool
    {
        $pattern = '/^(\d{4})-(\d{2})-(\d{2})(?:[T ](\d{2}):(\d{2})(?::(\d{2})(?:\.\d{1,9})?)?(?:Z|[+-]\d{2}(?::?\d{2})?)?)?$/i';

        if (!preg_match($pattern, $value, $parts)) {
            return false;
        }

        return checkdate((int) $parts[2], (int) $parts[3], (int) $parts[1])
            && (int) ($parts[4] ?? 0) < 24
            && (int) ($parts[5] ?? 0) < 60
            && (int) ($parts[6] ?? 0) < 60;
    }
}
