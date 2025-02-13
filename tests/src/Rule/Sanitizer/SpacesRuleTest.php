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
use Derafu\DataProcessor\Rule\Sanitizer\SpacesRule;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(SpacesRule::class)]
final class SpacesRuleTest extends TestCase
{
    private SpacesRule $rule;

    protected function setUp(): void
    {
        $this->rule = new SpacesRule();
    }

    #[DataProvider('spacesDataProvider')]
    public function testSpaces(string $input, string $expected): void
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

    public static function spacesDataProvider(): array
    {
        return [
            'multiple_spaces' => [
                'hello    world',
                'hello world',
            ],
            'tabs_and_spaces' => [
                "hello\t\t  world",
                'hello world',
            ],
            'newlines_and_spaces' => [
                "hello\n\n  world",
                'hello world',
            ],
            'mixed_whitespace' => [
                "hello\n\t    world",
                'hello world',
            ],
        ];
    }
}
