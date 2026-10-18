<?php

declare(strict_types=1);

/**
 * Derafu: Data Processor - Four-Phase Data Processing Library.
 *
 * Copyright (c) 2025 Esteban De La Fuente Rubio / Derafu <https://www.derafu.dev>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\TestsDataProcessor\ValueObject;

use Derafu\DataProcessor\ValueObject\FileInfo;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(FileInfo::class)]
final class FileInfoTest extends TestCase
{
    public function testItKeepsWhatItWasGiven(): void
    {
        $file = new FileInfo(UPLOAD_ERR_OK, '/tmp/file.txt', 1024, 'text/plain');

        $this->assertSame(UPLOAD_ERR_OK, $file->error);
        $this->assertSame('/tmp/file.txt', $file->path);
        $this->assertSame(1024, $file->size);
        $this->assertSame('text/plain', $file->type);
    }

    public function testWhatIsNotKnownIsNull(): void
    {
        $file = new FileInfo(UPLOAD_ERR_NO_FILE, null, null, null);

        $this->assertSame(UPLOAD_ERR_NO_FILE, $file->error);
        $this->assertNull($file->path);
        $this->assertNull($file->size);
        $this->assertNull($file->type);
    }
}
