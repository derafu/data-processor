<?php

declare(strict_types=1);

/**
 * Derafu: Data Processor - Four-Phase Data Processing Library.
 *
 * Copyright (c) 2025 Esteban De La Fuente Rubio / Derafu <https://www.derafu.dev>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\TestsDataProcessor\Rule\Transformer;

use Derafu\DataProcessor\Exception\TransformationException;
use Derafu\DataProcessor\Rule\Transformer\String\LowercaseRule;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(LowercaseRule::class)]
final class LowercaseRuleTest extends TestCase
{
    private LowercaseRule $rule;

    protected function setUp(): void
    {
        $this->rule = new LowercaseRule();
    }

    #[DataProvider('lowercaseDataProvider')]
    public function testLowercase(string $input, string $expected): void
    {
        $result = $this->rule->transform($input);
        $this->assertSame($expected, $result);
    }

    public function testNonStringValue(): void
    {
        $this->expectException(TransformationException::class);
        $value = 123;
        $result = $this->rule->transform($value);
    }

    public static function lowercaseDataProvider(): array
    {
        return [
            'mixed_case' => [
                'Hello World',
                'hello world',
            ],
            'uppercase' => [
                'HELLO WORLD',
                'hello world',
            ],
            'already_lowercase' => [
                'hello world',
                'hello world',
            ],
            'numbers_and_symbols' => [
                'Hello123!@#',
                'hello123!@#',
            ],
            'accented_chars' => [
                'JOSÉ MARÍA',
                'josé maría',
            ],
            'empty_string' => [
                '',
                '',
            ],
        ];
    }
}
