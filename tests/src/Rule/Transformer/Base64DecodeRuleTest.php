<?php

declare(strict_types=1);

/**
 * Derafu: Derafu: Data Processor - Four-Phase Data Processing Library.
 *
 * Copyright (c) 2025 Esteban De La Fuente Rubio / Derafu <https://www.derafu.org>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\TestsDataProcessor\Rule\Transformer;

use Derafu\DataProcessor\Exception\TransformationException;
use Derafu\DataProcessor\Rule\Transformer\Base64\Base64DecodeRule;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(Base64DecodeRule::class)]
final class Base64DecodeRuleTest extends TestCase
{
    private Base64DecodeRule $rule;

    protected function setUp(): void
    {
        $this->rule = new Base64DecodeRule();
    }

    #[DataProvider('validBase64DecodeDataProvider')]
    public function testValidBase64Decode(mixed $input, mixed $expected): void
    {
        $result = $this->rule->transform($input);
        $this->assertSame($expected, $result);
    }

    public static function validBase64DecodeDataProvider(): array
    {
        return [
            'valid_base64' => ['SGVsbG8gV29ybGQ=', 'Hello World'],
            'empty_string' => ['', ''],
        ];
    }

    #[DataProvider('invalidBase64DecodeDataProvider')]
    public function testInvalidBase64Decode(mixed $input, mixed $expected): void
    {
        $this->expectException(TransformationException::class);

        $result = $this->rule->transform($input);
    }

    public static function invalidBase64DecodeDataProvider(): array
    {
        return [
            'invalid_base64' => ['Invalid Base64!', 'Invalid Base64!'],
            'non_string' => [123, 123],
        ];
    }
}
