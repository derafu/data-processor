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
use Derafu\DataProcessor\ValueObject\FileInfo;
use Derafu\File\Mime;
use Derafu\File\Path;

/**
 * Validates that the type of the file is one of the allowed.
 *
 * The type is detected by the content of the file, and it does not depend on
 * the type that the client says. A file of text can not be told apart by its
 * content (a CSV is detected as `text/plain`), so it is also allowed when its
 * name has the extension of an allowed type of text (`data.csv` for
 * `text/csv`).
 *
 * An allowed type can be a whole type with a wildcard in the subtype, as
 * `image/*`. Any other use of `*` is taken as text, so it never matches.
 *
 * An empty file is detected as `application/x-empty`, so it is only valid if
 * that type is one of the allowed.
 */
final class MimeTypeRule extends AbstractFileRule
{
    public function validate(mixed $value, array $parameters = []): void
    {
        $files = $this->validateFileArray($value);

        if (empty($parameters)) {
            throw new ValidationException('Allowed MIME types must be specified.');
        }

        $allowedTypes = array_map(
            static fn (string $type): string => strtolower(trim($type)),
            $parameters
        );
        foreach ($files as $file) {
            $type = $this->typeOf($file)
                ?? throw new ValidationException('File type could not be determined.');

            if (!$this->isAllowed($type, $file->name, $allowedTypes)) {
                throw new ValidationException([
                    'Invalid file type {type}. Allowed types: {allowed}.',
                    'type' => $type,
                    'allowed' => implode(', ', $allowedTypes),
                ]);
            }
        }
    }

    /**
     * Detects the type of a file by its content.
     *
     * @param FileInfo $file The file.
     * @return string|null The MIME type, or `null` if it can not be determined.
     */
    private function typeOf(FileInfo $file): ?string
    {
        if ($file->path !== null) {
            return Mime::fromFile($file->path);
        }

        $content = $file->upload !== null ? $this->startOf($file->upload) : null;

        return $content !== null ? Mime::fromContent($content) : null;
    }

    /**
     * Tells if the type that a file has is allowed.
     *
     * @param string $type The type detected by the content.
     * @param string|null $name The name that the client says the file has.
     * @param list<string> $allowedTypes The allowed types, in lower case.
     * @return bool `true` if the type is allowed, or if the content is text and
     * the extension of the name is one of an allowed type of text.
     */
    private function isAllowed(string $type, ?string $name, array $allowedTypes): bool
    {
        if (in_array($type, $allowedTypes, true)) {
            return true;
        }

        foreach ($allowedTypes as $allowedType) {
            if ($this->isWholeType($allowedType) && str_starts_with($type, substr($allowedType, 0, -1))) {
                return true;
            }
        }

        if ($type !== 'text/plain' || $name === null) {
            return false;
        }

        $typesOfTheExtension = Mime::typesOfExtension(Path::extension($name));
        foreach ($allowedTypes as $allowedType) {
            if (str_starts_with($allowedType, 'text/') && in_array($allowedType, $typesOfTheExtension, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Tells if an allowed type is a whole type, as `image/*`.
     *
     * Only the whole subtype can be the wildcard. A `*` in the type, or only
     * a part of the subtype (`image/jp*`), is not a whole type.
     *
     * @param string $allowedType The allowed type, in lower case.
     * @return bool
     */
    private function isWholeType(string $allowedType): bool
    {
        return preg_match('/^[^*\/]+\/\*$/', $allowedType) === 1;
    }
}
