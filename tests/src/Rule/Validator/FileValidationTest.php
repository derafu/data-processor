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
use Derafu\DataProcessor\Rule\Validator\File\AbstractFileRule;
use Derafu\DataProcessor\Rule\Validator\File\FileRule;
use Derafu\DataProcessor\Rule\Validator\File\ImageRule;
use Derafu\DataProcessor\Rule\Validator\File\MimeTypeRule;
use LogicException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\UploadedFileInterface;

#[CoversClass(AbstractFileRule::class)]
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

    private function createTempFile(string $name, string $content = 'x'): string
    {
        $path = $this->tempDir . '/' . $name;
        file_put_contents($path, $content);
        $this->tempFiles[] = $path;
        return $path;
    }

    // -------------------------------------------------------------------------
    // Tests for $_FILES simple format (single file).
    // -------------------------------------------------------------------------

    #[DataProvider('fileValidationProvider')]
    public function testFileValidation(array $file, array $rules, bool $shouldPass): void
    {
        $tempPath = $this->createTempFile(basename($file['tmp_name']));
        $file['tmp_name'] = $tempPath;

        $rule = match($rules[0]) {
            'image' => new ImageRule(),
            'file' => new FileRule(),
            'mimetype' => new MimeTypeRule(),
            default => throw new LogicException('Rule ' . $rules[0] . ' does not exist.')
        };

        if (!$shouldPass) {
            $this->expectException(ValidationException::class);
        }

        $rule->validate($file, array_slice($rules, 1));

        if ($shouldPass) {
            $this->assertTrue(true);
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
                    'error' => UPLOAD_ERR_OK,
                ],
                ['image', '2M'],
                true,
            ],
            'invalid_image_type' => [
                [
                    'tmp_name' => 'test.txt',
                    'type' => 'text/plain',
                    'size' => 1024,
                    'error' => UPLOAD_ERR_OK,
                ],
                ['image', '2M'],
                false,
            ],
            'valid_file_size' => [
                [
                    'tmp_name' => 'test.pdf',
                    'type' => 'application/pdf',
                    'size' => 1024 * 1024,
                    'error' => UPLOAD_ERR_OK,
                ],
                ['file', '2M'],
                true,
            ],
            'invalid_file_size' => [
                [
                    'tmp_name' => 'test.pdf',
                    'type' => 'application/pdf',
                    'size' => 3 * 1024 * 1024,
                    'error' => UPLOAD_ERR_OK,
                ],
                ['file', '2M'],
                false,
            ],
            'valid_mime_type' => [
                [
                    'tmp_name' => 'test.pdf',
                    'type' => 'application/pdf',
                    'error' => UPLOAD_ERR_OK,
                ],
                ['mimetype', 'application/pdf,application/x-pdf'],
                true,
            ],
            'invalid_mime_type' => [
                [
                    'tmp_name' => 'test.exe',
                    'type' => 'application/x-msdownload',
                    'error' => UPLOAD_ERR_OK,
                ],
                ['mimetype', 'application/pdf,application/x-pdf'],
                false,
            ],
        ];
    }

    // -------------------------------------------------------------------------
    // Tests for validateFileArray() via a concrete subclass.
    // -------------------------------------------------------------------------

    private function makeConcreteRule(): AbstractFileRule
    {
        return new class () extends AbstractFileRule {
            public function validate(mixed $value, array $parameters = []): void
            {
                $this->validateFileArray($value);
            }
        };
    }

    public function testFilesSimpleUploadError(): void
    {
        $rule = $this->makeConcreteRule();
        $this->expectException(ValidationException::class);
        $rule->validate([
            'tmp_name' => '/tmp/nonexistent.tmp',
            'error' => UPLOAD_ERR_INI_SIZE,
        ]);
    }

    public function testFilesSimpleFileNotExist(): void
    {
        $rule = $this->makeConcreteRule();
        $this->expectException(ValidationException::class);
        $rule->validate([
            'tmp_name' => '/tmp/this_file_does_not_exist_' . uniqid() . '.tmp',
            'error' => UPLOAD_ERR_OK,
        ]);
    }

    public function testFilesSimpleValid(): void
    {
        $rule = $this->makeConcreteRule();
        $path = $this->createTempFile('valid.txt');
        $rule->validate(['tmp_name' => $path, 'error' => UPLOAD_ERR_OK]);
        $this->assertTrue(true);
    }

    public function testFilesNativeMultipleValid(): void
    {
        $rule = $this->makeConcreteRule();
        $path1 = $this->createTempFile('multi1.txt');
        $path2 = $this->createTempFile('multi2.txt');
        $rule->validate([
            'tmp_name' => [$path1, $path2],
            'error' => [UPLOAD_ERR_OK, UPLOAD_ERR_OK],
        ]);
        $this->assertTrue(true);
    }

    public function testFilesNativeMultipleUploadError(): void
    {
        $rule = $this->makeConcreteRule();
        $path1 = $this->createTempFile('multi1.txt');
        $this->expectException(ValidationException::class);
        $rule->validate([
            'tmp_name' => [$path1, '/tmp/nonexistent.tmp'],
            'error' => [UPLOAD_ERR_OK, UPLOAD_ERR_INI_SIZE],
        ]);
    }

    public function testFilesNativeMultipleFileNotExist(): void
    {
        $rule = $this->makeConcreteRule();
        $path1 = $this->createTempFile('multi1.txt');
        $this->expectException(ValidationException::class);
        $rule->validate([
            'tmp_name' => [$path1, '/tmp/this_file_does_not_exist_' . uniqid() . '.tmp'],
            'error' => [UPLOAD_ERR_OK, UPLOAD_ERR_OK],
        ]);
    }

    public function testNormalizedMultipleValid(): void
    {
        $rule = $this->makeConcreteRule();
        $path1 = $this->createTempFile('norm1.txt');
        $path2 = $this->createTempFile('norm2.txt');
        $rule->validate([
            ['tmp_name' => $path1, 'error' => UPLOAD_ERR_OK],
            ['tmp_name' => $path2, 'error' => UPLOAD_ERR_OK],
        ]);
        $this->assertTrue(true);
    }

    public function testNormalizedMultipleUploadError(): void
    {
        $rule = $this->makeConcreteRule();
        $path1 = $this->createTempFile('norm1.txt');
        $this->expectException(ValidationException::class);
        $rule->validate([
            ['tmp_name' => $path1, 'error' => UPLOAD_ERR_OK],
            ['tmp_name' => '/tmp/nope.tmp', 'error' => UPLOAD_ERR_INI_SIZE],
        ]);
    }

    public function testInvalidFormatNonArray(): void
    {
        $rule = $this->makeConcreteRule();
        $this->expectException(ValidationException::class);
        $rule->validate('not-an-array');
    }

    public function testInvalidFormatBadArray(): void
    {
        $rule = $this->makeConcreteRule();
        $this->expectException(ValidationException::class);
        $rule->validate(['foo' => 'bar']);
    }

    // -------------------------------------------------------------------------
    // Tests for PSR-7 UploadedFileInterface.
    // -------------------------------------------------------------------------

    public function testPsr7SingleFileValid(): void
    {
        $mock = $this->createMock(UploadedFileInterface::class);
        $mock->method('getError')->willReturn(UPLOAD_ERR_OK);

        $rule = $this->makeConcreteRule();
        $rule->validate($mock);
        $this->assertTrue(true);
    }

    public function testPsr7SingleFileWithError(): void
    {
        $mock = $this->createMock(UploadedFileInterface::class);
        $mock->method('getError')->willReturn(UPLOAD_ERR_INI_SIZE);

        $rule = $this->makeConcreteRule();
        $this->expectException(ValidationException::class);
        $rule->validate($mock);
    }

    public function testPsr7MultipleFilesValid(): void
    {
        $mock1 = $this->createMock(UploadedFileInterface::class);
        $mock1->method('getError')->willReturn(UPLOAD_ERR_OK);
        $mock2 = $this->createMock(UploadedFileInterface::class);
        $mock2->method('getError')->willReturn(UPLOAD_ERR_OK);

        $rule = $this->makeConcreteRule();
        $rule->validate([$mock1, $mock2]);
        $this->assertTrue(true);
    }

    public function testPsr7MultipleFilesWithError(): void
    {
        $mock1 = $this->createMock(UploadedFileInterface::class);
        $mock1->method('getError')->willReturn(UPLOAD_ERR_OK);
        $mock2 = $this->createMock(UploadedFileInterface::class);
        $mock2->method('getError')->willReturn(UPLOAD_ERR_INI_SIZE);

        $rule = $this->makeConcreteRule();
        $this->expectException(ValidationException::class);
        $rule->validate([$mock1, $mock2]);
    }

    // -------------------------------------------------------------------------
    // Tests for parseSize().
    // -------------------------------------------------------------------------

    public function testParseSizeInvalidUnit(): void
    {
        $rule = new FileRule();
        $path = $this->createTempFile('size.txt');
        $this->expectException(ValidationException::class);
        $rule->validate(
            ['tmp_name' => $path, 'error' => UPLOAD_ERR_OK, 'size' => 100],
            ['1X']
        );
    }
}
