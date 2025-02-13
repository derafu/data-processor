<?php

declare(strict_types=1);

/**
 * Derafu: Derafu: Data Processor - Four-Phase Data Processing Library.
 *
 * Copyright (c) 2025 Esteban De La Fuente Rubio / Derafu <https://www.derafu.org>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\DataProcessor\Rule\Validator\File;

use Derafu\DataProcessor\Exception\ValidationException;

final class ImageRule extends AbstractFileRule
{
    private const ALLOWED_TYPES = [
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',
        'image/svg+xml',
    ];

    public function validate(mixed $value, array $parameters = []): void
    {
        $this->validateFileArray($value);

        if (!isset($value['type'])) {
            throw new ValidationException('File array must contain type.');
        }

        if (!in_array($value['type'], self::ALLOWED_TYPES)) {
            throw new ValidationException('File must be an image.');
        }

        if (isset($parameters[0])) {
            $maxSize = $this->parseSize($parameters[0]);
            if ($value['size'] > $maxSize) {
                throw new ValidationException(
                    sprintf('Image must not be larger than %s.', $parameters[0])
                );
            }
        }
    }
}
