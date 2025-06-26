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
use Derafu\DataProcessor\Rule\Sanitizer\RegexRemoveRule;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(RegexRemoveRule::class)]
final class RegexRemoveRuleTest extends TestCase
{
    private RegexRemoveRule $rule;

    protected function setUp(): void
    {
        $this->rule = new RegexRemoveRule();
    }

    #[DataProvider('regexRemoveDataProvider')]
    public function testRegexRemove(string $input, array $parameters, string $expected): void
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

    public static function regexRemoveDataProvider(): array
    {
        return [
            'remove_numbers' => [
                'abc123def456',
                ['/\d+/'],
                'abcdef',
            ],
            'remove_spaces' => [
                'hello   world',
                ['/\s+/'],
                'helloworld',
            ],
            'remove_html_comments' => [
                'Hello <!-- comment --> World',
                ['/<!--.*?-->/s'],
                'Hello  World',
            ],
            'remove_between_tags' => [
                '[remove]This[/remove] but keep this',
                ['/\[remove\].*?\[\/remove\]/'],
                ' but keep this',
            ],
            'no_matches' => [
                'hello world',
                ['/\d+/'],
                'hello world',
            ],
        ];
    }
}
