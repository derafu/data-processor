<?php

declare(strict_types=1);

/**
 * Derafu: Data Processor - Four-Phase Data Processing Library.
 *
 * Copyright (c) 2025 Esteban De La Fuente Rubio / Derafu <https://www.derafu.dev>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\DataProcessor\ValueObject;

/**
 * An uploaded file, whatever the shape it came in.
 *
 * A PSR-7 file, the array of a file of `$_FILES` and each file of the native
 * `$_FILES` of a field with `multiple` say the same things in different ways.
 * This is what they say, in one way.
 */
final readonly class FileInfo
{
    /**
     * @param int $error The upload error code (`UPLOAD_ERR_*`). `UPLOAD_ERR_OK`
     * when there was no error. A source that does not say it, or says it in a
     * way that is not a code, is `UPLOAD_ERR_NO_FILE`.
     * @param string|null $path The path of the file on the disk. `null` when
     * the file does not have one (PSR-7). An empty string when the source says
     * a path that is not a string, which is a path that does not exist.
     * @param int|null $size The size in bytes. `null` when it is not known.
     * @param string|null $type The MIME type that the client said the file has.
     * `null` when it has none.
     */
    public function __construct(
        public int $error,
        public ?string $path,
        public ?int $size,
        public ?string $type,
    ) {
    }
}
