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

use Derafu\DataProcessor\Rule\Transformer\Json\JsonEncodeRule;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(JsonEncodeRule::class)]
final class JsonEncodeRuleTest extends TestCase
{
    private JsonEncodeRule $rule;

    protected function setUp(): void
    {
        $this->rule = new JsonEncodeRule();
    }

    #[DataProvider('jsonEncodeDataProvider')]
    public function testJsonEncode(mixed $input, array $parameters, mixed $expected): void
    {
        $result = $this->rule->transform($input, $parameters);
        $this->assertSame($expected, $result);
    }

    public static function jsonEncodeDataProvider(): array
    {
        return [
            'array' => [
                ['name' => 'John', 'age' => 30],
                [],
                '{"name":"John","age":30}',
            ],
            'array_pretty' => [
                ['name' => 'John', 'age' => 30],
                ['pretty'],
                "{\n    \"name\": \"John\",\n    \"age\": 30\n}",
            ],
            'string' => [
                'Already a string',
                [],
                '"Already a string"',
            ],
            'number' => [
                123,
                [],
                '123',
            ],
        ];
    }
}
