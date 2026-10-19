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
use Derafu\DataProcessor\Rule\Sanitizer\RemoveNonPrintableRule;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(RemoveNonPrintableRule::class)]
final class RemoveNonPrintableRuleTest extends TestCase
{
    private RemoveNonPrintableRule $rule;

    protected function setUp(): void
    {
        $this->rule = new RemoveNonPrintableRule();
    }

    #[DataProvider('removeNonPrintableDataProvider')]
    public function testRemoveNonPrintable(string $input, string $expected): void
    {
        $result = $this->rule->sanitize($input);
        $this->assertSame($expected, $result);
    }

    public function testNonStringValue(): void
    {
        $this->expectException(SanitizationException::class);
        $value = 123;
        $result = $this->rule->sanitize($value);
    }

    /**
     * @return array<array-key, array<array-key, mixed>>
     */
    public static function removeNonPrintableDataProvider(): array
    {
        return [
            'control_chars' => [
                "Hello\x00World\x01",
                'HelloWorld',
            ],
            'tabs_and_newlines' => [
                "Hello\tWorld\n",
                "Hello\tWorld\n",
            ],
            'zero_width_space' => [
                "a\u{200B}b",
                'ab',
            ],
            'bidi_override' => [
                "file\u{202E}gpj.exe",
                'filegpj.exe',
            ],
            'other_format_characters' => [
                "a\u{00AD}b\u{2060}c\u{FEFF}d\u{200E}e",
                'abcde',
            ],
            'c1_control_characters' => [
                "a\u{0085}b\u{009F}c",
                'abc',
            ],
            'zero_width_joiners_are_kept' => [
                "\u{1F468}\u{200D}\u{1F469}\u{200D}\u{1F467}",
                "\u{1F468}\u{200D}\u{1F469}\u{200D}\u{1F467}",
            ],
            'zero_width_non_joiner_is_kept' => [
                "\u{0645}\u{200C}\u{06CC}",
                "\u{0645}\u{200C}\u{06CC}",
            ],
            'visible_unicode_is_kept' => [
                "Año \u{00A0}ñandú",
                "Año \u{00A0}ñandú",
            ],
            'null_bytes' => [
                "Hello\x00\x00World",
                'HelloWorld',
            ],
        ];
    }
}
