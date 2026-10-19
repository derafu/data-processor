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

use Derafu\DataProcessor\Contract\ValidatorRuleInterface;
use Derafu\DataProcessor\Exception\ValidationException;

/**
 * Validates a color as `<input type="color">` sends it: `#rrggbb`.
 *
 * It must have the `#` and six hexadecimal digits, in uppercase or lowercase.
 * The short form (`#fff`) and the names (`red`) are not valid.
 */
final class ColorRule implements ValidatorRuleInterface
{
    /**
     * {@inheritDoc}
     */
    public function validate(mixed $value, array $parameters = []): void
    {
        if (!is_string($value)) {
            throw new ValidationException('Value must be a string.');
        }

        if (!preg_match('/^#[0-9a-f]{6}$/Di', $value)) {
            throw new ValidationException('Invalid color format.');
        }
    }
}
