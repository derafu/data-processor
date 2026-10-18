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

use Derafu\DataProcessor\Exception\ValidationException;
use Derafu\DataProcessor\ValueObject\FileInfo;

final class MimeTypeRule extends AbstractFileRule
{
    public function validate(mixed $value, array $parameters = []): void
    {
        $types = array_map(
            static fn (FileInfo $file): ?string => $file->type,
            $this->validateFileArray($value)
        );
        if (in_array(null, $types, true)) {
            throw new ValidationException('File array must contain type.');
        }

        if (empty($parameters)) {
            throw new ValidationException('Allowed MIME types must be specified.');
        }

        $allowedTypes = array_map('trim', $parameters);
        foreach ($types as $type) {
            if (!in_array($type, $allowedTypes)) {
                throw new ValidationException([
                    'Invalid file type {type}. Allowed types: {allowed}.',
                    'type' => $type,
                    'allowed' => implode(', ', $allowedTypes),
                ]);
            }
        }
    }
}
