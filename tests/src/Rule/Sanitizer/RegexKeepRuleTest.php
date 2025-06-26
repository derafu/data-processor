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
use Derafu\DataProcessor\Rule\Sanitizer\RegexKeepRule;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(RegexKeepRule::class)]
final class RegexKeepRuleTest extends TestCase
{
    private RegexKeepRule $rule;

    protected function setUp(): void
    {
        $this->rule = new RegexKeepRule();
    }

    #[DataProvider('regexKeepDataProvider')]
    public function testRegexKeep(string $input, array $parameters, string $expected): void
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

    public static function regexKeepDataProvider(): array
    {
        return [
            'keep_numbers' => [
                'abc123def456',
                ['/\d+/'],
                '123456',
            ],
            'keep_words' => [
                'hello123world456',
                ['/[a-z]+/'],
                'helloworld',
            ],
            'keep_emails' => [
                'Contact us at: info@example.com or support@example.com',
                ['/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/'],
                'info@example.comsupport@example.com',
            ],
            'keep_multiple_patterns' => [
                'ID: ABC-123, Date: 2024-01-15',
                ['/[A-Z]+-\d+/'],
                'ABC-123',
            ],
            'no_matches' => [
                'hello world',
                ['/\d+/'],
                '',
            ],
        ];
    }
}
