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
use Derafu\DataProcessor\ValueObject\FileInfo;
use Psr\Http\Message\UploadedFileInterface;

abstract class AbstractFileRule implements ValidatorRuleInterface
{
    /**
     * Validates that the value is an uploaded file, or a list of them, and
     * returns the files.
     *
     * Each file must have no upload error and, if it has a path on the disk, be
     * there.
     *
     * @param mixed $value The value to validate: a file, or a list of them. See
     * `fileFrom()` and `filesFrom()` for the shapes that they can have.
     * @return list<FileInfo> The files of the value, in order.
     * @throws ValidationException If the value is not an uploaded file, or a
     * list of them, or one of its files is not valid.
     */
    protected function validateFileArray(mixed $value): array
    {
        $file = $this->fileFrom($value);
        if ($file !== null) {
            $this->checkFile($file, null);

            return [$file];
        }

        $files = $this->filesFrom($value);
        foreach ($files as $index => $file) {
            $this->checkFile($file, $index);
        }

        return array_values($files);
    }

    /**
     * Checks that a file has no upload error and, if it has a path on the disk,
     * that it is there.
     *
     * @param FileInfo $file The file.
     * @param int|string|null $index The position or key of the file in the list
     * it came in, `null` if it did not come in a list.
     * @throws ValidationException If the file is not valid.
     */
    private function checkFile(FileInfo $file, int|string|null $index): void
    {
        if ($file->error !== UPLOAD_ERR_OK) {
            throw $index === null
                ? new ValidationException('File has upload error.')
                : new ValidationException(['File at index {index} has upload error.', 'index' => $index]);
        }

        if ($file->path !== null && !file_exists($file->path)) {
            throw $index === null
                ? new ValidationException('File does not exist.')
                : new ValidationException(['File at index {index} does not exist.', 'index' => $index]);
        }
    }

    /**
     * Reads a value that is one file.
     *
     * The shapes are a PSR-7 file (if the interface is available) and the array
     * of a file of `$_FILES`, with `tmp_name` as a string.
     *
     * Only the shape is checked here. Whether the file is valid is not.
     *
     * @param mixed $value The value.
     * @return FileInfo|null `null` if the value is not one file.
     */
    private function fileFrom(mixed $value): ?FileInfo
    {
        if (interface_exists(UploadedFileInterface::class) && $value instanceof UploadedFileInterface) {
            return new FileInfo(
                $value->getError(),
                null,
                $value->getSize(),
                $value->getClientMediaType()
            );
        }

        if (is_array($value) && isset($value['tmp_name']) && is_string($value['tmp_name'])) {
            $error = $value['error'] ?? null;

            return new FileInfo(
                is_int($error) ? $error : UPLOAD_ERR_NO_FILE,
                $value['tmp_name'],
                $this->sizeFrom($value['size'] ?? null),
                $this->typeFrom($value['type'] ?? null)
            );
        }

        return null;
    }

    /**
     * Reads a value that is a list of files.
     *
     * The shapes are:
     *
     *   - The native `$_FILES` of a field with `multiple`: `tmp_name` is an
     *     array of paths, and `error`, `size` and `type` have an array too, with
     *     the same keys.
     *   - A list of PSR-7 files, or of arrays of a file of `$_FILES`.
     *
     * Only the shape is checked here. Whether the files are valid is not.
     *
     * @param mixed $value The value.
     * @return array<int|string, FileInfo> The files, with the key that each one
     * has in the value.
     * @throws ValidationException If the value is not a list of files.
     */
    private function filesFrom(mixed $value): array
    {
        if (!is_array($value)) {
            throw new ValidationException('Invalid file format.');
        }

        // $_FILES native multiple (tmp_name is an array of paths).
        if (isset($value['tmp_name']) && is_array($value['tmp_name'])) {
            $errors = $value['error'] ?? null;
            $sizes = $value['size'] ?? null;
            $types = $value['type'] ?? null;

            $files = [];
            foreach ($value['tmp_name'] as $index => $tmpName) {
                $error = is_array($errors) ? ($errors[$index] ?? null) : null;
                $files[$index] = new FileInfo(
                    is_int($error) ? $error : UPLOAD_ERR_NO_FILE,
                    is_string($tmpName) ? $tmpName : '',
                    $this->sizeFrom(is_array($sizes) ? ($sizes[$index] ?? null) : null),
                    $this->typeFrom(is_array($types) ? ($types[$index] ?? null) : null)
                );
            }

            return $files;
        }

        // A list of files.
        if (isset($value[0]) && (is_array($value[0]) || $value[0] instanceof UploadedFileInterface)) {
            $files = [];
            foreach ($value as $index => $item) {
                $file = $this->fileFrom($item);
                if ($file === null) {
                    throw new ValidationException('Invalid file format.');
                }
                $files[$index] = $file;
            }

            return $files;
        }

        throw new ValidationException('Invalid file format.');
    }

    /**
     * The size in bytes that a `$_FILES` array says its file has.
     *
     * @param mixed $size What the `size` key has, if it has anything.
     * @return int|null `null` if it is not a whole number: the size is not
     * known.
     */
    private function sizeFrom(mixed $size): ?int
    {
        if (is_int($size)) {
            return $size;
        }

        return is_string($size) && ctype_digit($size) ? (int)$size : null;
    }

    /**
     * The MIME type that a `$_FILES` array says its file has.
     *
     * @param mixed $type What the `type` key has, if it has anything.
     * @return string|null `null` if it is not a string: the file has no type.
     */
    private function typeFrom(mixed $type): ?string
    {
        return is_string($type) ? $type : null;
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
