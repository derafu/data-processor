<?php

declare(strict_types=1);

/**
 * Derafu: Data Processor - Four-Phase Data Processing Library.
 *
 * Copyright (c) 2025 Esteban De La Fuente Rubio / Derafu <https://www.derafu.org>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\TestsDataProcessor;

use Derafu\DataProcessor\RuleParser;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(RuleParser::class)]
final class RuleParserTest extends TestCase
{
    private RuleParser $parser;

    protected function setUp(): void
    {
        $this->parser = new RuleParser();
    }

    #[DataProvider('ruleParsingProvider')]
    public function testRuleParsing(string|array $input, array $expected): void
    {
        $result = $this->parser->parse($input);
        $this->assertSame($expected, $result);
    }

    public static function ruleParsingProvider(): array
    {
        return [
            'string_simple_validation' => [
                'required|email',
                [
                    'validate' => ['required', 'email'],
                ],
            ],
            'string_with_params' => [
                'min_length:3|max_length:10|between:1,100',
                [
                    'validate' => ['min_length:3', 'max_length:10', 'between:1,100'],
                ],
            ],
            'string_with_transform' => [
                't(lowercase|trim) required|email',
                [
                    'transform' => ['lowercase', 'trim'],
                    'validate' => ['required', 'email'],
                ],
            ],
            'string_with_sanitize' => [
                's(trim|strip_tags) required',
                [
                    'sanitize' => ['trim', 'strip_tags'],
                    'validate' => ['required'],
                ],
            ],
            'string_with_cast' => [
                'c(string) required|email',
                [
                    'cast' => 'string',
                    'validate' => ['required', 'email'],
                ],
            ],
            'string_complex' => [
                't(lowercase|base64_encode) s(trim|strip_tags) c(string) required|email|min_length:3',
                [
                    'transform' => ['lowercase', 'base64_encode'],
                    'sanitize' => ['trim', 'strip_tags'],
                    'cast' => 'string',
                    'validate' => ['required', 'email', 'min_length:3'],
                ],
            ],
            'array_simple' => [
                ['validate' => 'required|email'],
                [
                    'validate' => ['required', 'email'],
                ],
            ],
            'array_multiple_types' => [
                [
                    'transform' => 'lowercase|trim',
                    'validate' => 'required|email',
                ],
                [
                    'transform' => ['lowercase', 'trim'],
                    'validate' => ['required', 'email'],
                ],
            ],
            'array_already_parsed' => [
                [
                    'transform' => ['lowercase', 'trim'],
                    'validate' => ['required', 'email'],
                ],
                [
                    'transform' => ['lowercase', 'trim'],
                    'validate' => ['required', 'email'],
                ],
            ],
            'array_with_cast' => [
                [
                    'cast' => 'string',
                    'validate' => 'required|email',
                ],
                [
                    'cast' => 'string',
                    'validate' => ['required', 'email'],
                ],
            ],
            'string_multiple_spaces' => [
                't(lowercase|trim)    s(strip_tags)   required|email',
                [
                    'transform' => ['lowercase', 'trim'],
                    'sanitize' => ['strip_tags'],
                    'validate' => ['required', 'email'],
                ],
            ],
            'string_empty_groups' => [
                't() required|email',
                [
                    'validate' => ['required', 'email'],
                ],
            ],
            'string_all_empty_groups' => [
                't() s() c() v() required|email',
                [
                    'validate' => ['required', 'email'],
                ],
            ],
            'string_empty_cast' => [
                'c() required|email',
                [
                    'validate' => ['required', 'email'],
                ],
            ],
            'string_empty_sanitize' => [
                's() required|email',
                [
                    'validate' => ['required', 'email'],
                ],
            ],
            'string_empty_validate_group' => [
                'v() required|email',
                [
                    'validate' => ['required', 'email'],
                ],
            ],
        ];
    }
}
