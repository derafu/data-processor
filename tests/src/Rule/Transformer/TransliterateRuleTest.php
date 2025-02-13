<?php

declare(strict_types=1);

/**
 * Derafu: Derafu: Data Processor - Four-Phase Data Processing Library.
 *
 * Copyright (c) 2025 Esteban De La Fuente Rubio / Derafu <https://www.derafu.org>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\TestsDataProcessor\Rule\Transformer;

use Derafu\DataProcessor\Exception\TransformationException;
use Derafu\DataProcessor\Rule\Transformer\String\TransliterateRule;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(TransliterateRule::class)]
final class TransliterateRuleTest extends TestCase
{
    private TransliterateRule $rule;

    protected function setUp(): void
    {
        $this->rule = new TransliterateRule();
    }

    #[DataProvider('transliterateDataProvider')]
    public function testTransliterate(mixed $input, array $parameters, mixed $expected): void
    {
        $result = $this->rule->transform($input, $parameters);
        $this->assertSame($expected, $result);
    }

    public static function transliterateDataProvider(): array
    {
        return [
            'default_rules' => [
                'José María',
                [],
                'Jose Maria',
            ],
            'custom_rules' => [
                'Hello World',
                ['Any-Upper'],
                'HELLO WORLD',
            ],
            'accented_chars' => [
                'áéíóúñ',
                [],
                'aeioun',
            ],
            'empty_string' => [
                '',
                [],
                '',
            ],
        ];
    }

    #[DataProvider('invalidDransliterateDataProvider')]
    public function testInvalidTransliterate(mixed $input, array $parameters): void
    {
        $this->expectException(TransformationException::class);
        $result = $this->rule->transform($input, $parameters);
    }

    public static function invalidDransliterateDataProvider(): array
    {
        return [
            'non_string' => [
                123,
                [],
            ],
        ];
    }
}
