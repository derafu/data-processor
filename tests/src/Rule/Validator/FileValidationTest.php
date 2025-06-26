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

use Derafu\DataProcessor\Exception\ValidationException;
use Derafu\DataProcessor\Rule\Validator\File\FileRule;
use Derafu\DataProcessor\Rule\Validator\File\ImageRule;
use Derafu\DataProcessor\Rule\Validator\File\MimeTypeRule;
use LogicException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(ImageRule::class)]
#[CoversClass(FileRule::class)]
#[CoversClass(MimeTypeRule::class)]
final class FileValidationTest extends TestCase
{
    private string $tempDir;

    private array $tempFiles = [];

    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir() . '/file_rules_test_' . uniqid();
        mkdir($this->tempDir);
    }

    protected function tearDown(): void
    {
        foreach ($this->tempFiles as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }
        rmdir($this->tempDir);
    }

    private function createTempFile(string $name, string $content = ''): string
    {
        $path = $this->tempDir . '/' . $name;
        file_put_contents($path, $content);
        $this->tempFiles[] = $path;
        return $path;
    }

    #[DataProvider('fileValidationProvider')]
    public function testFileValidation(array $file, array $rules, bool $shouldPass): void
    {
        // Crear archivo temporal real
        $tempPath = $this->createTempFile(basename($file['tmp_name']));
        $file['tmp_name'] = $tempPath;

        $rule = match($rules[0]) {
            'image' => new ImageRule(),
            'file' => new FileRule(),
            'mimetype' => new MimeTypeRule(),
            default => throw new LogicException('Rule ' . $rules[0] . 'does not exist.')
        };

        if (!$shouldPass) {
            $this->expectException(ValidationException::class);
        }

        $rule->validate($file, array_slice($rules, 1));

        if ($shouldPass) {
            $this->assertTrue(true); // Llegó aquí sin excepción.
        }
    }

    public static function fileValidationProvider(): array
    {
        return [
            'valid_image' => [
                [
                    'tmp_name' => 'test.jpg',
                    'type' => 'image/jpeg',
                    'size' => 1024 * 1024,
                ],
                ['image', '2M'],
                true,
            ],
            'invalid_image_type' => [
                [
                    'tmp_name' => 'test.txt',
                    'type' => 'text/plain',
                    'size' => 1024,
                ],
                ['image', '2M'],
                false,
            ],
            'valid_file_size' => [
                [
                    'tmp_name' => 'test.pdf',
                    'type' => 'application/pdf',
                    'size' => 1024 * 1024,
                ],
                ['file', '2M'],
                true,
            ],
            'invalid_file_size' => [
                [
                    'tmp_name' => 'test.pdf',
                    'type' => 'application/pdf',
                    'size' => 3 * 1024 * 1024,
                ],
                ['file', '2M'],
                false,
            ],
            'valid_mime_type' => [
                [
                    'tmp_name' => 'test.pdf',
                    'type' => 'application/pdf',
                ],
                ['mimetype', 'application/pdf,application/x-pdf'],
                true,
            ],
            'invalid_mime_type' => [
                [
                    'tmp_name' => 'test.exe',
                    'type' => 'application/x-msdownload',
                ],
                ['mimetype', 'application/pdf,application/x-pdf'],
                false,
            ],
        ];
    }
}
