<?php

declare(strict_types=1);

/**
 * Derafu: Data Processor - Four-Phase Data Processing Library.
 *
 * Copyright (c) 2025 Esteban De La Fuente Rubio / Derafu <https://www.derafu.org>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\TestsDataProcessor\Rule\Sanitizer;

use Derafu\DataProcessor\Exception\SanitizationException;
use Derafu\DataProcessor\Rule\Sanitizer\HtmlSpecialCharsRule;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(HtmlSpecialCharsRule::class)]
final class HtmlSpecialCharsRuleTest extends TestCase
{
    private HtmlSpecialCharsRule $rule;

    protected function setUp(): void
    {
        $this->rule = new HtmlSpecialCharsRule();
    }

    #[DataProvider('htmlSpecialCharsDataProvider')]
    public function testHtmlSpecialChars(string $input, string $expected): void
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

    public static function htmlSpecialCharsDataProvider(): array
    {
        return [
            'html_tags' => [
                '<p>Hello</p>',
                '&lt;p&gt;Hello&lt;/p&gt;',
            ],
            'special_chars' => [
                '"Hello" & \'World\'',
                '&quot;Hello&quot; &amp; &apos;World&apos;',
            ],
            'mixed_content' => [
                '<script>alert("XSS");</script>',
                '&lt;script&gt;alert(&quot;XSS&quot;);&lt;/script&gt;',
            ],
            'no_special_chars' => [
                'Hello World',
                'Hello World',
            ],
            'empty_string' => [
                '',
                '',
            ],
        ];
    }
}
