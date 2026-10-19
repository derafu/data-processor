<?php

declare(strict_types=1);

/**
 * Derafu: Data Processor - Four-Phase Data Processing Library.
 *
 * Copyright (c) 2025 Esteban De La Fuente Rubio / Derafu <https://www.derafu.dev>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\DataProcessor\Rule\Validator\File;

use Derafu\DataProcessor\Abstract\Rule\Validator\File\AbstractFileRule;
use Derafu\DataProcessor\Exception\ValidationException;

final class FileRule extends AbstractFileRule
{
    public function validate(mixed $value, array $parameters = []): void
    {
        $limits = $this->sizeLimitsOf($parameters);
        $files = $this->validateFileArray($value, $limits['min'], $limits['minText']);

        if ($limits['max'] !== null) {
            foreach ($files as $file) {
                if ($file->size !== null && $file->size > $limits['max']) {
                    throw new ValidationException([
                        'File size must not be larger than {max}.',
                        'max' => $limits['maxText'],
                    ]);
                }
            }
        }
    }
}
