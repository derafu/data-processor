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

final class FileRule extends AbstractFileRule
{
    public function validate(mixed $value, array $parameters = []): void
    {
        $this->validateFileArray($value);

        if (isset($parameters[0])) {
            $maxSize = $this->parseSize($parameters[0]);
            if ($value['size'] > $maxSize) {
                throw new ValidationException(
                    sprintf('File must not be larger than %s.', $parameters[0])
                );
            }
        }
    }
}
