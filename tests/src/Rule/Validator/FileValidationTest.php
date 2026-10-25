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

use Derafu\DataProcessor\Abstract\Rule\Validator\File\AbstractFileRule;
use Derafu\DataProcessor\Exception\ValidationException;
use Derafu\DataProcessor\Processor;
use Derafu\DataProcessor\ProcessorFactory;
use Derafu\DataProcessor\Registrar\DefaultRuleRegistrar;
use Derafu\DataProcessor\Rule\Validator\File\FileRule;
use Derafu\DataProcessor\Rule\Validator\File\ImageRule;
use Derafu\DataProcessor\Rule\Validator\File\MimeTypeRule;
use Derafu\DataProcessor\RuleParser;
use Derafu\DataProcessor\RuleRegistry;
use Derafu\DataProcessor\RuleResolver;
use Derafu\DataProcessor\RuleSet;
use Derafu\DataProcessor\ValueObject\FileInfo;
use LogicException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use RuntimeException;

#[CoversClass(AbstractFileRule::class)]
#[CoversClass(ImageRule::class)]
#[CoversClass(FileRule::class)]
#[CoversClass(MimeTypeRule::class)]
#[UsesClass(FileInfo::class)]
#[UsesClass(Processor::class)]
#[UsesClass(ProcessorFactory::class)]
#[UsesClass(DefaultRuleRegistrar::class)]
#[UsesClass(RuleParser::class)]
#[UsesClass(RuleRegistry::class)]
#[UsesClass(RuleResolver::class)]
#[UsesClass(RuleSet::class)]
final class FileValidationTest extends TestCase
{
    private string $tempDir;

    /**
     * @var list<string>
     */
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

    /**
     * The content of a file of the fixtures: `image.jpeg`, `image.png`,
     * `document.pdf`, `text.txt` or `data.csv`.
     */
    private function fixture(string $name): string
    {
        $content = file_get_contents(dirname(__DIR__, 2) . '/../fixtures/files/' . $name);
        $this->assertIsString($content);

        return $content;
    }

    /**
     * Creates a temporary file with the content of a file of the fixtures.
     */
    private function createFixtureFile(string $fixture, ?string $name = null): string
    {
        return $this->createTempFile($name ?? $fixture, $this->fixture($fixture));
    }

    // -------------------------------------------------------------------------
    // Tests for $_FILES simple format (single file).
    // -------------------------------------------------------------------------

