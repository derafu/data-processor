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

use Derafu\DataProcessor\Exception\ValidationException;

final class MimeTypeRule extends AbstractFileRule
{
    public function validate(mixed $value, array $parameters = []): void
    {
        $this->validateFileArray($value);

        if (!isset($value['type'])) {
            throw new ValidationException('File array must contain type.');
        }

        if (empty($parameters)) {
            throw new ValidationException('Allowed MIME types must be specified.');
        }

        $allowedTypes = explode(',', $parameters[0]);
        if (!in_array($value['type'], $allowedTypes)) {
            throw new ValidationException(sprintf(
                'Invalid file type %s. Allowed types: %s.',
                $value['type'],
                implode(', ', $allowedTypes)
            ));
        }
    }
}
