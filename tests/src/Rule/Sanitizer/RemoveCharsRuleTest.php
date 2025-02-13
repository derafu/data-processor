<?php

declare(strict_types=1);

/**
 * Derafu: Derafu: Data Processor - Four-Phase Data Processing Library.
 *
 * Copyright (c) 2025 Esteban De La Fuente Rubio / Derafu <https://www.derafu.org>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\TestsDataProcessor\Rule\Sanitizer;

use Derafu\DataProcessor\Exception\SanitizationException;
use Derafu\DataProcessor\Rule\Sanitizer\RemoveCharsRule;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(RemoveCharsRule::class)]
final class RemoveCharsRuleTest extends TestCase
{
    private RemoveCharsRule $rule;

    protected function setUp(): void
    {
        $this->rule = new RemoveCharsRule();
    }

    #[DataProvider('removeCharsDataProvider')]
    public function testRemoveChars(string $input, array $parameters, string $expected): void
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

    public static function removeCharsDataProvider(): array
    {
        return [
            'single_char' => [
                'hello@world',
                ['@'],
                'helloworld',
            ],
            'multiple_chars' => [
                'hello@#$world',
                ['@#$'],
                'helloworld',
            ],
            'repeated_chars' => [
                'hello@@@@world',
                ['@'],
                'helloworld',
            ],
        ];
    }
}