    /**
     * @param array{tmp_name: string, size?: int, error: int} $file
     * @param list<string> $rules
     */
    #[DataProvider('fileValidationProvider')]
    public function testFileValidation(string $fixture, array $file, array $rules, bool $shouldPass): void
    {
        $file['name'] = basename($file['tmp_name']);
        $file['tmp_name'] = $this->createFixtureFile($fixture, $file['name']);

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
            $this->addToAssertionCount(1);
        }
    }

    /**
     * @return array<array-key, array<array-key, mixed>>
     */
    public static function fileValidationProvider(): array
    {
        return [
            'valid_image' => [
                'image.jpeg',
                [
                    'tmp_name' => 'test.jpg',
                    'size' => 1024 * 1024,
                    'error' => UPLOAD_ERR_OK,
                ],
                ['image', '2M'],
                true,
            ],
            'invalid_image_type' => [
                'text.txt',
                [
                    'tmp_name' => 'test.txt',
                    'size' => 1024,
                    'error' => UPLOAD_ERR_OK,
                ],
                ['image', '2M'],
                false,
            ],
            'valid_file_size' => [
                'document.pdf',
                [
                    'tmp_name' => 'test.pdf',
                    'size' => 1024 * 1024,
                    'error' => UPLOAD_ERR_OK,
                ],
                ['file', '2M'],
                true,
            ],
            'invalid_file_size' => [
                'document.pdf',
                [
                    'tmp_name' => 'test.pdf',
                    'size' => 3 * 1024 * 1024,
                    'error' => UPLOAD_ERR_OK,
                ],
                ['file', '2M'],
                false,
            ],
            'valid_mime_type' => [
                'document.pdf',
                [
                    'tmp_name' => 'test.pdf',
                    'error' => UPLOAD_ERR_OK,
                ],
                ['mimetype', 'application/pdf', 'application/x-pdf'],
                true,
            ],
            'invalid_mime_type' => [
                'image.jpeg',
                [
                    'tmp_name' => 'test.pdf',
                    'error' => UPLOAD_ERR_OK,
                ],
                ['mimetype', 'application/pdf', 'application/x-pdf'],
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
        $this->addToAssertionCount(1);
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
        $this->addToAssertionCount(1);
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
        $this->addToAssertionCount(1);
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

    public function testListWithAnElementThatIsNotAFileIsInvalidFormat(): void
    {
        $path = $this->createTempFile('list.txt');

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Invalid file format.');

        $this->makeConcreteRule()->validate([
            ['tmp_name' => $path, 'error' => UPLOAD_ERR_OK],
            'not-a-file',
        ]);
    }

    public function testEmptyArrayThrowsInvalidFormat(): void
    {
        // The validator itself has no concept of "optional" — an empty array
        // is currently an invalid format. Skipping for optional fields is the
        // pipeline's responsibility (ValueInspector::isBlank).
        $rule = $this->makeConcreteRule();
        $this->expectException(ValidationException::class);
        $rule->validate([]);
    }

    // -------------------------------------------------------------------------
    // Tests for PSR-7 UploadedFileInterface.
    // -------------------------------------------------------------------------

    public function testPsr7SingleFileValid(): void
    {
        $mock = $this->createStub(UploadedFileInterface::class);
        $mock->method('getError')->willReturn(UPLOAD_ERR_OK);

        $rule = $this->makeConcreteRule();
        $rule->validate($mock);
        $this->addToAssertionCount(1);
    }

    public function testPsr7SingleFileWithError(): void
    {
        $mock = $this->createStub(UploadedFileInterface::class);
        $mock->method('getError')->willReturn(UPLOAD_ERR_INI_SIZE);

        $rule = $this->makeConcreteRule();
        $this->expectException(ValidationException::class);
        $rule->validate($mock);
    }

    public function testPsr7MultipleFilesValid(): void
    {
        $mock1 = $this->createStub(UploadedFileInterface::class);
        $mock1->method('getError')->willReturn(UPLOAD_ERR_OK);
        $mock2 = $this->createStub(UploadedFileInterface::class);
        $mock2->method('getError')->willReturn(UPLOAD_ERR_OK);

        $rule = $this->makeConcreteRule();
        $rule->validate([$mock1, $mock2]);
        $this->addToAssertionCount(1);
    }

    public function testPsr7MultipleFilesWithError(): void
    {
        $mock1 = $this->createStub(UploadedFileInterface::class);
        $mock1->method('getError')->willReturn(UPLOAD_ERR_OK);
        $mock2 = $this->createStub(UploadedFileInterface::class);
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
    // -------------------------------------------------------------------------
    // Tests for the size and the type of PSR-7 files.
    // -------------------------------------------------------------------------

    private function psr7File(
        ?int $size,
        string $content = 'x',
        int $error = UPLOAD_ERR_OK,
        ?string $name = null,
        ?StreamInterface $stream = null
    ): UploadedFileInterface {
        $file = $this->createStub(UploadedFileInterface::class);
        $file->method('getError')->willReturn($error);
        $file->method('getSize')->willReturn($size);
        $file->method('getClientFilename')->willReturn($name);
        $file->method('getStream')->willReturn($stream ?? new MemoryStream($content));

        return $file;
    }

    public function testPsr7FileWithinTheMaximumSize(): void
    {
        (new FileRule())->validate($this->psr7File(1024), ['1Ki']);
        (new FileRule())->validate($this->psr7File(null), ['1Ki']);

        $this->addToAssertionCount(2);
    }

    public function testPsr7FileLargerThanTheMaximumSize(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('File size must not be larger than 1Ki.');

        (new FileRule())->validate($this->psr7File(1025), ['1Ki']);
    }

    public function testEveryPsr7FileOfAListIsComparedWithTheMaximumSize(): void
    {
        $rule = new FileRule();
        $rule->validate([$this->psr7File(10), $this->psr7File(20)], ['1Ki']);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('File size must not be larger than 1Ki.');

        $rule->validate([$this->psr7File(10), $this->psr7File(2048)], ['1Ki']);
    }

    public function testPsr7ImageIsAccepted(): void
    {
        (new ImageRule())->validate($this->psr7File(1024, $this->fixture('image.png')), ['1M']);
        (new ImageRule())->validate([
            $this->psr7File(1, $this->fixture('image.png')),
            $this->psr7File(1, $this->fixture('image.jpeg')),
        ]);

        $this->addToAssertionCount(2);
    }

    public function testPsr7FileThatIsNotAnImageIsRejected(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('File must be an image.');

        (new ImageRule())->validate($this->psr7File(1024, $this->fixture('document.pdf')));
    }

    public function testPsr7ImageLargerThanTheMaximumSizeIsRejected(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Image size must not be larger than 1Ki.');

        (new ImageRule())->validate($this->psr7File(2048, $this->fixture('image.png')), ['1Ki']);
    }

    public function testPsr7FileWithAStreamThatCannotBeReadHasNoType(): void
    {
        $withoutStream = $this->createStub(UploadedFileInterface::class);
        $withoutStream->method('getError')->willReturn(UPLOAD_ERR_OK);
        $withoutStream->method('getSize')->willReturn(1);
        $withoutStream->method('getStream')->willThrowException(new RuntimeException('There is no stream.'));

        $files = [
            'a stream that does not exist' => $withoutStream,
            'a stream that cannot be moved' => $this->psr7File(1, $this->fixture('image.png'), stream: new MemoryStream($this->fixture('image.png'), seekable: false)),
            'a stream that fails when it is moved' => $this->psr7File(1, $this->fixture('image.png'), stream: new MemoryStream($this->fixture('image.png'), failsOnRewind: true)),
        ];

        foreach ($files as $case => $file) {
            try {
                (new ImageRule())->validate($file);
                $this->fail('The image of ' . $case . ' must be rejected.');
            } catch (ValidationException $e) {
                $this->assertSame('File must be an image.', $e->getMessage(), $case);
            }

            try {
                (new MimeTypeRule())->validate($file, ['image/png']);
                $this->fail('The type of ' . $case . ' must not be determined.');
            } catch (ValidationException $e) {
                $this->assertSame('File type could not be determined.', $e->getMessage(), $case);
            }
        }
    }

    public function testPsr7StreamIsLeftWhereItWasAfterBeingRead(): void
    {
        $stream = new MemoryStream($this->fixture('image.png'));
        $stream->seek(7);

        (new ImageRule())->validate($this->psr7File(1, stream: $stream));
        (new MimeTypeRule())->validate($this->psr7File(1, stream: $stream), ['image/png']);

        $this->assertSame(7, $stream->tell());
    }

    public function testPsr7StreamThatGivesLessThanWhatIsAskedIsReadUntilTheEnd(): void
    {
        $stream = new MemoryStream($this->fixture('image.png'), chunk: 8);

        (new ImageRule())->validate($this->psr7File(1, stream: $stream));
        (new MimeTypeRule())->validate($this->psr7File(1, stream: $stream), ['image/png']);

        $this->addToAssertionCount(2);
    }

    public function testPsr7StreamThatStopsGivingContentIsTakenAsItIs(): void
    {
        $stream = new MemoryStream($this->fixture('image.png'), stalls: true);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('File must be an image.');

        (new ImageRule())->validate($this->psr7File(1, stream: $stream));
    }

    public function testOnlyTheStartOfAPsr7StreamIsRead(): void
    {
        $stream = new MemoryStream($this->fixture('image.png') . str_repeat("\0", 2 * 1024 * 1024));

        (new MimeTypeRule())->validate($this->psr7File(1, stream: $stream), ['image/png']);

        $this->assertSame(0, $stream->tell());
    }

    public function testPsr7FileOfAllowedMimeType(): void
    {
        (new MimeTypeRule())->validate($this->psr7File(1, $this->fixture('document.pdf')), ['application/pdf', 'text/plain']);
        (new MimeTypeRule())->validate([
            $this->psr7File(1, $this->fixture('text.txt')),
            $this->psr7File(1, $this->fixture('document.pdf')),
        ], ['application/pdf', 'text/plain']);

        $this->addToAssertionCount(2);
    }

    public function testPsr7FileOfAnotherMimeType(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Invalid file type image/png. Allowed types: application/pdf, text/plain.');

        (new MimeTypeRule())->validate($this->psr7File(1, $this->fixture('image.png')), ['application/pdf', 'text/plain']);
    }

    public function testWholeTypeAllowsAnySubtype(): void
    {
        (new MimeTypeRule())->validate($this->psr7File(1, $this->fixture('image.png')), ['image/*']);
        (new MimeTypeRule())->validate($this->psr7File(1, $this->fixture('image.jpeg')), [' Image/* ']);
        (new MimeTypeRule())->validate($this->psr7File(1, $this->fixture('document.pdf')), ['image/*', 'application/*']);
        (new MimeTypeRule())->validate($this->psr7File(1, $this->fixture('text.txt')), ['text/*']);

        $this->addToAssertionCount(4);
    }

    public function testWholeTypeDoesNotAllowAnotherType(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Invalid file type application/pdf. Allowed types: image/*.');

        (new MimeTypeRule())->validate($this->psr7File(1, $this->fixture('document.pdf')), ['image/*']);
    }

    /**
     * @param string $pattern
     */
    #[DataProvider('wildcardThatIsNotAWholeTypeProvider')]
    public function testWildcardThatIsNotAWholeTypeIsTextAndNeverMatches(string $pattern): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Invalid file type image/png. Allowed types: ' . $pattern . '.');

        (new MimeTypeRule())->validate($this->psr7File(1, $this->fixture('image.png')), [$pattern]);
    }

    /**
     * @return array<string, array{string}>
     */
    public static function wildcardThatIsNotAWholeTypeProvider(): array
    {
        return [
            'any type' => ['*/*'],
            'any subtype of any type' => ['*/png'],
            'part of the subtype' => ['image/p*'],
            'part of the type' => ['im*/png'],
            'only the type' => ['image*'],
            'without type' => ['/*'],
            'with a type that has a bar' => ['image/png/*'],
        ];
    }

    public function testWholeTypeWorksThroughTheProcessor(): void
    {
        $file = $this->psr7File(1, $this->fixture('image.png'));

        $result = ProcessorFactory::create()->process($file, ['validate' => ['mimetype:image/*']]);

        $this->assertSame($file, $result);
    }

    public function testTheTypeRulesNeedTheAllowedTypesWhenTheFileIsPsr7(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Allowed MIME types must be specified.');

        (new MimeTypeRule())->validate($this->psr7File(1, $this->fixture('image.png')));
    }

    public function testPsr7TextIsAllowedByTheExtensionOfItsClientFilename(): void
    {
        $content = $this->fixture('data.csv');

        (new MimeTypeRule())->validate($this->psr7File(1, $content, name: 'data.csv'), ['text/csv']);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Invalid file type text/plain. Allowed types: text/csv.');

        (new MimeTypeRule())->validate($this->psr7File(1, $content, name: 'data.txt'), ['text/csv']);
    }

    public function testNativeMultipleFilesWithAnUploadErrorThatIsNotAnArray(): void
    {
        $path = $this->createTempFile('a.txt');

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('File at index 0 has upload error.');

        (new FileRule())->validate(['tmp_name' => [$path], 'error' => UPLOAD_ERR_OK]);
    }

    public function testNativeMultipleFilesWithATmpNameThatIsNotAString(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('File at index 0 does not exist.');

        (new FileRule())->validate(['tmp_name' => [123], 'error' => [UPLOAD_ERR_OK]]);
    }

    // -------------------------------------------------------------------------
    // Tests for the empty files and the size limits.
    // -------------------------------------------------------------------------

    /**
     * @return array{tmp_name: string, size: int, error: int}
     */
    private function emptyFile(): array
    {
        return [
            'tmp_name' => $this->createTempFile('empty.txt', ''),
            'size' => 0,
            'error' => UPLOAD_ERR_OK,
        ];
    }

    public function testEmptyFileIsRejectedByDefaultByTheSizeRules(): void
    {
        foreach ([new FileRule(), new ImageRule()] as $rule) {
            try {
                $rule->validate($this->emptyFile());
                $this->fail('An empty file must be rejected.');
            } catch (ValidationException $e) {
                $this->assertSame('File is empty.', $e->getMessage());
            }
        }
    }

    public function testEmptyFileIsRejectedByItsTypeByTheMimeTypeRule(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Invalid file type application/x-empty. Allowed types: image/png.');

        (new MimeTypeRule())->validate($this->emptyFile(), ['image/png']);
    }

    public function testEmptyFileIsAcceptedWithAMinimumSizeOfZero(): void
    {
        (new FileRule())->validate($this->emptyFile(), ['min_size=0']);
        (new ImageRule())->validate($this->emptyFile(), ['min_size=0']);

        $this->addToAssertionCount(2);
    }

    public function testEmptyFileIsAcceptedByTheMimeTypeRuleIfItsTypeIsAllowed(): void
    {
        (new MimeTypeRule())->validate($this->emptyFile(), ['image/png', 'application/x-empty']);

        $this->addToAssertionCount(1);
    }

    public function testAnEmptyFileThatIsAllowedDoesNotExemptTheOthersOfTheListFromTheirType(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('File must be an image.');

        (new ImageRule())->validate([
            $this->emptyFile(),
            ['tmp_name' => $this->createFixtureFile('text.txt'), 'error' => UPLOAD_ERR_OK, 'size' => 17],
        ], ['min_size=0']);
    }

    public function testAMinimumSizeOfZeroDoesNotMoveTheMaximumSize(): void
    {
        $rule = new FileRule();
        $rule->validate($this->emptyFile(), ['1Ki', 'min_size=0']);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('File size must not be larger than 1Ki.');

        $file = $this->emptyFile();
        $file['size'] = 2048;
        $rule->validate($file, ['min_size=0', '1Ki']);
    }

    public function testEmptyFileOfAListIsRejectedWithItsIndex(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('File at index 1 is empty.');

        (new FileRule())->validate([
            ['tmp_name' => $this->createTempFile('full.txt'), 'error' => UPLOAD_ERR_OK, 'size' => 1],
            $this->emptyFile(),
        ]);
    }

    public function testEmptyFileOfANativeMultipleIsRejectedWithItsIndex(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('File at index 1 is empty.');

        (new FileRule())->validate([
            'tmp_name' => [$this->createTempFile('full.txt'), $this->createTempFile('empty.txt', '')],
            'error' => [UPLOAD_ERR_OK, UPLOAD_ERR_OK],
            'size' => [1, 0],
        ]);
    }

    public function testListWithEmptyFilesIsAcceptedWithAMinimumSizeOfZero(): void
    {
        (new FileRule())->validate([$this->emptyFile(), $this->emptyFile()], ['min_size=0']);

        $this->addToAssertionCount(1);
    }

    public function testEmptyPsr7FileIsRejected(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('File is empty.');

        (new FileRule())->validate($this->psr7File(0));
    }

    public function testEmptyPsr7FileIsAcceptedByTheMimeTypeRuleIfItsTypeIsAllowed(): void
    {
        (new MimeTypeRule())->validate($this->psr7File(0, ''), ['image/png', 'application/x-empty']);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Invalid file type application/x-empty. Allowed types: image/png.');

        (new MimeTypeRule())->validate($this->psr7File(0, ''), ['image/png']);
    }

    public function testFileThatDoesNotSayItsSizeIsNotRejectedAsEmpty(): void
    {
        (new FileRule())->validate($this->psr7File(null));

        $this->addToAssertionCount(1);
    }

    public function testUploadErrorIsReportedBeforeTheFileBeingEmpty(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('File has upload error.');

        (new FileRule())->validate($this->psr7File(0, error: UPLOAD_ERR_PARTIAL));
    }

    // -------------------------------------------------------------------------
    // Tests for the minimum and the maximum size.
    // -------------------------------------------------------------------------

    public function testMaximumSizeAsANamedParameterIsTheSameAsThePositionalOne(): void
    {
        foreach ([new FileRule(), new ImageRule()] as $rule) {
            $content = $this->fixture('image.png');
            $rule->validate($this->psr7File(1024, $content), ['max_size=1Ki']);

            try {
                $rule->validate($this->psr7File(2048, $content), ['max_size=1Ki']);
                $this->fail('A file larger than the maximum size must be rejected.');
            } catch (ValidationException $e) {
                $this->assertStringEndsWith('size must not be larger than 1Ki.', $e->getMessage());
            }
        }
    }

    /**
     * @param list<string> $parameters
     */
    #[DataProvider('sizesWithoutUnitProvider')]
    public function testASizeWithoutUnitIsShownInBytes(int $size, array $parameters, string $message): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage($message);

        (new FileRule())->validate($this->psr7File($size), $parameters);
    }

    /**
     * @return array<string, array{int, list<string>, string}>
     */
    public static function sizesWithoutUnitProvider(): array
    {
        return [
            'maximum' => [3000, ['2048'], 'File size must not be larger than 2048 bytes.'],
            'maximum of one' => [2, ['max_size=1'], 'File size must not be larger than 1 byte.'],
            'minimum' => [50, ['min_size=100'], 'File size must not be smaller than 100 bytes.'],
        ];
    }

    public function testFileSmallerThanTheMinimumSizeIsRejected(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('File size must not be smaller than 1Ki.');

        (new FileRule())->validate($this->psr7File(512), ['min_size=1Ki']);
    }

    public function testImageSmallerThanTheMinimumSizeIsRejected(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('File size must not be smaller than 1Ki.');

        (new ImageRule())->validate($this->psr7File(512, $this->fixture('image.png')), ['min_size=1Ki']);
    }

    public function testEmptyFileIsRejectedAsEmptyIfThereIsAMinimumSizeLargerThanZero(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('File is empty.');

        (new FileRule())->validate($this->psr7File(0), ['min_size=1Ki']);
    }

    public function testEveryFileOfAListIsComparedWithTheMinimumSize(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('File size must not be smaller than 1Ki.');

        (new FileRule())->validate([$this->psr7File(2048), $this->psr7File(512)], ['min_size=1Ki']);
    }

    public function testFileBetweenTheMinimumAndTheMaximumSizeIsAccepted(): void
    {
        (new FileRule())->validate($this->psr7File(1500), ['max_size=2Ki', 'min_size=1Ki']);
        (new FileRule())->validate($this->psr7File(1500), ['min_size=1Ki', '2Ki']);

        $this->addToAssertionCount(2);
    }

    public function testMinimumSizeAsLargeAsTheMaximumSizeIsAccepted(): void
    {
        (new FileRule())->validate($this->psr7File(1024), ['max_size=1Ki', 'min_size=1Ki']);

        $this->addToAssertionCount(1);
    }

    public function testMinimumSizeLargerThanTheMaximumSizeIsRejected(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Minimum size must not be larger than the maximum size.');

        (new FileRule())->validate($this->psr7File(1024), ['max_size=1Ki', 'min_size=2Ki']);
    }

    public function testAMaximumSizeOfZeroIsAcceptedWithAMinimumSizeOfZero(): void
    {
        (new FileRule())->validate($this->psr7File(0), ['max_size=0', 'min_size=0']);

        $this->addToAssertionCount(1);
    }

    /**
     * @param list<string> $parameters
     */
    #[DataProvider('invalidParametersProvider')]
    public function testInvalidParametersAreRejected(array $parameters, string $message): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage($message);

        (new FileRule())->validate($this->psr7File(1024), $parameters);
    }

    /**
     * @return array<string, array{list<string>, string}>
     */
    public static function invalidParametersProvider(): array
    {
        return [
            'unknown name' => [['other=1'], 'Invalid parameter: other=1'],
            'repeated minimum' => [['min_size=1', 'min_size=2'], 'Invalid parameter: min_size=2'],
            'repeated maximum' => [['max_size=1Ki', 'max_size=2Ki'], 'Invalid parameter: max_size=2Ki'],
            'positional and named maximum' => [['1Ki', 'max_size=2Ki'], 'Invalid parameter: max_size=2Ki'],
            'two positional' => [['1Ki', '2Ki'], 'Invalid parameter: 2Ki'],
            'invalid minimum' => [['min_size=big'], 'Invalid size.'],
            'invalid maximum' => [['max_size=big'], 'Invalid size.'],
            'empty minimum' => [['min_size='], 'Invalid size.'],
        ];
    }

    // -------------------------------------------------------------------------
    // Tests for the files normalized in a list.
    // -------------------------------------------------------------------------

    public function testEveryFileOfANativeMultipleIsComparedWithTheMaximumSize(): void
    {
        $rule = new FileRule();
        $paths = [$this->createTempFile('a.txt'), $this->createTempFile('b.txt')];

        $rule->validate(['tmp_name' => $paths, 'error' => [UPLOAD_ERR_OK, UPLOAD_ERR_OK], 'size' => [10, 20]], ['1Ki']);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('File size must not be larger than 1Ki.');

        $rule->validate(['tmp_name' => $paths, 'error' => [UPLOAD_ERR_OK, UPLOAD_ERR_OK], 'size' => [10, 2048]], ['1Ki']);
    }

    public function testEveryFileOfANativeMultipleIsCheckedAgainstTheAllowedTypes(): void
    {
        $files = [
            'tmp_name' => [$this->createFixtureFile('image.png'), $this->createFixtureFile('document.pdf')],
            'error' => [UPLOAD_ERR_OK, UPLOAD_ERR_OK],
        ];

        (new MimeTypeRule())->validate($files, ['image/png', 'application/pdf']);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Invalid file type application/pdf. Allowed types: image/png.');

        (new MimeTypeRule())->validate($files, ['image/png']);
    }

    public function testEveryFileOfANativeMultipleMustBeAnImage(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('File must be an image.');

        (new ImageRule())->validate([
            'tmp_name' => [$this->createFixtureFile('image.png'), $this->createFixtureFile('document.pdf')],
            'error' => [UPLOAD_ERR_OK, UPLOAD_ERR_OK],
        ]);
    }

    public function testEveryFileOfANativeMultipleUsesItsOwnName(): void
    {
        $files = [
            'tmp_name' => [$this->createFixtureFile('data.csv', 'a.tmp'), $this->createFixtureFile('data.csv', 'b.tmp')],
            'error' => [UPLOAD_ERR_OK, UPLOAD_ERR_OK],
            'name' => ['a.csv', 'b.csv'],
        ];

        (new MimeTypeRule())->validate($files, ['text/csv']);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Invalid file type text/plain. Allowed types: text/csv.');

        $files['name'] = ['a.csv', 'b.txt'];
        (new MimeTypeRule())->validate($files, ['text/csv']);
    }

    public function testTheMessageOfAListHasTheIndexOfTheFileThatFailed(): void
    {
        $ok = ['tmp_name' => $this->createTempFile('ok.txt'), 'error' => UPLOAD_ERR_OK];
        $rule = new FileRule();

        $cases = [
            'File at index 1 has upload error.' => [$ok, ['tmp_name' => '/tmp/x.tmp', 'error' => UPLOAD_ERR_PARTIAL]],
            'File at index 1 does not exist.' => [$ok, ['tmp_name' => '/tmp/nope_' . uniqid(), 'error' => UPLOAD_ERR_OK]],
            'File at index 2 has upload error.' => [$ok, $ok, ['tmp_name' => '/tmp/x.tmp', 'error' => UPLOAD_ERR_PARTIAL]],
        ];

        foreach ($cases as $message => $files) {
            try {
                $rule->validate($files);
                $this->fail('A list with a file that is not valid must be rejected.');
            } catch (ValidationException $e) {
                $this->assertSame($message, $e->getMessage());
            }
        }
    }

    public function testTheMessageOfAListKeepsTheKeyThatTheFileHadInTheValue(): void
    {
        $ok = ['tmp_name' => $this->createTempFile('ok.txt'), 'error' => UPLOAD_ERR_OK];

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('File at index second has upload error.');

        (new FileRule())->validate([
            $ok,
            'second' => ['tmp_name' => '/tmp/x.tmp', 'error' => UPLOAD_ERR_PARTIAL],
        ]);
    }

    // -------------------------------------------------------------------------
    // Tests for the rule mimetype through the processor.
    // -------------------------------------------------------------------------

    /**
     * @return array{tmp_name: string, name: string, size: int, error: int}
     */
    private function fileOfFixture(string $fixture): array
    {
        return [
            'tmp_name' => $this->createFixtureFile($fixture, 'upload.tmp'),
            'name' => $fixture,
            'size' => 1,
            'error' => UPLOAD_ERR_OK,
        ];
    }

    public function testMimetypeWithSeveralTypesThroughTheProcessor(): void
    {
        $processor = ProcessorFactory::create();
        $file = $this->fileOfFixture('image.jpeg');

        $result = $processor->process($file, ['validate' => ['mimetype:image/png,image/jpeg']]);

        $this->assertSame($file, $result);
    }

    public function testMimetypeWithSeveralTypesRejectsAnotherTypeThroughTheProcessor(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Invalid file type application/pdf. Allowed types: image/png, image/jpeg.');

        ProcessorFactory::create()->process(
            $this->fileOfFixture('document.pdf'),
            ['validate' => ['mimetype:image/png,image/jpeg']]
        );
    }

    public function testMimetypeOfTextByTheExtensionThroughTheProcessor(): void
    {
        $file = $this->fileOfFixture('data.csv');

        $result = ProcessorFactory::create()->process($file, ['validate' => ['mimetype:text/csv']]);

        $this->assertSame($file, $result);
    }

    // -------------------------------------------------------------------------
    // Tests for the type that is detected by the content.
    // -------------------------------------------------------------------------

    public function testTheTypeThatTheClientSaysIsNotUsed(): void
    {
        $text = ['tmp_name' => $this->createFixtureFile('text.txt'), 'type' => 'image/png', 'error' => UPLOAD_ERR_OK];

        try {
            (new ImageRule())->validate($text);
            $this->fail('A text that says to be an image must be rejected.');
        } catch (ValidationException $e) {
            $this->assertSame('File must be an image.', $e->getMessage());
        }

        try {
            (new MimeTypeRule())->validate($text, ['image/png']);
            $this->fail('A text that says to be a PNG must be rejected.');
        } catch (ValidationException $e) {
            $this->assertSame('Invalid file type text/plain. Allowed types: image/png.', $e->getMessage());
        }

        $png = ['tmp_name' => $this->createFixtureFile('image.png', 'a.tmp'), 'type' => 'text/plain', 'error' => UPLOAD_ERR_OK];
        (new ImageRule())->validate($png);
        (new MimeTypeRule())->validate($png, ['image/png']);

        $this->addToAssertionCount(2);
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function imageFormatProvider(): array
    {
        return [
            'jpeg' => ['image.jpeg', ''],
            'png' => ['image.png', ''],
            'gif' => ['', 'R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7'],
            'webp' => ['', 'UklGRhoAAABXRUJQVlA4TA0AAAAvAAAAEAcQERGIiP4HAA=='],
        ];
    }

    #[DataProvider('imageFormatProvider')]
    public function testTheFormatsOfImageThatAreAllowed(string $fixture, string $base64): void
    {
        $content = $fixture !== '' ? $this->fixture($fixture) : (string) base64_decode($base64);

        (new ImageRule())->validate(['tmp_name' => $this->createTempFile('image.bin', $content), 'error' => UPLOAD_ERR_OK]);
        (new ImageRule())->validate($this->psr7File(strlen($content), $content));

        $this->addToAssertionCount(2);
    }

    /**
     * @return array<string, array{string}>
     */
    public static function notAnImageProvider(): array
    {
        return [
            'a pdf' => ['%PDF-1.4'],
            'a text' => ['hello'],
            'a text that is too short to be an image' => ['a'],
            'binary content' => ["\x00\x01\x02\x03\xff\xfe\xfd\xfc"],
            'an svg' => ['<svg xmlns="http://www.w3.org/2000/svg" width="1" height="1"/>'],
        ];
    }

    #[DataProvider('notAnImageProvider')]
    public function testWhatIsNotAnImageIsRejectedWhateverItsNameAndTypeSay(string $content): void
    {
        $file = [
            'tmp_name' => $this->createTempFile('image.png', $content),
            'name' => 'image.png',
            'type' => 'image/png',
            'size' => strlen($content),
            'error' => UPLOAD_ERR_OK,
        ];

        try {
            (new ImageRule())->validate($file);
            $this->fail('What is not an image must be rejected.');
        } catch (ValidationException $e) {
            $this->assertSame('File must be an image.', $e->getMessage());
        }

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('File must be an image.');

        (new ImageRule())->validate($this->psr7File(strlen($content), $content, name: 'image.png'));
    }

    public function testAllowedTypesDoNotDependOnCaseOrSpaces(): void
    {
        (new MimeTypeRule())->validate(
            ['tmp_name' => $this->createFixtureFile('image.png'), 'error' => UPLOAD_ERR_OK],
            [' Image/PNG ']
        );

        $this->addToAssertionCount(1);
    }

    public function testTextIsAllowedByTheExtensionOfItsName(): void
    {
        foreach (['data.csv', 'DATA.CSV', 'archive.tar.csv'] as $name) {
            (new MimeTypeRule())->validate(
                ['tmp_name' => $this->createFixtureFile('data.csv', 'upload.tmp'), 'name' => $name, 'error' => UPLOAD_ERR_OK],
                ['text/csv']
            );
        }

        $this->addToAssertionCount(3);
    }

    public function testTextIsRejectedWhenTheExtensionOfItsNameIsNotOneOfAnAllowedType(): void
    {
        $names = ['data.txt' => 'data.txt', 'without extension' => 'data', 'no name' => null];

        foreach ($names as $case => $name) {
            $file = ['tmp_name' => $this->createFixtureFile('data.csv', 'upload.tmp'), 'error' => UPLOAD_ERR_OK];
            if ($name !== null) {
                $file['name'] = $name;
            }

            try {
                (new MimeTypeRule())->validate($file, ['text/csv']);
                $this->fail('The text of "' . $case . '" must be rejected.');
            } catch (ValidationException $e) {
                $this->assertSame('Invalid file type text/plain. Allowed types: text/csv.', $e->getMessage(), $case);
            }
        }
    }

    public function testTheExtensionOfAnAllowedTypeThatIsNotTextDoesNotAllowText(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Invalid file type text/plain. Allowed types: application/pdf.');

        (new MimeTypeRule())->validate(
            ['tmp_name' => $this->createFixtureFile('text.txt'), 'name' => 'document.pdf', 'error' => UPLOAD_ERR_OK],
            ['application/pdf']
        );
    }

    public function testTheExtensionOfATypeOfTextDoesNotAllowAnotherKindOfContent(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Invalid file type image/png. Allowed types: text/csv.');

        (new MimeTypeRule())->validate(
            ['tmp_name' => $this->createFixtureFile('image.png'), 'name' => 'data.csv', 'error' => UPLOAD_ERR_OK],
            ['text/csv']
        );
    }
}
