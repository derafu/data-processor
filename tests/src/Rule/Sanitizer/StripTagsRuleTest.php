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
use Derafu\DataProcessor\Rule\Sanitizer\StripTagsRule;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(StripTagsRule::class)]
final class StripTagsRuleTest extends TestCase
{
    private StripTagsRule $rule;

    protected function setUp(): void
    {
        $this->rule = new StripTagsRule();
    }

    #[DataProvider('stripTagsDataProvider')]
    public function testStripTags(string $input, array $parameters, string $expected): void
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

    public static function stripTagsDataProvider(): array
    {
        return [
            'remove_all_tags' => [
                '<p>Hello <b>world</b></p>',
                [],
                'Hello world',
            ],
            'allow_p_tag' => [
                '<p>Hello <b>world</b></p>',
                ['<p>'],
                '<p>Hello world</p>',
            ],
            'allow_multiple_tags' => [
                '<p>Hello <b>world</b></p>',
                ['<p><b>'],
                '<p>Hello <b>world</b></p>',
            ],
            'remove_script_tag' => [
                '<p>Hello</p><script>alert("XSS")</script>',
                ['<p>'],
                '<p>Hello</p>alert("XSS")',
            ],
        ];
    }
}
