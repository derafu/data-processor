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
use Derafu\DataProcessor\Rule\Sanitizer\TrimRule;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(TrimRule::class)]
final class TrimRuleTest extends TestCase
{
    private TrimRule $rule;

    protected function setUp(): void
    {
        $this->rule = new TrimRule();
    }

    #[DataProvider('trimDataProvider')]
    public function testTrim(string $input, array $parameters, string $expected): void
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

    public static function trimDataProvider(): array
    {
        return [
            'basic_whitespace' => [
                ' hello world ',
                [],
                'hello world',
            ],
            'multiple_spaces' => [
                "  hello  \n\t  world  ",
                [],
                "hello  \n\t  world",
            ],
            'custom_chars' => [
                '--hello world--',
                ['-'],
                'hello world',
            ],
            'multiple_custom_chars' => [
                '.-hello world-.',
                ['.-'],
                'hello world',
            ],
        ];
    }
}
