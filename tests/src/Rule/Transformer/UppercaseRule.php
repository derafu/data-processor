<?php

declare(strict_types=1);

/**
 * Derafu: Data Processor - Four-Phase Data Processing Library.
 *
 * Copyright (c) 2025 Esteban De La Fuente Rubio / Derafu <https://www.derafu.org>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\TestsDataProcessor\Rule\Transformer;

use Derafu\DataProcessor\Rule\Transformer\String\UppercaseRule;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(UppercaseRule::class)]
final class UppercaseRuleTest extends TestCase
{
    private UppercaseRule $rule;

    protected function setUp(): void
    {
        $this->rule = new UppercaseRule();
    }

    #[DataProvider('uppercaseDataProvider')]
    public function testUppercase(string $input, string $expected): void
    {
        $result = $this->rule->transform($input);
        $this->assertSame($expected, $result);
    }

    public function testNonStringValue(): void
    {
        $value = 123;
        $result = $this->rule->transform($value);
        $this->assertSame($value, $result);
    }

    public static function uppercaseDataProvider(): array
    {
        return [
            'mixed_case' => [
                'Hello World',
                'HELLO WORLD',
            ],
            'lowercase' => [
                'hello world',
                'HELLO WORLD',
            ],
            'already_uppercase' => [
                'HELLO WORLD',
                'HELLO WORLD',
            ],
            'numbers_and_symbols' => [
                'hello123!@#',
                'HELLO123!@#',
            ],
            'accented_chars' => [
                'josé maría',
                'JOSÉ MARÍA',
            ],
            'empty_string' => [
                '',
                '',
            ],
        ];
    }
}
