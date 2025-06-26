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
            'null_bytes' => [
                "Hello\x00\x00World",
                'HelloWorld',
            ],
        ];
    }
}
