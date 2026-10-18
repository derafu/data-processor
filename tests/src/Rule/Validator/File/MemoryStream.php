<?php

declare(strict_types=1);

/**
 * Derafu: Data Processor - Four-Phase Data Processing Library.
 *
 * Copyright (c) 2025 Esteban De La Fuente Rubio / Derafu <https://www.derafu.dev>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\TestsDataProcessor\Rule\Validator\File;

use Psr\Http\Message\StreamInterface;
use RuntimeException;

/**
 * A PSR-7 stream of a string, for the tests, that can fail in the ways that a
 * real one can.
 */
final class MemoryStream implements StreamInterface
{
    private int $position = 0;

    /**
     * @param string $content The content of the stream.
     * @param bool $seekable Whether the stream can be moved.
     * @param int|null $chunk The most bytes that a read gives, to be as a
     * stream that gives less than what it is asked.
     * @param bool $failsOnRewind Whether moving the stream fails.
     * @param bool $stalls Whether the stream never gives content and never
     * reaches its end, as a stream that is waiting for data.
     */
    public function __construct(
        private readonly string $content,
        private readonly bool $seekable = true,
        private readonly ?int $chunk = null,
        private readonly bool $failsOnRewind = false,
        private readonly bool $stalls = false,
    ) {
    }

    public function __toString(): string
    {
        return $this->content;
    }

    public function close(): void
    {
    }

    public function detach()
    {
        return null;
    }

    public function getSize(): int
    {
        return strlen($this->content);
    }

    public function tell(): int
    {
        return $this->position;
    }

    public function eof(): bool
    {
        return !$this->stalls && $this->position >= strlen($this->content);
    }

    public function isSeekable(): bool
    {
        return $this->seekable;
    }

    public function seek(int $offset, int $whence = SEEK_SET): void
    {
        $this->position = $offset;
    }

    public function rewind(): void
    {
        if ($this->failsOnRewind) {
            throw new RuntimeException('The stream can not be rewound.');
        }

        $this->position = 0;
    }

    public function isWritable(): bool
    {
        return false;
    }

    public function write(string $string): int
    {
        throw new RuntimeException('The stream can not be written.');
    }

    public function isReadable(): bool
    {
        return true;
    }

    public function read(int $length): string
    {
        if ($this->stalls) {
            return '';
        }

        $length = $this->chunk !== null ? min($length, $this->chunk) : $length;
        $data = substr($this->content, $this->position, $length);
        $this->position += strlen($data);

        return $data;
    }

    public function getContents(): string
    {
        return $this->read(strlen($this->content));
    }

    public function getMetadata(?string $key = null)
    {
        return $key === null ? [] : null;
    }
}
