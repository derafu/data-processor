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

/**
 * Validates that the file is an image, by its content.
 *
 * The file is read as an image (`getimagesize()`), and what it is does not
 * depend on the type or the name that the client says. SVG is not allowed, as
 * it is code that can run in a browser: to allow it, use the rule `mimetype`
 * with `image/svg+xml`.
 */
final class ImageRule extends AbstractFileRule
{
    private const ALLOWED_TYPES = [
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',
    ];

    public function validate(mixed $value, array $parameters = []): void
    {
        $limits = $this->sizeLimitsOf($parameters);
        $files = $this->validateFileArray($value, $limits['min'], $limits['minText']);

        foreach ($files as $file) {
            // An empty file that has passed the minimum size is allowed, and
            // it has no content to be read as an image.
            if ($file->size === 0) {
                continue;
            }

            if (!in_array($this->imageTypeOf($file), self::ALLOWED_TYPES, true)) {
                throw new ValidationException('File must be an image.');
            }

            if ($limits['max'] !== null && $file->size !== null && $file->size > $limits['max']) {
                throw new ValidationException([
                    'Image size must not be larger than {max}.',
                    'max' => $limits['maxText'],
                ]);
            }
        }
    }

    /**
     * Reads a file as an image and gives its type.
     *
     * @param FileInfo $file The file.
     * @return string|null The MIME type of the image, or `null` if the file is
     * not an image that can be read.
     */
    private function imageTypeOf(FileInfo $file): ?string
    {
        // The notice of a file that is too short to be an image is not an
        // error to report: that file is just not an image.
        set_error_handler(static fn (): bool => true);

        try {
            if ($file->path !== null) {
                $info = getimagesize($file->path);
            } else {
                $content = $file->upload !== null ? $this->startOf($file->upload) : null;
                $info = $content !== null ? getimagesizefromstring($content) : false;
            }
        } finally {
            restore_error_handler();
        }

        return $info !== false ? $info['mime'] : null;
    }
}
