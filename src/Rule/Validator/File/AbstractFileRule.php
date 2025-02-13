<?php

declare(strict_types=1);

/**
 * Derafu: Data Processor - Four-Phase Data Processing Library.
 *
 * Copyright (c) 2025 Esteban De La Fuente Rubio / Derafu <https://www.derafu.org>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\DataProcessor\Rule\Validator\File;

use Derafu\DataProcessor\Contract\ValidatorRuleInterface;
use Derafu\DataProcessor\Exception\ValidationException;

abstract class AbstractFileRule implements ValidatorRuleInterface
{
    protected function validateFileArray(mixed $value): void
    {
        if (!is_array($value)) {
            throw new ValidationException('Value must be a file array.');
        }

        if (!isset($value['tmp_name'])) {
            throw new ValidationException('File array must contain tmp_name.');
        }

        if (!file_exists($value['tmp_name'])) {
            throw new ValidationException('File does not exist.');
        }
    }

    protected function parseSize(string $size): int
    {
        $units = ['B' => 1, 'K' => 1024, 'M' => 1024 * 1024, 'G' => 1024 * 1024 * 1024];
        $unit = strtoupper(substr($size, -1));
        $value = (int)substr($size, 0, -1);

        if (!isset($units[$unit])) {
            throw new ValidationException('Invalid size unit.');
        }

        return $value * $units[$unit];
    }
}
