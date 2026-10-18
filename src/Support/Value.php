<?php

declare(strict_types=1);

/**
 * Derafu: Data Processor - Four-Phase Data Processing Library.
 *
 * Copyright (c) 2025 Esteban De La Fuente Rubio / Derafu <https://www.derafu.dev>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\DataProcessor\Support;

/**
 * What the package says about a value that is being processed.
 */
final class Value
{
    /**
     * Tells if a value is empty: it has nothing in it to process.
     *
     * It is the only definition of empty in the package. The processor skips
     * the rules of a value that is empty, unless the rule `required` is there,
     * and `required` rejects the values that are empty.
     *
     * @param mixed $value The value.
     * @return bool `true` for `null`, an empty string and an empty array.
     * `false` for any other value, including `0`, `'0'` and `false`.
     */
    public static function isEmpty(mixed $value): bool
    {
        return $value === null || $value === '' || $value === [];
    }
}
