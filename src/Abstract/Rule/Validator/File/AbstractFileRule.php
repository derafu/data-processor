<?php

declare(strict_types=1);

/**
 * Derafu: Data Processor - Four-Phase Data Processing Library.
 *
 * Copyright (c) 2025 Esteban De La Fuente Rubio / Derafu <https://www.derafu.dev>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\DataProcessor\Abstract\Rule\Validator\File;

use Derafu\DataProcessor\Contract\ValidatorRuleInterface;
use Derafu\DataProcessor\Exception\ValidationException;
use Derafu\DataProcessor\ValueObject\FileInfo;
use Derafu\File\ByteSize;
use Psr\Http\Message\UploadedFileInterface;
use RuntimeException;

abstract class AbstractFileRule implements ValidatorRuleInterface
{
    /**
     * Bytes of the start of a file that are read to know its type. It is the
     * most that `libmagic` reads.
     */
    private const START_BYTES = 1048576;

    /**
     * Reads the size limits that a rule has as parameters.
     *
     * The parameters are `max_size=<size>` and `min_size=<size>`, in any order.
     * A parameter that is only a size, as `2M`, is the maximum size: it is the
     * short form of `max_size=2M`. The sizes are read as `ByteSize::parse()`
     * does (`2048`, `2M`, `1.5Mi`).
     *
     * The minimum size is 1 byte unless `min_size` is given, so a file that is
     * empty is not valid: `min_size=0` allows it.
     *
     * @param list<string> $parameters The parameters of the rule.
     * @return array{min: int, minText: string, max: int|null, maxText: string|null}
     * The limits in bytes, with the text to show of each one in a message.
     * The maximum is `null` if there is none.
     * @throws ValidationException If a parameter is not valid, is repeated, or
     * the minimum size is larger than the maximum size.
     */
    protected function sizeLimitsOf(array $parameters): array
    {
        $texts = ['min_size' => null, 'max_size' => null];
        foreach ($parameters as $parameter) {
            [$name, $text] = str_contains($parameter, '=')
                ? explode('=', $parameter, 2)
                : ['max_size', $parameter];

            if (!array_key_exists($name, $texts) || $texts[$name] !== null) {
                throw new ValidationException([
                    'Invalid parameter: {parameter}',
                    'parameter' => $parameter,
                ]);
            }

            $texts[$name] = $text;
        }

        $max = $texts['max_size'] !== null ? $this->parseSize($texts['max_size']) : null;
        $min = $texts['min_size'] !== null ? $this->parseSize($texts['min_size']) : 1;

        if ($texts['min_size'] !== null && $max !== null && $min > $max) {
            throw new ValidationException('Minimum size must not be larger than the maximum size.');
        }

        return [
            'min' => $min,
            'minText' => $this->sizeText($texts['min_size'] ?? '1'),
            'max' => $max,
            'maxText' => $texts['max_size'] !== null ? $this->sizeText($texts['max_size']) : null,
        ];
    }

    /**
     * Writes a size as it is shown in a message.
     *
     * A size with a unit (`2M`) is shown as it was written. A number of bytes
     * has no unit, so it gets one: `2048 bytes`.
     *
     * @param string $size The size, as it was written in the parameters.
     * @return string The size to show.
     */
    private function sizeText(string $size): string
    {
        if (!ctype_digit($size)) {
            return $size;
        }

        return $size === '1' ? '1 byte' : $size . ' bytes';
    }

    /**
     * Validates that the value is an uploaded file, or a list of them, and
     * returns the files.
     *
     * Each file must have no upload error, be on the disk if it has a path
     * there, and not be smaller than the minimum size. A file that does not say
     * its size is not rejected for it.
     *
     * @param mixed $value The value to validate: a file, or a list of them. See
     * `fileFrom()` and `filesFrom()` for the shapes that they can have.
     * @param int $minSize The minimum size of each file, in bytes.
     * @param string $minText The minimum size as it is written, to report it.
     * @return list<FileInfo> The files of the value, in order.
     * @throws ValidationException If the value is not an uploaded file, or a
     * list of them, or one of its files is not valid.
     */
    protected function validateFileArray(mixed $value, int $minSize = 0, string $minText = '0'): array
    {
        $file = $this->fileFrom($value);
        if ($file !== null) {
            $this->checkFile($file, null, $minSize, $minText);

            return [$file];
        }

        $files = $this->filesFrom($value);
        foreach ($files as $index => $file) {
            $this->checkFile($file, $index, $minSize, $minText);
        }

        return array_values($files);
    }

    /**
     * Checks that a file has no upload error, that it is on the disk if it has
     * a path there, and that it is not smaller than the minimum size.
     *
     * @param FileInfo $file The file.
     * @param int|string|null $index The position or key of the file in the list
     * it came in, `null` if it did not come in a list.
     * @param int $minSize The minimum size, in bytes.
     * @param string $minText The minimum size as it is written, to report it.
     * @throws ValidationException If the file is not valid.
     */
    private function checkFile(FileInfo $file, int|string|null $index, int $minSize, string $minText): void
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

        if ($file->size === null || $file->size >= $minSize) {
            return;
        }

        if ($file->size === 0) {
            throw $index === null
                ? new ValidationException('File is empty.')
                : new ValidationException(['File at index {index} is empty.', 'index' => $index]);
        }

        throw new ValidationException([
            'File size must not be smaller than {min}.',
            'min' => $minText,
        ]);
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
                $value->getClientFilename(),
                $value
            );
        }

        if (is_array($value) && isset($value['tmp_name']) && is_string($value['tmp_name'])) {
            $error = $value['error'] ?? null;

            return new FileInfo(
                is_int($error) ? $error : UPLOAD_ERR_NO_FILE,
                $value['tmp_name'],
                $this->sizeFrom($value['size'] ?? null),
                $this->nameFrom($value['name'] ?? null)
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
            $names = $value['name'] ?? null;

            $files = [];
            foreach ($value['tmp_name'] as $index => $tmpName) {
                $error = is_array($errors) ? ($errors[$index] ?? null) : null;
                $files[$index] = new FileInfo(
                    is_int($error) ? $error : UPLOAD_ERR_NO_FILE,
                    is_string($tmpName) ? $tmpName : '',
                    $this->sizeFrom(is_array($sizes) ? ($sizes[$index] ?? null) : null),
                    $this->nameFrom(is_array($names) ? ($names[$index] ?? null) : null)
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
     * The name that a `$_FILES` array says its file has.
     *
     * @param mixed $name What the `name` key has, if it has anything.
     * @return string|null `null` if it is not a string: the file has no name.
     */
    private function nameFrom(mixed $name): ?string
    {
        return is_string($name) ? $name : null;
    }

    /**
     * Reads the start of the content of a PSR-7 file, to know its type.
     *
     * The stream is left in the position that it had. A stream that can not be
     * moved is not read, because reading it would take the content from whoever
     * uses it next.
     *
     * @param UploadedFileInterface $upload The file.
     * @return string|null The first bytes of the content, or `null` if they
     * can not be read: the stream is not available, can not be moved or fails.
     */
    protected function startOf(UploadedFileInterface $upload): ?string
    {
        try {
            $stream = $upload->getStream();
            if (!$stream->isSeekable()) {
                return null;
            }

            $position = $stream->tell();
            $stream->rewind();

            try {
                $content = '';
                while (strlen($content) < self::START_BYTES && !$stream->eof()) {
                    $chunk = $stream->read(self::START_BYTES - strlen($content));
                    if ($chunk === '') {
                        break;
                    }
                    $content .= $chunk;
                }
            } finally {
                $stream->seek($position);
            }

            return $content;
        } catch (RuntimeException) {
            return null;
        }
    }

    /**
     * Reads a size that a rule has as a parameter.
     *
     * @param string $size The size, as `ByteSize::parse()` reads it (`2048`,
     * `2M`, `1.5Mi`).
     * @return int The size in bytes.
     * @throws ValidationException If the size is not valid.
     */
    protected function parseSize(string $size): int
    {
        return ByteSize::parse($size) ?? throw new ValidationException('Invalid size.');
    }
}
