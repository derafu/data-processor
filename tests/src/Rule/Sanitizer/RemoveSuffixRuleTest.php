<?php

declare(strict_types=1);

/**
 * Derafu: Data Processor - Four-Phase Data Processing Library.
 *
 * Copyright (c) 2025 Esteban De La Fuente Rubio / Derafu <https://www.derafu.dev>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\TestsDataProcessor\Rule\Sanitizer;

use Derafu\DataProcessor\Exception\SanitizationException;
use Derafu\DataProcessor\Rule\Sanitizer\RemoveSuffixRule;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(RemoveSuffixRule::class)]
final class RemoveSuffixRuleTest extends TestCase
{
    private RemoveSuffixRule $rule;

    protected function setUp(): void
    {
        $this->rule = new RemoveSuffixRule();
    }

    #[DataProvider('removeSuffixDataProvider')]
    public function testRemoveSuffix(string $input, array $parameters, string $expected): void
    {
        $result = $this->rule->sanitize($input, $parameters);
        $this->assertSame($expected, $result);
    }

    public function testNonStringValue(): void
    {
        $this->expectException(SanitizationException::class);
        $value = 123;
        $result = $this->rule->sanitize($value);
    }

    public static function removeSuffixDataProvider(): array
    {
        return [
            'single_suffix' => [
                'HelloSuffix',
                ['Suffix'],
                'Hello',
            ],
            'multiple_suffixes' => [
                'Example.jpg',
                ['.png', '.jpg', '.gif'],
                'Example',
            ],
            'no_matching_suffix' => [
                'Hello World',
                ['Suffix'],
                'Hello World',
            ],
            'empty_string' => [
                '',
                ['Suffix'],
                '',
            ],
            'suffix_equals_string' => [
                'suffix',
                ['suffix'],
                '',
            ],
            'case_sensitive' => [
                'HelloSUFFIX',
                ['suffix'],
                'HelloSUFFIX',
            ],
        ];
    }
}
