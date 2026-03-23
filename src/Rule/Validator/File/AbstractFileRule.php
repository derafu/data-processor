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

use Derafu\DataProcessor\Contract\ValidatorRuleInterface;
use Derafu\DataProcessor\Exception\ValidationException;
use Psr\Http\Message\UploadedFileInterface;

abstract class AbstractFileRule implements ValidatorRuleInterface
{
    protected function validateFileArray(mixed $value): void
    {
        // Case 1 & 2: PSR-7 UploadedFileInterface (if available).
        if (interface_exists(UploadedFileInterface::class)) {
            // Single PSR-7 file.
            if ($value instanceof UploadedFileInterface) {
                if ($value->getError() !== UPLOAD_ERR_OK) {
                    throw new ValidationException('Uploaded file has error.');
                }
                return;
            }

            // Indexed array of PSR-7 files.
            if (is_array($value) && isset($value[0]) && $value[0] instanceof UploadedFileInterface) {
                foreach ($value as $file) {
                    $this->validateFileArray($file);
                }
                return;
            }
        }

        if (!is_array($value)) {
            throw new ValidationException('Invalid file format.');
        }

        // Case 3: $_FILES simple (single file, tmp_name is a string).
        if (isset($value['tmp_name']) && is_string($value['tmp_name'])) {
            if (!isset($value['error']) || $value['error'] !== UPLOAD_ERR_OK) {
                throw new ValidationException('File has upload error.');
            }
            if (!file_exists($value['tmp_name'])) {
                throw new ValidationException('File does not exist.');
            }
            return;
        }

        // Case 4a: $_FILES native multiple (tmp_name is an array of paths).
        if (isset($value['tmp_name']) && is_array($value['tmp_name'])) {
            foreach ($value['tmp_name'] as $index => $tmpName) {
                $error = $value['error'][$index] ?? UPLOAD_ERR_NO_FILE;
                if ($error !== UPLOAD_ERR_OK) {
                    throw new ValidationException(
                        sprintf('File at index %d has upload error.', $index)
                    );
                }
                if (!file_exists($tmpName)) {
                    throw new ValidationException(
                        sprintf('File at index %d does not exist.', $index)
                    );
                }
            }
            return;
        }

        // Case 4b: Normalized multiple (indexed array of single $_FILES-like arrays).
        if (isset($value[0]) && is_array($value[0])) {
            foreach ($value as $v) {
                $this->validateFileArray($v);
            }
            return;
        }

        throw new ValidationException('Invalid file format.');
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
