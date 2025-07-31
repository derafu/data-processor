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

// use Psr\Http\Message\UploadedFileInterface;

abstract class AbstractFileRule implements ValidatorRuleInterface
{
    protected function validateFileArray(mixed $value): void
    {
        // // Check if PSR-7 UploadedFileInterface is available.
        // if (class_exists(UploadedFileInterface::class)) {
        //     // PSR-7 UploadedFileInterface (single file).
        //     if ($value instanceof UploadedFileInterface) {
        //         if ($value->getError() !== UPLOAD_ERR_OK) {
        //             throw new ValidationException('Uploaded file has error.');
        //         }
        //         return;
        //     }

        //     // Array of PSR-7 UploadedFileInterface.
        //     if (is_array($value) && isset($value[0]) && $value[0] instanceof UploadedFileInterface) {
        //         foreach ($value as $file) {
        //             $this->validateFileArray($file); // Recursive.
        //         }
        //         return;
        //     }
        // }

        // // $_FILES simple.
        // if (is_array($value) && isset($value['tmp_name'])) {
        //     if (!is_string($value['tmp_name']) || !file_exists($value['tmp_name'])) {
        //         throw new ValidationException('File does not exist.');
        //     }
        //     if (!isset($value['error']) || $value['error'] !== UPLOAD_ERR_OK) {
        //         throw new ValidationException('File has upload error.');
        //     }
        //     return;
        // }

        // // $_FILES multiple.
        // if (is_array($value)) {
        //     foreach ($value as $v) {
        //         $this->validateFileArray($v); // Recursive.
        //     }
        //     return;
        // }

        // // Any other case.
        // throw new ValidationException('Invalid file format.');
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
