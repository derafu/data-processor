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
use Derafu\DataProcessor\Rule\Sanitizer\SubStringRule;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(SubStringRule::class)]
final class SubStringRuleTest extends TestCase
{
    private SubStringRule $rule;

    protected function setUp(): void
    {
        $this->rule = new SubStringRule();
    }

    #[DataProvider('substringDataProvider')]
    public function testSubstring(string $input, array $parameters, string $expected): void
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

    public static function substringDataProvider(): array
    {
        return [
            'length_only' => [
                'Hello World',
                ['3'],
                'Hel',
            ],
            'start_and_length' => [
                'Hello World',
                ['1', '5'],
                'ello ',
            ],
            'start_from_zero' => [
                'Hello World',
                ['0', '5'],
                'Hello',
            ],
            'shorter_than_length' => [
                'Hi',
                ['5'],
                'Hi',
            ],
            'unicode_string' => [
                'José María',
                ['4'],
                'José',
            ],
            'empty_string' => [
                '',
                ['5'],
                '',
            ],
        ];
    }
}
