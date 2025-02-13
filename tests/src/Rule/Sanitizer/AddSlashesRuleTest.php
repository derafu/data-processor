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
use Derafu\DataProcessor\Rule\Sanitizer\AddSlashesRule;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(AddSlashesRule::class)]
final class AddSlashesRuleTest extends TestCase
{
    private AddSlashesRule $rule;

    protected function setUp(): void
    {
        $this->rule = new AddSlashesRule();
    }

    #[DataProvider('addSlashesDataProvider')]
    public function testAddSlashes(string $input, string $expected): void
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

    public static function addSlashesDataProvider(): array
    {
        return [
            'single_quotes' => [
                "O'Reilly",
                "O\'Reilly",
            ],
            'double_quotes' => [
                'He said "Hello"',
                'He said \"Hello\"',
            ],
            'backslashes' => [
                'C:\Windows',
                'C:\\\\Windows',
            ],
            'mixed_quotes' => [
                'O\'Reilly says "Hello"',
                'O\\\'Reilly says \"Hello\"',
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
