<?php

declare(strict_types=1);

/**
 * Derafu: Data Processor - Four-Phase Data Processing Library.
 *
 * Copyright (c) 2025 Esteban De La Fuente Rubio / Derafu <https://www.derafu.dev>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\TestsDataProcessor\Rule\Transformer;

use Derafu\DataProcessor\Exception\TransformationException;
use Derafu\DataProcessor\Rule\Transformer\Base64\Base64EncodeRule;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(Base64EncodeRule::class)]
final class Base64EncodeRuleTest extends TestCase
{
    private Base64EncodeRule $rule;

    protected function setUp(): void
    {
        $this->rule = new Base64EncodeRule();
    }

    #[DataProvider('validBase64EncodeDataProvider')]
    public function testValidBase64Encode(mixed $input, mixed $expected): void
    {
        $result = $this->rule->transform($input);
        $this->assertSame($expected, $result);
    }

    public static function validBase64EncodeDataProvider(): array
    {
        return [
            'simple_string' => ['Hello World', 'SGVsbG8gV29ybGQ='],
            'empty_string' => ['', ''],
            'with_special_chars' => ['Hello@World', 'SGVsbG9AV29ybGQ='],
        ];
    }

    #[DataProvider('invalidBase64EncodeDataProvider')]
    public function testInvalidBase64Encode(mixed $input, mixed $expected): void
    {
        $this->expectException(TransformationException::class);

        $result = $this->rule->transform($input);
    }

    public static function invalidBase64EncodeDataProvider(): array
    {
        return [
            'non_string' => [123, 123],
        ];
    }
}
